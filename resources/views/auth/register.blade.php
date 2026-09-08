<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-theme-init />
    <title>Create Your Account - {{ config('app.name', 'LibraSync') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-parchment-100 dark:bg-bark-950 transition-colors">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-[380px_1fr]">

        <!-- Brand / how-it-works panel -->
        <div class="hidden lg:flex flex-col relative overflow-hidden bg-gradient-to-b from-maroon-900 to-bark-900 text-parchment-100 px-8 py-10">
            <a href="{{ route('login') }}" class="flex items-center gap-2 mb-8">
                <svg class="w-8 h-8 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <div>
                    <div class="text-lg font-bold">Libra<span class="text-amber-300">Sync</span></div>
                    <div class="text-[9.5px] tracking-widest text-maroon-300 uppercase -mt-1">E-Library Management System</div>
                </div>
            </a>

            <h1 class="text-2xl font-bold leading-snug">Welcome to Libra<span class="text-amber-300">Sync</span>!</h1>
            <h2 class="text-lg font-semibold text-amber-200 mb-3">Self-Registration</h2>
            <p class="text-sm text-maroon-200/90 leading-relaxed mb-8">
                Create your account to access the library system. All registrations are subject to verification and approval by the Super Admin.
            </p>

            <h3 class="text-sm font-semibold text-amber-200 mb-4">How it works</h3>
            <div class="space-y-4 mb-8">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0 text-sm font-bold">1</div>
                    <div>
                        <div class="text-sm font-semibold">Create Account</div>
                        <div class="text-xs text-maroon-300">Fill out the registration form with your information.</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0 text-sm font-bold">2</div>
                    <div>
                        <div class="text-sm font-semibold">Verification</div>
                        <div class="text-xs text-maroon-300">Your account will be reviewed and verified by the Super Admin.</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0 text-sm font-bold">3</div>
                    <div>
                        <div class="text-sm font-semibold">Get Approved</div>
                        <div class="text-xs text-maroon-300">You'll be notified once your account is approved.</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0 text-sm font-bold">4</div>
                    <div>
                        <div class="text-sm font-semibold">Start Using LibraSync</div>
                        <div class="text-xs text-maroon-300">Log in and enjoy borrowing books and other library features!</div>
                    </div>
                </div>
            </div>

            <div class="mt-auto bg-white/5 border border-white/10 rounded-xl p-4 flex gap-3">
                <svg class="w-6 h-6 text-amber-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                <div>
                    <div class="font-semibold text-sm">Important Note</div>
                    <div class="text-xs text-maroon-300 mt-0.5">Your account will be locked and inactive until approved by the Super Admin.</div>
                </div>
            </div>
        </div>

        <!-- Form panel -->
        <div class="px-4 sm:px-8 py-8" x-data="registrationWizard()">

            <div class="flex items-center justify-between mb-6">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-bark-500 dark:text-bark-400 hover:text-maroon-700 dark:hover:text-amber-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    Back to Login
                </a>
                <x-dark-mode-toggle />
            </div>

            <div class="max-w-4xl">
                <h1 class="text-2xl font-bold text-bark-800 dark:text-parchment-100">Create Your Account</h1>
                <p class="text-bark-500 dark:text-bark-400 text-sm mt-1 mb-6">Select your role and fill out the form to get started</p>

                <!-- Stepper -->
                <div class="flex items-center mb-8">
                    <template x-for="(label, i) in ['Choose Role', 'Personal Information', 'Review & Submit']" :key="i">
                        <div class="flex items-center flex-1 last:flex-none">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                                     :class="step > i+1 ? 'bg-emerald-500 text-white' : (step === i+1 ? 'bg-maroon-700 text-white' : 'bg-bark-200 dark:bg-bark-700 text-bark-500 dark:text-bark-300')">
                                    <span x-show="step <= i+1" x-text="i+1"></span>
                                    <svg x-show="step > i+1" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </div>
                                <span class="text-xs font-medium mt-1.5 text-bark-500 dark:text-bark-400" x-text="label"></span>
                            </div>
                            <div class="flex-1 h-px bg-bark-200 dark:bg-bark-700 mx-2 last:hidden"></div>
                        </div>
                    </template>
                </div>

                @if ($errors->any())
                    <div class="mb-6 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-lg px-4 py-3 text-sm">
                        Please fix the following before continuing:
                        <ul class="list-disc list-inside mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="role" x-model="role">

                    <!-- Step 1: Choose Role -->
                    <div x-show="step === 1">
                        <h2 class="text-sm font-semibold text-maroon-700 dark:text-amber-300 uppercase tracking-wide mb-3">Step 1: Choose Your Role</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <label class="relative flex gap-4 p-5 rounded-xl border-2 cursor-pointer transition-colors"
                                   :class="role === 'student' ? 'border-maroon-600 bg-maroon-50 dark:bg-maroon-900/20' : 'border-bark-200 dark:border-bark-700'">
                                <input type="radio" value="student" x-model="role" class="absolute top-4 right-4 text-maroon-600 focus:ring-maroon-500">
                                <div class="w-12 h-12 rounded-full bg-maroon-100 dark:bg-maroon-900/40 text-maroon-700 dark:text-maroon-300 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443" /></svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-bark-800 dark:text-parchment-100">I am a Student</div>
                                    <div class="text-xs text-bark-500 dark:text-bark-400 mt-1">Choose this option if you are currently enrolled as a student.</div>
                                </div>
                            </label>

                            <label class="relative flex gap-4 p-5 rounded-xl border-2 cursor-pointer transition-colors"
                                   :class="role === 'teacher' ? 'border-maroon-600 bg-maroon-50 dark:bg-maroon-900/20' : 'border-bark-200 dark:border-bark-700'">
                                <input type="radio" value="teacher" x-model="role" class="absolute top-4 right-4 text-maroon-600 focus:ring-maroon-500">
                                <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-bark-800 dark:text-parchment-100">I am a Teacher</div>
                                    <div class="text-xs text-bark-500 dark:text-bark-400 mt-1">Choose this option if you are a faculty or teaching staff.</div>
                                </div>
                            </label>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" @click="goNext()" class="inline-flex items-center gap-2 bg-maroon-700 hover:bg-maroon-800 text-white font-semibold px-5 py-2.5 rounded-lg transition-colors">
                                Continue
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Personal Information -->
                    <div x-show="step === 2">
                        <h2 class="text-sm font-semibold text-maroon-700 dark:text-amber-300 uppercase tracking-wide mb-3">Step 2: Personal Information</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter your full name"
                                    class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Enter your email address"
                                    class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Contact Number *</label>
                                <input type="text" name="contact_number" value="{{ old('contact_number') }}" required
                                    inputmode="numeric" pattern="[0-9]{7,15}" maxlength="15"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    oninvalid="this.setCustomValidity('Contact Number must contain numbers only.')"
                                    onchange="this.setCustomValidity('')" onkeyup="this.setCustomValidity('')"
                                    placeholder="09xxxxxxxxxx"
                                    class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                                <p class="text-[11px] text-bark-400 mt-1">Numbers only, no letters or symbols.</p>
                            </div>

                            <!-- Student-only fields -->
                            <template x-if="role === 'student'">
                                <div>
                                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Department *</label>
                                    <select name="department" x-model="department" @change="courseProgram = ''" :required="role === 'student'"
                                        class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                                        <option value="">Select department</option>
                                        <option value="College of Education" @selected(old('department') === 'College of Education')>College of Education</option>
                                        <option value="College of Criminology" @selected(old('department') === 'College of Criminology')>College of Criminology</option>
                                        <option value="College of Computer Studies" @selected(old('department') === 'College of Computer Studies')>College of Computer Studies</option>
                                        <option value="School of Hospitality and Tourism Management" @selected(old('department') === 'School of Hospitality and Tourism Management')>School of Hospitality and Tourism Management</option>
                                    </select>
                                </div>
                            </template>
                            <template x-if="role === 'student'">
                                <div>
                                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Course / Program *</label>
                                    <select name="course_program" x-model="courseProgram" :disabled="!department" :required="role === 'student'"
                                        class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">{{ old('department') ? 'Select course / program' : 'Select a department first' }}</option>
                                        <template x-for="course in coursesByDepartment[department] || []" :key="course">
                                            <option :value="course" x-text="course" :selected="course === courseProgram"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="role === 'student'">
                                <div>
                                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">School ID *</label>
                                    <input type="text" name="school_or_employee_id" value="{{ old('school_or_employee_id') }}" :required="role === 'student'"
                                        inputmode="numeric" pattern="[0-9]+" maxlength="20"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        oninvalid="this.setCustomValidity('School ID must contain numbers only.')"
                                        onchange="this.setCustomValidity('')" onkeyup="this.setCustomValidity('')"
                                        placeholder="Enter your School ID"
                                        class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                                    <p class="text-[11px] text-bark-400 mt-1">Numbers only. Leading zeroes (e.g. 00123456) are kept as-is.</p>
                                </div>
                            </template>
                            <template x-if="role === 'student'">
                                <div>
                                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Year Level *</label>
                                    <select name="year_level_position" :required="role === 'student'"
                                        class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                                        <option value="">Select year level</option>
                                        @foreach($yearLevels ?? ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $level)
                                            <option value="{{ $level }}" @selected(old('year_level_position') === $level)>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </template>

                            <!-- Teacher-only field -->
                            <template x-if="role === 'teacher'">
                                <div>
                                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Employee ID *</label>
                                    <input type="text" name="school_or_employee_id" value="{{ old('school_or_employee_id') }}" :required="role === 'teacher'"
                                        inputmode="numeric" pattern="[0-9]+" maxlength="20"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        oninvalid="this.setCustomValidity('Employee ID must contain numbers only.')"
                                        onchange="this.setCustomValidity('')" onkeyup="this.setCustomValidity('')"
                                        placeholder="Enter your Employee ID"
                                        class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                                    <p class="text-[11px] text-bark-400 mt-1">Numbers only. Leading zeroes are kept as-is.</p>
                                </div>
                            </template>

                            <div>
                                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Password *</label>
                                <input type="password" name="password" required placeholder="Create a password"
                                    class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Confirm Password *</label>
                                <input type="password" name="password_confirmation" required placeholder="Confirm your password"
                                    class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <button type="button" @click="step = 1" class="inline-flex items-center gap-2 border border-bark-200 dark:border-bark-700 text-bark-700 dark:text-parchment-100 font-medium px-5 py-2.5 rounded-lg hover:bg-parchment-200 dark:hover:bg-bark-800">
                                Back
                            </button>
                            <button type="button" @click="goNext()" class="inline-flex items-center gap-2 bg-maroon-700 hover:bg-maroon-800 text-white font-semibold px-5 py-2.5 rounded-lg transition-colors">
                                Continue
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Review & Submit -->
                    <div x-show="step === 3">
                        <h2 class="text-sm font-semibold text-maroon-700 dark:text-amber-300 uppercase tracking-wide mb-3">Step 3: Review &amp; Submit</h2>
                        <div class="bg-white dark:bg-bark-900 border border-bark-200 dark:border-bark-800 rounded-xl p-5 mb-4 text-sm">
                            <p class="text-bark-500 dark:text-bark-400">You're about to register as a <span class="font-semibold text-bark-800 dark:text-parchment-100" x-text="role || '—'"></span>. Please double check Step 2 before submitting &mdash; your account will remain locked until the Super Admin verifies it.</p>
                        </div>
                        <label class="flex items-center gap-2 mb-6">
                            <input type="checkbox" name="terms" required class="rounded border-bark-300 text-maroon-600 focus:ring-maroon-500">
                            <span class="text-sm text-bark-600 dark:text-bark-300">I agree to the <a href="{{ route('legal.terms') }}" target="_blank" class="text-maroon-600 dark:text-maroon-400 hover:underline">Terms of Use</a> and <a href="{{ route('legal.privacy') }}" target="_blank" class="text-maroon-600 dark:text-maroon-400 hover:underline">Privacy Policy</a>.</span>
                        </label>
                        <div class="flex justify-between">
                            <button type="button" @click="step = 2" class="inline-flex items-center gap-2 border border-bark-200 dark:border-bark-700 text-bark-700 dark:text-parchment-100 font-medium px-5 py-2.5 rounded-lg hover:bg-parchment-200 dark:hover:bg-bark-800">
                                Back
                            </button>
                            <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 bg-maroon-700 hover:bg-maroon-800 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-lg transition-colors">
                                <span x-show="!submitting">Submit Registration</span>
                                <span x-show="submitting">Submitting&hellip;</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function registrationWizard() {
            return {
                step: 1,
                role: '{{ old("role", "") }}',
                department: '{{ old("department", "") }}',
                courseProgram: '{{ old("course_program", "") }}',
                submitting: false,
                coursesByDepartment: {
                    'College of Education': ['BSED'],
                    'College of Criminology': ['FB', 'LD', 'ID', 'QD'],
                    'College of Computer Studies': ['BSIT', 'ACT'],
                    'School of Hospitality and Tourism Management': ['HM', 'TM'],
                },
                goNext() {
                    if (this.step === 1 && !this.role) {
                        alert('Please choose a role to continue.');
                        return;
                    }
                    if (this.step === 2) {
                        const panel = this.$root.querySelector('[x-show="step === 2"]');
                        const inputs = panel.querySelectorAll('input[required], select[required]');
                        for (const input of inputs) {
                            if (!input.reportValidity()) return;
                        }
                        const pass = panel.querySelector('input[name="password"]').value;
                        const confirm = panel.querySelector('input[name="password_confirmation"]').value;
                        if (pass !== confirm) {
                            alert('Passwords do not match.');
                            return;
                        }
                    }
                    this.step++;
                }
            }
        }
    </script>
</body>
</html>
