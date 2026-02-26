# Module Development Guide

This guide provides instructions for developing modules in the Blank Starter Kit application.

## Table of Contents

1. [Overview](#overview)
2. [File Upload Guidelines](#file-upload-guidelines)
3. [Creating a New Module](#creating-a-new-module)
4. [Spatie Media Library Integration](#spatie-media-library-integration)
5. [Best Practices](#best-practices)

---

## Overview

This application uses **Laravel 12** with the following key packages:
- **Spatie Permission** - Role & Permission management
- **Spatie Media Library** - File & Media management
- **Spatie Activity Log** - Activity tracking
- **Yajra DataTables** - Server-side tables

---

## File Upload Guidelines

### Supported File Types

| File Type | Purpose | Allowed Extensions | Max Size |
|-----------|---------|-------------------|----------|
| **Images** | Profile pictures, avatars | jpg, jpeg, png, gif, webp | 5 MB |
| **Documents** | Resumes, certificates, PDFs | pdf only | 5 MB per file |

### Media Collections

| Collection Name | Type | Max Files | Purpose |
|-----------------|------|-----------|---------|
| `profile_picture` | Image | 1 | User/Employee profile photo |
| `resume` | Document (PDF) | 1 | Employee resume/CV |
| `certificates` | Document (PDF) | 5 | Employee certificates |
| `documents` | Document (PDF) | 10 | Other employee documents |

### File Upload Validation Rules

```php
// Image files (profile pictures)
'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'];

// PDF documents (single)
'resume' => ['nullable', 'file', 'mimes:pdf', 'max:5120'];

// PDF documents (multiple)
'certificates' => ['nullable', 'array', 'max:5'],
'certificates.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
'documents' => ['nullable', 'array', 'max:10'],
'documents.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120'];
```

### Form Requirements

When creating file upload forms:

1. **Add enctype to form tag:**
   ```blade
   <form action="{{ route('route.name') }}" method="POST" enctype="multipart/form-data">
   ```

2. **Use appropriate accept attribute:**
   ```blade
   <!-- Images -->
   <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp">

   <!-- PDF Documents -->
   <input type="file" name="resume" accept=".pdf,application/pdf">
   ```

3. **For multiple file uploads:**
   ```blade
   <input type="file" name="certificates[]" accept=".pdf,application/pdf" multiple>
   ```

### Storing Files

```php
// Single file (replaces existing)
if ($request->hasFile('profile_picture')) {
    $employee->clearMediaCollection('profile_picture');
    $employee->addMediaFromRequest('profile_picture')
        ->toMediaCollection('profile_picture');
}

// Multiple files (adds to existing)
if ($request->hasFile('certificates')) {
    foreach ($request->file('certificates') as $file) {
        $employee->addMedia($file)
            ->toMediaCollection('certificates');
    }
}
```

### Retrieving Files

```php
// Get single file
$url = $employee->getFirstMediaUrl('profile_picture');
$media = $employee->getFirstMedia('profile_picture');

// Get multiple files
$certificates = $employee->getMedia('certificates');

// Get file with conversion (thumbnail)
$thumbUrl = $employee->getFirstMediaUrl('profile_picture', 'thumb');
```

### Displaying Files in Views

```blade
<!-- Display image -->
@if($employee->getFirstMediaUrl('profile_picture'))
    <img src="{{ $employee->getFirstMediaUrl('profile_picture') }}" alt="{{ $employee->name }}">
@else
    <div class="placeholder">No image</div>
@endif

<!-- Display documents list -->
@foreach($employee->getMedia('certificates') as $cert)
    <a href="{{ $cert->getUrl() }}" target="_blank">
        {{ $cert->file_name }}
    </a>
@endforeach
```

---

## Creating a New Module

### 1. Model Setup

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        // Your fields...
    ];

    // Define media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->useDisk('public');
    }

    // Define media conversions (thumbnails, etc.)
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10);
    }
}
```

### 2. Migration

```php
Schema::create('your_models', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    // Add your columns...
    $table->timestamps();
});
```

### 3. Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\YourModel;
use App\Http\Requests\StoreYourModelRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class YourModelController extends Controller
{
    public function index(): View
    {
        $items = YourModel::latest()->paginate(10);
        return view('your_models.index', compact('items'));
    }

    public function create(): View
    {
        return view('your_models.create');
    }

    public function store(StoreYourModelRequest $request): RedirectResponse
    {
        $item = YourModel::create($request->validated());

        // Handle file upload
        if ($request->hasFile('thumbnail')) {
            $item->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        return to_route('your_models.index')
            ->with('status', 'Item created successfully.');
    }

    public function show(YourModel $yourModel): View
    {
        return view('your_models.show', ['item' => $yourModel]);
    }

    public function edit(YourModel $yourModel): View
    {
        return view('your_models.edit', ['item' => $yourModel]);
    }

    public function update(UpdateYourModelRequest $request, YourModel $yourModel): RedirectResponse
    {
        $yourModel->update($request->validated());

        // Handle file update
        if ($request->hasFile('thumbnail')) {
            $yourModel->clearMediaCollection('thumbnail');
            $yourModel->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        return to_route('your_models.index')
            ->with('status', 'Item updated successfully.');
    }

    public function destroy(YourModel $yourModel): RedirectResponse
    {
        $yourModel->delete();
        return back()->with('status', 'Item deleted successfully.');
    }
}
```

