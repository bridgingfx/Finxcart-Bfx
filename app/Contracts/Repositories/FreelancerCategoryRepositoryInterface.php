<?php

namespace App\Contracts\Repositories;

interface FreelancerCategoryRepositoryInterface extends RepositoryInterface
{
    public function updateStatus(string|int $id, int $status): bool;

    public function hasChildren(string|int $id): bool;
}
