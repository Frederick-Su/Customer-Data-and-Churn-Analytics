@if(isset($image) && $image)
<div class="{{ $boxClass ?? 'overflow-hidden rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-2 cursor-pointer group' }}"
     @click="openModal('{{ $image['url'] }}', '{{ $modal ?? '' }}')">
    <img
        src="{{ $image['url'] }}"
        alt="{{ $alt ?? '' }}"
        class="{{ $imageClass ?? 'w-full h-auto object-contain max-h-[500px] mx-auto rounded-sm' }}"
    >
</div>
@endif