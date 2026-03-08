<?php
?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-clock-history me-2"></i>
                Record Info
            </h3>
        </div>

        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted">ID:</td>
                    <td>{{ $element->id }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Sort Order:</td>
                    <td>{{ $element->sort_order }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Created:</td>
                    <td>{{ $element->created_at->format('M d, Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Updated:</td>
                    <td>{{ $element->updated_at->format('M d, Y H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
