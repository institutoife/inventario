<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('assets.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user) && $user->hasPermission('assets.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $this->view($user, $asset) && $user->hasPermission('assets.update');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Asset $asset): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Asset $asset): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, Asset $asset): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }

    public function assign(User $user, Asset $asset): bool
    {
        return false;
    }

    public function transfer(User $user, Asset $asset): bool
    {
        return false;
    }

    public function relieve(User $user, Asset $asset): bool
    {
        return false;
    }

    public function sendToWarehouse(User $user, Asset $asset): bool
    {
        return false;
    }

    public function reportMissing(User $user, Asset $asset): bool
    {
        return false;
    }

    public function replace(User $user, Asset $asset): bool
    {
        return false;
    }

    public function revalue(User $user, Asset $asset): bool
    {
        return false;
    }
}
