<?php

namespace LaraExperts\Bolt;

use Closure;

trait Configuration
{
    /**
     * you can overwrite any model and use your own
     */
    protected array $boltModels = [];

    protected array $customSchema = [
        'form' => null,
        'section' => null,
        'field' => null,
    ];

    protected array $hideResources = [];

    /**
     * available extensions, leave it null to disable the extensions tab from the forms
     */
    protected ?array $extensions = null;

    /**
     * the resources navigation group
     */
    protected Closure | string $navigationGroupLabel = 'Bolt';

    protected bool $formActionsAreSticky = false;

    public function customSchema(array $schema): static
    {
        $this->customSchema = $schema;

        return $this;
    }

    public function getCustomSchema(): array
    {
        return $this->customSchema;
    }

    public static function getSchema(string $type): ?string
    {
        return (new static)::get()->getCustomSchema()[$type];
    }

    public function boltModels(array $models): static
    {
        $this->boltModels = $models;

        return $this;
    }

    public function getBoltModels(): array
    {
        return $this->boltModels;
    }

    public static function getModel(string $model): ?string
    {
        return array_merge(
            config('form-bolt.models'),
            (new static)::get()->getBoltModels()
        )[$model] ?? null;
    }

    public function navigationGroupLabel(Closure | string $label): static
    {
        $this->navigationGroupLabel = $label;

        return $this;
    }

    public function getNavigationGroupLabel(): Closure | string
    {
        return $this->evaluate($this->navigationGroupLabel);
    }

    public function formActionsAreSticky(bool $condition = false): static
    {
        $this->formActionsAreSticky = $condition;

        return $this;
    }

    public function isFormActionsAreSticky(): bool
    {
        return $this->evaluate($this->formActionsAreSticky);
    }

    public function extensions(?array $extensions): static
    {
        $this->extensions = $extensions;

        return $this;
    }

    public function getExtensions(): ?array
    {
        return $this->extensions;
    }

    public function hideResources(array $resources): static
    {
        $this->hideResources = $resources;

        return $this;
    }

    public function getHiddenResources(): ?array
    {
        return $this->hideResources;
    }
}
