<?php
$permissionKey = str_replace('-', ' ', $module);
?>
@can('delete '.$permissionKey)
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Danger Zone
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted small">Once deleted, this {{$permissionKey}} record cannot be recovered.</p>
            <form action="{{ route($module.'.destroy', $record) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete {{ $record->name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i> Delete {{$moduleTitle}}
                </button>
            </form>
        </div>
    </div>
@endcan
