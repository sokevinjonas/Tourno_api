<?php

namespace App\Jobs;

use App\Mail\DepositReminderMail;
use App\Models\CoinTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindPendingDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public CoinTransaction $transaction,
        public ?string $paymentUrl = null
    ) {}

    public function handle(): void
    {
        // Recharger la transaction depuis la base de données
        $this->transaction->refresh();

        // Vérifier si la transaction est toujours en attente/processing
        if (in_array($this->transaction->status, ['pending', 'processing'])) {
            Log::info("Sending deposit reminder for transaction {$this->transaction->uuid}");

            // Envoyer un email de rappel
            $user = $this->transaction->user;
            Mail::to($user)->send(new DepositReminderMail($user, $this->transaction, $this->paymentUrl));

            Log::info("Deposit reminder sent to user {$user->id}");
        } else {
            Log::info("Transaction {$this->transaction->uuid} is no longer pending (status: {$this->transaction->status}), skipping reminder");
        }
    }
}
