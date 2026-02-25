<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Milenmk\LaravelLocations\Models\City;
use Milenmk\LaravelLocations\Models\Country;
use Yajra\DataTables\Facades\DataTables;

class HolidayController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Holiday::with(['country', 'city'])->orderBy('date');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date_formatted', function (Holiday $holiday) {
                    return $holiday->date->format('M d, Y');
                })
                ->addColumn('type_badge', function (Holiday $holiday) {
                    $class = $holiday->holiday_type === 'global' ? 'bg-primary' : 'bg-info';

                    return '<span class="badge '.$class.'">'.ucfirst($holiday->holiday_type).'</span>';
                })
                ->addColumn('location', function (Holiday $holiday) {
                    if ($holiday->holiday_type === 'global') {
                        return '<span class="text-muted">—</span>';
                    }

                    $location = [];
                    if ($holiday->country) {
                        $location[] = $holiday->country->name;
                    }
                    if ($holiday->city) {
                        $location[] = $holiday->city->name;
                    }

                    return $location ? implode(', ', $location) : '<span class="text-muted">—</span>';
                })
                ->addColumn('recurring_badge', function (Holiday $holiday) {
                    if ($holiday->is_recurring) {
                        return '<span class="badge bg-success"><i class="bi bi-arrow-repeat me-1"></i>Yes</span>';
                    }

                    return '<span class="text-muted">No</span>';
                })
                ->addColumn('status_badge', function (Holiday $holiday) {
                    $class = $holiday->is_active ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$class.'">'.($holiday->is_active ? 'Active' : 'Inactive').'</span>';
                })
                ->addColumn('action', function (Holiday $holiday) {
                    $showUrl = route('holidays.show', $holiday);
                    $editUrl = route('holidays.edit', $holiday);
                    $deleteUrl = route('holidays.destroy', $holiday);

                    return '
                        <div class="btn-group btn-group-sm">
                            <a href="'.$showUrl.'" class="btn btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="'.$editUrl.'" class="btn btn-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-delete"
                                data-url="'.$deleteUrl.'"
                                data-name="'.e($holiday->name).'" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['type_badge', 'location', 'recurring_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('holidays.index');
    }

    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        $cities = City::orderBy('name')->get();

        return view('holidays.create', compact('countries', 'cities'));
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        Holiday::create($request->validated());

        return to_route('holidays.index')->with('status', 'Holiday created successfully.');
    }

    public function show(Holiday $holiday): View
    {
        $holiday->load(['country', 'city']);

        return view('holidays.show', compact('holiday'));
    }

    public function edit(Holiday $holiday): View
    {
        $countries = Country::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $holiday->load(['country', 'city']);

        return view('holidays.edit', compact('holiday', 'countries', 'cities'));
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $holiday->update($request->validated());

        return to_route('holidays.index')->with('status', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $holiday->delete();

        return response()->json(['message' => 'Holiday deleted successfully.']);
    }

    public function getHolidaysForDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
        ]);

        $query = Holiday::active()
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date');

        // Filter by location
        if ($request->country_id || $request->city_id) {
            $query->where(function ($q) use ($request) {
                $q->where('holiday_type', 'global')
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('holiday_type', 'regional');
                        if ($request->country_id) {
                            $q2->where('country_id', $request->country_id);
                        }
                        if ($request->city_id) {
                            $q2->where('city_id', $request->city_id);
                        }
                    });
            });
        }

        $holidays = $query->get()->map(function ($holiday) {
            return [
                'id' => $holiday->id,
                'name' => $holiday->name,
                'date' => $holiday->date->format('Y-m-d'),
                'type' => $holiday->holiday_type,
                'is_recurring' => $holiday->is_recurring,
            ];
        });

        return response()->json($holidays);
    }
}
