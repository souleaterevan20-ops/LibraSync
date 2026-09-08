<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * List every staff member (Super Admins + Library Staffs, excluding
     * yourself) with a last-message preview and unread count, newest first.
     */
    public function index()
    {
        $me = Auth::user();

        $contacts = User::whereIn('role', ['super_admin', 'student_assistant'])
            ->where('id', '!=', $me->id)
            ->get()
            ->map(function (User $contact) use ($me) {
                $lastMessage = Message::where(function ($q) use ($me, $contact) {
                    $q->where('sender_id', $me->id)->where('recipient_id', $contact->id);
                })->orWhere(function ($q) use ($me, $contact) {
                    $q->where('sender_id', $contact->id)->where('recipient_id', $me->id);
                })->latest()->first();

                $unread = Message::where('sender_id', $contact->id)
                    ->where('recipient_id', $me->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'user' => $contact,
                    'last_message' => $lastMessage,
                    'unread' => $unread,
                ];
            })
            ->sortByDesc(fn ($c) => $c['last_message']?->created_at ?? $c['user']->created_at)
            ->values();

        return view('chat.index', compact('contacts'));
    }

    /**
     * Open a thread with a specific contact and mark their messages to me as read.
     */
    public function show(User $contact)
    {
        $this->authorizeContact($contact);

        $me = Auth::user();

        $messages = Message::where(function ($q) use ($me, $contact) {
            $q->where('sender_id', $me->id)->where('recipient_id', $contact->id);
        })->orWhere(function ($q) use ($me, $contact) {
            $q->where('sender_id', $contact->id)->where('recipient_id', $me->id);
        })->orderBy('created_at')->get();

        Message::where('sender_id', $contact->id)
            ->where('recipient_id', $me->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('chat.show', compact('contact', 'messages'));
    }

    /**
     * Poll for new messages (used by the auto-refreshing thread view) and
     * mark incoming ones as read. Returns a rendered partial.
     */
    public function poll(User $contact)
    {
        $this->authorizeContact($contact);

        $me = Auth::user();

        $messages = Message::where(function ($q) use ($me, $contact) {
            $q->where('sender_id', $me->id)->where('recipient_id', $contact->id);
        })->orWhere(function ($q) use ($me, $contact) {
            $q->where('sender_id', $contact->id)->where('recipient_id', $me->id);
        })->orderBy('created_at')->get();

        Message::where('sender_id', $contact->id)
            ->where('recipient_id', $me->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('chat.partials.messages', compact('messages', 'contact'));
    }

    public function store(Request $request, User $contact)
    {
        $this->authorizeContact($contact);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'],
        ]);

        if (blank($validated['body'] ?? null) && ! $request->hasFile('attachment')) {
            return redirect()->back()->with('error', 'Write a message or attach a file before sending.');
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentMime = $file->getClientMimeType();
            $attachmentPath = $file->store('chat-attachments', 'public');
        }

        Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $contact->id,
            'body' => $validated['body'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
        ]);

        return redirect()->route('chat.show', $contact);
    }

    /**
     * Only Super Admins and Library Staffs may message each other —
     * Students/Teachers never reach this controller (route middleware
     * already blocks them), and staff may only message other staff.
     */
    private function authorizeContact(User $contact): void
    {
        if (! in_array($contact->role, ['super_admin', 'student_assistant'], true) || $contact->id === Auth::id()) {
            abort(403);
        }
    }
}
