{{--
    Reusable File Input

    Usage:
    @include('form.file', ['var' => [
        'name'     => 'avatar',
        'label'    => 'Profile Photo',
        'accept'   => 'image/*',
        'multiple' => false,
        'preview'  => $model->avatar_url,  // optional existing file URL/path
        'div'      => 'col-md-6',
    ]])
--}}
@php
    use App\View\Components\Form\InputFile;
    $input = new InputFile($var ?? []);
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

    @if($input->preview)
        <div class="mb-2">
            @if($input->isImage($input->preview))
                <img src="{{ $input->preview }}" class="img-thumbnail" style="max-height:80px;" alt="Current file">
            @else
                <a href="{{ $input->preview }}" target="_blank" class="small">
                    <i class="bi bi-paperclip"></i> Current file
                </a>
            @endif
        </div>
    @endif

    <input
        type="file"
        name="{{ $input->name }}{{ $input->multiple ? '[]' : '' }}"
        id="{{ $input->id }}"
        class="form-control @error($input->name) is-invalid @enderror"
        @if($input->accept)   accept="{{ $input->accept }}"   @endif
        @if($input->multiple) multiple @endif
        @if($input->required) required @endif
        @if($input->disabled) disabled @endif
        {!! $input->extraHtml() !!}
    >

    @error($input->name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
