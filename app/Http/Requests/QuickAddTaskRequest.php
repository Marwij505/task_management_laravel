<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class QuickAddTaskRequest extends FormRequest
{
    /**
     * Quick Add hanya boleh dipakai oleh user yang sudah login.
     * Middleware route tetap menjadi lapisan keamanan utama.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validasi data inti Quick Add Task.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'deadline' => [
                'required',
                'date_format:Y-m-d',
            ],

            'priority' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                ]),
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'source' => [
                'nullable',
                'string',
                Rule::in([
                    'dashboard',
                    'tasks.index',
                    'calendar',
                ]),
            ],
        ];
    }

    /**
     * Pesan error yang ditampilkan oleh modal Quick Add.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.string' => 'Task title must be text.',
            'title.max' => 'Task title may not be greater than 150 characters.',

            'deadline.required' => 'Deadline is required.',
            'deadline.date_format' => 'Deadline must use a valid date.',

            'priority.required' => 'Priority is required.',
            'priority.in' => 'Priority must be Low, Medium, or High.',

            'category.string' => 'Category must be text.',
            'category.max' => 'Category may not be greater than 100 characters.',

            'source.in' => 'Invalid Quick Add source.',
        ];
    }

    /**
     * Normalisasi input sebelum validasi.
     *
     * Penting:
     * field nullable harus benar-benar menjadi NULL saat kosong.
     * Jangan mengubah field nullable menjadi string kosong.
     */
    protected function prepareForValidation(): void
    {
        $category = $this->input('category');
        $source = $this->input('source');

        $this->merge([
            'title' => trim(
                (string) $this->input('title', '')
            ),

            'deadline' => trim(
                (string) $this->input('deadline', '')
            ),

            'priority' => strtolower(trim(
                (string) $this->input('priority', '')
            )),

            'category' => $category === null
                || trim((string) $category) === ''
                    ? null
                    : trim((string) $category),

            'source' => $source === null
                || trim((string) $source) === ''
                    ? null
                    : trim((string) $source),
        ]);
    }

    /**
     * Quick Add adalah endpoint internal JSON.
     *
     * Karena itu validation error HARUS selalu berbentuk JSON 422,
     * bukan redirect 302 ke halaman sebelumnya.
     *
     * Format ini juga sesuai dengan quick-add-task.js:
     *
     * {
     *   "success": false,
     *   "message": "...",
     *   "errors": {
     *      "title": ["..."]
     *   }
     * }
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422)
        );
    }
}