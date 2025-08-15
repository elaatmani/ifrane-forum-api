<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRole($role, $get);

    public function community(User $user, $params = [], $get = true);

    /**
     * Get similar users for a user based on configurable recommendation factors
     *
     * @param User $user The user to get recommendations for
     * @param array $params Optional parameters including factor configuration, limits, etc.
     * @param bool $get Whether to execute the query (true) or return query builder (false)
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder
     */
    public function getSimilarUsers(User $user, array $params = [], bool $get = true);

    public function actAsRole($role);
}