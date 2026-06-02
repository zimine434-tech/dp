@php
    use Stevebauman\Purify\Facades\Purify;

    $htmlContent = $html ?? '';
    $clean = filled($htmlContent) ? Purify::clean($htmlContent) : '';
    $extraClass = $class ?? '';
@endphp
@if($clean !== '')
    <div @class(['rich-text max-w-none text-gray-700 [&_p]:my-2 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0 [&_ul]:my-2 [&_ol]:my-2 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:my-0.5 [&_a]:text-blue-600 [&_a]:underline [&_strong]:font-semibold [&_h1]:text-xl [&_h1]:font-bold [&_h2]:text-lg [&_h2]:font-semibold', $extraClass])>
        {!! $clean !!}
    </div>
@endif
