<?php

namespace App\View\Components\Form;

class InputCheckbox extends BaseInput
{
    public array  $options;
    public ?string $groupLabel;
    public bool   $inline;
    public mixed  $checked;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name:        $params['name']        ?? '',
            label:       $params['label']       ?? null,
            type:        'checkbox',
            value:       $params['value']       ?? $params['val'] ?? null,
            id:          $params['id']          ?? null,
            divClass:    $params['div']         ?? $params['container_class'] ?? 'col-md-3',
            inputClass:  $params['class']       ?? '',
            labelClass:  $params['label_class'] ?? null,
            required:    $params['required']    ?? false,
            disabled:    $params['disabled']    ?? false,
            tooltip:     $params['tooltip']     ?? null,
            extraAttrs:  $params['params']      ?? [],
        );

        $this->options    = $params['options']  ?? [];
        $this->groupLabel = count($this->options) ? ($params['label'] ?? null) : null;
        $this->inline     = $params['inline']   ?? false;
        $this->checked    = $params['checked']  ?? null;
    }

    public function isChecked(mixed $optVal): bool
    {
        $current = old($this->name) ?? $this->checked ?? $this->value;

        if (is_array($current)) {
            return in_array((string) $optVal, array_map('strval', $current));
        }

        return (string) $optVal === (string) $current || $current === true || $current == 1;
    }

    public function render()
    {
        return view('components.form.input-checkbox');
    }
}
