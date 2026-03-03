<?php

namespace App\View\Components\Form;

class InputSelect extends BaseInput
{
    public array $options;

    public ?string $prompt;

    public bool $multiple;

    public bool $select2;

    public bool $allowClear;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name: $params['name'] ?? '',
            label: $params['label'] ?? null,
            type: 'select',
            value: $params['value'] ?? $params['val'] ?? null,
            id: $params['id'] ?? null,
            divClass: $params['div'] ?? $params['container_class'] ?? 'col-md-3',
            formClass: 'form-select',
            inputClass: $params['class'] ?? '',
            labelClass: $params['label_class'] ?? null,
            required: $params['required'] ?? false,
            disabled: $params['disabled'] ?? false,
            tooltip: $params['tooltip'] ?? null,
            extraAttrs: $params['params'] ?? [],
        );

        $this->options = $params['options'] ?? [];
        $this->prompt = $params['prompt'] ?? null;
        $this->multiple = $params['multiple'] ?? false;
        $this->select2 = $params['select2'] ?? false;
        $this->allowClear = $params['allow_clear'] ?? false;
    }

    public function isSelected(mixed $optVal): bool
    {
        if ($this->hasOldInput()) {
            $current = old($this->oldKey);
        } else {
            $current = $this->value;
        }

        if (is_array($current)) {
            return in_array((string) $optVal, array_map('strval', $current));
        }

        return (string) $optVal === (string) $current;
    }

    public function render()
    {
        return view('components.form.input-select');
    }
}
