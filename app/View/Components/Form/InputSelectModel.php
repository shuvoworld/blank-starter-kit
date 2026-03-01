<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class InputSelectModel extends BaseInput
{
    public Collection $options;
    public ?string    $prompt;
    public bool       $multiple;

    public function __construct(array $params = [])
    {
        parent::__construct(
            name:       $params['name']       ?? '',
            label:      $params['label']      ?? null,
            type:       'select',
            value:      $params['value']      ?? $params['val'] ?? null,
            id:         $params['id']         ?? null,
            divClass:   $params['div']        ?? $params['container_class'] ?? 'col-md-3',
            inputClass: trim('form-select ' . ($params['class'] ?? '')),
            labelClass: $params['label_class'] ?? null,
            required:   $params['required']   ?? false,
            disabled:   $params['disabled']   ?? false,
            tooltip:    $params['tooltip']    ?? null,
            extraAttrs: $params['params']     ?? [],
        );

        $this->inputClass = trim('form-select ' . ($params['class'] ?? ''));
        $this->prompt     = $params['prompt']   ?? null;
        $this->multiple   = $params['multiple'] ?? false;
        $this->options    = $this->resolveOptions($params);
    }

    private function resolveOptions(array $params): Collection
    {
        $modelClass  = $params['model']       ?? null;
        $keyField    = $params['key_field']   ?? 'id';
        $labelField  = $params['label_field'] ?? 'name';
        $conditions  = $params['conditions']  ?? [];
        $scopes      = $params['scopes']      ?? [];
        $orderBy     = $params['order_by']    ?? [$labelField, 'asc'];
        $queryFn     = $params['query']       ?? null;

        if (! $modelClass) {
            return collect();
        }

        $query = $modelClass::query();

        foreach ($conditions as $column => $val) {
            $query->where($column, $val);
        }

        foreach ($scopes as $scope) {
            is_array($scope)
                ? $query->{$scope[0]}(...array_slice($scope, 1))
                : $query->$scope();
        }

        if ($queryFn instanceof Closure) {
            $query = $queryFn($query) ?? $query;
        }

        return $query
            ->orderBy(...(array) $orderBy)
            ->get()
            ->mapWithKeys(fn (Model $row) => [
                $row->{$keyField} => $row->{$labelField},
            ]);
    }

    public function isSelected(mixed $optVal): bool
    {
        $current = $this->hasOldInput() ? old($this->oldKey) : $this->value;

        if (is_array($current)) {
            return in_array((string) $optVal, array_map('strval', $current));
        }

        return (string) $optVal === (string) $current;
    }

    public function render()
    {
        return view('components.form.input-select-model');
    }
}
