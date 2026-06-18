<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string|max:10000',
            'type' => 'sometimes|in:holiday,assignment,lecture,schedule,general,urgent,emergency,meeting,important',
            'course_id' => 'nullable|exists:courses,id',
            'is_pinned' => 'boolean',
            'is_published' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'send_telegram' => 'boolean',
            'attachments.*' => 'file|max:10240',
        ];
    }
}
