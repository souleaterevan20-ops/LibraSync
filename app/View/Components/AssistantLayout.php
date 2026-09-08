<?php

namespace App\View\Components;

use App\Models\BorrowRecord;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class AssistantLayout extends Component
{
    public int $unreadNotifications;
    public int $unreadMessages;
    public int $pendingRegistrations;
    public int $pendingBorrowRequests;
    public int $overdueReturns;

    public function __construct(
        public ?string $title = 'Library Staff Dashboard',
        ?int $unreadNotifications = null,
    ) {
        $this->unreadNotifications = $unreadNotifications ?? (Auth::check() ? Auth::user()->unreadNotifications()->count() : 0);
        $this->unreadMessages = Auth::check() ? Message::where('recipient_id', Auth::id())->where('is_read', false)->count() : 0;
        $this->pendingRegistrations = User::where('is_verified', false)->count();
        $this->pendingBorrowRequests = BorrowRecord::where('status', 'pending')->count();
        $this->overdueReturns = BorrowRecord::where('status', 'borrowed')->where('due_at', '<', now())->count();
    }

    public function render(): View
    {
        return view('layouts.assistant');
    }
}
