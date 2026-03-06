<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeCrudModule extends Command
{
    protected $signature = 'make:crud-module
                            {name : PascalCase module name (e.g. PostCategory)}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate a complete CRUD module boilerplate (migration, model, observer, policy, requests, controllers, views)';

    /** @var array<string, string> Placeholder token → resolved value */
    private array $replacements = [];

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            $this->error('Module name must be PascalCase with no spaces or special characters (e.g. PostCategory).');

            return self::FAILURE;
        }

        $this->buildReplacements($name);
        $this->generateFiles();
        $this->printNextSteps();

        return self::SUCCESS;
    }

    private function buildReplacements(string $name): void
    {
        $singular = $name;
        $plural = Str::plural($singular);

        $this->replacements = [
            // Plural variants must come before singular to avoid partial matches
            '{module_names}' => Str::snake($plural),                          // post_categories
            '{module names}' => strtolower($this->titleCaseWords($plural)),   // post categories
            '{Module Names}' => $this->titleCaseWords($plural),               // Post Categories
            '{module-names}' => Str::kebab($plural),                          // post-categories
            // Singular variants
            '{module_name}' => Str::snake($singular),                        // post_category
            '{module name}' => strtolower($this->titleCaseWords($singular)), // post category
            '{Module Name}' => $this->titleCaseWords($singular),             // Post Category
            '{ModuleName}' => $singular,                                    // PostCategory
            '{moduleName}' => Str::camel($singular),                        // postCategory
        ];
    }

    /** Convert PascalCase string to "Title Case Words" (e.g. PostCategory → Post Category). */
    private function titleCaseWords(string $pascal): string
    {
        return trim(preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $pascal));
    }

    private function generateFiles(): void
    {
        $module = $this->replacements['{ModuleName}'];
        $kebabName = $this->replacements['{module-names}'];
        $snakeName = $this->replacements['{module_names}'];
        $timestamp = now()->format('Y_m_d_His');

        $files = [
            "database/migrations/{$timestamp}_create_{$snakeName}_table.php" => 'migration.stub',
            "app/Models/{$module}.php" => 'model.stub',
            "app/Observers/{$module}Observer.php" => 'observer.stub',
            "app/Policies/{$module}Policy.php" => 'policy.stub',
            "app/Http/Requests/Store{$module}Request.php" => 'store-request.stub',
            "app/Http/Requests/Update{$module}Request.php" => 'update-request.stub',
            "app/Http/Controllers/{$module}Controller.php" => 'controller.stub',
            "app/Http/Controllers/{$module}DataTableController.php" => 'datatable-controller.stub',
            "resources/views/{$kebabName}/index.blade.php" => 'views/index.blade.stub',
            "resources/views/{$kebabName}/form.blade.php" => 'views/form.blade.stub',
            "resources/views/{$kebabName}/show.blade.php" => 'views/show.blade.stub',
        ];

        $stubsPath = base_path('stubs/crud-module');

        foreach ($files as $destination => $stub) {
            $this->writeFile($destination, "{$stubsPath}/{$stub}");
        }
    }

    private function writeFile(string $relativePath, string $stubPath): void
    {
        $fullPath = base_path($relativePath);

        if (file_exists($fullPath) && ! $this->option('force')) {
            $this->warn("  SKIPPED  {$relativePath} (already exists, use --force to overwrite)");

            return;
        }

        $content = file_get_contents($stubPath);
        $content = str_replace(
            array_keys($this->replacements),
            array_values($this->replacements),
            $content
        );

        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($fullPath, $content);

        $this->info("  CREATED  {$relativePath}");
    }

    private function printNextSteps(): void
    {
        $module = $this->replacements['{ModuleName}'];
        $kebab = $this->replacements['{module-names}'];
        $moduleNames = $this->replacements['{module names}'];

        $this->newLine();
        $this->line('<fg=yellow;options=bold>Next steps:</>');
        $this->newLine();

        $this->line('  <fg=cyan>1. Add your columns to the migration, then run:</>');
        $this->line('     php artisan migrate');
        $this->line('     <fg=gray>// Permissions are created automatically by the migration:</>');
        $this->line("     <fg=gray>//   {$kebab}.view, {$kebab}.create, {$kebab}.update, {$kebab}.delete</>");
        $this->newLine();

        $this->line('  <fg=cyan>2. Add your columns to $fillable (and casts) in the Model.</>');
        $this->newLine();

        $this->line('  <fg=cyan>3. Add validation rules to StoreRequest and UpdateRequest.</>');
        $this->newLine();

        $this->line('  <fg=cyan>4. Add routes to routes/web.php (inside the auth middleware group):</>');
        $this->newLine();
        $this->line("     use App\\Http\\Controllers\\{$module}Controller;");
        $this->line("     use App\\Http\\Controllers\\{$module}DataTableController;");
        $this->newLine();
        $this->line("     Route::crudModule('{$kebab}', {$module}Controller::class, {$module}DataTableController::class);");
        $this->newLine();
        $this->line('     <fg=gray>// Need extra routes? Pass a closure as the 4th argument:</>');
        $this->line("     <fg=gray>// Route::crudModule('{$kebab}', {$module}Controller::class, {$module}DataTableController::class, function () \\{</>");
        $this->line("     <fg=gray>//     Route::post('/{record}/action', [{$module}Controller::class, 'action'])->name('action');</>");
        $this->line('     <fg=gray>// });</>');
        $this->newLine();

        $this->line('  <fg=cyan>5. Add a sidebar link in resources/views/layouts/dashboard/partials/sidebar.blade.php</>');
        $this->newLine();

        $this->line('  <fg=cyan>6. Customise the views (form fields, table columns, show page).</>');
        $this->newLine();
    }
}
