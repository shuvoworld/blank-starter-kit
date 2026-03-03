{{--
    Reusable Textarea

    Usage:
    @include('form.textarea', ['var' => [
        'name'        => 'description',
        'label'       => 'Description',
        'value'       => $model->description,
        'placeholder' => 'Enter description...',
        'rows'        => 4,
        'div'         => 'col-md-12',
        'required'    => true,
    ]])
--}}
@php
    use App\View\Components\Form\InputTextarea;
    $input = new InputTextarea($var ?? []);
@endphp

<div class="{{ $input->divClass }} mb-3 {{ $errors->has($input->name) ? 'has-error' : '' }}">

    @if($input->label)
        <label for="{{ $input->id }}" class="form-label {{ $input->labelClass }}">
            {!! $input->label !!}
            @if($input->required)<span class="text-danger">*</span>@endif
            @if($input->tooltip)
                <i class="bi bi-question-circle text-muted ms-1"
                   data-bs-toggle="tooltip" title="{{ $input->tooltip }}"></i>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $input->name }}"
        id="{{ $input->id }}"
        class="{{ $input->inputClass }} @error($input->name) is-invalid @enderror"
        rows="{{ $input->rows }}"
        placeholder="{{ $input->placeholder }}"
        @if($input->required)  required  @endif
        @if($input->disabled)  disabled  @endif
        @if($input->readonly)  readonly  @endif
        {!! $input->extraHtml() !!}
    >{{ $input->resolvedValue() }}</textarea>

    @error($input->name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
