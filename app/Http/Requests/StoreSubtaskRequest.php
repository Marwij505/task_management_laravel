<?php

namespace App\Http\Requests;

class StoreSubtaskRequest extends JsonFormRequest
{
    /**
     * Validation untuk membuat subtask baru.
     */
    public function rules(): array
    {
        return [
            /*
             * ID parent task.
             *
             * Tidak memakai exists:tasks,id di sini
             * karena ownership akan diperiksa di controller.
             *
             * Dengan demikian keberadaan task milik user lain
             * tidak bocor melalui validation.
             */
            'task_id' => [
                'required',
                'integer',
                'min:1',
            ],

            /*
             * Isi checklist.
             */
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

            'task_id.integer' =>
                'Invalid task ID.',

            'title.required' =>
                'Subtask title is required.',

            'title.max' =>
                'Subtask title may not be greater than 180 characters.',
        ];
    }


    /**
     * Rapikan judul sebelum validation.
     */
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