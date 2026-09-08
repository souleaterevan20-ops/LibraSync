<?php

namespace App\View\Components;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class AnnouncementBanner extends Component
{
    public $announcements;

    public function __construct()
    {
        if (! Auth::check()) {
            $this->announcements = collect();
            return;
        }

        $dismissedIds = Auth::user()->announcementDismissals()->pluck('announcement_id');

        $this->announcements = Announcement::active()
            ->whereNotIn('id', $dismissedIds)
            ->orderByDesc('posted_at')
            ->get();
    }

    public function render(): View
    {
        return view('components.announcement-banner');
    }
}
