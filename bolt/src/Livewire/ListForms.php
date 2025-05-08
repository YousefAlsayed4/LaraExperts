<?php

namespace LaraExperts\Bolt\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use Artesaos\SEOTools\Facades\SEOTools; // Add this line

class ListForms extends Component
{
    public function render(): View
    {
        SEOTools::setTitle(__('Forms') . ' - ' . config('form.site_title'));
        SEOTools::setDescription(__('Forms') . ' - ' . config('form.site_description') . ' ' . config('form.site_title'));
        SEOTools::metatags()->addMeta('theme-color', config('form.site_color'));
        SEOTools::twitter()->setSite(config('form.site_title', 'Laravel'));
    
        $forms = config('form-bolt.models.Form')::query()
            ->whereNull('extensions')
            ->where('is_active', 1)
            ->orderBy('ordering')
            ->get();
    
        return view(app('boltTheme') . '.list-forms', compact('forms'))
            ->layout(config('form.layout'));
    }
    
}