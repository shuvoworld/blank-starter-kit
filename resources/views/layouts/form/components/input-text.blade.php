{{--
    Reusable Text Input — supports types: text, password, email, number, tel,
    date, time, datetime-local, week, month, url, color, range, search, hidden

    Usage:
    @include('form.text', ['var' => [
        'name'        => 'email',
        'label'       => 'Email Address',
        'type'        => 'email',          // default: text
        'value'       => $user->email,
        'placeholder' => 'you@example.com',
        'div'         => 'col-md-6',       // wrapper column class
        'class'       => 'my-extra-class', // appended to form-control
        'label_class' => 'fw-bold',
        'required'    => true,
        'disabled'    => false,
        'readonly'    => false,
        'autofocus'   => true,
        'tooltip'     => 'We will never share your email.',
        'params'      => ['data-foo' => 'bar'],  // extra HTML attrs
    ]])
--}}
@php
    use App\View\Components\Form\InputText;
    $input = new InputText($var ?? []);
@endphp

@if($input->type === 'hidden')
    <input type="hidden"
           name="{{ $input->name }}"
           id="{{ $input->id }}"
           value="{{ $input->resolvedValue() }}">
@else
    <div class="{{ $input->divClass }} mb-3">

        {{-- Label --}}
        @if($input->label)
            <label for="{{ $input->id }}"
                   class="form-label {{ $input->labelClass }}">
                {{ $input->label }}
                @if($input->required)
                    <span class="text-danger">*</span>
                @endif
                @if($input->tooltip)
                    <i class="bi bi-question-circle text-muted ms-1"
                       data-bs-toggle="tooltip"
                       title="{{ $input->tooltip }}"></i>
                @endif
            </label>
        @endif

        {{-- Input --}}
        <input
            type="{{ $input->type }}"
            name="{{ $input->name }}"
            id="{{ $input->id }}"
            class="{{ $input->inputClass }} @error($input->name) is-invalid @enderror"
            value="{{ $input->resolvedValue() }}"
            placeholder="{{ $input->placeholder }}"
            @if($input->required)  required  @endif
            @if($input->disabled)  disabled  @endif
            @if($input->readonly)  readonly  @endif
            @if($input->autofocus) autofocus @endif
            {!! $input->extraHtml() !!}
        >

        {{-- Inline error --}}
        @error($input->name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif
