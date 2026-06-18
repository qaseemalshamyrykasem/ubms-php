<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Course::query()->with('department');

        if ($user->isRepresentative()) {
            $batch = $user->representativeProfile?->batch;
            if ($batch) {
                $q->whereHas('batches', fn($qq) => $qq->where('batches.id', $batch->id));
            }
        } elseif ($user->isStudent()) {
            $batch = $user->studentProfile?->batch;
            if ($batch) {
                $q->whereHas('batches', fn($qq) => $qq->where('batches.id', $batch->id));
            }
        } elseif ($user->isCollegeAdmin()) {
            $collegeId = $user->collegeAdminProfile?->college_id;
            $q->whereHas('department', fn($qq) => $qq->where('college_id', $collegeId));
        }

        if ($request->department_id) $q->where('department_id', $request->department_id);
        if ($request->search) {
            $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->search}%")
                   ->orWhere('name_ar', 'like', "%{$request->search}%")
                   ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        return response()->json($q->orderBy('code')->paginate($request->per_page ?? 20));
    }

    public function show(Course $course)
    {
        $course->load('department', 'files');
        return response()->json(['course' => $course]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!($user->isCollegeAdmin() || $user->isSuperAdmin() || $user->isRepresentative())) {
            return response()->json(['message' => __('auth.unauthorized')], 403);
        }

        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:courses,code',
            'description' => 'nullable|string|max:5000',
            'credit_hours' => 'integer|min:1|max:10',
            'instructor_name' => 'nullable|string|max:255',
            'instructor_email' => 'nullable|email|max:255',
            'instructor_phone' => 'nullable|string|max:30',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
        ]);

        $course = Course::create($data);
        if (!empty($data['batch_ids'])) {
            $course->batches()->sync($data['batch_ids']);
        }
        return response()->json(['course' => $course, 'message' => __('courses.created')], 201);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'credit_hours' => 'integer|min:1|max:10',
            'instructor_name' => 'nullable|string|max:255',
            'instructor_email' => 'nullable|email|max:255',
            'instructor_phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
        ]);

        $course->update($data);
        if (isset($data['batch_ids'])) {
            $course->batches()->sync($data['batch_ids']);
        }
        return response()->json(['course' => $course, 'message' => __('courses.updated')]);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['message' => __('courses.deleted')]);
    }

    public function uploadFile(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:20480',
            'type' => 'nullable|in:syllabus,lecture,reference,assignment,other',
            'description' => 'nullable|string|max:1000',
        ]);

        $path = $request->file('file')->store("courses/{$course->id}", 'public');
        $file = CourseFile::create([
            'course_id' => $course->id,
            'uploaded_by' => $request->user()->id,
            'name' => $request->name,
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientOriginalExtension(),
            'file_size' => $request->file('file')->getSize(),
            'description' => $request->description,
            'type' => $request->type ?? 'lecture',
        ]);

        return response()->json(['file' => $file, 'message' => __('courses.file_uploaded')], 201);
    }

    public function downloadFile(Course $course, CourseFile $file)
    {
        if ($file->course_id !== $course->id) abort(404);
        if (!Storage::disk('public')->exists($file->file_path)) abort(404);
        return Storage::disk('public')->download($file->file_path, $file->name);
    }
}
