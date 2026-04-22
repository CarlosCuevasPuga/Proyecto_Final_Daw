<?php
// ──────────────────────────────────────────────────────────────
// app/Http/Requests/Auth/LoginRequest.php
// ──────────────────────────────────────────────────────────────
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'Introduce un email válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json(['errores' => $validator->errors()], 422)
        );
    }
}
