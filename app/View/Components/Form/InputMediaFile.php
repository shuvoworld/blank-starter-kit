<?php

namespace App\View\Components\Form;

use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InputMediaFile extends BaseInput
{
    /** @var Media|MediaCollection|null */
    public mixed $media;

    /** Explicit URL override for image preview (e.g. a 'thumb' conversion). */
    public ?string $previewUrl;

    /** Rendering style: 'image-circle' | 'image' | 'file' */
    public string $inputType;

    public bool $multiple;

    public ?string $accept;

    public ?string $helpText;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name: $params['name'] ?? '',
            label: $params['label'] ?? null,
            type: 'file',
            id: $params['id'] ?? null,
            divClass: $params['div'] ?? $params['container_class'] ?? 'col-md-12',
            inputClass: $params['class'] ?? '',
            labelClass: $params['label_class'] ?? null,
            required: $params['required'] ?? false,
            disabled: $params['disabled'] ?? false,
            tooltip: $params['tooltip'] ?? null,
            extraAttrs: $params['params'] ?? [],
        );

        $this->media = $params['media'] ?? null;
        $this->previewUrl = $params['preview_url'] ?? null;
        $this->inputType = $params['type'] ?? 'file';
        $this->multiple = $params['multiple'] ?? false;
        $this->accept = $params['accept'] ?? null;
        $this->helpText = $params['help_text'] ?? null;
    }

    public function hasMedia(): bool
    {
        if ($this->media instanceof Media) {
            return true;
        }

        if ($this->media instanceof MediaCollection) {
            return $this->media->isNotEmpty();
        }

        return false;
    }

    public function isImageType(): bool
    {
        return in_array($this->inputType, ['image', 'image-circle'], true);
    }

    /**
     * Returns iterable of Media objects — works for both single and collection.
     *
     * @return iterable<Media>
     */
    public function mediaItems(): iterable
    {
        if ($this->media instanceof Media) {
            return [$this->media];
        }

        return $this->media ?? [];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('layouts.form.components.input-media-file');
    }
}
