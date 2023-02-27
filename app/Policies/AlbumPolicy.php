<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Album;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlbumPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    // viewAny, view, create, update, delete, restore, and forceDelete

    public function view(User $user, Album $album): bool
    {
        return $user->id === $album->user_id;
    }

    public function update(User $user, Album $album): bool
    {
        return $user->id === $album->user_id;
    }

    public function delete(User $user, Album $album): bool
    {
        return $user->id === $album->user_id;
    }
}
