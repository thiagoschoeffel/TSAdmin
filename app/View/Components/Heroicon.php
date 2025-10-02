<?php

namespace App\View\Components;

use DOMDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;

class Heroicon extends Component
{
    public string $name;

    public string $variant;

    public ?string $size;

    public ?string $srText;

    public bool $srOnly;

    public function __construct(string $name, string $variant = 'outline', ?string $size = null, ?string $srText = null, bool $srOnly = false)
    {
        $this->name = Str::of($name)->kebab()->value();
        $this->variant = $this->resolveVariant($variant);
        $this->size = $size;
        $this->srText = $srText;
        $this->srOnly = $srOnly;
    }

    public function render(): string
    {
        /** @var ComponentAttributeBag $attributes */
        $attributes = $this->attributes ?? new ComponentAttributeBag();

        $attributes = $this->mergeAttributes($attributes);

        $decorated = $this->applyAttributes($this->loadSvg(), $attributes);

        if ($this->srText) {
            return $decorated . '<span class="sr-only">' . e($this->srText) . '</span>';
        }

        return $decorated;
    }

    protected function mergeAttributes(ComponentAttributeBag $attributes): ComponentAttributeBag
    {
        $attributes = $attributes->class($this->sizeClass());

        if ($this->srOnly) {
            $attributes = $attributes->class('sr-only');
        }

        $defaults = [
            'aria-hidden' => $this->srText ? 'false' : 'true',
            'role' => 'img',
        ];

        if (! $attributes->has('focusable')) {
            $defaults['focusable'] = 'false';
        }

        if ($this->srText && ! $attributes->has('aria-label')) {
            $defaults['aria-label'] = $this->srText;
        }

        return $attributes->merge($defaults);
    }

    protected function sizeClass(): string
    {
        if ($this->size === null) {
            return 'h-6 w-6';
        }

        if (is_numeric($this->size)) {
            return 'h-' . $this->size . ' w-' . $this->size;
        }

        return $this->size;
    }

    protected function resolveVariant(string $variant): string
    {
        return match (Str::kebab($variant)) {
            'solid' => 'solid',
            'mini' => 'mini',
            default => 'outline',
        };
    }

    protected function loadSvg(): string
    {
        $path = resource_path("svg/heroicons/{$this->variant}/{$this->name}.svg");

        if (! File::exists($path)) {
            throw new InvalidArgumentException("Heroicon [{$this->variant}/{$this->name}] not found. Run `npm install` and `npm run icons` to publish the SVGs.");
        }

        return File::get($path);
    }

    protected function applyAttributes(string $svg, ComponentAttributeBag $attributes): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($svg);
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new InvalidArgumentException("The SVG markup for [{$this->variant}/{$this->name}] could not be parsed.");
        }

        $svgElement = $dom->documentElement;

        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }

            $svgElement->setAttribute($key, (string) $value);
        }

        return $dom->saveXML($svgElement);
    }
}
