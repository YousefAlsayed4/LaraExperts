@php
    $colors = \Illuminate\Support\Arr::toCssStyles([
        \Filament\Support\get_color_css_variables($Form->options['primary_color'] ?? 'primary', shades: [50, 100, 200, 300, 400, 500, 600, 700, 800, 900]),
    ]);
@endphp

<div class="not-prose" style="{{ $colors }}">

    @if(!$inline)
        @if( (optional($Form->options)['logo'] === null && optional($Form->options)['cover'] === null))
            <x-slot name="header">
                <h2>{{ $Form->name ?? '' }}</h2>
                <p class="text-gray-400 text-mdd my-2">{{ $Form->description ?? '' }}</p>

                @if($Form->start_date !== null)
                    <div class="text-gray-400 text-sm">
                        @svg('heroicon-o-calendar','h-4 w-4 inline-flex')
                        <span>{{ __('Available from') }}:</span>
                        <span>{{ optional($Form->start_date)->format(\Filament\Infolists\Infolist::$defaultDateDisplayFormat) }}</span>,
                        <span>{{ __('to') }}:</span>
                        <span>{{ optional($Form->end_date)->format(\Filament\Infolists\Infolist::$defaultDateDisplayFormat) }}</span>
                    </div>
                @endif
            </x-slot>
        @endif

        <x-slot name="breadcrumbs">
            @if($Form->extensions === null)
                <li class="flex items-center">
                    <a href="{{ route('bolt.forms.list') }}">{{ __('Forms') }}</a>
                    @svg('heroicon-o-document-text','fill-current w-4 h-4 mx-3 rtl:rotate-180')
                </li>
            @else
                <li class="flex items-center">
                    <a href="{{ \LaraExperts\Bolt\Facades\Extensions::init($Form, 'route') }}">{{ \LaraExperts\Bolt\Facades\Extensions::init($Form, 'label') }}</a>
                    @svg('heroicon-o-document-text','fill-current w-4 h-4 mx-3 rtl:rotate-180')
                </li>
            @endif
            <li class="flex items-center">
                {{ $Form->name }}
            </li>
        </x-slot>
    @endif

    @if(!$inline)
        @include($boltTheme.'.loading')
    @endif

    @if(optional($Form->options)['logo'] !== null && optional($Form)->options['cover'] !== null)
        <div style="background-image: url('{{ \Illuminate\Support\Facades\Storage::disk(config('form-bolt.uploadDisk'))->url($Form->options['cover']) }}')"
             class="flex justify-start items-center px-4 py-6 gap-4 rounded-lg bg-clip-border bg-origin-border bg-cover bg-center">
            <div>
                <img
                    class="bg-white rounded-full shadow-md shadow-custom-100 sm:w-24 object-cover"
                    src="{{ \Illuminate\Support\Facades\Storage::disk(config('form-bolt.uploadDisk'))->url($Form->options['logo']) }}"
                    alt="logo"
                />
            </div>
            <div class="bg-white/40 p-4 space-y-1 rounded-lg w-full text-left">
                <h4 class="text-custom-600 text-2xl font-bold dark:text-white">
                    {{ $Form->name ?? '' }}
                </h4>
                @if(filled($Form->description))
                    <h5 class="text-custom-600 font-normal">
                        {{ $Form->description ?? '' }}
                    </h5>
                @endif
                @if($Form->start_date !== null)
                    <div class="text-custom-800 flex items-center justify-start gap-2 text-sm">
                        @svg('heroicon-o-calendar','h-5 w-5 inline-flex')
                        <span class="flex items-center justify-center gap-1">
                            <span>{{ __('Available from') }}:</span>
                            <span>{{ optional($Form->start_date)->format(\Filament\Infolists\Infolist::$defaultDateDisplayFormat) }}</span>,
                            <span>{{ __('to') }}:</span>
                            <span>{{ optional($Form->end_date)->format(\Filament\Infolists\Infolist::$defaultDateDisplayFormat) }}</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($sent)
        @include($boltTheme.'.submitted')
    @else
        <x-filament-panels::form wire:submit.prevent="store" :class="!$inline ? 'mx-2' : ''">
            @if(!$inline)
                {{ \LaraExperts\Bolt\Facades\Bolt::renderHookBlade('lara-form.before') }}
            @endif

            {!! \LaraExperts\Bolt\Facades\Extensions::init($Form, 'render',$extensionData) !!}

            @if(!empty($Form->details))
                <div class="m-4">
                    <x-filament::section :compact="true">
                        {!! nl2br($Form->details) !!}
                    </x-filament::section>
                </div>
            @endif

            {{ $this->form }}

            <div class="px-4 py-2 text-center">
                <x-filament::button
                    form="store"
                    type="submit"
                    :color="$Form->options['primary_color'] ?? 'primary'"
                >
                    {{ __('Save') }}
                </x-filament::button>
            </div>

            @if(!$inline)
                {{ \LaraExperts\Bolt\Facades\Bolt::renderHookBlade('lara-form.after') }}
            @endif
        </x-filament-panels::form>

        <x-filament-actions::modals/>
    @endif
</div>