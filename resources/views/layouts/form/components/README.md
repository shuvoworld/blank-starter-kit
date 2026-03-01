# Reusable Form Input System — Bootstrap 5 / Laravel

A zero-dependency, `@include`-driven form component system. Each input is driven
by a single `$var` array — no Blade components, no extra directives required.

---

## File Structure

```
app/View/Components/Form/
├── BaseInput.php          ← shared logic (value resolution, id derivation, extras)
├── InputText.php          ← text, password, email, number, tel, date, time, url, color, range, hidden
├── InputTextarea.php      ← textarea
├── InputSelect.php        ← select from static array (single & multiple)
├── InputSelectModel.php   ← select driven by an Eloquent model query
├── InputCheckbox.php      ← checkbox (single & group)
├── InputRadio.php         ← radio button group
├── InputFile.php          ← file upload (with optional preview)
└── InputSwitch.php        ← Bootstrap 5 toggle switch

resources/views/components/form/
├── input-text.blade.php
├── input-textarea.blade.php
├── input-select.blade.php
├── input-select-model.blade.php
├── input-checkbox.blade.php
├── input-radio.blade.php
├── input-file.blade.php
└── input-switch.blade.php

resources/views/form/          ← thin entry-point wrappers
├── text.blade.php             → @include('components.form.input-text')
├── textarea.blade.php
├── select.blade.php
├── select-model.blade.php
├── checkbox.blade.php
├── radio.blade.php
├── file.blade.php
└── switch.blade.php
```

---

## Quick Usage

```blade
{{-- Any text-like input --}}
@include('form.text', ['var' => [
    'name'        => 'organization_name',
    'label'       => 'Organization Name',
    'placeholder' => 'Enter Your Organization Name',
    'div'         => 'col-md-12 col-sm-12',
]])

{{-- Email --}}
@include('form.text', ['var' => [
    'name'     => 'email',
    'label'    => 'Email',
    'type'     => 'email',
    'required' => true,
    'tooltip'  => 'We will never share your email.',
    'div'      => 'col-md-6',
]])

{{-- Select (static array) --}}
@include('form.select', ['var' => [
    'name'    => 'status',
    'label'   => 'Status',
    'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
    'value'   => $model->status,
    'prompt'  => '-- Select --',
    'div'     => 'col-md-4',
]])

{{-- Select (Eloquent model) --}}
@include('form.select-model', ['var' => [
    'name'  => 'role_id',
    'label' => 'Role',
    'model' => \App\Models\Role::class,
    'value' => $user->role_id,
    'div'   => 'col-md-4',
]])

{{-- Radio group --}}
@include('form.radio', ['var' => [
    'name'    => 'gender',
    'label'   => 'Gender',
    'options' => ['m' => 'Male', 'f' => 'Female'],
    'value'   => $model->gender,
    'inline'  => true,
    'div'     => 'col-md-6',
]])

{{-- Checkbox group --}}
@include('form.checkbox', ['var' => [
    'name'    => 'roles[]',
    'label'   => 'Roles',
    'options' => ['admin' => 'Admin', 'editor' => 'Editor'],
    'value'   => $model->roles,
    'div'     => 'col-md-6',
]])

{{-- Toggle switch --}}
@include('form.switch', ['var' => [
    'name'    => 'is_active',
    'label'   => 'Active',
    'checked' => $model->is_active,
    'div'     => 'col-md-3',
]])

{{-- File with image preview --}}
@include('form.file', ['var' => [
    'name'    => 'avatar',
    'label'   => 'Profile Photo',
    'accept'  => 'image/*',
    'preview' => $model->avatar_url,
    'div'     => 'col-md-6',
]])

{{-- Textarea --}}
@include('form.textarea', ['var' => [
    'name'  => 'notes',
    'label' => 'Notes',
    'rows'  => 5,
    'div'   => 'col-md-12',
]])
```

---

## Select Model — Extended Usage

