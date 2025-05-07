<?php

return [
    /**
     * set the default theme for all form packages
     *
     * Available layouts with artemis:
     *
     * breeze, daisy, another-portfolio
     */
    'theme' => 'form',

    /**
     * set the defualt layout component
     *
     * Available layouts with artemis:
     *
     * form::themes.breeze.layouts.app
     * form::themes.daisy.layouts.app
     * form::themes.another-portfolio.layouts.app
     */
    'layout' => 'form::components.app',

    /**
     * this will be set up the default seo site title. read more about it in 'laravel-seo'.
     */
    'site_title' => config('app.name', 'Laravel'),

    /**
     * this will be setup the default seo site description. read more about it in 'laravel-seo'.
     */
    'site_description' => 'All about ' . config('app.name', 'Laravel'),

    /**
     * this will be setup the default seo site color theme. read more about it in 'laravel-seo'.
     */
    'site_color' => '#F5F5F4',

    /** set the default menu to use in header nav */
    'header_menu' => 'main-header-menu',
];
