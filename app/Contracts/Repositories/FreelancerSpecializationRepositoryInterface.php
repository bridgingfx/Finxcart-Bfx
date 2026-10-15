<?php

namespace App\Contracts\Repositories;

interface FreelancerSpecializationRepositoryInterface extends RepositoryInterface
{
    public function updateStatus(string|int $id, int $status): bool;
}
