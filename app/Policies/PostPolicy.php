<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view posts');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Super admins and editors can view all posts
        if ($user->can('edit posts')) {
            return true;
        }

        // Authors and contributors can view their own posts
        return $user->can('view own posts') && $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create posts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Super admins and editors can edit all posts
        if ($user->can('edit posts')) {
            return true;
        }

        // Authors can edit their own posts
        return $user->can('edit own posts') && $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Super admins and editors can delete all posts
        if ($user->can('delete posts')) {
            return true;
        }

        // Authors can delete their own posts
        return $user->can('delete own posts') && $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Post $post): bool
    {
        // Only editors and super admins can publish posts
        return $user->can('publish posts');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->can('delete posts');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->can('delete posts');
    }
}
