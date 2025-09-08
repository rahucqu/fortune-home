<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view media');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Media $media): bool
    {
        // Super admins and editors can view all media
        if ($user->can('view media')) {
            return true;
        }

        // Authors and contributors can view their own media
        return $user->can('view own media') && $media->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('upload media');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Media $media): bool
    {
        // Super admins and editors can edit all media
        if ($user->can('edit media')) {
            return true;
        }

        // Authors can edit their own media
        return $user->can('edit own media') && $media->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Media $media): bool
    {
        // Super admins and editors can delete all media
        if ($user->can('delete media')) {
            return true;
        }

        // Authors can delete their own media
        return $user->can('delete own media') && $media->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Media $media): bool
    {
        return $user->can('delete media');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Media $media): bool
    {
        return $user->can('delete media');
    }
}
