<?php

namespace App\View\Components;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public int $unreadNotifications;
    public int $pendingRegistrations;
    public int $unreadMessages;

    public function __construct(
        public ?string $title = 'Super Admin Dashboard',
        ?int $unreadNotifications = null,
        ?int $pendingRegistrations = null,
    ) {
        $this->unreadNotifications = $unreadNotifications ?? (Auth::check() ? Auth::user()->unreadNotifications()->count() : 0);
        $this->pendingRegistrations = $pendingRegistrations ?? (Auth::check() ? \App\Models\User::where('is_verified', false)->count() : 0);
        $this->unreadMessages = Auth::check() ? Message::where('recipient_id', Auth::id())->where('is_read', false)->count() : 0;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
