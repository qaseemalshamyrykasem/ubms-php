<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'type' => 'required|in:holiday,assignment,lecture,schedule,general,urgent,emergency,meeting,important',
            'course_id' => 'nullable|exists:courses,id',
            'is_pinned' => 'boolean',
            'is_published' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
            'expires_at' => 'nullable|date|after:now',
            'send_telegram' => 'boolean',
            'attachments.*' => 'file|max:10240', // 10MB
        ];
    }
}
