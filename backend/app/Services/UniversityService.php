<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\College;
use App\Models\Department;
use App\Models\Level;
use App\Models\Section;
use App\Models\University;
use Illuminate\Support\Facades\DB;

class UniversityService
{
    public function createUniversity(array $data): University
    {
        return University::create($data);
    }

    public function createCollege(array $data): College
    {
        return College::create($data);
    }

    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    public function createLevel(array $data): Level
    {
        return Level::create($data);
    }

    public function createSection(array $data): Section
    {
        return Section::create($data);
    }

    public function createBatch(array $data): Batch
    {
        if (empty($data['code'])) {
            $section = Section::with(['level.department.college'])->find($data['section_id']);
            $data['code'] = $this->generateBatchCode($section, $data['start_year'] ?? now()->year);
        }
        return Batch::create($data);
    }

    public function generateBatchCode(Section $section, int $year): string
    {
        $dept = $section?->level?->department;
        $prefix = $dept ? strtoupper(substr($dept->code, 0, 4)) : 'BATCH';
        $count = Batch::where('section_id', $section->id)->count();
        return sprintf('%s-%d-%02d', $prefix, $year, $count + 1);
    }

    public function getHierarchyForUser($user): array
    {
        if ($user->isSuperAdmin()) {
            return University::with(['colleges.departments.levels.sections.batches'])
                ->get()
                ->toArray();
        }

        if ($user->isCollegeAdmin()) {
            $collegeId = $user->collegeAdminProfile?->college_id;
            return College::with(['departments.levels.sections.batches'])
                ->where('id', $collegeId)
                ->get()
                ->toArray();
        }

        if ($user->isRepresentative() || $user->isStudent()) {
            $batch = $user->representativeProfile?->batch
                ?? $user->studentProfile?->batch;
            if (!$batch) return [];
            return Batch::with(['section.level.department.college.university'])
                ->where('id', $batch->id)
                ->get()
                ->toArray();
        }

        return [];
    }
}
