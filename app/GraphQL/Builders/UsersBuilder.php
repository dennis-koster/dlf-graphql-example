<?php

declare(strict_types=1);

namespace App\GraphQL\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UsersBuilder
{
    public function __invoke($_, array $args): Builder
    {
        $builder = User::query();

        if (isset($args['createdAfter'])) {
            $builder->where('created_at', '>=', $args['createdAfter']);
        }

        return $builder;
    }
}
