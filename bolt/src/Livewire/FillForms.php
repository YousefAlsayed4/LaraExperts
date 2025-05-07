<?php

namespace LaraExperts\Bolt\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use LaraExperts\Bolt\Concerns\Designer;
use LaraExperts\Bolt\Events\FormMounted;
use LaraExperts\Bolt\Events\FormSent;
use LaraExperts\Bolt\Facades\Extensions;
use LaraExperts\Bolt\Models\Form; 
use Livewire\Component;
use Artesaos\SEOTools\Facades\SEOMeta;



/**
 * @property mixed $form
 */
class FillForms extends Component implements Forms\Contracts\HasForms
{
    use Designer;
    use InteractsWithForms;

    public Form $Form;

    public array $extensionData;

    public array $formData = [];

    public bool $sent = false;

    public bool $inline = false;

    protected static ?string $boltFormDesigner = null;
    

    public function getBoltFormDesigner(): ?string
    {
        return static::$boltFormDesigner;
    }

    public static function getBoltFormDesignerUsing(?string $form): void
    {
        static::$boltFormDesigner = $form;
    }

    protected function getFormSchema(): array
    {
        $getDesignerClass = $this->getBoltFormDesigner() ?? Designer::class;

        return $getDesignerClass::ui($this->Form, $this->inline);
    }

    protected function getFormModel(): Form
    {
        return $this->Form;
    }

    /**
     * @throws \Throwable
     */
    public function mount(
        mixed $slug,
        mixed $extensionSlug = null,
        mixed $extensionData = [],
        mixed $inline = false,
    ): void {
        $this->inline = $inline;

        $this->Form = config('form-bolt.models.Form')::query()
            ->with(['fields', 'sections.fields'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $this->extensionData = Extensions::init($this->Form, 'canView', ['extensionSlug' => $extensionSlug, 'extensionData' => $extensionData]) ?? [];

        foreach ($this->Form->fields as $field) {
            $this->formData[$field->id] = '';
        }

        $this->form->fill();

        event(new FormMounted($this->Form));
    }

    public function store(): void
    {
        $this->validate();

        Extensions::init($this->Form, 'preStore', $this->extensionData);

        $response = config('form-bolt.models.Response')::create([
            'form_id' => $this->Form->id,
            'user_id' => (auth()->check()) ? auth()->user()->id : null,
            'status' => 'NEW',
            'notes' => '',
        ]);

        $fieldsData = Arr::except($this->form->getState()['formData'], 'extensions');

        foreach ($fieldsData as $field => $value) {
            $setValue = $value;

            if (! empty($setValue) && is_array($setValue)) {
                $value = json_encode($value);
            }
            config('form-bolt.models.FieldResponse')::create([
                'response' => (! empty($value)) ? $value : '',
                'response_id' => $response->id,
                'form_id' => $this->Form->id,
                'field_id' => $field,
            ]);
        }

        event(new FormSent($response));

        $this->extensionData['response'] = $response;
        $this->extensionData['extensionsComponent'] = $this->form->getState()['formData']['extensions'] ?? [];

        $extensionItemId = Extensions::init($this->Form, 'store', $this->extensionData) ?? [];
        $this->extensionData['extInfo'] = $extensionItemId;

        $response->update(['extension_item_id' => $extensionItemId['itemId'] ?? null]);

        if (isset($this->Form->options['emails-notification']) && ! empty($this->Form->options['emails-notification'])) {
            $emails = explode(',', $this->Form->options['emails-notification']);

            foreach ($emails as $email) {
                $mailable = config('form-bolt.defaultMailable');
                Mail::to($email)->send(new $mailable($this->Form, $response));
            }
        }

        $this->sent = true;
    }

    public function render(): View
    {

        if (! $this->inline) {
            // seo()
            //     ->title($this->Form->name . ' - ' . __('Forms') . ' - ' . config('form.site_title', 'Laravel'))
            //     ->description($this->Form->description . ' - ' . config('form.site_description') . ' ' . config('form.site_title'))
            //     ->site(config('form.site_title', 'Laravel'))
            //     ->rawTag('favicon', '<link rel="icon" type="image/x-icon" href="' . asset('favicon/favicon.ico') . '">')
            //     ->rawTag('<meta name="theme-color" content="' . config('form.site_color') . '" />')
            //     ->withUrl()
            //     ->twitter();

            // SEOMeta::setTitle($this->Form->name . ' - ' . __('Forms') . ' - ' . config('form.site_title', 'Laravel'));
            // SEOMeta::setDescription($this->Form->description . ' - ' . config('form.site_description') . ' ' . config('form.site_title'));
        }

        $view = match (true) {
            $this->Form->need_login => 'form::errors.login-required',
            ! $this->Form->date_available => 'form::errors.date-not-available',
            $this->Form->onePerUser() => 'form::errors.one-entry-per-user',
            default => app('boltTheme') . '.fill-forms',
        };

        if ($this->inline) {
            return view($view);
        }

        return view($view)->layout(config('form.layout'));
    }
}
