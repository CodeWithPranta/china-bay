@props(['title' => '', 'accent' => '', 'link' => null, 'linkText' => 'View All →'])
<div class="flex items-center justify-between">
    <h2 class="h2">{!! $title !!} @if ($accent)<span>{{ $accent }}</span>@endif</h2>
    @if ($link)
        <a href="{{ $link }}" class="text-xs font-medium text-brand hover:underline">{{ $linkText }}</a>
    @endif
</div>
