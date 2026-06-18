<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function view(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $announcement->batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) {
            return $announcement->batch_id === $user->representativeProfile?->batch_id;
        }
        if ($user->isStudent()) {
            return $announcement->batch_id === $user->studentProfile?->batch_id
                && $announcement->is_published;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isRepresentative() || $user->isCollegeAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $announcement->batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) {
            return $announcement->batch_id === $user->representativeProfile?->batch_id
                && $announcement->user_id === $user->id;
        }
        return false;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }
}
