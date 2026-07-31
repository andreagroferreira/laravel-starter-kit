<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

final class PostPolicy
{
    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can('posts.update-any')) {
            return true;
        }

        return $user->can('posts.update-own') && $post->author_id === $user->id;
    }

    public function delete(User $user): bool
    {
        return $user->can('posts.delete');
    }

    public function publish(User $user): bool
    {
        return $user->can('posts.publish');
    }
}
