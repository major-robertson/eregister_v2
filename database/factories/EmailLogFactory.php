<?php

namespace Database\Factories;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<EmailLog>
 */
class EmailLogFactory extends Factory
{
    protected $model = EmailLog::class;

    public function definition(): array
    {
        return [
            'mailable' => \App\Mail\FilingMilestone::class,
            'subject' => 'Your lien was recorded — eRegister',
            'from_address' => 'contact@eregister.com',
            'to' => [fake()->safeEmail()],
            'message_id' => fake()->uuid(),
            'status' => EmailLog::STATUS_SENT,
            'html_body' => '<html><head></head><body><p>Hello</p></body></html>',
        ];
    }

    public function about(Model $record): static
    {
        return $this->state(fn () => [
            'related_type' => $record->getMorphClass(),
            'related_id' => $record->getKey(),
        ]);
    }
}
