<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementDismissal;
use Illuminate\Support\Facades\Auth;

class AnnouncementDismissController extends Controller
{
    public function show(Announcement $announcement)
    {
        return view('announcements.show', compact('announcement'));
    }

    public function store(Announcement $announcement)
    {
        AnnouncementDismissal::firstOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => Auth::id()],
            ['dismissed_at' => now()]
        );

        return redirect()->back();
    }
}
