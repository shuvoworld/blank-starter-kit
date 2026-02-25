<?php

namespace App\View\Components\Form;

class InputRadio extends BaseInput
{
    public array $options;
    public bool  $inline;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name:        $params['name']        ?? '',
            label:       $params['label']       ?? null,
            type:        'radio',
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

        $this->options = $params['options'] ?? [];
        $this->inline  = $params['inline']  ?? false;
    }

    public function render()
    {
        return view('components.form.input-radio');
    }
}
