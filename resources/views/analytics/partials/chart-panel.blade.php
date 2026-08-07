@if(isset($image) && $image)
<div class="{{ $boxClass ?? 'overflow-hidden rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group' }}"
     @click="openModal('{{ $image['url'] }}', '{{ $modal ?? '' }}')">
    <img
        src="{{ $image['url'] }}"
        alt="{{ $alt ?? '' }}"
        class="{{ $imageClass ?? 'w-full h-auto object-contain max-h-[500px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200' }}"
    >
</div>
@endif