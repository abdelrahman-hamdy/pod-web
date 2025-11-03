@extends('layouts.app')

@section('title', 'Edit Profile - People Of Data')

@section('content')

    <div class="max-w-4xl mx-auto px-6 py-8">
        <!-- Page Header -->
        <x-page-header 
            title="Edit Profile"
            description="Update your personal information and professional details" />

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" 
                 class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
                <span class="block sm:inline">{{ session('success') }}</span>
                <button @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <i class="ri-close-line text-green-500 hover:text-green-700"></i>
                </button>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="profile-edit-form">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profile Picture -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-3">Profile Picture</label>
                        <div class="flex items-center space-x-6">
                            <div class="relative w-20 h-20 flex-shrink-0">
                                <div id="original-avatar" class="w-full h-full">
                                    <x-avatar 
                                        :src="$user->avatar ?? null"
                                        :name="$user->name ?? 'User'"
                                        size="xl"
                                        class="w-full h-full"
                                        :color="$user->avatar_color ?? null" />
                                </div>
                                <div id="avatar-preview" class="hidden absolute inset-0 w-20 h-20 rounded-full overflow-hidden z-10">
                                    <img id="preview-img" src="" alt="Preview" class="w-full h-full object-cover rounded-full">
                                </div>
                            </div>
                            <div>
                                <input type="file" name="avatar" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden" id="avatar-upload">
                                <button 
                                    type="button"
                                    class="inline-flex items-center justify-center font-medium rounded-lg transition-colors border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 px-3 py-1.5 text-sm"
                                    onclick="document.getElementById('avatar-upload').click()">
                                    <i class="ri-upload-line mr-2"></i>
                                    Upload New Photo
                                </button>
                                <button type="button" id="remove-avatar" class="hidden ml-2 px-3 py-1 text-sm text-red-600 hover:text-red-700 border border-red-300 rounded-lg">
                                    Remove
                                </button>
                                <p class="text-sm text-slate-500 mt-2">JPG, PNG, WEBP up to 2MB</p>
                                @error('avatar')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- First Name -->
                    <x-forms.input 
                        name="first_name"
                        label="First Name"
                        value="{{ old('first_name', $user->first_name) }}"
                        required />

                    <!-- Last Name -->
                    <x-forms.input 
                        name="last_name"
                        label="Last Name"
                        value="{{ old('last_name', $user->last_name) }}"
                        required />

                    <!-- Email -->
                    <x-forms.input 
                        type="email"
                        name="email"
                        label="Email Address"
                        value="{{ old('email', $user->email) }}"
                        required />

                    <!-- Job Title -->
                    <x-forms.input 
                        name="title"
                        label="Job Title"
                        value="{{ old('title', $user->title) }}"
                        placeholder="e.g. Senior Data Scientist" />

                    <!-- Company -->
                    <x-forms.input 
                        name="company"
                        label="Company"
                        value="{{ old('company', $user->company) }}"
                        placeholder="e.g. Microsoft Egypt" />
                    
                    <!-- City -->
                    <x-forms.input 
                        name="city"
                        label="City"
                        value="{{ old('city', $user->city) }}"
                        placeholder="e.g. Cairo" />

                    <!-- Phone -->
                    <x-forms.input 
                        type="tel"
                        name="phone"
                        label="Phone Number"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="+20 xxx xxx xxxx" />

                    <!-- Country -->
                    <x-forms.input 
                        name="country"
                        label="Country"
                        value="{{ old('country', $user->country) }}"
                        placeholder="e.g. Egypt" />

                    <!-- Gender -->
                    @php
                        $genderOptions = collect(\App\Gender::cases())->map(function($case) {
                            return ['value' => $case->value, 'label' => $case->getLabel()];
                        })->toArray();
                    @endphp
                    <x-forms.select 
                        name="gender"
                        label="Gender"
                        :options="$genderOptions"
                        :value="$user->gender?->value" />

                    <!-- Birthday -->
                    <x-forms.input 
                        type="date"
                        name="birthday"
                        label="Birthday"
                        value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}" />
                </div>

                <!-- Bio -->
                <div class="mt-6">
                    <x-forms.textarea 
                        name="bio"
                        label="About Me"
                        rows="4"
                        placeholder="Tell us about yourself, your expertise, and your interests..."
                        value="{{ old('bio', $user->bio) }}" />
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Change Password</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Current Password -->
                    <div class="md:col-span-2">
                        <label for="current_password" class="block text-sm font-medium text-slate-700 mb-2">
                            Current Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password"
                                name="current_password"
                                id="current_password"
                                placeholder="Enter your current password"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-12 text-slate-900 placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors @error('current_password') border-red-300 @enderror"
                            />
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility('current_password')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i id="current_password-icon" class="ri-eye-line text-xl"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            New Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Enter new password"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-12 text-slate-900 placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors @error('password') border-red-300 @enderror"
                            />
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility('password')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i id="password-icon" class="ri-eye-line text-xl"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-slate-500 mt-1">Minimum 8 characters</p>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                placeholder="Confirm new password"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-12 text-slate-900 placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors @error('password_confirmation') border-red-300 @enderror"
                            />
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility('password_confirmation')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i id="password_confirmation-icon" class="ri-eye-line text-xl"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-sm text-slate-500 mt-4">
                    <i class="ri-information-line mr-1"></i>
                    Leave blank if you don't want to change your password
                </p>
            </div>

            <!-- Professional Information -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Professional Information</h2>
                
                @php
                    $experienceLevelOptions = collect(\App\ExperienceLevel::cases())->map(function($case) {
                        return ['value' => $case->value, 'label' => $case->getLabel()];
                    })->toArray();
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Experience Level -->
                    <x-forms.select
                        name="experience_level"
                        label="Experience Level"
                        :options="$experienceLevelOptions"
                        :value="$user->experience_level?->value"
                        :showPlaceholder="false" />

                    <!-- LinkedIn URL -->
                    <x-forms.input 
                        type="url"
                        name="linkedin_url"
                        label="LinkedIn Profile"
                        value="{{ old('linkedin_url', $user->linkedin_url) }}"
                        placeholder="https://linkedin.com/in/username" />

                    <!-- Website URL -->
                    <x-forms.input 
                        type="url"
                        name="website_url"
                        label="Website"
                        value="{{ old('website_url', $user->website_url) }}"
                        placeholder="https://yourwebsite.com" />

                    <!-- GitHub URL -->
                    <x-forms.input 
                        type="url"
                        name="github_url"
                        label="GitHub Profile"
                        value="{{ old('github_url', $user->github_url) }}"
                        placeholder="https://github.com/username" />
                </div>

                <!-- Skills -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Skills</label>
                    <div class="space-y-3" x-data="{ skills: {{ json_encode(old('skills', $user->skills ?? [])) }}, newSkill: '' }">
                        <div class="flex space-x-2">
                            <input 
                                type="text"
                                name="skill_input"
                                placeholder="Add a skill (e.g. Python, Machine Learning)"
                                x-model="newSkill"
                                @keydown.enter.prevent="
                                    if (newSkill.trim() && !skills.includes(newSkill.trim())) {
                                        skills.push(newSkill.trim());
                                        newSkill = '';
                                    }
                                "
                                class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors" />
                            <button 
                                type="button"
                                @click="
                                    if (newSkill.trim() && !skills.includes(newSkill.trim())) {
                                        skills.push(newSkill.trim());
                                        newSkill = '';
                                    }
                                "
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="ri-add-line"></i> Add
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap gap-2" x-show="skills.length > 0">
                            <template x-for="(skill, index) in skills" :key="index">
                                <div class="inline-flex items-center bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm">
                                    <span x-text="skill"></span>
                                    <button type="button" 
                                            @click="skills.splice(index, 1)"
                                            class="ml-2 text-indigo-500 hover:text-indigo-700">
                                        <i class="ri-close-line text-sm"></i>
                                    </button>
                                    <input type="hidden" name="skills[]" :value="skill">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('profile.show') }}"
                   class="inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 focus:ring-indigo-500 px-4 py-2 text-sm">
                    <i class="ri-close-line mr-2"></i>
                    Cancel
                </a>
                
                <x-forms.button 
                    type="submit"
                    variant="primary"
                    icon="ri-save-line">
                    Save Changes
                </x-forms.button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Password visibility toggle function
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            } else {
                field.type = 'password';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const avatarUpload = document.getElementById('avatar-upload');
            const avatarPreview = document.getElementById('avatar-preview');
            const previewImg = document.getElementById('preview-img');
            const removeAvatarBtn = document.getElementById('remove-avatar');
            const originalAvatar = document.getElementById('original-avatar');

            // Avatar upload preview
            if (avatarUpload) {
                avatarUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file size (2MB max)
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size must be less than 2MB');
                            this.value = '';
                            return;
                        }

                        // Validate file type (restrict to supported types)
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Unsupported image format. Please upload a JPG, PNG, or WEBP file.');
                            this.value = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (previewImg) {
                                previewImg.src = e.target.result;
                            }
                            if (originalAvatar) {
                                originalAvatar.style.display = 'none';
                            }
                            if (avatarPreview) {
                                avatarPreview.classList.remove('hidden');
                            }
                            if (removeAvatarBtn) {
                                removeAvatarBtn.classList.remove('hidden');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Remove avatar
            if (removeAvatarBtn) {
                removeAvatarBtn.addEventListener('click', function() {
                    if (avatarUpload) {
                        avatarUpload.value = '';
                    }
                    if (originalAvatar) {
                        originalAvatar.style.display = 'block';
                    }
                    if (avatarPreview) {
                        avatarPreview.classList.add('hidden');
                    }
                    if (previewImg) {
                        previewImg.src = '';
                    }
                    removeAvatarBtn.classList.add('hidden');
                });
            }

            // Only handle the profile edit form - not other forms on the page
            const profileForm = document.getElementById('profile-edit-form');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    // Show loading state only
                    const submitButton = profileForm.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<i class="ri-loader-line animate-spin"></i> Saving...';
                    }
                    // Let the form submit naturally - validation happens on the server
                });
            }
        });
    </script>
    @endpush

@endsection