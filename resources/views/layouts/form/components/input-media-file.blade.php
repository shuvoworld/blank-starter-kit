{{--
    Reusable Spatie MediaLibrary File Input

    Usage:
    @include('layouts.form.inputs.media-file', ['var' => [
        'name'        => 'profile_picture',
        'label'       => 'Profile Picture',
        'type'        => 'image-circle',     // 'image-circle' | 'image' | 'file'
        'media'       => $record?->getFirstMedia('profile_picture'),
        'preview_url' => $record?->getFirstMediaUrl('profile_picture', 'thumb'),
        'accept'      => 'image/jpeg,image/png,image/gif,image/webp',
        'div'         => 'col-12',
        'help_text'   => 'Leave empty to keep current image.',
    ]])

    @include('layouts.form.inputs.media-file', ['var' => [
        'name'      => 'resume',
        'label'     => 'Resume/CV',
        'type'      => 'file',
        'media'     => $record?->getFirstMedia('resume'),
        'accept'    => '.pdf,application/pdf',
        'div'       => 'col-12',
        'help_text' => 'PDF only, max 5MB. New file replaces existing.',
    ]])

    @include('layouts.form.inputs.media-file', ['var' => [
        'name'      => 'certificates',
        'label'     => 'Certificates',
        'type'      => 'file',
        'media'     => $record?->getMedia('certificates'),
        'multiple'  => true,
        'accept'    => '.pdf,application/pdf',
        'div'       => 'col-12',
        'help_text' => 'PDF only, max 5 files, 5MB each. New files are added.',
    ]])
--}}
@php
    use App\View\Components\Form\InputMediaFile;
    $input = new InputMediaFile($var ?? []);
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

    {{-- ── Image types: circular or rectangular thumbnail ─────────────────── --}}
    @if($input->isImageType())
        <div class="d-flex align-items-center gap-3">
            {{-- Existing image preview --}}
            @if($input->hasMedia() && ($input->previewUrl ?? $input->media?->getUrl()))
                @php $imgUrl = $input->previewUrl ?? $input->media->getUrl(); @endphp
                @if($input->inputType === 'image-circle')
                    <img src="{{ $imgUrl }}"
                         alt="{{ $input->label }}"
                         class="rounded-circle flex-shrink-0"
                         width="80" height="80"
                         style="object-fit: cover;">
                @else
                    <img src="{{ $imgUrl }}"
                         alt="{{ $input->label }}"
                         class="img-thumbnail flex-shrink-0"
                         style="max-height: 80px; width: auto;">
                @endif
            @else
                @if($input->inputType === 'image-circle')
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @else
                    <div class="bg-secondary d-flex align-items-center justify-content-center text-white flex-shrink-0"
                         style="width: 80px; height: 80px; font-size: 2rem; border-radius: 4px;">
                        <i class="bi bi-image"></i>
                    </div>
                @endif
            @endif

            {{-- File input --}}
            <div class="flex-grow-1">
                <input type="file"
                       name="{{ $input->name }}"
                       id="{{ $input->id }}"
                       class="form-control @error($input->name) is-invalid @enderror"
                       @if($input->accept)   accept="{{ $input->accept }}"   @endif
                       @if($input->required) required @endif
                       @if($input->disabled) disabled @endif
                       {!! $input->extraHtml() !!}>
                @error($input->name)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($input->helpText)
                    <div class="form-text">{{ $input->helpText }}</div>
                @endif
            </div>
        </div>

    {{-- ── File type ────────────────────────────────────────────────────────── --}}
    @else
        {{-- Existing file(s) --}}
        @if($input->hasMedia())
            @if($input->multiple)
                <div class="mb-2">
                    <small class="text-muted d-block mb-1">Current files:</small>
                    @foreach($input->mediaItems() as $mediaItem)
                        <a href="{{ $mediaItem->getUrl() }}" target="_blank"
                           class="badge bg-info text-decoration-none me-1 mb-1">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>{{ $mediaItem->file_name }}
                        </a>
                    @endforeach
                </div>
            @else
                @php $mediaItem = $input->media; @endphp
                <div class="alert alert-info alert-dismissible fade show py-2 mb-2" role="alert">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                    <strong>Current:</strong> {{ $mediaItem->file_name }}
                    <span class="text-muted ms-1">({{ $mediaItem->human_readable_size }})</span>
                    <a href="{{ $mediaItem->getUrl() }}" target="_blank"
                       class="btn btn-sm btn-outline-primary ms-2">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        @endif

        {{-- File input --}}
        <input type="file"
               name="{{ $input->name }}{{ $input->multiple ? '[]' : '' }}"
               id="{{ $input->id }}"
               class="form-control @error($input->name) is-invalid @enderror"
               @if($input->accept)   accept="{{ $input->accept }}"   @endif
               @if($input->multiple) multiple @endif
               @if($input->required) required @endif
               @if($input->disabled) disabled @endif
               {!! $input->extraHtml() !!}>
        @error($input->name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if($input->helpText)
            <div class="form-text">{{ $input->helpText }}</div>
        @endif
    @endif

</div>
