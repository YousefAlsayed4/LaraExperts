<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}').defer }">
        @include('form::filament.resources.response-resource.pages.show-entry')
    </div>
</x-dynamic-component>