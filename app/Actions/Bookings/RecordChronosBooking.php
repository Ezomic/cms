<?php

declare(strict_types=1);

namespace App\Actions\Bookings;

use App\Http\Requests\StoreChronosBookingRequest;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

class RecordChronosBooking
{
    /**
     * @param  array<string, string|null>  $data  the validated "booking" object
     */
    public function handle(string $event, array $data): void
    {
        try {
            $this->record($event, $data);
        } catch (UniqueConstraintViolationException) {
            // Two deliveries for the same new uuid raced and the other one
            // inserted first. The row exists now, so this pass updates it.
            $this->record($event, $data);
        }
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function record(string $event, array $data): void
    {
        $booking = Booking::firstWhere('uuid', $data['uuid']);
        $cancelledAt = $this->cancelledAt($event, $data);

        if ($booking === null) {
            Booking::create([...$this->attributes($data), 'cancelled_at' => $cancelledAt]);

            return;
        }

        // Deliveries can arrive out of order. Once cancelled, a late or
        // replayed event must not un-cancel the booking or reset read_at.
        if ($booking->cancelled_at !== null) {
            return;
        }

        $booking->fill([...$this->attributes($data), 'cancelled_at' => $cancelledAt]);

        if ($cancelledAt !== null) {
            $booking->forceFill(['read_at' => null]);
        }

        $booking->save();
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function cancelledAt(string $event, array $data): ?CarbonImmutable
    {
        if (isset($data['cancelled_at'])) {
            return $this->time($data['cancelled_at']);
        }

        return $event === StoreChronosBookingRequest::EVENT_CANCELLED ? CarbonImmutable::now() : null;
    }

    /**
     * @param  array<string, string|null>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'uuid' => $data['uuid'],
            'type' => $data['type'],
            'starts_at' => $this->time((string) $data['starts_at']),
            'ends_at' => $this->time((string) $data['ends_at']),
            'guest_name' => $data['guest_name'],
            'guest_email' => $data['guest_email'],
            'guest_phone' => $data['guest_phone'] ?? null,
            'guest_note' => $data['guest_note'] ?? null,
            'guest_timezone' => $data['guest_timezone'],
            'locale' => $data['locale'],
            'booked_at' => $this->time((string) $data['created_at']),
        ];
    }

    private function time(string $value): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(StoreChronosBookingRequest::TIME_FORMAT, $value, 'UTC')
            ?: throw new \InvalidArgumentException("Unparseable time: {$value}");
    }
}
