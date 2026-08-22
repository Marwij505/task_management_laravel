<?php

namespace App\Http\Requests;

class ReorderSubtaskRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'task_id' => [
                'required',
                'integer',
                'min:1',
            ],

            /*
             * ordered_ids berisi seluruh ID subtask
             * berdasarkan urutan terbaru.
             *
             * Contoh:
             *
             * [5, 2, 8, 7]
             */
            'ordered_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ordered_ids.*' => [
                'required',
                'integer',
                'min:1',
                'distinct',
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'task_id.required' =>
                'Task ID is required.',

            'ordered_ids.required' =>
                'Subtask order is required.',

            'ordered_ids.array' =>
                'Invalid subtask order.',

            'ordered_ids.*.distinct' =>
                'Duplicate subtask ID detected.',
        ];
    }
}