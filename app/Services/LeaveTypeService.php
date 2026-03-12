<?php

namespace App\Services;

use App\Events\LeaveTypeCreated;
use App\Events\LeaveTypeDeleted;
use App\Events\LeaveTypeUpdated;
use App\Models\LeaveType;
use App\Services\BaseService\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeaveTypeService extends BaseService
{
    public function __construct()
    {
        $this->modelClass = LeaveType::class;
    }

    // ── BEFORE hooks — validation & guards (no side effects here) ────────

    protected function beforeCreate(array $data): void
    {
        $this->validateBusinessRules($data);
        Log::info('LeaveType about to be created', ['data' => $data]);
    }

    protected function beforeUpdate(Model $record, array $data): void
    {
        $this->validateBusinessRules($data, $record);
        Log::info('LeaveType about to be updated', ['id' => $record->id]);
    }

    protected function beforeDelete(Model $record): void
    {
        // Guard: block deletion if active leave requests exist
        $inUse = $record->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($inUse) {
            throw ValidationException::withMessages([
                'leave_type' => 'Cannot delete a leave type with active leave requests.',
            ]);
        }
    }

    // ── AFTER hooks — side effects (emails, events, cache clearing) ───────

    protected function afterCreate(Model $record, array $data): void
    {
        // event(new LeaveTypeCreated($record));
        Log::info('LeaveType created', ['id' => $record->id, 'name' => $record->name]);
    }

    protected function afterUpdate(Model $record, array $data): void
    {
        // event(new LeaveTypeUpdated($record));
        Log::info('LeaveType updated', ['id' => $record->id, 'changes' => $record->getChanges()]);
    }

    protected function afterDelete(Model $record): void
    {
        // event(new LeaveTypeDeleted($record));
        Log::warning('LeaveType deleted', ['id' => $record->id, 'name' => $record->name]);
    }

    // ── Business rules ────────────────────────────────────────────────────

    private function validateBusinessRules(array $data, ?LeaveType $existing = null): void
    {
        $this->validateRequireApproval($data);
        $this->validateGenderSpecific($data, $existing);
    }

    private function validateRequireApproval(array $data): void
    {
        if (isset($data['requires_approval']) && $data['requires_approval'] != 1) {
            throw ValidationException::withMessages([
                'requires_approval' => 'Requires approval must be enabled.',
            ]);
        }
    }

    private function validateGenderSpecific(array $data, ?LeaveType $existing = null): void
    {
        if (!empty($data['is_gender_specific']) && empty($data['applicable_gender'])) {
            throw ValidationException::withMessages([
                'applicable_gender' => 'Applicable gender is required when leave type is gender specific.',
            ]);
        }

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