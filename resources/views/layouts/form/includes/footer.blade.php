<?php
?>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>
        {{ $editing ? 'Update' : 'Create' }} {{$moduleTitle}}
    </button>
    <a href="{{ route($module.'.index') }}" class="btn btn-secondary">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
</div>