<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyPractitionersOfNewPatient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * Takes the patient's id rather than the model itself - if the patient
     * is deleted between dispatch and processing, SerializesModels would
     * otherwise throw a ModelNotFoundException on unserialize and mark this
     * job permanently failed for a perfectly legitimate race. Fetching it
     * manually lets that case be a quiet no-op instead.
     */
    public function __construct(public int $patientId) {}

    public function handle(TelegramService $telegram): void
    {
        if (! $telegram->isConfigured()) {
            return;
        }

        $patient = Patient::find($this->patientId);

        if (! $patient) {
            return;
        }

        $message = sprintf(
            "🆕 <b>New patient registered</b>\n\n%s\nID: %s\nAge/Sex: %s / %s\nPhone: %s",
            e($patient->full_name),
            e($patient->patient_id),
            $patient->age,
            ucfirst($patient->sex),
            e($patient->phone)
        );

        // Collection::each() stops iterating the moment its callback returns
        // false, and sendMessage() returns false on a failed send - so this
        // loop must not let that value escape the closure, or one blocked
        // practitioner would silently swallow every notification after them.
        User::role('Practitioner')
            ->whereNotNull('telegram_chat_id')
            ->get()
            ->each(function (User $practitioner) use ($telegram, $message): void {
                $telegram->sendMessage($practitioner->telegram_chat_id, $message);
            });
    }
}
