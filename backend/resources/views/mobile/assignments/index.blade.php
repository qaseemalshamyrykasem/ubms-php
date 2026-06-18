@extends('mobile.layouts.app')
@section('title', 'الواجبات - UBMS')

@php
    $user = auth()->user();
    $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
    $assignments = \App\Models\Assignment::where('batch_id', $batch?->id)
        ->with('course', 'author', 'attachments')
        ->orderBy('deadline')
        ->paginate(15);
@endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">📚 الواجبات</div>
    <div class="app-bar-actions">
        @if (in_array($user->primaryRole(), ['representative', 'college_admin', 'super_admin']))
            <a href="/mobile/assignments/create" class="icon-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            </a>
        @endif
    </div>
</div>

<div class="main fade-in">
    @forelse ($assignments as $assignment)
        @php
            $overdue = \Carbon\Carbon::parse($assignment->deadline)->isPast();
            $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($assignment->deadline), false);
        @endphp
        <a href="/mobile/assignments/{{ $assignment->id }}" class="list-item" style="display: block;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="width: 40px; height: 40px; background: {{ $overdue ? 'rgba(239, 68, 68, 0.2)' : 'rgba(99, 102, 241, 0.2)' }}; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    {{ $overdue ? '⚠️' : '📋' }}
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; flex-wrap: wrap;">
                        @if ($assignment->course)
                            <span class="badge badge-general">{{ $assignment->course->code }}</span>
                        @endif
                        <span class="badge {{ $overdue ? 'badge-danger' : 'badge-success' }}">
                            {{ $overdue ? 'منتهي' : ($daysLeft == 0 ? 'اليوم' : $daysLeft . ' يوم') }}
                        </span>
                    </div>
                    <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">{{ $assignment->title }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4; margin-bottom: 6px;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($assignment->description), 80) }}
                    </div>
                    <div style="display: flex; gap: 12px; font-size: 11px; color: var(--text-muted);">
                        <span>⏰ {{ \Carbon\Carbon::parse($assignment->deadline)->format('Y/m/d H:i') }}</span>
                        @if ($assignment->attachments->count() > 0)
                            <span>📎 {{ $assignment->attachments->count() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <p>لا توجد واجبات</p>
        </div>
    @endforelse

    @if ($assignments->hasPages())
        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 16px;">
            <a href="{{ $assignments->previousPageUrl() }}" class="btn btn-secondary {{ $assignments->onFirstPage() ? 'disabled' : '' }}" style="width: auto;">السابق</a>
            <a href="{{ $assignments->nextPageUrl() }}" class="btn btn-secondary {{ !$assignments->hasMorePages() ? 'disabled' : '' }}" style="width: auto;">التالي</a>
        </div>
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
