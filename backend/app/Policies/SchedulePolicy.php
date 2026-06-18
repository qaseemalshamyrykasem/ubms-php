<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function create(User $user): bool
    {
        return $user->isRepresentative() || $user->isCollegeAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Schedule $schedule): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isCollegeAdmin()) {
            return $schedule->batch->section->level->department->college_id === $user->collegeAdminProfile?->college_id;
        }
        if ($user->isRepresentative()) return $schedule->batch_id === $user->representativeProfile?->batch_id;
        return false;
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->update($user, $schedule);
    }
}
