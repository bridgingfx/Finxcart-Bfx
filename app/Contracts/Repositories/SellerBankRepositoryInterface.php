<?php

namespace App\Contracts\Repositories;

interface SellerBankRepositoryInterface extends RepositoryInterface
{
    /**
     * Atomically deactivate all of a seller's banks and activate the given one.
     *
     * @param int $sellerId
     * @param int $bankId
     * @return bool
     */
    public function setActive(int $sellerId, int $bankId): bool;
}
