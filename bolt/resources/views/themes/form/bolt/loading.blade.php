<div>
    <div class="bolt-loading"></div>
    @teleport('.bolt-loading')
        <div wire:loading class="px-4">
            @svg('heroicon-o-document-text', 'text-primary-600 w-8 h-8 animate-spin')
        </div>
    @endteleport
</div>
