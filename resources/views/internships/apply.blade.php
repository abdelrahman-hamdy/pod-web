@extends('layouts.app')

@section('title', $internship ? 'Apply for ' . $internship->title : 'Submit General Application - People Of Data')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <!-- Page Title -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">
            {{ $internship ? 'Apply for ' . $internship->title : 'Submit General Application' }}
        </h1>
        <p class="text-slate-600">
            {{ $internship ? 'Join our internship program and gain valuable experience' : 'Apply for internship opportunities at People Of Data' }}
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

    <form method="POST" action="{{ route('internships.applications.store') }}" class="space-y-8">
        @csrf
        <input type="hidden" name="internship_id" value="{{ $internship_id ?? '' }}">

        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Personal Information</h2>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                        <input type="text" 
                               id="full_name"
                               name="full_name"
                               value="{{ old('full_name', auth()->user()->name) }}"
                               placeholder="Enter your full name"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                        <input type="email" 
                               id="email"
                               value="{{ auth()->user()->email }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-slate-500"
                               readonly />
                        <p class="text-xs text-slate-500 mt-1">Your email from your profile</p>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">Phone Number *</label>
                        <input type="tel" 
                               id="phone"
                               name="phone"
                               value="{{ old('phone', auth()->user()->phone) }}"
                               placeholder="Enter your phone number"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <div>
                        <label for="university" class="block text-sm font-medium text-slate-700 mb-2">University/Institution</label>
                        <input type="text" 
                               id="university"
                               name="university"
                               value="{{ old('university') }}"
                               placeholder="e.g., Cairo University"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div>
                        <label for="major" class="block text-sm font-medium text-slate-700 mb-2">Major/Field of Study</label>
                        <input type="text" 
                               id="major"
                               name="major"
                               value="{{ old('major') }}"
                               placeholder="e.g., Computer Science, Statistics"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div>
                        <label for="graduation_status" class="block text-sm font-medium text-slate-700 mb-2">Graduation Status</label>
                        <select id="graduation_status"
                                name="graduation_status"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Status</option>
                            @foreach($graduationStatuses as $value => $label)
                                <option value="{{ $value }}" {{ old('graduation_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Experience -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Experience</h2>
            <div>
                <label for="experience" class="block text-sm font-medium text-slate-700 mb-2">Previous Experience</label>
                <textarea id="experience"
                          name="experience"
                          rows="4"
                          placeholder="Describe any previous internships, projects, or relevant work experience..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('experience') }}</textarea>
                <p class="text-xs text-slate-500 mt-1">Share your background and relevant experience</p>
            </div>
        </div>

        <!-- Areas of Interest -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Areas of Interest *</h2>
            <div>
                <p class="text-sm text-slate-600 mb-4">Select one or more categories that interest you:</p>
                <div class="flex flex-wrap gap-2" id="interest-categories">
                    @foreach($categories as $category)
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="interest_categories[]" 
                                   value="{{ $category->id }}"
                                   class="hidden interest-checkbox"
                                   {{ in_array($category->id, old('interest_categories', [])) ? 'checked' : '' }}>
                            <span class="px-4 py-2 rounded-full text-sm font-medium transition-all
                                {{ in_array($category->id, old('interest_categories', [])) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                {{ $category->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('interest_categories')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Availability -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Availability</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="availability_start" class="block text-sm font-medium text-slate-700 mb-2">Available Start Date *</label>
                    <input type="date" 
                           id="availability_start"
                           name="availability_start"
                           value="{{ old('availability_start') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required />
                </div>

                <div>
                    <label for="availability_end" class="block text-sm font-medium text-slate-700 mb-2">Available End Date *</label>
                    <input type="date" 
                           id="availability_end"
                           name="availability_end"
                           value="{{ old('availability_end') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required />
                </div>
            </div>
        </div>

        <!-- Motivation -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Motivation</h2>
            <div>
                <label for="motivation" class="block text-sm font-medium text-slate-700 mb-2">Why do you want to join our internship program? *</label>
                <textarea id="motivation"
                          name="motivation"
                          rows="6"
                          placeholder="Tell us why you're interested in this internship and what you hope to achieve..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          required>{{ old('motivation') }}</textarea>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Terms and Conditions</h2>
            <div class="flex items-start">
                <input type="checkbox" 
                       id="terms"
                       name="terms"
                       value="1"
                       {{ old('terms') ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 mt-1"
                       required />
                <label for="terms" class="ml-2 text-sm text-slate-700">
                    I agree to the terms and conditions of the internship program and understand that this application will be reviewed by the People Of Data team. *
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <a href="{{ $internship ? route('internships.show', $internship) : route('internships.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                <i class="ri-arrow-left-line mr-2"></i>
                {{ $internship ? 'Back to Internship' : 'Back to Internships' }}
            </a>
            
            <div class="flex space-x-4">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ri-send-plane-line mr-2"></i>
                    Submit Application
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle interest category checkbox pills
    const checkboxes = document.querySelectorAll('.interest-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            const span = label.querySelector('span');
            if (this.checked) {
                span.classList.remove('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
                span.classList.add('bg-indigo-600', 'text-white');
            } else {
                span.classList.remove('bg-indigo-600', 'text-white');
                span.classList.add('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
            }
        });
    });
});
</script>
@endpush
@endsection
