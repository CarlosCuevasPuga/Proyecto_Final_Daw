<?php
// ──────────────────────────────────────────────────────────────
// app/Http/Requests/Restaurante/ValoracionRequest.php
// ──────────────────────────────────────────────────────────────
namespace App\Http\Requests\Restaurante;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValoracionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'puntuacion.required' => 'La puntuación es obligatoria.',
            'puntuacion.min'      => 'La puntuación mínima es 1.',
            'puntuacion.max'      => 'La puntuación máxima es 5.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json(['errores' => $validator->errors()], 422)
        );
    }
}
