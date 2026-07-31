<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class UniqueSlug
{
    /**
     * Slugify $source and append -2, -3, … until it is unique
     * within the given query scope.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $scope
     */
    public static function make(Builder $scope, string $source, string $column = 'slug'): string
    {
        $base = Str::slug($source);

        if ($base === '') {
            $base = 'sem-titulo';
        }

        $slug = $base;
        $suffix = 2;

        while ((clone $scope)->where($column, $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
