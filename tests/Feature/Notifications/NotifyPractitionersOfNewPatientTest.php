<?php

namespace Tests\Feature\Notifications;

use App\Jobs\NotifyPractitionersOfNewPatient;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotifyPractitionersOfNewPatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        config(['services.telegram.bot_token' => 'test-token']);
    }

    protected function makePatient(): Patient
    {
        return Patient::create([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => '+251900000001',
        ]);
    }

    public function test_only_practitioners_with_a_linked_chat_id_are_notified(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $linked = User::factory()->create(['telegram_chat_id' => '111']);
        $linked->assignRole('Practitioner');

        $unlinked = User::factory()->create();
        $unlinked->assignRole('Practitioner');

        $patient = $this->makePatient();

        NotifyPractitionersOfNewPatient::dispatchSync($patient->id);

        Http::assertSentCount(1);
        Http::assertSent(fn (HttpRequest $request) => $request['chat_id'] === '111' && str_contains($request['text'], $patient->patient_id));
    }

    public function test_a_failed_send_does_not_stop_the_remaining_recipients(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::sequence()
                ->push(['ok' => false], 403)
                ->push(['ok' => true], 200),
        ]);

        $first = User::factory()->create(['telegram_chat_id' => '111']);
        $first->assignRole('Practitioner');

        $second = User::factory()->create(['telegram_chat_id' => '222']);
        $second->assignRole('Practitioner');

        $patient = $this->makePatient();

        NotifyPractitionersOfNewPatient::dispatchSync($patient->id);

        Http::assertSentCount(2);
    }

    public function test_nothing_is_sent_when_the_bot_token_is_not_configured(): void
    {
        config(['services.telegram.bot_token' => null]);

        Http::fake();

        $practitioner = User::factory()->create(['telegram_chat_id' => '111']);
        $practitioner->assignRole('Practitioner');

        $patient = $this->makePatient();

        NotifyPractitionersOfNewPatient::dispatchSync($patient->id);

        Http::assertNothingSent();
    }

    public function test_a_deleted_patient_is_a_quiet_no_op(): void
    {
        Http::fake();

        $practitioner = User::factory()->create(['telegram_chat_id' => '111']);
        $practitioner->assignRole('Practitioner');

        NotifyPractitionersOfNewPatient::dispatchSync(999999);

        Http::assertNothingSent();
    }
}
