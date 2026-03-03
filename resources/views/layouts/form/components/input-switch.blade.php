{{--
    Reusable Toggle Switch

    Usage:
    @include('form.switch', ['var' => [
        'name'    => 'is_active',
        'label'   => 'Active',
        'value'   => 1,
        'checked' => $model->is_active,
        'div'     => 'col-md-3',
    ]])
--}}
@php
    use App\View\Components\Form\InputSwitch;
    $input = new InputSwitch($var ?? []);
@endphp

<div class="{{ $input->divClass }} mb-3">
    <div class="form-check form-switch">
        {{-- Hidden field ensures unchecked = 0 --}}
        <input type="hidden" name="{{ $input->name }}" value="0">

        <input
            type="checkbox"
            name="{{ $input->name }}"
            id="{{ $input->id }}"
            value="{{ $input->value ?? 1 }}"
            class="form-check-input @error($input->name) is-invalid @enderror"
            role="switch"
            {{ $input->isChecked($input->value ?? 1) ? 'checked' : '' }}
            @if($input->required) required @endif
            @if($input->disabled) disabled @endif
            {!! $input->extraHtml() !!}
        >

        @if($input->label)
            <label class="form-check-label {{ $input->labelClass }}" for="{{ $input->id }}">
                {{ $input->label }}
                @if($input->tooltip)
                    <i class="bi bi-question-circle text-muted ms-1"
                       data-bs-toggle="tooltip" title="{{ $input->tooltip }}"></i>
                @endif
            </label>
        @endif
    </div>

    @error($input->name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
