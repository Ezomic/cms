<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreChronosBookingRequest extends FormRequest
{
    public const TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    public const EVENT_CREATED = 'booking.created';

    public const EVENT_CANCELLED = 'booking.cancelled';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $time = 'date_format:'.self::TIME_FORMAT;

        return [
            'event' => ['required', 'string', Rule::in([self::EVENT_CREATED, self::EVENT_CANCELLED])],
            'booking' => ['required', 'array'],
            'booking.uuid' => ['required', 'string', 'uuid'],
            'booking.type' => ['required', 'string', 'max:255'],
            'booking.starts_at' => ['required', 'string', $time],
            'booking.ends_at' => ['required', 'string', $time],
            'booking.guest_name' => ['required', 'string', 'max:255'],
            'booking.guest_email' => ['required', 'string', 'email', 'max:255'],
            'booking.guest_phone' => ['nullable', 'string', 'max:255'],
            'booking.guest_note' => ['nullable', 'string', 'max:10000'],
            'booking.guest_timezone' => ['required', 'string', 'max:64'],
            'booking.locale' => ['required', 'string', 'max:16'],
            'booking.cancelled_at' => ['nullable', 'string', $time],
            'booking.created_at' => ['required', 'string', $time],
        ];
    }

    /**
     * Only the JSON body is signed, so only the JSON body is validated: the
     * query string is never merged in, and a missing Content-Type header does
     * not turn a valid delivery into an empty one.
     *
     * @return array<array-key, mixed>
     */
    public function validationData(): array
    {
        return $this->json()->all();
    }

    /**
     * The sender is a server, not a browser: it gets a 422 whatever Accept
     * header it sent, never the redirect a form post would get.
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
