<?php

namespace JohnPetersonG17\JwtAuthentication;

enum PersistanceDriver {
    case Redis;
    // TODO support for other persistance drivers
}