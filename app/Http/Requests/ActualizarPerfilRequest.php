<?php
// ──────────────────────────────────────────────────────────────
// app/Http/Requests/User/ActualizarPerfilRequest.php
// ──────────────────────────────────────────────────────────────
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarPerfilRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'    => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:150'],
            'password'  => ['sometimes', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json(['errores' => $validator->errors()], 422)
        );
    }
}
