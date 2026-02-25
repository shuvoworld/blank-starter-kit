<?php

namespace App\View\Components\Form;

class InputSwitch extends InputCheckbox
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }

    public function render()
    {
        return view('components.form.input-switch');
    }
}
