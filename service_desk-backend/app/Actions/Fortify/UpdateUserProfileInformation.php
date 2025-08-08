<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'nama_user' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('tblm_user', 'email')->ignore($user->id_user, 'id_user')],
            'nik_user' => ['required', 'string', 'max:50', Rule::unique('tblm_user', 'nik_user')->ignore($user->id_user, 'id_user')],
            'id_divisi' => ['nullable', 'exists:tblm_divisi,id_divisi'],
        ])->validate();

        $user->forceFill([
            'nama_user' => $input['nama_user'],
            'email' => $input['email'],
            'nik_user' => $input['nik_user'],
            'id_divisi' => $input['id_divisi'] ?? null,
        ])->save();

        if ($user instanceof MustVerifyEmail &&
            $input['email'] !== $user->email) {
            $this->updateVerifiedUser($user, $input);
        }
    }

    /**
     * Update the given verified user's profile information.
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'nama_user' => $input['nama_user'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}