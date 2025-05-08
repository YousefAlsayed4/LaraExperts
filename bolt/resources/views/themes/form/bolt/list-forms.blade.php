<div class="mx-4">

    <x-slot name="header">
        <h2>{{ __('List All Forms') }}</h2>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="flex items-center">
            {{ __('Forms') }}
        </li>
    </x-slot>

    {{ \LaraExperts\Bolt\Facades\Bolt::renderHookBlade('lara-forms.before') }}

    <div class="mx-4">

        <x-slot name="header">
            <h2>{{ __('List All Forms') }}</h2>
        </x-slot>
    
        <x-slot name="breadcrumbs">
            <li class="flex items-center">
                {{ __('Forms') }}
            </li>
        </x-slot>
    
        {{ \LaraExperts\Bolt\Facades\Bolt::renderHookBlade('lara-forms.before') }}
    
        <div class="space-y-4">
            @foreach($forms as $form)
                <x-filament::section class="flex items-start gap-4">
                    @if($form->logo !== null)
                        <img alt="{{ $form->name }} Logo" class="w-24 h-24 object-center object-cover rounded" src="{{ $form->logo_url }}"/>
                    @endif
    
                    <div class="flex flex-col flex-1">
                        <p class="font-semibold text-lg">
                            {{ $form->name }}
                        </p>
    
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $form->description }}
                        </p>
    
                        <a href="{{ route('bolt.form.show', ['slug' => $form->slug]) }}"
                           class="inline-block mt-2 py-1.5 px-3 bg-primary-600 text-white rounded hover:bg-primary-700 transition">
                            {{ __('View Form') }}
                        </a>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    
        {{ \LaraExperts\Bolt\Facades\Bolt::renderHookBlade('lara-forms.before') }}
    
    </div>
    
    

    {{ \LaraExperts\Bolt\Facades\Bolt::renderHookBlade('lara-forms.before') }}

</div>
