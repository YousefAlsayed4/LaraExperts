<?php

use Illuminate\Support\Facades\Route;
use LaraExperts\Bolt\Livewire\FillForms;
use LaraExperts\Bolt\Livewire\ListEntries;
use LaraExperts\Bolt\Livewire\ListForms;
use LaraExperts\Bolt\Livewire\ShowEntry;

Route::domain(config('form-bolt.domain'))
    ->prefix(config('form-bolt.prefix'))
    ->name('bolt.')
    ->middleware(config('form-bolt.middleware'))
    ->group(function () {
        Route::get('/', ListForms::class)
            ->name('forms.list');

        Route::get('/entries', ListEntries::class)->name('entries.list')
            ->middleware('auth');

        Route::get('/entry/{responseID}', ShowEntry::class)
            ->name('entry.show')
            ->middleware('auth');

        // if (class_exists(\LaraZeus\BoltPro\BoltProServiceProvider::class)) {
        //     Route::get('embed/{slug}', \LaraZeus\BoltPro\Livewire\EmbedForm::class)
        //         ->name('form.embed');
        // }

        Route::get('{slug}/{extensionSlug?}', FillForms::class)
            ->name('form.show');
    });
