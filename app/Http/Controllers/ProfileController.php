<?php

namespace App\Http\Controllers;

use App\ExperienceLevel;
use App\Gender;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function show(Request $request, $id = null)
    {
        $user = $id ? User::with(['experiences', 'portfolios'])->findOrFail($id) : User::with(['experiences', 'portfolios'])->findOrFail(Auth::id());

        // Support internal pagination for recent posts via query parameter (used by initial next page URL)
        if ($request->has('posts_page')) {
            return $this->userPosts($request, $user->id);
        }

        return view('profile.show', compact('user'));
    }

    /**
     * Return paginated recent posts HTML for a user (used by infinite scroll on profile page).
     */
    public function userPosts(Request $request, $id)
    {
        $perPage = (int) ($request->input('per_page', 5));
        $page = (int) ($request->input('page', $request->input('posts_page', 1)));

        $user = User::findOrFail($id);

        $paginator = $user->posts()
            ->with(['user', 'likes' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        // Render posts into HTML using the same card component used in home page
        $html = '';
        foreach ($paginator->items() as $post) {
            // Calculate if the current user liked this post
            $post->is_liked = $post->likes->isNotEmpty();
            $html .= view('components.post-card', ['post' => $post])->render();
        }

        return response()->json([
            'html' => $html,
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'has_more' => $paginator->hasMorePages(),
        ]);
    }

    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Debug logging
        \Log::info('Profile update request received', [
            'user_id' => $user->id,
            'has_avatar' => $request->hasFile('avatar'),
            'skills' => $request->input('skills'),
            'request_data' => $request->except(['password', 'password_confirmation', 'avatar']),
        ]);

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
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
        ];

        // Add password validation rules if user wants to change password
        // Only require if current_password is provided (main indicator of intent to change)
        if ($request->filled('current_password')) {
            $rules['current_password'] = ['required'];
            $rules['password'] = ['required', 'confirmed', 'min:8'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Profile update validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->except(['password', 'password_confirmation', 'current_password']),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        $userData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'city', 'country', 'gender',
            'bio', 'title', 'company', 'birthday', 'skills', 'experience_level', 'education',
            'portfolio_links', 'linkedin_url', 'github_url', 'twitter_url', 'website_url',
        ]);

        // Convert empty strings to null for optional fields
        if (empty($userData['gender'])) {
            $userData['gender'] = null;
        } else {
            // Ensure gender is a string value
            $userData['gender'] = (string) $userData['gender'];
        }

        if (empty($userData['experience_level'])) {
            $userData['experience_level'] = null;
        } else {
            // Ensure experience_level is a string value
            $userData['experience_level'] = (string) $userData['experience_level'];
        }

        \Log::info('User data before update', [
            'gender' => $userData['gender'] ?? null,
            'experience_level' => $userData['experience_level'] ?? null,
        ]);

        // Handle password change if provided
        if ($request->filled('current_password') && $request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $userData['password'] = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');

            \Log::info('Avatar file received', [
                'original_name' => $avatar->getClientOriginalName(),
                'mime' => $avatar->getClientMimeType(),
                'size' => $avatar->getSize(),
                'is_valid' => $avatar->isValid(),
            ]);

            // Always save as JPEG for consistency
            $filename = 'avatar_'.$user->id.'_'.time().'.jpg';

            try {
                $manager = new ImageManager(new Driver);

                // Read & crop-cover the image to a square, then resize to 400x400
                $image = $manager->read($avatar->getPathname());
                $image = $image->cover(400, 400);

                // Encode to JPEG (quality 85)
                $imageContent = $image->toJpeg(85)->toString();

                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists('avatars/'.basename($user->avatar))) {
                    Storage::disk('public')->delete('avatars/'.basename($user->avatar));
                }

                // Store the processed image in storage/app/public/avatars
                Storage::disk('public')->put('avatars/'.$filename, $imageContent);

                // Set the database path (Laravel will prepend /storage automatically via accessor)
                $userData['avatar'] = 'avatars/'.$filename;
            } catch (\Throwable $e) {
                \Log::error('Avatar upload failed', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);

                return back()->withErrors(['avatar' => 'Failed to process image. Please try again.'])->withInput();
            }
        }

        // Update the name field
        $userData['name'] = $userData['first_name'].' '.$userData['last_name'];

        // Update the user
        $user->update($userData);

        // Check if profile is now complete
        $user->update(['profile_completed' => $user->isProfileComplete()]);

        // Reload the user to get the casted values
        $user->refresh();

        $successMessage = 'Profile updated successfully!';
        if ($request->filled('current_password') && $request->filled('password')) {
            $successMessage = 'Profile and password updated successfully!';
        }

        \Log::info('Profile updated successfully', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($userData),
            'gender' => $user->gender?->value,
            'experience_level' => $user->experience_level?->value,
            'password_changed' => $request->filled('current_password') && $request->filled('password'),
        ]);

        $redirectUrl = $request->headers->get('referer') ?? route('profile.edit');

        return redirect($redirectUrl)->with('success', $successMessage);
    }

    /**
     * Show profile completion form.
     */
    public function showCompletionForm()
    {
        $user = Auth::user();

        // If user has already seen onboarding, redirect to home
        // Check if column exists to prevent errors during migration deployment
        if (Schema::hasColumn('users', 'profile_onboarding_seen') && $user->profile_onboarding_seen) {
            return redirect()->route('home');
        }

        return view('profile.complete', compact('user'));
    }

    /**
     * Handle profile completion.
     */
    public function completeProfile(Request $request)
    {
        \Log::info('Profile completion form submitted', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        $user = Auth::user();

        // Mark that user has seen the onboarding, regardless of whether they complete all fields
        // Check if column exists to prevent errors during migration deployment
        if (Schema::hasColumn('users', 'profile_onboarding_seen')) {
            $user->profile_onboarding_seen = true;
            $user->save();
        }

        $result = $this->update($request);

        \Log::info('Profile completion update result', [
            'is_redirect' => $result instanceof \Illuminate\Http\RedirectResponse,
            'result_type' => get_class($result),
        ]);

        // Check if update was successful (redirect without errors)
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            $session = $result->getSession();

            if ($session && $session->has('errors')) {
                // If there were validation errors, redirect to home anyway but with info message
                \Log::info('Profile completion had validation errors, redirecting to home anyway');

                return redirect()->route('home')->with('info', 'Welcome! Some profile fields need correction. You can update them from your profile settings.');
            }

            // Success - no validation errors
            \Log::info('Redirecting to home after successful profile completion');

            return redirect()->route('home')->with('success', 'Profile saved successfully! You can update it anytime from your profile settings.');
        }

        // Fallback - shouldn't reach here, but redirect to home anyway
        return redirect()->route('home')->with('info', 'Welcome! You can complete your profile from settings anytime.');
    }

    /**
     * Handle profile skip - mark onboarding as seen without filling details.
     */
    public function skipProfile()
    {
        $user = Auth::user();

        if (! $user) {
            \Log::error('No authenticated user in skipProfile - redirecting to login');

            return redirect()->route('login')->with('message', 'Please log in to continue.');
        }

        try {
            // Check if column exists to prevent errors during migration deployment
            if (Schema::hasColumn('users', 'profile_onboarding_seen')) {
                $user->profile_onboarding_seen = true;
            }
            $user->profile_completed = $user->isProfileComplete();
            $user->save();

            \Log::info('Profile onboarding skipped successfully', ['user_id' => $user->id]);

            return redirect()->route('home')->with('success', 'Welcome to People Of Data! You can complete your profile anytime from settings.');
        } catch (\Exception $e) {
            \Log::error('Error in skipProfile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('home')->with('error', 'Something went wrong, but welcome to People Of Data!');
        }
    }

    /**
     * Show profile completion progress.
     */
    public function getCompletionProgress()
    {
        $user = Auth::user();

        return response()->json([
            'percentage' => $user->getProfileCompletionPercentage(),
            'is_complete' => $user->isProfileComplete(),
            'missing_fields' => $this->getMissingFields($user),
        ]);
    }

    /**
     * Get missing required fields for profile completion.
     */
    private function getMissingFields($user)
    {
        $requiredFields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone' => 'Phone Number',
            'city' => 'City',
            'country' => 'Country',
            'gender' => 'Gender',
            'bio' => 'Bio',
            'skills' => 'Skills',
            'experience_level' => 'Experience Level',
        ];

        $missing = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($user->{$field})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Delete user account (soft delete).
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        // Soft delete by setting is_active to false
        $user->update(['is_active' => false]);

        Auth::logout();

        return redirect('/')->with('message', 'Your account has been deactivated successfully.');
    }

    /**
     * Store a new experience
     */
    public function storeExperience(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'company_url' => 'nullable|url|max:500',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'is_current' => 'nullable|in:1,0,true,false,"1","0","true","false"',
                'description' => 'nullable|string|max:1000',
            ]);

            $validated['user_id'] = Auth::id();

            // Convert is_current to boolean
            if (isset($validated['is_current'])) {
                $validated['is_current'] = filter_var($validated['is_current'], FILTER_VALIDATE_BOOLEAN);
            } else {
                $validated['is_current'] = false;
            }

            if ($validated['is_current']) {
                $validated['end_date'] = null;
            }

            \Log::info('Creating experience', ['data' => $validated]);

            \App\Models\Experience::create($validated);

            \Log::info('Experience created successfully');

            return response()->json(['success' => true, 'message' => 'Experience added successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Experience validation failed', ['errors' => $e->errors()]);

            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Experience creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Failed to create experience. Please try again.'], 500);
        }
    }

    /**
     * Get a specific experience
     */
    public function getExperience($id)
    {
        $experience = \App\Models\Experience::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'experience' => [
                'id' => $experience->id,
                'title' => $experience->title,
                'company' => $experience->company,
                'company_url' => $experience->company_url,
                'start_date' => $experience->start_date->format('Y-m'),
                'end_date' => $experience->end_date ? $experience->end_date->format('Y-m') : null,
                'is_current' => $experience->is_current,
                'description' => $experience->description,
            ],
        ]);
    }

    /**
     * Update an experience
     */
    public function updateExperience(Request $request, $id)
    {
        $experience = \App\Models\Experience::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'company_url' => 'nullable|url|max:500',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'nullable|in:1,0,true,false,"1","0","true","false"',
            'description' => 'nullable|string|max:1000',
        ]);

        // Convert is_current to boolean
        if (isset($validated['is_current'])) {
            $validated['is_current'] = filter_var($validated['is_current'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $validated['is_current'] = false;
        }

        if ($validated['is_current']) {
            $validated['end_date'] = null;
        }

        $experience->update($validated);

        return response()->json(['success' => true, 'message' => 'Experience updated successfully']);
    }

    /**
     * Delete an experience
     */
    public function deleteExperience($id)
    {
        $experience = \App\Models\Experience::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $experience->delete();

        return response()->json(['success' => true, 'message' => 'Experience deleted successfully']);
    }

    /**
     * Store a new portfolio
     */
    public function storePortfolio(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'url' => 'required|url|max:500',
            ]);

            $validated['user_id'] = Auth::id();

            \App\Models\Portfolio::create($validated);

            return response()->json(['success' => true, 'message' => 'Portfolio added successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Portfolio validation failed', ['errors' => $e->errors()]);

            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Portfolio creation failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to create portfolio. Please try again.'], 500);
        }
    }

    /**
     * Get a specific portfolio
     */
    public function getPortfolio($id)
    {
        $portfolio = \App\Models\Portfolio::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'portfolio' => [
                'id' => $portfolio->id,
                'title' => $portfolio->title,
                'description' => $portfolio->description,
                'url' => $portfolio->url,
            ],
        ]);
    }

    /**
     * Update a portfolio
     */
    public function updatePortfolio(Request $request, $id)
    {
        try {
            $portfolio = \App\Models\Portfolio::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'url' => 'required|url|max:500',
            ]);

            $portfolio->update($validated);

            return response()->json(['success' => true, 'message' => 'Portfolio updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Portfolio validation failed', ['errors' => $e->errors()]);

            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Portfolio update failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to update portfolio. Please try again.'], 500);
        }
    }

    /**
     * Delete a portfolio
     */
    public function deletePortfolio($id)
    {
        $portfolio = \App\Models\Portfolio::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $portfolio->delete();

        return response()->json(['success' => true, 'message' => 'Portfolio deleted successfully']);
    }
}
