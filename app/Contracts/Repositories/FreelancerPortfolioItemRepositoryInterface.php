<?php

namespace App\Contracts\Repositories;

interface FreelancerPortfolioItemRepositoryInterface extends RepositoryInterface
{
    public function getBySellerId(string|int $sellerId, array $relations = []): \Illuminate\Database\Eloquent\Collection;
}
