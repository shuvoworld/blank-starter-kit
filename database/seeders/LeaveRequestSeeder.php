<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user and leave types
        $user = User::first();
        $annualLeave = LeaveType::where('code', 'AL')->first();
        $sickLeave = LeaveType::where('code', 'SL')->first();

        if (! $user || ! $annualLeave) {
            $this->command->warn('Unable to seed leave requests: missing users or leave types.');

            return;
        }

        $currentYear = now()->year;

        // Sample leave requests
        $requests = [
            [
                'user_id' => $user->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::createFromDate($currentYear, 3, 15)->format('Y-m-d'),
                'end_date' => Carbon::createFromDate($currentYear, 3, 20)->format('Y-m-d'),
                'reason' => 'Family vacation',
                'total_days' => 4,
                'status' => 'approved',
                'year' => $currentYear,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
                'approved_at' => Carbon::now()->subDays(29),
                'approved_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::createFromDate($currentYear, 2, 10)->format('Y-m-d'),
                'end_date' => Carbon::createFromDate($currentYear, 2, 12)->format('Y-m-d'),
                'reason' => 'Medical appointment',
                'total_days' => 2,
                'status' => 'approved',
                'year' => $currentYear,
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(45),
                'approved_at' => Carbon::now()->subDays(44),
                'approved_by' => $user->id,
            ],
            [
                'user_id' => $user->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::createFromDate($currentYear, 6, 1)->format('Y-m-d'),
                'end_date' => Carbon::createFromDate($currentYear, 6, 5)->format('Y-m-d'),
                'reason' => 'Summer vacation planning',
                'total_days' => 3,
                'status' => 'pending',
                'year' => $currentYear,
            ],
        ];

        foreach ($requests as $request) {
            LeaveRequest::firstOrCreate(
                [
                    'user_id' => $request['user_id'],
                    'leave_type_id' => $request['leave_type_id'],
                    'start_date' => $request['start_date'],
                ],
                $request
            );
        }

        $this->command->info('Leave requests seeded successfully.');
    }
}
