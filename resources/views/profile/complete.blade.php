<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Your Profile - People Of Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <style>
        /* Add more padding for dropdown carets */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236366f1'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.75rem 0.75rem;
            padding-right: 3rem;
        }
        
        /* Ensure select options appear below the field */
        select {
            position: relative;
            z-index: 1;
        }
        
        /* Force dropdown to appear below */
        @media (max-width: 768px) {
            select {
                position: relative;
            }
        }
        
        /* Container for proper dropdown positioning */
        .select-wrapper {
            position: relative;
            z-index: auto;
        }
        
        select:focus {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50 min-h-screen flex items-center justify-center p-8">
        <div class="max-w-3xl w-full">
        <!-- Card -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8" style="overflow: visible;">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">
                    Complete Your Profile
                </h1>
                <p class="text-slate-600">
                    Add some information to help others get to know you better
                </p>
            </div>

            <!-- Avatar -->
            <div class="text-center mb-8">
                <div class="inline-block relative">
                    <div class="w-24 h-24 rounded-full ring-4 ring-white shadow-lg overflow-hidden">
                        <img id="avatar-preview" 
                             src="{{ auth()->user()->avatar ? auth()->user()->avatar : (auth()->user()->avatar_color ? '#' : '/default-avatar.jpg') }}" 
                             alt="{{ auth()->user()->name }}" 
                             class="w-24 h-24 rounded-full object-cover"
                             style="display: {{ auth()->user()->avatar ? 'block' : 'none' }};">
                        <div id="avatar-placeholder" class="w-24 h-24 rounded-full flex items-center justify-center {{ auth()->user()->avatar_color ?? 'bg-slate-200' }} text-slate-700 text-2xl font-semibold" style="display: {{ auth()->user()->avatar ? 'none' : 'flex' }};">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                    </div>
                    <label for="avatar" class="absolute bottom-0 right-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center hover:bg-indigo-700 transition-colors cursor-pointer shadow-lg ring-2 ring-white">
                        <i class="ri-camera-line text-sm"></i>
                    </label>
                </div>
            </div>

            <!-- Profile Completion Form -->
            <form method="POST" action="{{ route('profile.complete.submit') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- Hidden fields for required data -->
                <input type="hidden" name="first_name" value="{{ $user->first_name }}">
                <input type="hidden" name="last_name" value="{{ $user->last_name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                
                <!-- Hidden file input inside the form so it submits -->
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp" class="hidden">

                <!-- Personal Information -->
                <div class="border-b border-slate-200 pb-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Personal Information</h2>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                                Phone Number
                            </label>
                            <input 
                                type="text" 
                                name="phone" 
                                id="phone"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="+20 123 456 7890"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div x-data="{ open: false, value: '{{ old('gender', $user->gender) }}', options: [{value: '', label: 'Select Gender'}, {value: 'male', label: 'Male'}, {value: 'female', label: 'Female'}] }" class="relative">
                            <label for="gender" class="block text-sm font-medium text-slate-700 mb-2">
                                Gender
                            </label>
                            <div class="relative">
                                <input type="hidden" name="gender" x-model="value">
                                <button type="button" 
                                        @click="open = !open" 
                                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors text-left flex items-center justify-between">
                                    <span x-text="options.find(o => o.value === value)?.label || 'Select Gender'"></span>
                                    <i class="ri-arrow-down-s-line"></i>
                                </button>
                                <div x-show="open" 
                                     @click.outside="open = false"
                                     x-transition
                                     class="absolute z-50 mt-1 w-full bg-white border border-slate-300 rounded-lg shadow-lg max-h-48 overflow-auto">
                                    <template x-for="option in options" :key="option.value">
                                        <button type="button" 
                                                @click="value = option.value; open = false"
                                                :class="{'bg-indigo-50': value === option.value}"
                                                class="w-full text-left px-4 py-2 hover:bg-slate-50 transition-colors">
                                            <span x-text="option.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birthday -->
                        <div>
                            <label for="birthday" class="block text-sm font-medium text-slate-700 mb-2">
                                Birthday
                            </label>
                            <input 
                                type="date" 
                                name="birthday" 
                                id="birthday"
                                value="{{ old('birthday', $user->birthday) }}"
                                max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('birthday') border-red-300 @enderror">
                            @error('birthday')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="border-b border-slate-200 pb-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Location</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-slate-700 mb-2">
                                City
                            </label>
                            <input 
                                type="text" 
                                name="city" 
                                id="city"
                                value="{{ old('city', $user->city) }}"
                                placeholder="e.g. Cairo"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('city') border-red-300 @enderror">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-medium text-slate-700 mb-2">
                                Country
                            </label>
                            <input 
                                type="text" 
                                name="country" 
                                id="country"
                                value="{{ old('country', $user->country) }}"
                                placeholder="e.g. Egypt"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('country') border-red-300 @enderror">
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="border-b border-slate-200 pb-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Professional Information</h2>
                    
                    <!-- Job Title, Company, and Experience Level in one row -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <!-- Job Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-slate-700 mb-2">
                                Job Title
                            </label>
                            <input 
                                type="text" 
                                name="title" 
                                id="title"
                                value="{{ old('title', $user->title) }}"
                                placeholder="e.g. Data Scientist"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('title') border-red-300 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Company -->
                        <div>
                            <label for="company" class="block text-sm font-medium text-slate-700 mb-2">
                                Company
                            </label>
                            <input 
                                type="text" 
                                name="company" 
                                id="company"
                                value="{{ old('company', $user->company) }}"
                                placeholder="e.g. Tech Company"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('company') border-red-300 @enderror">
                            @error('company')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Experience Level -->
                        <div x-data="{ open: false, value: '{{ old('experience_level', $user->experience_level?->value ?? '') }}', options: [
                            {value: '', label: '{{ __('Select Experience Level') }}'},
                            @foreach(\App\ExperienceLevel::cases() as $level)
                            {value: '{{ $level->value }}', label: '{{ $level->getLabel() }}', selected: {{ old('experience_level', $user->experience_level?->value) === $level->value ? 'true' : 'false' }}},
                            @endforeach
                        ] }" class="relative">
                            <label for="experience_level" class="block text-sm font-medium text-slate-700 mb-2">
                                Experience Level
                            </label>
                            <div class="relative">
                                <input type="hidden" name="experience_level" x-model="value">
                                <button type="button" 
                                        @click="open = !open" 
                                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors text-left flex items-center justify-between">
                                    <span x-text="options.find(o => o.value === value)?.label || options[0].label"></span>
                                    <i class="ri-arrow-down-s-line"></i>
                                </button>
                                <div x-show="open" 
                                     @click.outside="open = false"
                                     x-transition
                                     class="absolute z-50 mt-1 w-full bg-white border border-slate-300 rounded-lg shadow-lg max-h-48 overflow-auto">
                                    <template x-for="option in options" :key="option.value">
                                        <button type="button" 
                                                @click="value = option.value; open = false"
                                                :class="{'bg-indigo-50': value === option.value}"
                                                class="w-full text-left px-4 py-2 hover:bg-slate-50 transition-colors">
                                            <span x-text="option.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            @error('experience_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Bio -->
                    <div class="mb-4">
                        <label for="bio" class="block text-sm font-medium text-slate-700 mb-2">
                            About You
                        </label>
                        <textarea 
                            name="bio" 
                            id="bio"
                            rows="4"
                            placeholder="Tell us about yourself..."
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('bio') border-red-300 @enderror">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Skills -->
                    <div>
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

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4">
                    <form method="POST" action="{{ route('profile.complete.skip') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-6 py-3 text-slate-600 hover:text-slate-800 font-medium transition-colors">
                            Skip for now
                        </button>
                    </form>
                    <button type="submit" id="submit-btn" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="submit-text">Complete Profile</span>
                        <span id="submit-loading" class="hidden">
                            <i class="ri-loader-4-line animate-spin mr-2"></i> Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Handle form submission with loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
        });

        // Avatar preview functionality
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    const placeholder = document.getElementById('avatar-placeholder');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
