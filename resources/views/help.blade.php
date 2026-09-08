@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="Help &amp; Support">

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-1">Library Operating Hours</h2>
            <p class="text-xs text-bark-400 mb-4">Have a concern, question, or request? Please visit the school library in person during these hours — Students and Teachers don't have access to the internal staff chat.</p>

            <div class="divide-y divide-bark-100 dark:divide-bark-800">
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-bark-700 dark:text-bark-200">Monday – Friday</span>
                    <span class="text-sm font-semibold text-bark-800 dark:text-parchment-100">8:00 AM – 4:00 PM</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-bark-700 dark:text-bark-200">Saturday</span>
                    <span class="text-sm font-semibold text-bark-800 dark:text-parchment-100">8:00 AM – 12:00 NN</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-bark-700 dark:text-bark-200">Sunday</span>
                    <span class="text-sm font-semibold text-rose-600">Closed</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-2">Need Help?</h2>
            <p class="text-sm text-bark-600 dark:text-bark-300 leading-relaxed">
                For borrowing questions, penalties, or account issues, our library staff at the
                circulation desk can help you during operating hours. Check the <a href="{{ route('about.index') }}" class="text-maroon-700 dark:text-maroon-300 font-semibold underline">About System</a> page
                for more information about LibraSync itself.
            </p>
        </div>
    </div>
</x-dynamic-component>
