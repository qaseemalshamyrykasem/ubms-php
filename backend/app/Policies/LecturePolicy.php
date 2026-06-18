<?php

namespace App\Policies;

use App\Models\Lecture;
use App\Models\Schedule;
use App\Models\User;

class LecturePolicy
{
    public function view(User $user, Lecture $lecture): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $lecture->batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) return $lecture->batch_id === $user->representativeProfile?->batch_id;
        if ($user->isStudent()) return $lecture->batch_id === $user->studentProfile?->batch_id;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isRepresentative() || $user->isCollegeAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Lecture $lecture): bool
    {
        return $this->create($user) && $this->view($user, $lecture);
    }
}
