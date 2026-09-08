<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')->orderByDesc('posted_at')->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:3000'],
            'priority' => ['nullable', 'in:normal,important,urgent'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video' => ['nullable', 'mimes:mp4,webm', 'max:51200'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $announcement = Announcement::create([
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'message' => $validated['message'] ?? null,
            'priority' => $validated['priority'] ?? Announcement::PRIORITY_NORMAL,
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('announcements', 'public') : null,
            'video_path' => $request->hasFile('video') ? $request->file('video')->store('announcements', 'public') : null,
            'posted_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        AuditLogger::log('Posted Announcement', $announcement, "\"{$announcement->title}\" posted to all dashboards.");

        // Spec #33: "Only IMPORTANT/URGENT announcements should trigger
        // external SMS/email by default. This prevents spam." Normal-
        // priority announcements only ever show in-app (the dashboard
        // banner), which already exists and is untouched by this.
        if ($announcement->shouldNotifyExternally()) {
            Notifier::sendExternalToRole(
                ['student', 'teacher'],
                $announcement->title,
                $announcement->message ?: 'A new announcement has been posted. Open LibraSync to view it.',
                route('announcements.show', $announcement)
            );
        }

        return redirect()->back()->with('status', "Announcement posted successfully. \"{$announcement->title}\" is now live on every user's dashboard.");
    }

    public function destroy(Announcement $announcement)
    {
        $title = $announcement->title;
        AuditLogger::log('Deleted Announcement', $announcement, "Removed \"{$title}\".");
        $announcement->delete();

        return redirect()->back()->with('status', "Announcement \"{$title}\" has been removed.");
    }
}
