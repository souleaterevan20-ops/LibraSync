<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Year levels offered for students (spec #75). Kept as a single source
     * of truth so the backend "in:" rule and anything else that needs the
     * list (e.g. an API endpoint later) can't drift from each other.
     */
    public const YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', ['yearLevels' => self::YEAR_LEVELS]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $isStudent = $request->input('role') === 'student';

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:student,teacher'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],

            // Spec #73: Contact Number — digits only, both ends. The frontend
            // strips non-digits as the user types, but the backend is the
            // real gate since client-side checks can always be bypassed.
            'contact_number' => ['required', 'string', 'regex:/^[0-9]+$/', 'digits_between:7,15'],

            'department' => [$isStudent ? 'required' : 'nullable', 'string', 'in:College of Education,College of Criminology,College of Computer Studies,School of Hospitality and Tourism Management'],
            'course_program' => [$isStudent ? 'required' : 'nullable', 'string', 'in:BSIT,ACT,TM,HM,BSED,LD,FB,ID,QD'],

            // Spec #74: School/Employee ID — digits only, but validated (not
            // cast) as a string so values with leading zeroes such as
            // "00123456" are preserved exactly as entered. The column is
            // already a VARCHAR, so nothing here converts it to an integer.
            'school_or_employee_id' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],

            // Spec #75: Year Level — students must pick one of the school's
            // actual year levels; teachers don't submit this field at all
            // (their form step never renders it), so it stays nullable here.
            'year_level_position' => [
                $isStudent ? 'required' : 'nullable',
                'string',
                $isStudent ? Rule::in(self::YEAR_LEVELS) : 'max:100',
            ],

            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'contact_number.regex' => 'Contact Number must contain numbers only.',
            'contact_number.digits_between' => 'Contact Number must be between 7 and 15 digits.',
            'school_or_employee_id.regex' => 'School/Employee ID must contain numbers only.',
            'year_level_position.in' => 'Please select a valid Year Level.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'contact_number' => $validated['contact_number'],
            'department' => $isStudent ? $validated['department'] : null,
            'course_program' => $isStudent ? $validated['course_program'] : null,
            'school_or_employee_id' => $validated['school_or_employee_id'],
            'year_level_position' => $isStudent ? $validated['year_level_position'] : null,
            'is_verified' => false, // Locked out until the Super Admin verifies the account.
        ]);

        event(new Registered($user));

        \App\Services\Notifier::sendToRole(
            ['super_admin', 'student_assistant'],
            'registration',
            'New registration submitted',
            "{$user->name} ({$user->role}) submitted a registration and is awaiting approval.",
            route('admin.users.index')
        );

        return redirect()->route('login')->with('status', 'Your account was created and is pending Super Admin approval. You will be able to log in once it is verified.');
    }
}
