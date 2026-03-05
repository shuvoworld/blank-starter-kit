<?php
?>
<div class="card-body">
    <table class="table table-sm table-borderless mb-0">
        <tr>
            <td class="text-muted">ID:</td>
            <td>{{ $leaveType->id }}</td>
        </tr>
        <tr>
            <td class="text-muted">Sort Order:</td>
            <td>{{ $leaveType->sort_order }}</td>
        </tr>
        <tr>
            <td class="text-muted">Created:</td>
            <td>{{ $leaveType->created_at->format('M d, Y H:i') }}</td>
        </tr>
        <tr>
            <td class="text-muted">Updated:</td>
            <td>{{ $leaveType->updated_at->format('M d, Y H:i') }}</td>
        </tr>
    </table>
</div>

