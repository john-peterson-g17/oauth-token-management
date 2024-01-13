<?php

namespace JohnPetersonG17\OAuthTokenManagement\Persistance;

use JohnPetersonG17\OAuthTokenManagement\Persistance\Repositories\RedisGrantRepository;

enum Driver: string {
    case None = '';
    case Redis = RedisGrantRepository::class;
    // TODO support for other persistance drivers
}