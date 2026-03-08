<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Milenmk\LaravelLocations\Models\City;
use Milenmk\LaravelLocations\Models\Country;

class HolidayController extends BaseController
{
    public function __construct(HolidayDataTableController $dataTableController)
    {
        $this->model = Holiday::class;
        $this->routePrefix = 'holidays';
        $this->viewPrefix = 'holidays';
        $this->resourceName = 'Holiday';
        $this->dataTableController = $dataTableController;
    }

    protected function createViewData(): array
    {
        return [
            'countries' => Country::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ];
    }

    protected function editViewData(Model $record): array
    {
        $record->load(['country', 'city']);

        return [
            'countries' => Country::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ];
    }

    public function show(int|string $record): View
    {
        $holiday = $this->findRecord($record);
        $holiday->load(['country', 'city']);
        $this->authorizeAction('view', $holiday);

        return view('holidays.show', compact('holiday'));
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        $this->authorizeAction('create');

        Holiday::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateHolidayRequest $request, int|string $record): RedirectResponse
    {
        $holiday = $this->findRecord($record);
        $this->authorizeAction('update', $holiday);

        $holiday->update($request->validated());

        return $this->successRedirect('updated');
    }

    /**
     * Get holidays for a date range (used for leave calculations).
     */
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
