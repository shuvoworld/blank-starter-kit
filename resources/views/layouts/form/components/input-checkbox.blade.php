{{--
    Reusable Checkbox (single or group)

    Single:
    @include('form.checkbox', ['var' => [
        'name'    => 'agree',
        'label'   => 'I agree to the terms',
        'value'   => 1,
        'checked' => old('agree', $model->agree),
        'div'     => 'col-md-6',
    ]])

    Group (multiple checkboxes):
    @include('form.checkbox', ['var' => [
        'name'    => 'roles[]',
        'label'   => 'Roles',
        'options' => ['admin' => 'Admin', 'user' => 'User'],
        'value'   => $model->roles,   // array of checked values
        'div'     => 'col-md-6',
    ]])
--}}
@php
    use App\View\Components\Form\InputCheckbox;
    $input = new InputCheckbox($var ?? []);
@endphp

<div class="{{ $input->divClass }} mb-3 {{ $errors->has($input->name) ? 'has-error' : '' }}">

    @if($input->groupLabel)
        <label class="form-label d-block {{ $input->labelClass }}">
            {!! $input->groupLabel !!}
            @if($input->required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @if($input->options)
        {{-- Checkbox group --}}
        @foreach($input->options as $optVal => $optLabel)
            <div class="form-check {{ $input->inline ? 'form-check-inline' : '' }}">
                <input type="checkbox"
                       name="{{ $input->name }}"
                       id="{{ $input->id }}_{{ $loop->index }}"
                       value="{{ $optVal }}"
                       class="form-check-input @error($input->name) is-invalid @enderror"
                       {{ $input->isChecked($optVal) ? 'checked' : '' }}
                       @if($input->disabled) disabled @endif
                       {!! $input->extraHtml() !!}>
                <label class="form-check-label" for="{{ $input->id }}_{{ $loop->index }}">
                    {{ $optLabel }}
                </label>
            </div>
        @endforeach
    @else
        {{-- Single checkbox --}}
        <div class="form-check">
            <input type="checkbox"
                   name="{{ $input->name }}"
                   id="{{ $input->id }}"
                   value="{{ $input->value ?? 1 }}"
                   class="form-check-input @error($input->name) is-invalid @enderror"
                   {{ $input->isChecked($input->value ?? 1) ? 'checked' : '' }}
                   @if($input->required) required @endif
                   @if($input->disabled) disabled @endif
                   {!! $input->extraHtml() !!}>
            @if($input->label)
                <label class="form-check-label {{ $input->labelClass }}" for="{{ $input->id }}">
                    {!! $input->label !!}
                </label>
            @endif
        </div>
    @endif

    @error($input->name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
