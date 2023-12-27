<?php

namespace JohnPetersonG17\JwtAuthentication\Persistance;

use JohnPetersonG17\JwtAuthentication\Persistance\Repositories\RedisGrantRepository;

enum Driver: string {
    case None = '';
    case Redis = RedisGrantRepository::class;
    // TODO support for other persistance drivers
}