### 4. Form Request Validation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreYourModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
        ];
    }
}
```

### 5. Routes

```php
// routes/web.php
Route::resource('your-models', YourModelController::class);
```

### 6. Views

**Create Form:**
```blade
<form action="{{ route('your-models.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="text" name="name" required>
    <input type="file" name="thumbnail" accept="image/*">
    <button type="submit">Submit</button>
</form>
```

---

## Spatie Media Library Integration

### Model Requirements

1. Implement `HasMedia` interface
2. Use `InteractsWithMedia` trait
3. Define collections in `registerMediaCollections()`
4. Define conversions in `registerMediaConversions()`

### Common Methods

| Method | Description |
|--------|-------------|
| `addMedia($file)` | Add a file from path |
| `addMediaFromRequest($field)` | Add from form request |
| `addMediaFromUrl($url)` | Add from remote URL |
| `getMedia($collection)` | Get all files from collection |
| `getFirstMedia($collection)` | Get first file |
| `getFirstMediaUrl($collection, $conversion)` | Get file URL |
| `clearMediaCollection($collection)` | Remove all files |

---

## Best Practices

### Security
- Always validate file types and sizes
- Use `mimes` and `max` validation rules
- Never trust client-side validation only
- Scan uploaded files for malware in production

### Performance
- Use media conversions for thumbnails
- Lazy load media collections when possible
- Consider using queues for bulk uploads

### User Experience
- Show file size and type hints
- Display upload progress for large files
- Allow file replacement without deleting entire record
- Show existing files with download options in edit forms

### Storage
- Use `public` disk for user-accessible files
- Use `s3` or cloud storage for production
- Configure proper permissions
- Regular cleanup of orphaned media files

---

## Quick Reference

### Standard File Upload Rules

```php
// Images (JPG, PNG, GIF, WebP - Max 5MB)
'avatar' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120']

// PDF Documents (Max 5MB)
'document' => ['nullable', 'file', 'mimes:pdf', 'max:5120']

// Multiple PDF (Max 5 files, 5MB each)
'documents' => ['nullable', 'array', 'max:5'],
'documents.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120']
```

### Standard Media Collections

```php
// Single image
$this->addMediaCollection('avatar')
    ->singleFile()
    ->acceptsMimeTypes(['image/jpeg', 'image/png'])
    ->useDisk('public');

// Multiple documents
$this->addMediaCollection('documents')
    ->acceptsMimeTypes(['application/pdf'])
    ->useDisk('public');
```

---

For more information, refer to:
- [Spatie Media Library Docs](https://spatie.be/docs/laravel-medialibrary/v11)
- [Laravel Validation Docs](https://laravel.com/docs/validation)
