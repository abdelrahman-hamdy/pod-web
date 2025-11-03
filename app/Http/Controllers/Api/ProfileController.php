<?php

namespace App\Http\Controllers\Api;

use App\ExperienceLevel;
use App\Gender;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfileController extends BaseApiController
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['experiences', 'portfolios']);

        return $this->successResponse(new UserResource($user));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:'.implode(',', array_column(Gender::cases(), 'value'))],
            'bio' => ['nullable', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'experience_level' => ['nullable', 'in:'.implode(',', array_column(ExperienceLevel::cases(), 'value'))],
            'education' => ['nullable', 'array'],
            'education.*.institution' => ['required_with:education', 'string', 'max:255'],
            'education.*.degree' => ['required_with:education', 'string', 'max:255'],
            'education.*.field' => ['required_with:education', 'string', 'max:255'],
            'education.*.year' => ['required_with:education', 'integer', 'min:1900', 'max:'.date('Y')],
            'portfolio_links' => ['nullable', 'array'],
            'portfolio_links.*' => ['url', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'github_url' => ['nullable', 'url', 'max:500'],
            'twitter_url' => ['nullable', 'url', 'max:500'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'required_with:current_password', 'confirmed', 'min:8'],
        ];

        $validated = $request->validate($rules);

        $userData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'city', 'country', 'gender',
            'bio', 'title', 'company', 'birthday', 'skills', 'experience_level', 'education',
            'portfolio_links', 'linkedin_url', 'github_url', 'twitter_url', 'website_url',
        ]);

        // Convert empty strings to null
        $userData['gender'] = empty($userData['gender']) ? null : (string) $userData['gender'];
        $userData['experience_level'] = empty($userData['experience_level']) ? null : (string) $userData['experience_level'];

        // Handle password change
        if ($request->filled('current_password') && $request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return $this->validationErrorResponse([
                    'current_password' => ['Current password is incorrect.'],
                ]);
            }

            $userData['password'] = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = 'avatar_'.$user->id.'_'.time().'.jpg';

            try {
                $manager = new ImageManager(new Driver);
                $image = $manager->read($avatar->getPathname());
                $image = $image->cover(400, 400);
                $imageContent = $image->toJpeg(85)->toString();

                // Delete old avatar
                if ($user->avatar && Storage::disk('public')->exists('avatars/'.basename($user->avatar))) {
                    Storage::disk('public')->delete('avatars/'.basename($user->avatar));
                }

                // Store new avatar
                Storage::disk('public')->put('avatars/'.$filename, $imageContent);
                $userData['avatar'] = 'avatars/'.$filename;
            } catch (\Throwable $e) {
                return $this->errorResponse('Failed to process avatar image', null, 500);
            }
        }

        // Update name
        $userData['name'] = $userData['first_name'].' '.$userData['last_name'];

        // Update user
        $user->update($userData);

        return $this->successResponse(new UserResource($user->fresh()), 'Profile updated successfully');
    }

    /**
     * Get profile completion progress.
     */
    public function getProgress(Request $request): JsonResponse
    {
        $user = $request->user();
        $percentage = $user->getProfileCompletionPercentage();

        return $this->successResponse([
            'percentage' => $percentage,
            'is_complete' => $user->isProfileComplete(),
        ]);
    }

    /**
     * Complete profile.
     */
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        // Mark profile as completed and onboarding as seen
        $user->update([
            'profile_completed' => $user->isProfileComplete(),
            'profile_onboarding_seen' => true,
        ]);

        return $this->successResponse(new UserResource($user), 'Profile completed successfully');
    }

    /**
     * Upload avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $avatar = $request->file('avatar');
        $filename = 'avatar_'.$user->id.'_'.time().'.jpg';

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($avatar->getPathname());
            $image = $image->cover(400, 400);
            $imageContent = $image->toJpeg(85)->toString();

            // Delete old avatar
            if ($user->avatar && Storage::disk('public')->exists('avatars/'.basename($user->avatar))) {
                Storage::disk('public')->delete('avatars/'.basename($user->avatar));
            }

            // Store new avatar
            Storage::disk('public')->put('avatars/'.$filename, $imageContent);
            $user->update(['avatar' => 'avatars/'.$filename]);

            return $this->successResponse([
                'avatar_url' => asset('storage/avatars/'.$filename),
            ], 'Avatar uploaded successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to process avatar image', null, 500);
        }
    }

    /**
     * Get experience.
     */
    public function getExperience(Request $request, int $id): JsonResponse
    {
        $experience = $request->user()->experiences()->findOrFail($id);

        return $this->successResponse($experience);
    }

    /**
     * Store experience.
     */
    public function storeExperience(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        $experience = $request->user()->experiences()->create($validated);

        return $this->successResponse($experience, 'Experience added successfully', 201);
    }

    /**
     * Update experience.
     */
    public function updateExperience(Request $request, int $id): JsonResponse
    {
        $experience = $request->user()->experiences()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        $experience->update($validated);

        return $this->successResponse($experience->fresh(), 'Experience updated successfully');
    }

    /**
     * Delete experience.
     */
    public function deleteExperience(Request $request, int $id): JsonResponse
    {
        $experience = $request->user()->experiences()->findOrFail($id);
        $experience->delete();

        return $this->successResponse(null, 'Experience deleted successfully', 204);
    }

    /**
     * Get portfolio.
     */
    public function getPortfolio(Request $request, int $id): JsonResponse
    {
        $portfolio = $request->user()->portfolios()->findOrFail($id);

        return $this->successResponse($portfolio);
    }

    /**
     * Store portfolio.
     */
    public function storePortfolio(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'url' => ['required', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $portfolio = $request->user()->portfolios()->create($validated);

        return $this->successResponse($portfolio, 'Portfolio added successfully', 201);
    }

    /**
     * Update portfolio.
     */
    public function updatePortfolio(Request $request, int $id): JsonResponse
    {
        $portfolio = $request->user()->portfolios()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'url' => ['required', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $portfolio->update($validated);

        return $this->successResponse($portfolio->fresh(), 'Portfolio updated successfully');
    }

    /**
     * Delete portfolio.
     */
    public function deletePortfolio(Request $request, int $id): JsonResponse
    {
        $portfolio = $request->user()->portfolios()->findOrFail($id);
        $portfolio->delete();

        return $this->successResponse(null, 'Portfolio deleted successfully', 204);
    }
}
