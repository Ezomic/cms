<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * @template TModel of Model
 */
trait HandlesSoftDeleteActions
{
    /**
     * @return class-string<TModel>
     */
    abstract protected function softDeleteModel(): string;

    public function restore(int $id): RedirectResponse
    {
        $this->trashed($id)->restore();

        return back()->with('status', $this->softDeleteLabel().' restored.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $model = $this->trashed($id);

        $this->beforeForceDelete($model);

        $model->forceDelete();

        return back()->with('status', $this->softDeleteLabel().' permanently deleted.');
    }

    /**
     * Hook for cleanup (e.g. stored files) before a record is permanently removed.
     *
     * @param  TModel  $model
     */
    protected function beforeForceDelete(Model $model): void {}

    /**
     * @return TModel
     */
    protected function trashed(int $id): Model
    {
        return $this->softDeleteModel()::onlyTrashed()->findOrFail($id);
    }

    protected function softDeleteLabel(): string
    {
        return class_basename($this->softDeleteModel());
    }
}
