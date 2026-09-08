<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Assistant\DashboardController as AssistantDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return match (Auth::user()->role) {
            'super_admin' => app(AdminDashboardController::class)->index(),
            'student_assistant' => app(AssistantDashboardController::class)->index(),
            default => app(StudentDashboardController::class)->index(), // student, teacher
        };
    }
}
