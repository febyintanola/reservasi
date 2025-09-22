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
            $sent = $email->send();
            if (!$sent) {
                // Capture SMTP/dialog debug output and log it
                $debug = method_exists($email, 'printDebugger') ? $email->printDebugger(['headers' => true, 'body' => true]) : 'no debugger available';
                log_message('error', 'Email send returned false. Debug: ' . print_r($debug, true));
            }
            return $sent;
        } catch (\Throwable $e) {
            // On exception capture the email debugger (if available) and full exception
            $debug = '';
            try {
                $debug = method_exists($email ?? null, 'printDebugger') ? ($email->printDebugger(['headers' => true, 'body' => true]) ?? '') : '';
            } catch (\Throwable $inner) {
                $debug .= ' (failed to get debugger: '.$inner->getMessage().')';
            }
            log_message('error', 'Email send exception: '.$e->getMessage().' Debug: '.print_r($debug, true));
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

        // Fallback: gunakan tanggal_pergi jika start_datetime tidak ada
        if (!isset($assignment->start_datetime)) {
            if (isset($assignment->tanggal_pergi)) {
                $assignment->start_datetime = $assignment->tanggal_pergi;
            } else {
                log_message('error', 'Penugasan tidak memiliki properti start_datetime maupun tanggal_pergi. Notifikasi tidak dikirim.');
                return;
            }
        }
        $waktu = date('d-m-Y H:i', strtotime($assignment->start_datetime));
      $subject = 'Pemberitahuan Penugasan: Booking ' . htmlspecialchars((string)$assignment->car_booking_id);

      // Professional, polite Indonesian email HTML
      $notes = htmlspecialchars((string)($assignment->notes ?? '-'));
      $notes = $notes === '' ? '-' : nl2br($notes);

      $displayName = htmlspecialchars($this->resolveDriverName($driver));
      $html = '<!doctype html><html><head><meta charset="utf-8"><title>' . $subject . '</title></head><body style="font-family: Arial, sans-serif; color: #222;">'
          . '<p>Yth. Bapak/Ibu ' . $displayName . ',</p>'
          . '<p>Anda telah menerima penugasan baru. Berikut detail penugasan:</p>'
          . '<table cellpadding="4" cellspacing="0" border="0" style="border-collapse: collapse;">'
          . '<tr><td style="vertical-align: top; font-weight:600;">Booking ID</td><td>:</td><td>' . htmlspecialchars((string)$assignment->car_booking_id) . '</td></tr>'
          . '<tr><td style="vertical-align: top; font-weight:600;">Waktu Mulai</td><td>:</td><td>' . htmlspecialchars($waktu) . '</td></tr>'
          . '<tr><td style="vertical-align: top; font-weight:600;">Mobil</td><td>:</td><td>' . htmlspecialchars((string)$assignment->mobil_jenis) . ' (' . htmlspecialchars((string)$assignment->mobil_plat) . ')</td></tr>'
          . '<tr><td style="vertical-align: top; font-weight:600;">Catatan</td><td>:</td><td>' . $notes . '</td></tr>'
          . '</table>'
          . '<p>Mohon siapkan diri dan pastikan kendaraan dalam kondisi baik sebelum jadwal. Jika ada kendala, harap konfirmasi ke tim operasional sesegera mungkin.</p>'
          . '<p>Terima kasih,<br>' . htmlspecialchars($this->fromName) . '</p>'
          . '</body></html>';

      $this->sendEmailMessage($to, $subject, $html);
      // Log as plain text — convert <br> to newlines for readability
      $this->logNotification($driver->user_id ?? null, $subject, strip_tags(str_replace('<br>', "\n", $html)));
    }

    /** Kirim notifikasi pengingat penugasan ke driver. */
    public function notifyDriverReminder(object $assignment, object $driver): void
    {
      $to = $this->resolveDriverEmail($driver);
      if (!$to) return;

      if (!isset($assignment->start_datetime)) {
        log_message('error', 'Penugasan tidak memiliki properti start_datetime. Reminder tidak dikirim.');
        return;
      }

      $waktu = date('d-m-Y H:i', strtotime($assignment->start_datetime));
    $subject = 'Pengingat Penugasan: Booking ' . htmlspecialchars((string)$assignment->car_booking_id);

    $notes = htmlspecialchars((string)($assignment->notes ?? '-'));
    $notes = $notes === '' ? '-' : nl2br($notes);

    $displayName = htmlspecialchars($this->resolveDriverName($driver));
    $html = '<!doctype html><html><head><meta charset="utf-8"><title>' . $subject . '</title></head><body style="font-family: Arial, sans-serif; color: #222;">'
        . '<p>Yth. Bapak/Ibu ' . $displayName . ',</p>'
        . '<p>Ini merupakan pengingat bahwa Anda memiliki penugasan yang akan dimulai pada:</p>'
        . '<ul>'
        . '<li><strong>Booking ID:</strong> ' . htmlspecialchars((string)$assignment->car_booking_id) . '</li>'
        . '<li><strong>Waktu Mulai:</strong> ' . htmlspecialchars($waktu) . '</li>'
        . '<li><strong>Mobil:</strong> ' . htmlspecialchars((string)$assignment->mobil_jenis) . ' (' . htmlspecialchars((string)$assignment->mobil_plat) . ')</li>'
        . '</ul>'
        . '<p>Catatan: ' . $notes . '</p>'
        . '<p>Mohon hadir tepat waktu dan pastikan segala persiapan telah dilakukan. Jika ada perubahan, segera hubungi tim operasional.</p>'
        . '<p>Hormat kami,<br>' . htmlspecialchars($this->fromName) . '</p>'
        . '</body></html>';

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

    /**
     * Resolve a human-friendly display name for the driver.
     * Tries multiple properties and falls back to the linked user record.
     */
    private function resolveDriverName(object $driver): string
    {
        // Common property candidates
        $candidates = [
            'name',
            'fullname',
            'full_name',
            'display_name',
            'driver_name',
            'username',
        ];

        foreach ($candidates as $prop) {
            if (!empty($driver->{$prop}) && is_string($driver->{$prop})) {
                return (string)$driver->{$prop};
            }
        }

        // Fallback: try linked user record
        $userId = $driver->user_id ?? null;
        if ($userId) {
            try {
                $user = (new UserModel())->find($userId);
                if ($user) {
                    if (!empty($user['name'])) return (string)$user['name'];
                    if (!empty($user['fullname'])) return (string)$user['fullname'];
                    if (!empty($user['email'])) return (string)$user['email'];
                }
            } catch (\Throwable $e) {
                log_message('error','Resolve driver name failed: '.$e->getMessage());
            }
        }

        // Last resort fallback text
        return 'Rekan Pengemudi';
    }
}