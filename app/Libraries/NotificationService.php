<?php
namespace App\Libraries;

use App\Models\NotificationModel;
use App\Models\UserModel;
use Config\Services;

/**
 * Layanan notifikasi sederhana via email (Gmail) + pencatatan ke tabel notifications.
 * Telegram dimatikan secara default.
 */
class NotificationService
{
    protected string $fromEmail;
    protected string $fromName;
    protected bool   $useEmail;
    // Telegram disabled (Gmail only)
    protected bool   $useTelegram = false;
    protected ?string $telegramBotToken = null;
    protected ?string $defaultTelegramChatId = null;

    public function __construct()
    {
        $this->fromEmail = config('email.fromEmail') ?? env('email.fromEmail') ?? 'no-reply@example.com';
        $this->fromName  = config('email.fromName')  ?? env('email.fromName')  ?? 'Sistem Reservasi';

        // Force email notifications ON by default (Gmail)
        $this->useEmail  = true;
        // Explicitly disable Telegram
        $this->useTelegram = false;
    }

    // Backward-compatible helper kept (unused). Prefer sendEmailMessage.
    private function sendEmail(string $to, string $subject, string $view, array $data = []): bool
    {
        if (!$this->useEmail) return false;
        try {
            $email = Services::email();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage(view($view, $data, ['saveData' => false]));
            return $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Email send failed: '.$e->getMessage());
            return false;
        }
    }

    // New: direct HTML message sender for email (Gmail)
    private function sendEmailMessage(string $to, string $subject, string $html): bool
    {
        if (!$this->useEmail) return false;
        try {
            $email = Services::email();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setTo($to);
            $email->setSubject($subject);
            // Ensure HTML mail type if supported by the service
            if (method_exists($email, 'setMailType')) {
                $email->setMailType('html');
            }
            $email->setMessage($html);
            return $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Email send failed: '.$e->getMessage());
            return false;
        }
    }

    // Align with NotificationModel fields: user_id, title, message, link, is_read
    private function logNotification(?int $userId, string $title, string $message, ?string $link = null): void
    {
        try {
            $model = new NotificationModel();
            $model->insert([
                'user_id' => $userId,
                'title'   => $title,
                'message' => $message,
                'link'    => $link,
                'is_read' => 0,
            ]);
        } catch (\Throwable $e) {
            log_message('error','Notification log failed: '.$e->getMessage());
        }
    }

    /** Kirim notifikasi penugasan baru ke driver. */
    public function notifyDriverAssignment(object $assignment, object $driver): void
    {
        // Resolve recipient email
        $to = $this->resolveDriverEmail($driver);
        if (!$to) {
            log_message('info','Driver belum memiliki email (user_id/email tidak ditemukan)');
            return;
        }

        $waktu = date('d-m-Y H:i', strtotime($assignment->start_datetime));
        $subject = 'Penugasan Baru';
        $html = '<h3>Penugasan Baru</h3>'
              . '<p>'
              . 'Booking ID: <strong>' . htmlspecialchars((string)$assignment->car_booking_id) . '</strong><br>'
              . 'Waktu Mulai: ' . htmlspecialchars($waktu) . '<br>'
              . 'Mobil: ' . htmlspecialchars((string)$assignment->mobil_jenis) . ' (' . htmlspecialchars((string)$assignment->mobil_plat) . ')<br>'
              . 'Catatan: ' . htmlspecialchars((string)($assignment->notes ?: '-'))
              . '</p>';

        $this->sendEmailMessage($to, $subject, $html);
        $this->logNotification($driver->user_id ?? null, $subject, strip_tags(str_replace('<br>', "\n", $html)));
    }

    /** Kirim notifikasi pengingat penugasan ke driver. */
    public function notifyDriverReminder(object $assignment, object $driver): void
    {
       $to = $this->resolveDriverEmail($driver);
       if (!$to) return;

       $waktu = date('d-m-Y H:i', strtotime($assignment->start_datetime));
       $subject = 'Reminder Penugasan';
       $html = '<h3>Reminder Penugasan</h3>'
             . '<p>Penugasan dengan Booking ID: <strong>' . htmlspecialchars((string)$assignment->car_booking_id) . '</strong> akan dimulai pada <strong>' . htmlspecialchars($waktu) . '</strong>.<br>'
             . 'Mobil: ' . htmlspecialchars((string)$assignment->mobil_jenis) . ' (' . htmlspecialchars((string)$assignment->mobil_plat) . ')<br>'
             . 'Jangan lupa untuk bersiap-siap.</p>';

        $this->sendEmailMessage($to, $subject, $html);
        $this->logNotification($driver->user_id ?? null, $subject, strip_tags(str_replace('<br>', "\n", $html)));
    }

    private function resolveDriverEmail(object $driver): ?string
    {
        // Try direct property first
        if (!empty($driver->email) && filter_var($driver->email, FILTER_VALIDATE_EMAIL)) {
            return $driver->email;
        }
        // Fallback to linked user account
        $userId = $driver->user_id ?? null;
        if ($userId) {
            try {
                $user = (new UserModel())->find($userId);
                if ($user && !empty($user['email']) && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    return $user['email'];
                }
            } catch (\Throwable $e) {
                log_message('error','Resolve driver email failed: '.$e->getMessage());
            }
        }
        return null;
    }
}