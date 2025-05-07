<?php

namespace LaraExperts\Bolt\Filament\Helpers;

use LaraExperts\Bolt\Filament\Enums\Http;
use LaraExperts\Bolt\Filament\Helpers\Traits\SendsResponse;
use Illuminate\Contracts\Support\Responsable;

class APIResponse extends \Symfony\Component\HttpFoundation\Response implements Responsable
{
    use SendsResponse;

    public function __construct(
        public readonly string $status = "success",
        public readonly Http $code = Http::OK,
        public readonly string $message = 'Request completed successfully',
        public readonly array|object $body =  [],
        public readonly ?array $errors = null,
    ) {
        parent::__construct();
    }
}
