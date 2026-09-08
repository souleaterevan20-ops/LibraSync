<?php

namespace App\Providers;

use App\Models\BorrowRecord;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Live circulation counts for the sidebar badges, shared with both the
        // Super Admin and Library Staff/Assistant layouts on every page — not just
        // the dashboard — so the numbers are never stale or missing.
        View::composer(['layouts.admin', 'layouts.assistant'], function ($view) {
            if (! Auth::check()) {
                return;
            }

            $pendingRegistrations = User::where('is_verified', false)->count();
            $pendingBorrowRequests = BorrowRecord::where('status', 'pending')->count();
            $overdueReturns = BorrowRecord::where('status', 'borrowed')->where('due_at', '<', now())->count();

            $view->with([
                'pendingRegistrations' => $pendingRegistrations,
                'pendingBorrowRequests' => $pendingBorrowRequests,
                'overdueReturns' => $overdueReturns,
                'totalCirculationAttention' => $pendingRegistrations + $pendingBorrowRequests + $overdueReturns,
            ]);
        });
    }
}