```blade
{{-- Custom key / label fields --}}
@include('form.select-model', ['var' => [
    'name'        => 'country_id',
    'label'       => 'Country',
    'model'       => \App\Models\Country::class,
    'key_field'   => 'iso_code',
    'label_field' => 'display_name',
    'value'       => $user->country_id,
]])

{{-- Simple where conditions --}}
@include('form.select-model', ['var' => [
    'name'       => 'category_id',
    'label'      => 'Category',
    'model'      => \App\Models\Category::class,
    'conditions' => ['active' => 1, 'type' => 'product'],
    'value'      => $item->category_id,
]])

{{-- Named Eloquent scopes --}}
@include('form.select-model', ['var' => [
    'name'   => 'tag_id',
    'label'  => 'Tag',
    'model'  => \App\Models\Tag::class,
    'scopes' => ['published', ['forTeam', auth()->id()]],
    'value'  => $post->tag_id,
]])

{{-- Full closure for complex queries --}}
@include('form.select-model', ['var' => [
    'name'  => 'manager_id',
    'label' => 'Manager',
    'model' => \App\Models\User::class,
    'query' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'manager'))
                           ->where('department_id', $dept->id),
    'value' => $employee->manager_id,
]])

{{-- Multiple select --}}
@include('form.select-model', ['var' => [
    'name'     => 'permission_ids',
    'label'    => 'Permissions',
    'model'    => \App\Models\Permission::class,
    'multiple' => true,
    'value'    => $role->permissions->pluck('id')->toArray(),
    'prompt'   => '-- Select Permissions --',
]])
```

---

## All $var Keys

| Key              | Default       | Types                   | Description                                       |
|------------------|---------------|-------------------------|---------------------------------------------------|
| `name`           | *(required)*  | all                     | HTML name attribute                               |
| `label`          | `null`        | all                     | Label text (HTML allowed)                         |
| `type`           | `'text'`      | text                    | Input type                                        |
| `value` / `val`  | `null`        | all                     | Pre-filled value                                  |
| `id`             | auto          | all                     | HTML id (auto-derived from name)                  |
| `div`            | `'col-md-3'`  | all                     | Wrapper Bootstrap column class                    |
| `container_class`| `'col-md-3'`  | all                     | Alias for `div`                                   |
| `class`          | `''`          | all                     | Extra CSS class on the input                      |
| `label_class`    | `null`        | all                     | Extra CSS class on the label                      |
| `placeholder`    | `''`          | text, textarea          | Placeholder text                                  |
| `required`       | `false`       | all                     | Adds `required` + red asterisk                    |
| `disabled`       | `false`       | all                     | Disables the field                                |
| `readonly`       | `false`       | text, textarea          | Makes field read-only                             |
| `autofocus`      | `false`       | text                    | Autofocus on page load                            |
| `tooltip`        | `null`        | all                     | BS5 tooltip text on label icon                    |
| `params`         | `[]`          | all                     | Extra HTML attrs e.g. `min`, `max`                |
| `options`        | `[]`          | select, radio, checkbox | `[value => label]` map                            |
| `prompt`         | `null`        | select, select-model    | Empty first `<option>`                            |
| `multiple`       | `false`       | select, select-model, checkbox, file | Multi-select / check               |
| `inline`         | `false`       | checkbox, radio         | Render inline                                     |
| `checked`        | `null`        | checkbox, switch        | Override checked state                            |
| `rows`           | `4`           | textarea                | Number of rows                                    |
| `accept`         | `null`        | file                    | Accepted MIME types / extensions                  |
| `preview`        | `null`        | file                    | Existing file URL to show                         |
| `model`          | `null`        | select-model            | Fully-qualified Eloquent class e.g. `Role::class` |
| `key_field`      | `'id'`        | select-model            | Model attribute to use as option value            |
| `label_field`    | `'name'`      | select-model            | Model attribute to use as option label            |
| `conditions`     | `[]`          | select-model            | `['column' => 'value']` where clauses             |
| `scopes`         | `[]`          | select-model            | Named scopes: `['active', ['status', 'x']]`       |
| `order_by`       | `[label, asc]`| select-model            | `['column', 'asc\|desc']`                         |
| `query`          | `null`        | select-model            | Closure receiving the Builder for full control    |

---

## Value Resolution Priority

1. `old('field_name')` — form re-population after validation failure
2. `$var['value']` or `$var['val']` — explicitly passed value
3. Empty string `''`

---

## Supported Input Types via `form.text`

`text` · `password` · `email` · `number` · `tel` · `date` · `datetime-local`  
`time` · `week` · `month` · `url` · `color` · `range` · `search` · `hidden`
