<?php

namespace App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class BaseController extends Controller
{
    protected string $routePrefix;

    protected string $viewPrefix;

    protected string $resourceName;

    protected string $model;

    protected ?BaseDataTableController $dataTableController = null;

    protected function findRecord(int|string $id): Model
    {
        return ($this->model)::findOrFail($id);
    }

    /**
     * Override to customize flash messages per action.
     * Only specify what differs — merged with defaults.
     */
    protected function messages(): array
    {
        return [];
    }

    private function resolveMessage(string $action): string
    {
        $defaults = [
            'created' => "{$this->resourceName} created successfully.",
            'updated' => "{$this->resourceName} updated successfully.",
            'deleted' => "{$this->resourceName} deleted successfully.",
        ];

        return array_merge($defaults, $this->messages())[$action]
            ?? 'Action completed successfully.';
    }

    protected function successRedirect(string $action): RedirectResponse
    {
        return to_route("{$this->routePrefix}.index")
            ->with('status', $this->resolveMessage($action));
    }

    /**
     * Authorization hook — override to add gate/policy checks.
     * Example: $this->authorize($ability, $record ?? User::class);
     */
    protected function authorizeAction(string $ability, ?Model $record = null): void {}

    public function index(Request $request): View
    {
        $this->authorizeAction('viewAny');
        $tableColumns = $this->dataTableController?->tableColumns() ?? [];
        // JS only needs data/name/orderable/searchable/className — not label or width
        $dtColumns = collect($tableColumns)->map(fn ($col) => [
            'data' => $col['data'],
            'name' => $col['name'],
            'orderable' => $col['orderable'] ?? true,
            'searchable' => $col['searchable'] ?? true,
            'className' => $col['className'] ?? '',
        ])->values()->all();

        return view("{$this->viewPrefix}.index", compact('tableColumns', 'dtColumns'));
    }

    public function create(): View
    {
        $this->authorizeAction('create');

        return view("{$this->viewPrefix}.form", array_merge(
            ['editing' => false, 'record' => null],
            $this->createViewData()
        ));
    }

    /**
     * Override to pass extra data to the form view on create.
     */
    protected function createViewData(): array
    {
        return [];
    }

    /**
     * Default redirects to edit. Override when show renders a dedicated view.
     */
    public function show(int|string $record): View|RedirectResponse
    {
        return to_route("{$this->routePrefix}.edit", $record);
    }

    public function edit(int|string $record): View
    {
        $model = $this->findRecord($record);
        $this->authorizeAction('update', $model);

        return view("{$this->viewPrefix}.form", array_merge(
            ['editing' => true, 'record' => $model],
            $this->editViewData($model)
        ));
    }

    /**
     * Override to pass extra data to the form view on edit.
     * $record is always available in the view — no need to return it here.
     */
    protected function editViewData(Model $record): array
    {
        return [];
    }

    /**
     * Handles both ajax (JSON) and standard (redirect) delete requests.
     * Override beforeDestroy() for guards instead of overriding this method.
     */
    public function destroy(int|string $record): JsonResponse|RedirectResponse
    {
        $model = $this->findRecord($record);
        $this->authorizeAction('delete', $model);

        $this->beforeDestroy($model);
        $model->delete();
        $this->afterDestroy($model);

        if (request()->ajax()) {
            return response()->json(['message' => $this->resolveMessage('deleted')]);
        }

        return $this->successRedirect('deleted');
    }

    /**
     * Hook called before deletion.
     * Throw an exception or abort() here to cancel the delete.
     *
     * Example:
     *   protected function beforeDestroy(Model $record): void {
     *       abort_if($record->is_system, 403, 'System records cannot be deleted.');
     *   }
     */
    protected function beforeDestroy(Model $record): void {}

    /**
     * Hook called after deletion.
     * Use for cleanup, logging, firing events, etc.
     */
    protected function afterDestroy(Model $record): void {}
}
