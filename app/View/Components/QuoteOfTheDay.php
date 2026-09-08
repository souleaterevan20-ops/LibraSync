<?php

namespace App\View\Components;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class QuoteOfTheDay extends Component
{
    public ?string $quote = null;

    public function __construct()
    {
        // Only show a quote when there are no active announcements system-wide.
        if (Announcement::active()->exists()) {
            return;
        }

        $quotes = config('quotes', []);

        if (empty($quotes)) {
            return;
        }

        // Deterministic per user, per day: same quote all day for this user,
        // a different quote for other users, and a new one tomorrow — with
        // no database writes needed at all.
        $userId = Auth::id() ?? 0;
        $dayOfYear = (int) now()->format('z'); // 0-365, resets each year
        $index = ($userId + $dayOfYear) % count($quotes);

        $this->quote = $quotes[$index];
    }

    public function render(): View
    {
        return view('components.quote-of-the-day');
    }
}
