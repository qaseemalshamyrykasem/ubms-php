<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function view(User $user, Batch $batch): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) return $batch->id === $user->representativeProfile?->batch_id;
        if ($user->isStudent()) return $batch->id === $user->studentProfile?->batch_id;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isCollegeAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Batch $batch): bool
    {
        return $this->create($user) && $this->view($user, $batch);
    }
}
