@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Edit Landing Page Section</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('landing-page-sections.index') }}">Landing Page</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-pencil me-2"></i> Edit Section
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('landing-page-sections.update', $landingPageSection) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" value="{{ ucfirst($landingPageSection->section) }}" readonly>
                            <input type="hidden" name="section" value="{{ $landingPageSection->section }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Key</label>
                            <input type="text" class="form-control" value="{{ $landingPageSection->key }}" readonly>
                            <input type="hidden" name="key" value="{{ $landingPageSection->key }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label</label>
                            <input type="text" class="form-control" value="{{ $landingPageSection->label }}" readonly>
                            <input type="hidden" name="label" value="{{ $landingPageSection->label }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst($landingPageSection->type) }}" readonly>
                            <input type="hidden" name="type" value="{{ $landingPageSection->type }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Value</label>
                            @if($landingPageSection->type === 'textarea')
                                <textarea class="form-control" name="value" rows="6">{{ old('value', $landingPageSection->value) }}</textarea>
                            @elseif($landingPageSection->type === 'text')
                                <input type="text" class="form-control" name="value" value="{{ old('value', $landingPageSection->value) }}">
                            @elseif($landingPageSection->type === 'image')
                                <input type="text" class="form-control" name="value" value="{{ old('value', $landingPageSection->value) }}" placeholder="Image URL">
                                <div class="form-text">Enter the image URL</div>
                            @elseif($landingPageSection->type === 'boolean')
                                <select class="form-select" name="value">
                                    <option value="1" {{ ($landingPageSection->value == '1') ? 'selected' : '' }}>True</option>
                                    <option value="0" {{ ($landingPageSection->value == '0') ? 'selected' : '' }}>False</option>
                                </select>
                            @elseif($landingPageSection->type === 'number')
                                <input type="number" class="form-control" name="value" value="{{ old('value', $landingPageSection->value) }}">
                            @else
                                <textarea class="form-control" name="value" rows="4">{{ old('value', $landingPageSection->value) }}</textarea>
                            @endif
                            @error('value')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $landingPageSection->sort_order) }}">
                            @error('sort_order')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $landingPageSection->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Save Changes
                                </button>
                                <a href="{{ route('landing-page-sections.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i> Cancel
                                </a>
                                <a href="/" target="_blank" class="btn btn-outline-primary ms-auto">
                                    <i class="bi bi-eye me-1"></i> Preview Page
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i> Section Info
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Section:</th>
                        <td><span class="badge bg-primary">{{ ucfirst($landingPageSection->section) }}</span></td>
                    </tr>
                    <tr>
                        <th>Key:</th>
                        <td><code>{{ $landingPageSection->key }}</code></td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td><span class="badge bg-info">{{ ucfirst($landingPageSection->type) }}</span></td>
                    </tr>
                    <tr>
                        <th>Sort Order:</th>
                        <td>{{ $landingPageSection->sort_order }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($landingPageSection->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-eye me-2"></i> Usage
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Use this value in your blade templates:</p>
                <div class="bg-dark text-light rounded p-2">
                    <code class="small">\App\Models\LandingPageSection::getValue('{{ $landingPageSection->key }}')</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
