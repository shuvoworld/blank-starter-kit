<?php

namespace App\View\Components\Form;

use Illuminate\Support\Str;
use Illuminate\View\Component;

class BaseInput extends Component
{
    public string $name;
    public string $id;
    public ?string $label;
    public ?string $type;
    public mixed $value;
    public string $divClass;
    public string $inputClass;
    public ?string $labelClass;
    public ?string $placeholder;
    public bool $required;
    public bool $disabled;
    public bool $readonly;
    public bool $autofocus;
    public ?string $tooltip;
    public array $extraAttrs;

    public function __construct(
        string  $name,
        ?string $label = null,
        ?string $type = 'text',
        mixed   $value = null,
        ?string $id = null,
        string  $divClass = 'col-md-3',
        string  $inputClass = '',
        ?string $labelClass = null,
        ?string $placeholder = null,
        bool    $required = false,
        bool    $disabled = false,
        bool    $readonly = false,
        bool    $autofocus = false,
        ?string $tooltip = null,
        array   $extraAttrs = [],
    ) {
        $this->name        = $name;
        $this->label       = $label;
        $this->type        = $type;
        $this->value       = $value;
        $this->id          = $id ?? $this->nameToId($name);
        $this->divClass    = $divClass;
        $this->inputClass  = trim('form-control ' . $inputClass);
        $this->labelClass  = $labelClass;
        $this->placeholder = $placeholder ?? '';
        $this->required    = $required;
        $this->disabled    = $disabled;
        $this->readonly    = $readonly;
        $this->autofocus   = $autofocus;
        $this->tooltip     = $tooltip;
        $this->extraAttrs  = $extraAttrs;
    }

    /**
     * Resolve the field value:
     * old() → passed value → element property → ''
     */
    public function resolvedValue(): mixed
    {
        if (old($this->name) !== null) {
            return old($this->name);
        }

        return $this->value ?? '';
    }

    /**
     * Convert field name (e.g. user[address][city]) to a valid HTML id.
     */
    protected function nameToId(string $name): string
    {
        $id = str_replace(['[', ']', '__'], '_', $name);
        return trim($id, '_');
    }

    /**
     * Build extra HTML attribute string from $extraAttrs array.
     */
    public function extraHtml(): string
    {
        return collect($this->extraAttrs)
            ->map(fn($v, $k) => "$k=\"$v\"")
            ->implode(' ');
    }

    public function render()
    {
        return view('components.form.base-input');
    }
}
