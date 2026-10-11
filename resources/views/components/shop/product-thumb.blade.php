@props(['product', 'class' => 'size-10'])
@if (! empty($product['image']))
    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" class="{{ $class }} shrink-0 rounded-lg bg-slate-50 object-cover">
@else
    <span class="{{ $class }} grid shrink-0 place-items-center rounded-lg bg-slate-50 text-xl" aria-hidden="true">{{ $product['emoji'] ?? '' }}</span>
@endif
