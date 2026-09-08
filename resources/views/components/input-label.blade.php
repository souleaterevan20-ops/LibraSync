@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-bark-700 dark:text-bark-200']) }}>
    {{ $value ?? $slot }}
</label>
