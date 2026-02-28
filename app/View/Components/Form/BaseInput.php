<?php

namespace App\View\Components\Form;

use Illuminate\Support\Arr;
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

    /**
     * The dot-notation key used for old() lookups.
     * e.g. "roles[]" → "roles", "user[address][city]" → "user.address.city"
     */
    protected string $oldKey;

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
        $this->oldKey      = $this->nameToOldKey($name);
    }

    /**
     * Resolve the field value — priority:
     *   1. old input (present in session after a failed validation)
     *   2. explicitly passed $value / $val
     *   3. empty string
     *
     * Uses array_key_exists on the flashed old-input bag so that an
     * explicitly-submitted empty string or unchecked checkbox (null)
     * is still treated as "old input exists" and returned correctly.
     */
    public function resolvedValue(): mixed
    {
        return old($this->oldKey, $this->value ?? '');
    }

    /**
     * Returns true if the field has been through a validation round-trip,
     * even when the submitted value was empty / null / 0.
     */
    public function hasOldInput(): bool
    {
        // session()->has('_old_input') means a redirect-with-old happened
        if (! session()->has('_old_input')) {
            return false;
        }

        return Arr::has(session('_old_input', []), $this->oldKey);
    }

    /**
     * Convert an HTML name to Laravel's dot-notation old() key.
     *
     *  "email"               → "email"
     *  "roles[]"             → "roles"
     *  "user[address][city]" → "user.address.city"
     */
    protected function nameToOldKey(string $name): string
    {
        // Strip trailing [] for plain array fields (e.g. "tags[]")
        $key = rtrim($name, '[]');

        // Convert remaining [bracket][notation] to dot.notation
        $key = str_replace(['[', ']'], ['.', ''], $key);

        // Collapse any double-dots that arise from empty brackets
        $key = preg_replace('/\.{2,}/', '.', $key);

        return trim($key, '.');
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
