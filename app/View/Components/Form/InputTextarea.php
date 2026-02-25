<?php

namespace App\View\Components\Form;

class InputTextarea extends BaseInput
{
    public int $rows;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name:        $params['name']        ?? '',
            label:       $params['label']       ?? null,
            type:        'textarea',
            value:       $params['value']       ?? $params['val'] ?? null,
            id:          $params['id']          ?? null,
            divClass:    $params['div']         ?? $params['container_class'] ?? 'col-md-3',
            inputClass:  $params['class']       ?? '',
            labelClass:  $params['label_class'] ?? null,
            placeholder: $params['placeholder'] ?? null,
            required:    $params['required']    ?? false,
            disabled:    $params['disabled']    ?? false,
            readonly:    $params['readonly']    ?? false,
            tooltip:     $params['tooltip']     ?? null,
            extraAttrs:  $params['params']      ?? [],
        );

        $this->rows = $params['rows'] ?? 4;
    }

    public function render()
    {
        return view('components.form.input-textarea');
    }
}
