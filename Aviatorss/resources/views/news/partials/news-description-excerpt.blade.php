@php
    use Illuminate\Support\Str;

    $raw = trim((string) ($description ?? ''));
    $max = (int) ($limit ?? 200);
    $moreUrl = isset($url) ? trim((string) $url) : '';

    $nonSpace = static function (string $c): bool {
        return $c !== '' && !preg_match('/\s/u', $c);
    };

    if ($raw === '') {
        $plain = '';
        $body = '';
        $truncated = false;
        $showEllipsisLink = false;
    } else {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
        if ($plain === '') {
            $body = '';
            $truncated = false;
            $showEllipsisLink = false;
        } else {
            $len = Str::length($plain);
            if ($len <= $max) {
                $body = $plain;
                $truncated = false;
                $showEllipsisLink = false;
            } else {
                $end = $max;
                if ($end < $len) {
                    $lastInChunk = Str::substr($plain, $end - 1, 1);
                    $firstAfter = Str::substr($plain, $end, 1);
                    if ($nonSpace($lastInChunk) && $nonSpace($firstAfter)) {
                        while ($end < $len && $nonSpace(Str::substr($plain, $end, 1))) {
                            $end++;
                        }
                    }
                }
                $body = Str::substr($plain, 0, $end);
                $truncated = Str::length($plain) > Str::length($body);
                $showEllipsisLink = $truncated && $moreUrl !== '';
            }
        }
    }
@endphp
@if($body !== '')
    {{ $body }}@if($showEllipsisLink)<a href="{{ $moreUrl }}" class="ml-0.5 inline font-medium whitespace-nowrap text-blue-600 hover:text-blue-800 hover:underline" title="Подробнее" aria-label="Подробнее">...</a>@elseif($truncated)<span class="ml-0.5">...</span>@endif
@endif
