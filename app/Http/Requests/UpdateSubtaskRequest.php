<?php

namespace App\Http\Requests;

class UpdateSubtaskRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'task_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'subtask_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'title' => [
                'required',
                'string',
                'max:180',
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'task_id.required' =>
                'Task ID is required.',

            'subtask_id.required' =>
                'Subtask ID is required.',

            'title.required' =>
                'Subtask title is required.',

            'title.max' =>
                'Subtask title may not be greater than 180 characters.',
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim(
                (string) $this->input(
                    'title',
                    ''
                )
            ),
        ]);
    }
}