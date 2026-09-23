<?php

namespace App\Contracts\Repositories;

interface FreelancerServiceRepositoryInterface extends RepositoryInterface
{
    public function getBySellerId(string|int $sellerId, array $relations = []): \Illuminate\Database\Eloquent\Collection;

    public function updateActiveStatus(string|int $id, int $status): bool;
}
