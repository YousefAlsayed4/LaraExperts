<?php

// use LaraExperts\Bolt\Filament\Resources\CategoryResource;
use LaraExperts\Bolt\Filament\Resources\CollectionResource;
use LaraExperts\Bolt\Filament\Resources\FormResource;

use function Pest\Laravel\get;

it('can test', function () {
    expect(true)->toBeTrue();
});

// it('can render category list', function () {
// get(CategoryResource::getUrl('index'))->assertSuccessful();

//     get(CategoryResource::getUrl())
//         ->assertSuccessful();
//     });

it('can render collection list', function () {
get(CollectionResource::getUrl())
->assertSuccessful();
    });

it('can render form list', function () {
get(FormResource::getUrl())
->assertSuccessful();
    });
