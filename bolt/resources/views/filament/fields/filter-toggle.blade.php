<!-- resources/views/form/filament/fields/filter-toggle-dropdown.blade.php -->
<div x-data="{ open: false }" class="relative inline-block">
    <!-- Trigger Button -->
    <button
        @click="open = !open"
        type="button"
        class="filament-icon-button relative flex items-center justify-center h-10 w-10 rounded-full hover:bg-gray-100"
        x-bind:aria-expanded="open"
        aria-label="{{ __('Filters') }}"
    >
        <x-filament::icon
            icon="heroicon-o-adjustments-horizontal"
            class="w-5 h-5 text-gray-500"
        />
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-10 mt-0 w-64 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        style="right: 0px; top: 100%;"
    >
        <div class="py-2 max-h-64 overflow-y-auto">
            @foreach($filters as $filter)
                @if(!$filter->isHidden())
                    <div class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:click="toggleFilter('{{ $filter->getName() }}')"
                            @checked(in_array($filter->getName(), $activeFilterNames))
                            class="mr-2 rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                        >
                        <span>{{ $filter->getLabel() }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
