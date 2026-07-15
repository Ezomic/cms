<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

trait HandlesReordering
{
    /**
     * @return class-string<Model>
     */
    abstract protected function reorderModel(): string;

    public function reorder(Request $request): Response
    {
        $model = $this->reorderModel();
        $table = (new $model)->getTable();

        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists($table, 'id')],
        ]);

        foreach ($data['ids'] as $index => $id) {
            $model::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->noContent();
    }
}
