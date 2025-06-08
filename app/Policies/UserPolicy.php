<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function delete(User $user)
    {
        return $user->email === 'john@email.com';
    }

    public function update(User $authUser, User $user)
{
    // Only allow users to update (post) on their own account
    return $authUser->id === $user->id;
}
}
