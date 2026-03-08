<?php
$module = $module ?? null;
$element = $element ?? null;
$moduleTitle = $moduleTitle ?? null;
$permissionKey = str_replace('-', ' ', $module);
?>
<div class="mt-3">
    @if(isset($module))
        <a href="{{ route($module.'.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to {{$moduleTitle}}
        </a>
        @can('update '.$permissionKey)
            <a href="{{ route($module.'.edit', $element) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit {{$moduleTitle}}
            </a>
        @endcan
    @endif
</div>
