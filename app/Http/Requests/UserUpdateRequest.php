<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Hanya Superadmin yang boleh mengubah user.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    /**
     * Validasi data user.
     *
     * Role TIDAK berasal dari request.
     * Perubahan role akan dikendalikan server-side.
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],

            'password' => [
                'nullable',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }
}
