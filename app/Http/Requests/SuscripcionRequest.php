<?php
// ──────────────────────────────────────────────────────────────
// app/Http/Requests/User/SuscripcionRequest.php
// ──────────────────────────────────────────────────────────────
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SuscripcionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'in:premium_mensual,premium_anual'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan.required' => 'El plan es obligatorio.',
            'plan.in'       => 'El plan debe ser premium_mensual o premium_anual.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json(['errores' => $validator->errors()], 422)
        );
    }
}
