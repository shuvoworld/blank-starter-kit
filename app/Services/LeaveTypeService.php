<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Services\BaseService\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class LeaveTypeService extends BaseService
{
    public function __construct()
    {
        $this->modelClass = LeaveType::class;
    }

    protected function beforeCreate(array $data): void
    {
        $this->validateBusinessRules($data);
    }

    protected function beforeUpdate(Model $record, array $data): void
    {
        $this->validateBusinessRules($data, $record);
    }

    private function validateBusinessRules(array $data, ?LeaveType $existing = null): void
    {
        $this->validateRequireApproval($data);
        $this->validateGenderSpecific($data, $existing);
    }

    private function validateRequireApproval(array $data): void
    {
        // requires approval 1 custom validation
        if (isset($data['requires_approval']) && $data['requires_approval'] != 1) {
            throw ValidationException::withMessages([
                'requires_approval' => 'Carry forward limit cannot exceed max days per year.',
            ]);
        }


    }

    private function validateGenderSpecific(array $data, ?LeaveType $existing = null): void
    {
        // applicable_gender required when gender specific
        if (!empty($data['is_gender_specific']) && empty($data['applicable_gender'])) {
            throw ValidationException::withMessages([
                'applicable_gender' => 'Applicable gender is required when leave type is gender specific.',
            ]);
        }

        // no duplicate active gender-specific leave type — DB check
        if (!empty($data['is_gender_specific']) && !empty($data['applicable_gender'])) {
            $exists = LeaveType::where('applicable_gender', $data['applicable_gender'])
                ->where('is_active', true)
                ->when($existing, fn($q) => $q->whereNot('id', $existing->id))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'applicable_gender' => 'An active leave type for this gender already exists.',
                ]);
            }
        }
    }
}