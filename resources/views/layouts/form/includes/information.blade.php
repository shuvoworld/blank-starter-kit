<?php
?>
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-info-circle me-2"></i>
            Information
        </h3>
    </div>
    <div class="card-body">
        <p class="text-muted small"><i class="bi bi-dot"></i> Fields marked with <span class="text-danger">*</span> are required.</p>
        <p class="text-muted small"><i class="bi bi-dot"></i> <strong>Global {{$module}}</strong> apply to all employees.</p>
        <p class="text-muted small"><i class="bi bi-dot"></i> <strong>Regional {{$module}}</strong> apply to specific countries or cities.</p>
        <p class="text-muted small"><i class="bi bi-dot"></i> <strong>Recurring {{$module}}</strong> repeat every year on the same date.</p>
        <p class="text-muted small mb-0"><i class="bi bi-dot"></i> {{str_replace('-',' ',ucfirst($module))}} are excluded from leave day calculations.</p>
    </div>
</div>

