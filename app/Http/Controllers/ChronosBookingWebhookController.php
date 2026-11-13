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
        /** @var array<string, string|null> $booking */
        $booking = $request->validated('booking');

        $record->handle($request->string('event')->toString(), $booking);

        return response()->noContent();
    }
}
