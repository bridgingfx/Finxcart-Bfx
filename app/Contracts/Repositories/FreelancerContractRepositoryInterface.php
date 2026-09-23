<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface FreelancerContractRepositoryInterface extends RepositoryInterface
{
    public function getByCustomerId(int $customerId, array $relations = []): Collection;

    public function getBySellerId(int $sellerId, array $relations = []): Collection;
}
