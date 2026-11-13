<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Read only on purpose: bookings are owned by Chronos, which pushes them here.
 * Cancelling and editing happen there. Read state is the only thing the CMS
 * owns, so it is the only thing this controller writes.
 */
class BookingController extends Controller
{
    private const STATES = ['all', 'upcoming', 'past', 'cancelled'];

    private const DISPLAY_TIMEZONE = 'Europe/Amsterdam';

    public function index(Request $request): InertiaResponse
    {
        $state = $request->string('state')->toString();

        if (! in_array($state, self::STATES, true)) {
            $state = 'all';
        }

        $bookings = $this->inState($state)
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Booking $b) => [
                'id' => $b->id,
                'name' => $b->guest_name,
                'email' => $b->guest_email,
                'phone' => $b->guest_phone,
                'note' => $b->guest_note,
                'type' => $b->type,
                'starts' => $b->starts_at->setTimezone(self::DISPLAY_TIMEZONE)->format('D j M Y, H:i'),
                'ends' => $b->ends_at->setTimezone(self::DISPLAY_TIMEZONE)->format('H:i'),
                'guest_timezone' => $b->guest_timezone,
                'locale' => $b->locale,
                'is_cancelled' => $b->cancelled_at !== null,
                'cancelled_when' => $b->cancelled_at?->diffForHumans(),
                'is_past' => $b->starts_at->isPast(),
                'is_read' => $b->read_at !== null,
                'received' => $b->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'filters' => ['state' => $state],
            // Not "counts": a page prop of that name would replace the shared
            // sidebar counts instead of merging with them.
            'totals' => [
                'all' => $this->inState('all')->count(),
                'upcoming' => $this->inState('upcoming')->count(),
                'past' => $this->inState('past')->count(),
                'cancelled' => $this->inState('cancelled')->count(),
            ],
        ]);
    }

    public function markRead(Booking $booking): RedirectResponse
    {
        $booking->forceFill(['read_at' => now()])->save();

        return back();
    }

    public function markUnread(Booking $booking): RedirectResponse
    {
        $booking->forceFill(['read_at' => null])->save();

        return back();
    }

    /**
     * @return Builder<Booking>
     */
    private function inState(string $state): Builder
    {
        return Booking::query()
            ->when($state === 'upcoming', fn (Builder $query) => $query->whereNull('cancelled_at')->where('starts_at', '>=', now()))
            ->when($state === 'past', fn (Builder $query) => $query->whereNull('cancelled_at')->where('starts_at', '<', now()))
            ->when($state === 'cancelled', fn (Builder $query) => $query->whereNotNull('cancelled_at'));
    }
}
