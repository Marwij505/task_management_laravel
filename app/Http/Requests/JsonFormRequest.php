<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class JsonFormRequest extends FormRequest
{
    /**
     * Semua request turunan hanya dapat digunakan
     * oleh user yang sudah login.
     *
     * Authorization ownership tetap diperiksa lagi
     * di Controller.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }


    /**
     * ============================================================
     * JSON VALIDATION RESPONSE
     * ============================================================
     *
     * Semua endpoint internal Flowlist menggunakan JSON.
     *
     * Jika validasi gagal, response:
     *
     * HTTP 422
     *
     * {
     *     "success": false,
     *     "message": "...",
     *     "errors": {...}
     * }
     */
    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            response()->json([
                'success' => false,

                'message' =>
                    $validator
                        ->errors()
                        ->first(),

                'errors' =>
                    $validator
                        ->errors()
                        ->toArray(),
            ], 422)
        );
    }
}