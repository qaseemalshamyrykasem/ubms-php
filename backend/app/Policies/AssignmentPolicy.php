<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $assignment->batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) return $assignment->batch_id === $user->representativeProfile?->batch_id;
        if ($user->isStudent()) return $assignment->batch_id === $user->studentProfile?->batch_id;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isRepresentative() || $user->isCollegeAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $assignment->batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) {
            return $assignment->batch_id === $user->representativeProfile?->batch_id
                && $assignment->user_id === $user->id;
        }
        return false;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}
