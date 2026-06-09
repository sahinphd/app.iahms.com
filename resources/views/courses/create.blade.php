@extends('layouts.app')

@section('page_title', 'Create Course')

@section('content')
<div class="max-w-2xl mx-auto bg-slate-950/40 border border-slate-800 rounded-3xl p-6 md:p-8 shadow-lg">
    
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-100">Create New Course</h2>
        <p class="text-xs text-slate-400 mt-1">Design a new learning path for paramedical training</p>
    </div>

    <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label for="title" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Course Title</label>
            <input id="title" name="title" type="text" required value="{{ old('title') }}"
                class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-700"
                placeholder="e.g. Diploma in Radiography">
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Course Description</label>
            <textarea id="description" name="description" rows="5" required
                class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm placeholder-slate-700"
                placeholder="Explain what the course is about, target audience, and key learning outcomes...">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="thumbnail" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Course Thumbnail</label>
            <input id="thumbnail" name="thumbnail" type="file" accept="image/*"
                class="w-full px-4 py-2.5 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-400 focus:outline-none transition-colors duration-200 text-sm">
            <span class="text-[10px] text-slate-500 mt-1.5 block">JPEG, PNG, JPG, GIF up to 2MB (GCP storage enabled)</span>
        </div>

        <div>
            <label for="school_class_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Target School Class (Course Scope)</label>
            <select id="school_class_id" name="school_class_id"
                class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm cursor-pointer">
                <option value="">All Classes (Publicly visible to all students)</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
            <span class="text-[10px] text-slate-500 mt-1.5 block">Restrict this course visibility and enrollment capability to students of a specific class.</span>
        </div>

        @if(Auth::user()->isAdmin())
        <div>
            <label for="teacher_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Assign Course Teacher</label>
            <select id="teacher_id" name="teacher_id"
                class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-800 focus:border-brand-500 text-slate-100 focus:outline-none transition-colors duration-200 text-sm cursor-pointer">
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('teacher_id', Auth::id()) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }} ({{ $teacher->email }})</option>
                @endforeach
            </select>
            <span class="text-[10px] text-slate-500 mt-1.5 block">Select which teacher manages this course curriculum.</span>
        </div>
        @endif

        <div class="flex items-center space-x-3 select-none">
            <input id="is_published" name="is_published" type="checkbox" value="1"
                class="rounded bg-slate-900 border-slate-800 text-brand-600 focus:ring-brand-500/30">
            <label for="is_published" class="text-sm text-slate-300 font-medium cursor-pointer">Publish immediately</label>
        </div>

        <div class="pt-4 border-t border-slate-900 flex items-center justify-end space-x-3">
            <a href="{{ route('courses.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-800 hover:bg-slate-900 text-xs font-bold text-slate-400 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold transition-all duration-200 shadow-md shadow-brand-600/10">
                Save Course
            </button>
        </div>

    </form>

</div>
@endsection
