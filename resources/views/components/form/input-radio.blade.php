{{--
    Reusable Radio Button Group

    Usage:
    @include('form.radio', ['var' => [
        'name'    => 'gender',
        'label'   => 'Gender',
        'options' => ['m' => 'Male', 'f' => 'Female', 'o' => 'Other'],
        'value'   => $model->gender,
        'inline'  => true,
        'div'     => 'col-md-6',
        'required'=> true,
    ]])
--}}
@php
    use App\View\Components\Form\InputRadio;
    $input = new InputRadio($var ?? []);
@endphp

<div class="{{ $input->divClass }} mb-3 {{ $errors->has($input->name) ? 'has-error' : '' }}">

    @if($input->label)
        <label class="form-label d-block {{ $input->labelClass }}">
            {!! $input->label !!}
            @if($input->required)<span class="text-danger">*</span>@endif
            @if($input->tooltip)
                <i class="bi bi-question-circle text-muted ms-1"
                   data-bs-toggle="tooltip" title="{{ $input->tooltip }}"></i>
            @endif
        </label>
    @endif

    @foreach($input->options as $optVal => $optLabel)
        <div class="form-check {{ $input->inline ? 'form-check-inline' : '' }}">
            <input type="radio"
                   name="{{ $input->name }}"
                   id="{{ $input->id }}_{{ $loop->index }}"
                   value="{{ $optVal }}"
                   class="form-check-input @error($input->name) is-invalid @enderror"
                   {{ $input->resolvedValue() == $optVal ? 'checked' : '' }}
                   @if($input->required && $loop->first) required @endif
                   @if($input->disabled) disabled @endif
                   {!! $input->extraHtml() !!}>
            <label class="form-check-label" for="{{ $input->id }}_{{ $loop->index }}">
                {{ $optLabel }}
            </label>
        </div>
    @endforeach

    @error($input->name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
