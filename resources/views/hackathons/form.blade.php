@extends('layouts.app')

@section('title', $isEdit ? 'Edit Hackathon - ' . $hackathon->title : 'Create New Hackathon - People Of Data')

@section('content')
    
    <div class="max-w-4xl mx-auto px-6 py-8">
        <!-- Page Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">
                {{ $isEdit ? 'Edit Hackathon' : 'Create New Hackathon' }}
            </h1>
            <p class="text-slate-600">
                {{ $isEdit ? 'Update your hackathon information and settings' : 'Create an exciting coding competition for developers' }}
            </p>
        </div>

        <!-- Display Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="ri-check-line mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="ri-error-warning-line mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex">
                    <i class="ri-error-warning-line mr-2 mt-0.5"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form id="hackathon-form" 
              action="{{ $isEdit ? route('hackathons.update', $hackathon) : route('hackathons.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-8">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Basic Information</h2>
                <div class="space-y-6">
                    <!-- Hackathon Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-slate-700 mb-2">Hackathon Title *</label>
                        <input type="text" 
                               id="title"
                               name="title"
                               value="{{ old('title', $isEdit ? $hackathon->title : '') }}"
                               placeholder="e.g. AI for Social Good Hackathon"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-2">Description *</label>
                        <textarea id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Describe the hackathon objectives and what participants will work on..."
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  required>{{ old('description', $isEdit ? $hackathon->description : '') }}</textarea>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700 mb-2">Category</label>
                        <select id="category_id"
                                name="category_id"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $isEdit ? $hackathon->category_id : '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-slate-500 mt-1">Choose a category for this hackathon</p>
                    </div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Schedule</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-slate-700 mb-2">Start Date & Time *</label>
                        <input type="datetime-local" 
                               id="start_date"
                               name="start_date"
                               value="{{ old('start_date', $isEdit ? $hackathon->start_date->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-slate-700 mb-2">End Date & Time *</label>
                        <input type="datetime-local" 
                               id="end_date"
                               name="end_date"
                               value="{{ old('end_date', $isEdit ? $hackathon->end_date->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <div>
                        <label for="registration_deadline" class="block text-sm font-medium text-slate-700 mb-2">Registration Deadline *</label>
                        <input type="datetime-local" 
                               id="registration_deadline"
                               name="registration_deadline"
                               value="{{ old('registration_deadline', $isEdit ? $hackathon->registration_deadline->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>
                </div>
            </div>

            <!-- Location & Format -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Location & Format</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Format -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-4">Format *</label>
                        @php
                            $formatValue = old('format', $isEdit ? $hackathon->format->value : '');
                        @endphp
                        <div class="flex space-x-6">
                            <label class="flex items-center cursor-pointer" data-radio-group="format">
                                <input type="radio" 
                                       name="format" 
                                       value="online" 
                                       {{ $formatValue == 'online' ? 'checked' : '' }}
                                       class="sr-only format-radio" />
                                <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center mr-3 border-slate-300">
                                    <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden"></div>
                                </div>
                                <span class="text-sm text-slate-700">Online</span>
                            </label>
                            <label class="flex items-center cursor-pointer" data-radio-group="format">
                                <input type="radio" 
                                       name="format" 
                                       value="on-site" 
                                       {{ $formatValue == 'on-site' ? 'checked' : '' }}
                                       class="sr-only format-radio" />
                                <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center mr-3 border-slate-300">
                                    <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden"></div>
                                </div>
                                <span class="text-sm text-slate-700">On-Site</span>
                            </label>
                            <label class="flex items-center cursor-pointer" data-radio-group="format">
                                <input type="radio" 
                                       name="format" 
                                       value="hybrid" 
                                       {{ $formatValue == 'hybrid' ? 'checked' : '' }}
                                       class="sr-only format-radio" />
                                <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center mr-3 border-slate-300">
                                    <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden"></div>
                                </div>
                                <span class="text-sm text-slate-700">Hybrid</span>
                            </label>
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-slate-700 mb-2">
                            Location <span id="location-required" class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="location"
                               name="location"
                               value="{{ old('location', $isEdit ? ($hackathon->location ?? '') : '') }}"
                               placeholder="e.g., Cairo, Egypt or Online"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               />
                        <p class="mt-1 text-xs text-slate-500" id="location-help">Required for on-site and hybrid events. Optional for online events.</p>
                    </div>
                </div>
            </div>

            <!-- Cover Image -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Cover Image</h2>
                <div class="space-y-4">
                    @if($isEdit && $hackathon->banner_image)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Current Banner</label>
                            <img src="{{ uploaded_file($hackathon->banner_image) }}" alt="Current banner" class="w-full h-48 object-cover rounded-lg" />
                        </div>
                    @endif
                    
                    <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center">
                        <i class="ri-image-add-line text-2xl text-slate-400 mb-4"></i>
                        <p class="text-slate-600 mb-2">Click to upload or drag and drop</p>
                        <p class="text-sm text-slate-500">PNG, JPG up to 2MB</p>
                        <input type="file" 
                               name="banner_image" 
                               accept="image/*" 
                               class="hidden" 
                               id="cover-upload" />
                        <label for="cover-upload" class="cursor-pointer">
                            <div class="mt-4 inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50">
                                <i class="ri-upload-line mr-2"></i>
                                {{ ($isEdit && $hackathon->banner_image) ? 'Change Banner' : 'Choose Banner' }}
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Prize & Capacity -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Prize & Capacity</h2>
                <div class="space-y-6">
                    <div>
                        <label for="prize_pool" class="block text-sm font-medium text-slate-700 mb-2">Prize Pool (USD)</label>
                        <input type="number" 
                               id="prize_pool"
                               name="prize_pool"
                               value="{{ old('prize_pool', $isEdit ? $hackathon->prize_pool : '') }}"
                               placeholder="Enter total prize pool amount"
                               min="0"
                               step="100"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div>
                        <label for="max_participants" class="block text-sm font-medium text-slate-700 mb-2">Maximum Participants</label>
                        <input type="number" 
                               id="max_participants"
                               name="max_participants"
                               value="{{ old('max_participants', $isEdit ? $hackathon->max_participants : '') }}"
                               placeholder="Leave empty for unlimited"
                               min="1"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="min_team_size" class="block text-sm font-medium text-slate-700 mb-2">Minimum Team Size *</label>
                            <input type="number" 
                                   id="min_team_size"
                                   name="min_team_size"
                                   value="{{ old('min_team_size', $isEdit ? $hackathon->min_team_size : '2') }}"
                                   min="1"
                                   max="10"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   required />
                            <p class="text-sm text-slate-500 mt-1">Minimum number of team members</p>
                        </div>

                        <div>
                            <label for="max_team_size" class="block text-sm font-medium text-slate-700 mb-2">Maximum Team Size *</label>
                            <input type="number" 
                                   id="max_team_size"
                                   name="max_team_size"
                                   value="{{ old('max_team_size', $isEdit ? $hackathon->max_team_size : '6') }}"
                                   min="1"
                                   max="10"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   required />
                            <p class="text-sm text-slate-500 mt-1">Maximum number of team members</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technologies -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Technologies</h2>
                <div class="space-y-6">
                    <div>
                        <label for="skill_requirements" class="block text-sm font-medium text-slate-700 mb-2">Skill Level</label>
                        @php
                            $skillValue = old('skill_requirements', $isEdit ? ($hackathon->skill_requirements ? $hackathon->skill_requirements->value : '') : '');
                        @endphp
                        <select id="skill_requirements"
                                name="skill_requirements"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select skill level</option>
                            <option value="beginner" {{ $skillValue == 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ $skillValue == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ $skillValue == 'advanced' ? 'selected' : '' }}>Advanced</option>
                            <option value="all-levels" {{ $skillValue == 'all-levels' ? 'selected' : '' }}>All Levels</option>
                        </select>
                    </div>

                    <!-- Technologies & Tools -->
                    @php
                        // Handle old input or existing hackathon technologies
                        if (old('technologies') && is_array(old('technologies'))) {
                            $existingTechnologies = old('technologies');
                        } elseif ($isEdit && $hackathon->technologies) {
                            $existingTechnologies = $hackathon->technologies;
                        } else {
                            $existingTechnologies = [];
                        }
                    @endphp
                    <x-forms.skills-input 
                        name="technologies" 
                        label="Technologies & Tools" 
                        placeholder="Add a technology (e.g. React, Python, AWS, Docker)"
                        help="Add technologies and tools used in this hackathon"
                        :existingSkills="$existingTechnologies"
                        required="false" />
                </div>
            </div>

            <!-- Rules -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-6">Rules & Guidelines</h2>
                <div>
                    <label for="rules" class="block text-sm font-medium text-slate-700 mb-2">Rules</label>
                    <textarea id="rules"
                              name="rules"
                              rows="6"
                              placeholder="Enter hackathon rules, guidelines, and requirements..."
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('rules', $isEdit ? $hackathon->rules : '') }}</textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <a href="{{ $isEdit ? route('hackathons.show', $hackathon) : route('hackathons.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="ri-arrow-left-line mr-2"></i>
                    {{ $isEdit ? 'Back to Hackathon' : 'Back to Hackathons' }}
                </a>
                
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="{{ $isEdit ? 'ri-save-line' : 'ri-send-plane-line' }} mr-2"></i>
                        {{ $isEdit ? 'Update Hackathon' : 'Create Hackathon' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize radio button visual state
            function updateRadioButtonState(radio) {
                const container = radio.closest('label');
                const circle = container.querySelector('.w-5.h-5');
                const dot = container.querySelector('.w-2\\.5.h-2\\.5');
                
                if (radio.checked) {
                    circle.classList.remove('border-slate-300');
                    circle.classList.add('border-indigo-600');
                    dot.classList.remove('hidden');
                } else {
                    circle.classList.remove('border-indigo-600');
                    circle.classList.add('border-slate-300');
                    dot.classList.add('hidden');
                }
            }

            // Radio button functionality
            const radioGroups = document.querySelectorAll('input[type="radio"]');
            
            // Initialize state for all radio buttons
            radioGroups.forEach(radio => {
                updateRadioButtonState(radio);
            });
            
            radioGroups.forEach(radio => {
                radio.addEventListener('change', function() {
                    const groupName = this.name;
                    const groupRadios = document.querySelectorAll(`input[name="${groupName}"]`);
                    
                    groupRadios.forEach(r => {
                        updateRadioButtonState(r);
                    });
                });
            });

            // File upload preview
            const fileInput = document.getElementById('cover-upload');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const uploadArea = fileInput.closest('.border-dashed');
                            uploadArea.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-full h-48 object-cover rounded-lg mb-4" />
                                <button type="button" onclick="resetFileUpload()" class="text-sm text-red-600 hover:text-red-700">
                                    Remove Image
                                </button>
                            `;
                            // Re-add the original file input (hidden)
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'file';
                            hiddenInput.name = 'banner_image';
                            hiddenInput.accept = 'image/*';
                            hiddenInput.className = 'hidden';
                            hiddenInput.id = 'cover-upload-hidden';
                            uploadArea.appendChild(hiddenInput);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

// Reset file upload function
            window.resetFileUpload = function() {
                const uploadArea = document.querySelector('.border-dashed');
                uploadArea.innerHTML = `
                    <i class="ri-image-add-line text-2xl text-slate-400 mb-4"></i>
                    <p class="text-slate-600 mb-2">Click to upload or drag and drop</p>
                    <p class="text-sm text-slate-500">PNG, JPG up to 2MB</p>
                    <input type="file" 
                           name="banner_image" 
                           accept="image/*" 
                           class="hidden" 
                           id="cover-upload-reset" />
                    <label for="cover-upload-reset" class="cursor-pointer">
                        <div class="mt-4 inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50">
                            <i class="ri-upload-line mr-2"></i>
                            Choose File
                        </div>
                    </label>
                `;
                // Re-attach event listener
                const newFileInput = document.getElementById('cover-upload-reset');
                newFileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const uploadArea = newFileInput.closest('.border-dashed');
                            uploadArea.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-full h-48 object-cover rounded-lg mb-4" />
                                <button type="button" onclick="resetFileUpload()" class="text-sm text-red-600 hover:text-red-700">
                                    Remove Image
                                </button>
                            `;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            };

            // Location field conditional requirement based on format
            const formatRadios = document.querySelectorAll('.format-radio');
            const locationInput = document.getElementById('location');
            const locationRequired = document.getElementById('location-required');
            const locationHelp = document.getElementById('location-help');

            function updateLocationRequirement() {
                const selectedFormat = document.querySelector('.format-radio:checked')?.value;
                
                if (selectedFormat === 'online') {
                    locationInput.removeAttribute('required');
                    locationRequired.style.display = 'none';
                    locationHelp.textContent = 'Optional for online events.';
                    locationHelp.classList.remove('text-slate-500');
                    locationHelp.classList.add('text-slate-400');
                } else {
                    locationInput.setAttribute('required', 'required');
                    locationRequired.style.display = 'inline';
                    locationHelp.textContent = 'Required for on-site and hybrid events.';
                    locationHelp.classList.remove('text-slate-400');
                    locationHelp.classList.add('text-slate-500');
                }
            }

            // Set initial state
            updateLocationRequirement();

            // Update on format change
            formatRadios.forEach(radio => {
                radio.addEventListener('change', updateLocationRequirement);
            });
        });
    </script>
    @endpush
@endsection
