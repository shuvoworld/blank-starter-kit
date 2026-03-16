<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Leave Request Pending Approval</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #fd7e14; padding: 28px 32px; }
        .header h1 { margin: 0; color: #fff; font-size: 20px; }
        .body { padding: 32px; }
        .body p { margin: 0 0 16px; line-height: 1.6; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e9ecef; font-size: 14px; }
        .table th { background: #f8f9fa; color: #6c757d; font-weight: 600; width: 40%; }
        .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; margin-top: 8px; }
        .footer { background: #f8f9fa; padding: 16px 32px; font-size: 12px; color: #6c757d; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>New Leave Request Pending Approval</h1>
        </div>
        <div class="body">
            <p>A new leave request has been submitted and requires your approval.</p>

            <table class="table">
                <tr>
                    <th>Employee</th>
                    <td>{{ $leaveRequest->user->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $leaveRequest->user->email }}</td>
                </tr>
                <tr>
                    <th>Leave Type</th>
                    <td>{{ $leaveRequest->leaveType->name }}</td>
                </tr>
                <tr>
                    <th>Start Date</th>
                    <td>{{ $leaveRequest->start_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <th>End Date</th>
                    <td>{{ $leaveRequest->end_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <th>Total Days</th>
                    <td>{{ $leaveRequest->total_days }} day(s)</td>
                </tr>
                @if($leaveRequest->reason)
                <tr>
                    <th>Reason</th>
                    <td>{{ $leaveRequest->reason }}</td>
                </tr>
                @endif
                <tr>
                    <th>Submitted At</th>
                    <td>{{ $leaveRequest->created_at->format('M d, Y H:i') }}</td>
                </tr>
            </table>

            <a href="{{ $reviewUrl }}" class="btn">Review Request</a>
        </div>
        <div class="footer">
            This is an automated message. Please do not reply.
        </div>
    </div>
</body>
</html>
