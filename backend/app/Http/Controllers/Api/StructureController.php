<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\College;
use App\Models\Department;
use App\Models\Level;
use App\Models\Section;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Services\ReportService;
use App\Services\UniversityService;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function __construct(private UniversityService $service)
    {
    }

    public function hierarchy(Request $request)
    {
        return response()->json([
            'hierarchy' => $this->service->getHierarchyForUser($request->user()),
        ]);
    }

    public function universities()
    {
        return response()->json(['data' => University::where('is_active', true)->get()]);
    }

    public function colleges(Request $request)
    {
        $user = $request->user();
        $q = College::query();
        if ($user->isCollegeAdmin()) {
            $q->where('id', $user->collegeAdminProfile?->college_id);
        }
        return response()->json(['data' => $q->where('is_active', true)->get()]);
    }

    public function departments(Request $request)
    {
        $user = $request->user();
        $q = Department::query()->with('college');
        if ($user->isCollegeAdmin()) {
            $q->where('college_id', $user->collegeAdminProfile?->college_id);
        }
        if ($request->college_id) $q->where('college_id', $request->college_id);
        return response()->json(['data' => $q->where('is_active', true)->get()]);
    }

    public function levels(Request $request)
    {
        $q = Level::query()->with('department');
        if ($request->department_id) $q->where('department_id', $request->department_id);
        return response()->json(['data' => $q->where('is_active', true)->get()]);
    }

    public function sections(Request $request)
    {
        $q = Section::query()->with('level');
        if ($request->level_id) $q->where('level_id', $request->level_id);
        return response()->json(['data' => $q->where('is_active', true)->get()]);
    }

    public function batches(Request $request)
    {
        $user = $request->user();
        $q = Batch::query()->with(['section.level.department.college.university']);

        if ($user->isCollegeAdmin()) {
            $q->whereHas('section.level.department', function ($qq) use ($user) {
                $qq->where('college_id', $user->collegeAdminProfile?->college_id);
            });
        } elseif ($user->isRepresentative()) {
            $q->where('id', $user->representativeProfile?->batch_id);
        } elseif ($user->isStudent()) {
            $q->where('id', $user->studentProfile?->batch_id);
        }

        if ($request->section_id) $q->where('section_id', $request->section_id);
        if ($request->search) {
            $q->where(function ($qq) use ($request) {
                $qq->where('code', 'like', "%{$request->search}%")
                   ->orWhere('name_ar', 'like', "%{$request->search}%")
                   ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        return response()->json($q->orderBy('code')->paginate($request->per_page ?? 20));
    }

    public function createBatch(Request $request)
    {
        $user = $request->user();
        if (!($user->isCollegeAdmin() || $user->isSuperAdmin())) {
            return response()->json(['message' => __('auth.unauthorized')], 403);
        }

        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:batches,code',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'nullable|integer|min:2000|max:2100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $batch = $this->service->createBatch($data);
        return response()->json(['batch' => $batch, 'message' => __('batch.created')], 201);
    }

    public function showBatch(Batch $batch)
    {
        $batch->load(['section.level.department.college.university']);
        return response()->json(['batch' => $batch]);
    }

    public function batchStudents(Batch $batch)
    {
        $students = Student::where('batch_id', $batch->id)
            ->with('user')
            ->where('status', 'active')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'name' => $s->user->name,
                'name_ar' => $s->user->name_ar,
                'email' => $s->user->email,
                'phone' => $s->user->phone,
                'telegram_connected' => $s->user->telegramConnected(),
                'enrolled_at' => $s->enrolled_at,
            ]);
        return response()->json(['data' => $students]);
    }
}
