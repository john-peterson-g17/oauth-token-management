<?php

namespace JohnPetersonG17\OAuthTokenManagement\Persistance\Repositories;

use JohnPetersonG17\OAuthTokenManagement\Grant;

interface GrantRepository
{
    /**
     * Save a grant to the data store
     * This operation is an insert or update
     * @param Grant $grant
     * @return void
     */
    public function save(Grant $grant): void;

    /**
     * Find a grant by its user id
     * @param mixed $userId
     * @return Grant
     */
    public function find(mixed $userId): Grant;

    /**
     * Delete a grant from the data store by its user id
     * @param mixed userId
     * @return void
     */
    public function delete(mixed $userId): void;
}