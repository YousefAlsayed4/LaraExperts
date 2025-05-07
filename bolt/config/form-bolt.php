<?php

return [
    /**
     * set the default domain.
     */
    'domain' => null,

    /**
     * set the default path for the forms homepage.
     */
    'prefix' => 'bolt',

    /*
     * set database table prefix
     */
    'table-prefix' => 'bolt_',

    /**
     * the middleware you want to apply on all the blog routes
     * for example if you want to make your blog for users only, add the middleware 'auth'.
     */
    'middleware' => ['web'],

    /**
     * you can overwrite any model and use your own
     * you can also configure the model per panel in your panel provider using:
     * ->skyModels([ ... ])
     */
    'models' => [
        // 'Category' => \LaraExperts\Bolt\Models\Category::class,
        'Collection' => \LaraExperts\Bolt\Models\Collection::class,
        'Field' => \LaraExperts\Bolt\Models\Field::class,
        'FieldResponse' => \LaraExperts\Bolt\Models\FieldResponse::class,
        'Form' => \LaraExperts\Bolt\Models\Form::class,
        'FormsStatus' => \LaraExperts\Bolt\Models\FormsStatus::class,
        'Response' => \LaraExperts\Bolt\Models\Response::class,
        'Section' => \LaraExperts\Bolt\Models\Section::class,
        'User' => config('auth.providers.users.model'),
    ],

    'collectors' => [
        'fields' => [
            'path' => 'app/Form/Fields',
            'namespace' => '\\App\\Form\\Fields\\',
        ],

        'dataSources' => [
            'path' => 'app/Form/DataSources',
            'namespace' => 'App\\Form\\DataSources\\',
        ],
    ],

    'defaultMailable' => \LaraExperts\Bolt\Mail\FormSubmission::class,

    'uploadDisk' => env('BOLT_FILESYSTEM_DISK', 'public'),

    'uploadDirectory' => env('BOLT_FILESYSTEM_DIRECTORY', 'forms'),

    'uploadVisibility' => env('BOLT_FILESYSTEM_VISIBILITY', 'public'),

    'show_presets' => false,

    'allow_design' => false,

    /**
     * since `collections` or 'data sources' have many types, we cannot lazy load them
     * but we cache them for a while to get better performance
     * the key is: dataSource_*_response_md5
     *
     * here you can set the duration of the cache
     */
    'cache' => [
        'collection_values' => 30, // on seconds
    ],

    'boltTheme' => 'themes.form.bolt', // Add this line

];
