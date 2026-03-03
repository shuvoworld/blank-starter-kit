<?php

namespace App\View\Components\Form;

class InputFile extends BaseInput
{
    public ?string $accept;
    public bool    $multiple;
    public ?string $preview;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name:        $params['name']        ?? '',
            label:       $params['label']       ?? null,
            type:        'file',
            id:          $params['id']          ?? null,
            divClass:    $params['div']         ?? $params['container_class'] ?? 'col-md-3',
            inputClass:  $params['class']       ?? '',
            labelClass:  $params['label_class'] ?? null,
            required:    $params['required']    ?? false,
            disabled:    $params['disabled']    ?? false,
            tooltip:     $params['tooltip']     ?? null,
            extraAttrs:  $params['params']      ?? [],
        );

        $this->accept   = $params['accept']   ?? null;
        $this->multiple = $params['multiple'] ?? false;
        $this->preview  = $params['preview']  ?? null;
    }

    public function isImage(string $path): bool
    {
        return (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $path);
    }

    public function render()
    {
        return view('components.form.input-file');
    }
}
