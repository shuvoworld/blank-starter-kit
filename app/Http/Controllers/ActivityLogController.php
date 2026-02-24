<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Activity::with('causer')->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('log_name_badge', function (Activity $activity) {
                    $colours = [
                        'user' => 'primary',
                        'employee' => 'success',
                        'product' => 'info',
                        'role' => 'warning',
                        'permission' => 'secondary',
                    ];
                    $colour = $colours[$activity->log_name] ?? 'dark';

                    return '<span class="badge bg-'.$colour.'">'.ucfirst($activity->log_name).'</span>';
                })
                ->addColumn('event_badge', function (Activity $activity) {
                    $colours = [
                        'created' => 'success',
                        'updated' => 'primary',
                        'deleted' => 'danger',
                    ];
                    $colour = $colours[$activity->event] ?? 'secondary';
                    $icons = [
                        'created' => 'bi-plus-circle',
                        'updated' => 'bi-pencil',
                        'deleted' => 'bi-trash',
                    ];
                    $icon = $icons[$activity->event] ?? 'bi-activity';

                    return '<span class="badge bg-'.$colour.'"><i class="bi '.$icon.' me-1"></i>'.ucfirst($activity->event ?? '—').'</span>';
                })
                ->addColumn('subject_info', function (Activity $activity) {
                    if (! $activity->subject_type) {
                        return '<span class="text-muted">—</span>';
                    }
                    $type = class_basename($activity->subject_type);
                    $id = $activity->subject_id;

                    // Try to resolve the subject name from stored properties
                    $attrs = $activity->properties->get('attributes', []);
                    $old = $activity->properties->get('old', []);
                    $name = $attrs['name'] ?? $old['name'] ?? $attrs['email'] ?? null;

                    $display = $name ? e($name) : ('#'.$id);

                    return '<span class="badge bg-light text-dark border me-1">'.$type.'</span>'.$display;
                })
                ->addColumn('causer_name', function (Activity $activity) {
                    if (! $activity->causer) {
                        return '<span class="text-muted">System</span>';
                    }

                    return e($activity->causer->name ?? $activity->causer->email ?? 'Unknown');
                })
                ->addColumn('created_at_formatted', function (Activity $activity) {
                    return '<span title="'.$activity->created_at->toDateTimeString().'">'
                        .$activity->created_at->diffForHumans()
                        .'</span>';
                })
                ->addColumn('action', function (Activity $activity) {
                    $showUrl = route('activity-log.show', $activity);

                    return '<a href="'.$showUrl.'" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>';
                })
                ->rawColumns(['log_name_badge', 'event_badge', 'subject_info', 'causer_name', 'created_at_formatted', 'action'])
                ->make(true);
        }

        $logNames = Activity::distinct()->orderBy('log_name')->pluck('log_name');

        return view('activity-log.index', compact('logNames'));
    }

    public function show(Activity $activity): View
    {
        return view('activity-log.show', compact('activity'));
    }
}
