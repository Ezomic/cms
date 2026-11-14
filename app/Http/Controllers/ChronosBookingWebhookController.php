<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Bookings\RecordChronosBooking;
use App\Http\Requests\StoreChronosBookingRequest;
use Illuminate\Http\Response;

class ChronosBookingWebhookController extends Controller
{
    public function __invoke(StoreChronosBookingRequest $request, RecordChronosBooking $record): Response
    {
        /** @var array{event: string, booking: array<string, string|null>} $payload */
        $payload = $request->validated();

        $record->handle($payload['event'], $payload['booking']);

        return response()->noContent();
    }
}
