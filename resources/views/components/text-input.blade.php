@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 rounded-md shadow-sm']) }}>
