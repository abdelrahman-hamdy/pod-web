@extends('layouts.app')

@section('title', 'Create Internship - People Of Data')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <!-- Page Title -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">Create New Internship</h1>
        <p class="text-slate-600">
            Create and publish an internship opportunity to attract talented students and graduates
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

    <form method="POST" action="{{ route('internships.store') }}" class="space-y-8">
        @csrf

        <!-- Internship Details -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Internship Details</h2>
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-2">Internship Title *</label>
                    <input type="text" 
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           placeholder="e.g., Data Science Intern, Web Development Trainee"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-2">Description *</label>
                    <textarea id="description"
                              name="description"
                              rows="6"
                              placeholder="Describe the internship role, responsibilities, learning opportunities, and what makes this opportunity exciting..."
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              required>{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-slate-700 mb-2">Company Name *</label>
                        <input type="text" 
                               id="company_name"
                               name="company_name"
                               value="{{ old('company_name', auth()->user()->company ?? '') }}"
                               placeholder="e.g., Microsoft, Google, etc."
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-slate-700 mb-2">Category *</label>
                        <select id="category_id"
                                name="category_id"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location & Type -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Location & Type</h2>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="location" class="block text-sm font-medium text-slate-700 mb-2">Location *</label>
                        <input type="text" 
                               id="location"
                               name="location"
                               value="{{ old('location') }}"
                               placeholder="e.g., Cairo, Egypt or Remote"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               required />
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700 mb-2">Type *</label>
                        <select id="type"
                                name="type"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                            <option value="">Select type</option>
                            <option value="full_time" {{ old('type') == 'full_time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part_time" {{ old('type') == 'part_time' ? 'selected' : '' }}>Part-time</option>
                            <option value="remote" {{ old('type') == 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ old('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-slate-700 mb-2">Duration (Optional)</label>
                    <input type="text" 
                           id="duration"
                           name="duration"
                           value="{{ old('duration') }}"
                           placeholder="e.g., 3 months, 6 months, 1 year"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
            </div>
        </div>

        <!-- Important Dates -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Important Dates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="application_deadline" class="block text-sm font-medium text-slate-700 mb-2">Application Deadline *</label>
                    <input type="date" 
                           id="application_deadline"
                           name="application_deadline"
                           value="{{ old('application_deadline') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           required />
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-slate-700 mb-2">Start Date (Optional)</label>
                    <input type="date" 
                           id="start_date"
                           name="start_date"
                           value="{{ old('start_date') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <a href="{{ route('internships.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                <i class="ri-arrow-left-line mr-2"></i>
                Back to Internships
            </a>
            
            <div class="flex space-x-4">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ri-save-line mr-2"></i>
                    Create Internship
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for application_deadline to tomorrow
    const today = new Date();
    today.setDate(today.getDate() + 1);
    const tomorrow = today.toISOString().split('T')[0];
    document.getElementById('application_deadline').setAttribute('min', tomorrow);

    // Set minimum date for start_date to day after application_deadline
    const applicationDeadlineInput = document.getElementById('application_deadline');
    const startDateInput = document.getElementById('start_date');

    applicationDeadlineInput.addEventListener('change', function() {
        if (this.value) {
            const deadlineDate = new Date(this.value);
            deadlineDate.setDate(deadlineDate.getDate() + 1);
            const nextDay = deadlineDate.toISOString().split('T')[0];
            startDateInput.setAttribute('min', nextDay);
        }
    });
});
</script>
@endpush
@endsection

