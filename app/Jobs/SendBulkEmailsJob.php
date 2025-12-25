<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $emailData,
        public int $delayBetweenEmails = 500 // Délai en millisecondes
    ) {
        // $emailData format:
        // [
        //     [
        //         'recipient' => User object or email string,
        //         'mailable' => Mailable instance,
        //         'context' => ['user_id' => 1, 'type' => 'prize_notification'] // For logging
        //     ],
        //     ...
        // ]
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failureCount = 0;
        $total = count($this->emailData);

        Log::info("Starting bulk email job: {$total} emails to send");

        foreach ($this->emailData as $index => $emailInfo) {
            try {
                // Send email
                Mail::to($emailInfo['recipient'])->send($emailInfo['mailable']);

                $successCount++;

                // Log success
                $context = $emailInfo['context'] ?? [];
                Log::info("Bulk email sent successfully", array_merge([
                    'index' => $index + 1,
                    'total' => $total,
                    'mailable' => get_class($emailInfo['mailable']),
                ], $context));

                // Rate limiting: wait between emails (except for the last one)
                if ($index < $total - 1 && $this->delayBetweenEmails > 0) {
                    usleep($this->delayBetweenEmails * 1000); // Convert ms to microseconds
                }

            } catch (\Exception $e) {
                $failureCount++;
                $context = $emailInfo['context'] ?? [];

                Log::error("Failed to send bulk email", array_merge([
                    'index' => $index + 1,
                    'total' => $total,
                    'mailable' => get_class($emailInfo['mailable']),
                    'error' => $e->getMessage(),
                ], $context));

                // Continue to next email even if this one fails
                continue;
            }
        }

        Log::info("Bulk email job completed", [
            'total' => $total,
            'success' => $successCount,
            'failures' => $failureCount,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Bulk email job failed completely", [
            'total_emails' => count($this->emailData),
            'error' => $exception->getMessage(),
        ]);
    }
}
