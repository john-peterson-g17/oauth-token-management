<?php

namespace JohnPetersonG17\JwtAuthentication;

interface GrantRepository
{
    public function save(Grant $grant): void;
    public function find(mixed $userId): Grant;
    public function delete(Grant $grant): void;
}