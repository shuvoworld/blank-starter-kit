<?php

namespace App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;
use App\Services\BaseService\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

abstract class BaseController extends Controller
{
    protected string $routePrefix;

    protected string $viewPrefix;

    protected string $resourceName;

    protected string $model;

    protected ?BaseDataTableController $dataTableController = null;

    /**
     * Optional service — only set if the child controller needs one.
     * If null, BaseController handles create/update directly.
     */
    protected ?BaseService $service = null;

    // ─────────────────────────────────────────────
    // Core Helpers
    // ─────────────────────────────────────────────

    protected function findRecord(int|string $id): Model
    {
        return ($this->model)::findOrFail($id);
    }

    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        $this->authorize($ability, $record ?? $this->model);
    }

    protected function successRedirect(string $action): RedirectResponse
    {
        return to_route("{$this->routePrefix}.index")
            ->with('status', $this->resolveMessage($action));
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

    /**
     * Resolves the form request — either the specific class defined in
     * requestClass() or falls back to the incoming request as-is.
     */
    private function resolveRequest(Request $request): FormRequest|Request
    {
        return $this->requestClass()
            ? app($this->requestClass())
            : $request;
    }

    // ─────────────────────────────────────────────
    // Overridable Hooks
    // ─────────────────────────────────────────────

    /**
     * Override to define the form request class for store and update.
     * If null, falls back to the incoming request with no validation.
     *
     * Example:
     *   protected function requestClass(): string {
     *       return LeaveTypeRequest::class;
     *   }
     */
    protected function requestClass(): ?string
    {
        return null;
    }

    /**
     * Override to customize flash messages per action.
     * Only specify what differs — merged with defaults.
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Override to define file fields and their storage paths.
     * Example: ['supporting_document' => 'leave-types/documents']
     */
    protected function fileFields(): array
    {
        return [];
    }

    /**
     * Override to pass extra data to the form view on create.
     */
    protected function createViewData(): array
    {
        return [];
    }

    /**
     * Override to pass extra data to the form view on edit.
     */
    protected function editViewData(Model $record): array
    {
        return [];
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
     * Automatically cleans up file fields after deletion.
     * Override to add extra cleanup logic.
     */
    protected function afterDestroy(Model $record): void
    {
        foreach (array_keys($this->fileFields()) as $field) {
            if ($record->$field) {
                Storage::disk('public')->delete($record->$field);
            }
        }
    }

    // ─────────────────────────────────────────────
    // File Handling
    // ─────────────────────────────────────────────

    protected function handleFileUploads(FormRequest|Request $request, array $data, ?Model $existing = null): array
    {
        foreach ($this->fileFields() as $field => $storagePath) {
            if ($request->hasFile($field)) {
                if ($existing?->$field) {
                    Storage::disk('public')->delete($existing->$field);
                }
                $data[$field] = $request->file($field)->store($storagePath, 'public');
            } else {
                unset($data[$field]);
            }
        }

        return $data;
    }

    // ─────────────────────────────────────────────
    // CRUD Actions
    // ─────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorizeAction('viewAny');

        $tableColumns = $this->dataTableController?->tableColumns() ?? [];
        $dtColumns    = collect($tableColumns)->map(fn($col) => [
            'data'       => $col['data'],
            'name'       => $col['name'],
            'orderable'  => $col['orderable'] ?? true,
            'searchable' => $col['searchable'] ?? true,
            'className'  => $col['className'] ?? '',
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
     * Default redirects to edit.
     * Override when show renders a dedicated view.
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
     * If a service is set, delegates to service->create() which handles
     * business rules and after-create logic internally.
     * Otherwise, creates the model directly.
     * ValidationException is handled automatically by Laravel.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('create');

        $formRequest = $this->resolveRequest($request);
        $data        = $this->handleFileUploads($formRequest, $formRequest->validated());

        $this->service
            ? $this->service->create($data)
            : ($this->model)::create($data);

        return $this->successRedirect('created');
    }

    /**
     * If a service is set, delegates to service->update() which handles
     * business rules and after-update logic internally.
     * Otherwise, updates the model directly.
     * ValidationException is handled automatically by Laravel.
     */
    public function update(Request $request, int|string $record): RedirectResponse
    {
        $model = $this->findRecord($record);
        $this->authorizeAction('update', $model);

        $formRequest = $this->resolveRequest($request);
        $data        = $this->handleFileUploads($formRequest, $formRequest->validated(), $model);

        $this->service
            ? $this->service->update($model, $data)
            : $model->update($data);

        return $this->successRedirect('updated');
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
}