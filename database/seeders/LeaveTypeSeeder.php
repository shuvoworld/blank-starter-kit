<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Paid time off for vacation, personal time, or rest and relaxation.',
                'is_paid' => true,
                'requires_approval' => true,
                'max_days_per_year' => 20,
                'max_days_per_month' => null,
                'carry_forward' => true,
                'carry_forward_limit' => 5,
                'carry_forward_expiry_days' => 90,
                'requires_document' => false,
                'min_days_before_request' => 3,
                'max_consecutive_days' => null,
                'is_gender_specific' => false,
                'applicable_gender' => null,
                'is_paid_pro_rata' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Paid time off for illness or medical appointments.',
                'is_paid' => true,
                'requires_approval' => true,
                'max_days_per_year' => 10,
                'max_days_per_month' => 3,
                'carry_forward' => false,
                'carry_forward_limit' => null,
                'carry_forward_expiry_days' => null,
                'requires_document' => true,
                'min_days_before_request' => null,
                'max_consecutive_days' => 5,
                'is_gender_specific' => false,
                'applicable_gender' => null,
                'is_paid_pro_rata' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'description' => 'Short-term leave for personal reasons or emergencies.',
                'is_paid' => true,
                'requires_approval' => true,
                'max_days_per_year' => 12,
                'max_days_per_month' => 2,
                'carry_forward' => false,
                'carry_forward_limit' => null,
                'carry_forward_expiry_days' => null,
                'requires_document' => false,
                'min_days_before_request' => 1,
                'max_consecutive_days' => 3,
                'is_gender_specific' => false,
                'applicable_gender' => null,
                'is_paid_pro_rata' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Paid time off for expectant mothers before and after childbirth.',
                'is_paid' => true,
                'requires_approval' => true,
                'max_days_per_year' => 90,
                'max_days_per_month' => null,
                'carry_forward' => false,
                'carry_forward_limit' => null,
                'carry_forward_expiry_days' => null,
                'requires_document' => true,
                'min_days_before_request' => 30,
                'max_consecutive_days' => null,
                'is_gender_specific' => true,
                'applicable_gender' => 'female',
                'is_paid_pro_rata' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Paid time off for new fathers to bond with their child.',
                'is_paid' => true,
                'requires_approval' => true,
                'max_days_per_year' => 14,
                'max_days_per_month' => null,
                'carry_forward' => false,
                'carry_forward_limit' => null,
                'carry_forward_expiry_days' => null,
                'requires_document' => true,
                'min_days_before_request' => 7,
                'max_consecutive_days' => null,
                'is_gender_specific' => true,
                'applicable_gender' => 'male',
                'is_paid_pro_rata' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'description' => 'Unpaid time off for extended personal reasons when paid leave is exhausted.',
                'is_paid' => false,
                'requires_approval' => true,
                'max_days_per_year' => null,
                'max_days_per_month' => null,
                'carry_forward' => false,
                'carry_forward_limit' => null,
                'carry_forward_expiry_days' => null,
                'requires_document' => false,
                'min_days_before_request' => 7,
                'max_consecutive_days' => null,
                'is_gender_specific' => false,
                'applicable_gender' => null,
                'is_paid_pro_rata' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }

        $this->command->info('Leave types seeded successfully.');
    }
}
