<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = now()->year;

        $holidays = [
            [
                'name' => 'New Year\'s Day',
                'date' => $currentYear.'-01-01',
                'holiday_type' => 'global',
                'country_id' => null,
                'city_id' => null,
                'is_recurring' => true,
                'notes' => 'First day of the Gregorian calendar year',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'International Workers\' Day',
                'date' => $currentYear.'-05-01',
                'holiday_type' => 'global',
                'country_id' => null,
                'city_id' => null,
                'is_recurring' => true,
                'notes' => 'Labor Day / May Day',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Independence Day',
                'date' => $currentYear.'-03-26',
                'holiday_type' => 'global',
                'country_id' => null,
                'city_id' => null,
                'is_recurring' => true,
                'notes' => 'National Independence Day',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Victory Day',
                'date' => $currentYear.'-12-16',
                'holiday_type' => 'global',
                'country_id' => null,
                'city_id' => null,
                'is_recurring' => true,
                'notes' => 'Commemorates the victory in the Liberation War',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                [
                    'name' => $holiday['name'],
                    'date' => $holiday['date'],
                ],
                $holiday
            );
        }

        $this->command->info('Holidays seeded successfully.');
    }
}
