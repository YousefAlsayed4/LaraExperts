<div>
    <div class="max-w-4xl mx-auto px-4">
        <x-filament::section>
            @if(!empty($Form->options['confirmation-message']))
                <span class="text-md text-gray-600">
                    {!! $Form->options['confirmation-message'] !!}
                </span>
            @else
                <span class="text-md text-gray-600">
                    {{ __('the form') }}
                    <span class="font-semibold">{{ $Form->name ?? '' }}</span>
                    {{ __('submitted successfully') }}.
                </span>
            @endif

            {!! \LaraExperts\Bolt\Facades\Extensions::init($Form, 'SubmittedRender', ['extensionData' => $extensionData['extInfo']['itemId'] ?? 0]) !!}

        </x-filament::section>
    </div>
</div>
