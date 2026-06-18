<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\SiteNotification;
use App\Models\Student;
use App\Models\TelegramMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private ?string $botToken = null;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    public function generateVerificationCode(User $user): string
    {
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['telegram_verification_code' => $code]);
        return $code;
    }

    public function verifyUser(User $user, string $code, string $chatId, string $username = null): bool
    {
        if (!$user->telegram_verification_code || $user->telegram_verification_code !== $code) {
            return false;
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_username' => $username,
            'telegram_verified' => true,
            'telegram_connected_at' => now(),
            'telegram_verification_code' => null,
        ]);

        $this->sendMessage($chatId, __('telegram.verified', [], $user->locale ?? 'ar'));
        AuditLog::record('telegram.verify', $user);
        return true;
    }

    public function disconnect(User $user): void
    {
        $user->update([
            'telegram_chat_id' => null,
            'telegram_username' => null,
            'telegram_verified' => false,
            'telegram_connected_at' => null,
            'telegram_verification_code' => null,
        ]);
        AuditLog::record('telegram.disconnect', $user);
    }

    public function sendMessage(string $chatId, string $text, array $options = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram bot not configured.');
            return null;
        }

        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        try {
            $resp = Http::timeout(15)
                ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $payload);

            $data = $resp->json();

            $log = TelegramMessage::create([
                'chat_id' => $chatId,
                'message' => $text,
                'payload' => $payload,
                'status' => $resp->successful() ? 'sent' : 'failed',
                'telegram_message_id' => $data['result']['message_id'] ?? null,
                'error' => $resp->failed() ? ($data['description'] ?? 'Unknown error') : null,
            ]);

            return $data;
        } catch (\Throwable $e) {
            TelegramMessage::create([
                'chat_id' => $chatId,
                'message' => $text,
                'payload' => $payload,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            Log::error('Telegram send failed', ['err' => $e->getMessage()]);
            return null;
        }
    }

    public function broadcastToBatch(Batch $batch, string $message, array $options = []): array
    {
        $results = ['sent' => 0, 'failed' => 0];
        $studentIds = Student::where('batch_id', $batch->id)->where('status', 'active')->pluck('user_id');
        $users = User::whereIn('id', $studentIds)->whereNotNull('telegram_chat_id')->where('telegram_verified', true)->get();

        foreach ($users as $u) {
            $resp = $this->sendMessage($u->telegram_chat_id, $message, $options);
            if ($resp) $results['sent']++; else $results['failed']++;
        }

        return $results;
    }

    public function broadcastAnnouncement(Announcement $announcement): array
    {
        $typeLabel = __("announcements.types.{$announcement->type}", [], 'ar');
        $text = "📢 <b>{$typeLabel}</b>\n\n<b>{$announcement->title}</b>\n\n" .
                mb_substr(strip_tags($announcement->body), 0, 1500) . "\n\n" .
                "🏷 دفعة: {$announcement->batch->name_ar}";

        $announcement->update(['telegram_sent' => true]);
        return $this->broadcastToBatch($announcement->batch, $text);
    }

    public function broadcastAssignment(Assignment $assignment): array
    {
        $deadline = $assignment->deadline->format('Y-m-d H:i');
        $text = "📚 <b>واجب جديد</b>\n\n<b>{$assignment->title}</b>\n\n" .
                mb_substr(strip_tags($assignment->description ?? ''), 0, 800) . "\n\n" .
                "⏰ الموعد النهائي: {$deadline}\n" .
                "🏷 الدفعة: {$assignment->batch->name_ar}";

        return $this->broadcastToBatch($assignment->batch, $text);
    }

    public function handleWebhook(array $update): void
    {
        $message = $update['message'] ?? null;
        if (!$message) return;

        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');
        $username = $message['from']['username'] ?? null;

        if (str_starts_with($text, '/start ')) {
            $code = trim(substr($text, 7));
            $user = User::where('telegram_verification_code', $code)->first();
            if ($user) {
                $ok = $this->verifyUser($user, $code, $chatId, $username);
                $this->sendMessage($chatId, $ok ? '✅ تم ربط حسابك بنجاح!' : '❌ الكود غير صالح.');
            } else {
                $this->sendMessage($chatId, '❌ الكود غير صالح أو منتهي الصلاحية.');
            }
            return;
        }

        if ($text === '/start') {
            $this->sendMessage($chatId, 'مرحباً! هذا بوت نظام إدارة الدفعات الجامعية. استخدم الرابط من لوحة التحكم لربط حسابك.');
            return;
        }

        if (preg_match('/^\d{6}$/', $text)) {
            $user = User::where('telegram_verification_code', $text)->first();
            if ($user) {
                $ok = $this->verifyUser($user, $text, $chatId, $username);
                $this->sendMessage($chatId, $ok ? '✅ تم ربط حسابك بنجاح!' : '❌ الكود غير صالح.');
            } else {
                $this->sendMessage($chatId, '❌ الكود غير صالح. تأكد من إدخال الكود الموجود في لوحة التحكم.');
            }
            return;
        }
    }
}
