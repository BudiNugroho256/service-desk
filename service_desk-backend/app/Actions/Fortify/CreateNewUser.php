<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'nama_user' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tblm_user,email'],
            'password' => $this->passwordRules(),
            'nik_user' => ['required', 'string', 'max:50', 'unique:tblm_user,nik_user'],
            'role_user' => ['required', 'string'],
            'id_divisi' => ['nullable', 'exists:tblm_divisi,id_divisi'],
        ])->validate();

        return User::create([
            'nama_user' => $input['nama_user'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'nik_user' => $input['nik_user'],
            'role_user' => $input['role_user'],
            'id_divisi' => $input['id_divisi'] ?? null,
        ]);
    }
}