<?php

namespace App\Imports;

use App\Models\Batch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function __construct(public Batch $batch) {}

    public function model(array $row)
    {
        $user = User::where('email', $row['email'])->first();
        if ($user) {
            return null; // skip existing
        }

        $user = User::create([
            'name' => $row['name'] ?? $row['name_ar'] ?? 'طالب',
            'name_ar' => $row['name_ar'] ?? null,
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'password' => Hash::make($row['password'] ?? 'ubms12345'),
            'locale' => 'ar',
        ]);
        $user->assignRole('student');

        return new Student([
            'user_id' => $user->id,
            'batch_id' => $this->batch->id,
            'student_id' => $row['student_id'],
            'enrolled_at' => now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'student_id' => 'required|string',
        ];
    }
}
