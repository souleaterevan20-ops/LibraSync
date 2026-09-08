<?php

namespace App\Http\Controllers;

use App\Models\LibraryCommitteeMember;
use App\Models\LibrarySetting;
use App\Models\LibraryStaffDirectory;

class AboutController extends Controller
{
    public function index()
    {
        $settings = LibrarySetting::current();
        $committee = LibraryCommitteeMember::active()->ordered()->get();
        $staff = LibraryStaffDirectory::active()->ordered()->get();

        return view('about', compact('settings', 'committee', 'staff'));
    }
}
