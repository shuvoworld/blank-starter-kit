{{--
    Reusable Select / Dropdown

    Usage:
    @include('form.select', ['var' => [
        'name'       => 'status',
        'label'      => 'Status',
        'options'    => ['active' => 'Active', 'inactive' => 'Inactive'],
        'value'      => $model->status,
        'prompt'     => '-- Select Status --',   // optional empty first option
        'div'        => 'col-md-4',
        'required'   => true,
        'multiple'   => false,
        'select2'    => true,                    // enable Select2 enhancement
        'allow_clear' => true,                   // show clear button (requires prompt)
    ]])
--}}
@php
    use App\View\Components\Form\InputSelect;
    $input = new InputSelect($var ?? []);
@endphp

<div class="{{ $input->divClass }} mb-3">

    @if($input->label)
        <label for="{{ $input->id }}" class="form-label {{ $input->labelClass }}">
            {{ $input->label }}
            @if($input->required)<span class="text-danger">*</span>@endif
            @if($input->tooltip)
                <i class="bi bi-question-circle text-muted ms-1"
                   data-bs-toggle="tooltip" title="{{ $input->tooltip }}"></i>
            @endif
        </label>
    @endif

    <select
        name="{{ $input->name }}{{ $input->multiple ? '[]' : '' }}"
        id="{{ $input->id }}"
        class="{{ $input->inputClass }} @error($input->name) is-invalid @enderror"
        @if($input->required)  required  @endif
        @if($input->disabled)  disabled  @endif
        @if($input->multiple)  multiple  @endif
        @if($input->select2)
            data-toggle="select2"
            @if($input->allowClear) data-allow-clear="true" @endif
            @if($input->prompt) data-placeholder="{{ $input->prompt }}" @endif
        @endif
        {!! $input->extraHtml() !!}
    >
        @if($input->prompt)
            <option value="">{{ $input->select2 ? '' : $input->prompt }}</option>
        @endif

        @foreach($input->options as $optVal => $optLabel)
            <option value="{{ $optVal }}"
                {{ $input->isSelected($optVal) ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @error($input->name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

