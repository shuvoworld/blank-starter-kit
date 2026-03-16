<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmailLogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = EmailLog::query()->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('recipients', function (EmailLog $log): string {
                    $to = collect($log->to)->map(fn ($e) => '<span class="badge bg-light text-dark border me-1">'.e($e).'</span>')->implode('');

                    return $to ?: '<span class="text-muted">—</span>';
                })
                ->addColumn('status_badge', function (EmailLog $log): string {
                    return $log->status === 'sent'
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>'
                        : '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>';
                })
                ->addColumn('mailable_short', function (EmailLog $log): string {
                    if (! $log->mailable_class) {
                        return '<span class="text-muted">—</span>';
                    }

                    return '<span title="'.e($log->mailable_class).'" class="text-truncate d-inline-block" style="max-width:180px">'.e(class_basename($log->mailable_class)).'</span>';
                })
                ->addColumn('sent_at_formatted', function (EmailLog $log): string {
                    if (! $log->sent_at) {
                        return '<span class="text-muted">—</span>';
                    }

                    return '<span title="'.$log->sent_at->toDateTimeString().'">'.$log->sent_at->diffForHumans().'</span>';
                })
                ->addColumn('action', function (EmailLog $log): string {
                    $showUrl = route('email-logs.show', $log);

                    return '<a href="'.$showUrl.'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>';
                })
                ->rawColumns(['recipients', 'status_badge', 'mailable_short', 'sent_at_formatted', 'action'])
                ->make(true);
        }

        return view('email-logs.index');
    }

    public function show(EmailLog $emailLog): View
    {
        return view('email-logs.show', compact('emailLog'));
    }
}
