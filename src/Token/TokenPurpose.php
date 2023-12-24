<?php

namespace JohnPetersonG17\JwtAuthentication\Token;

enum TokenPurpose: string
{
    case ACCESS = 'access';
    case REFRESH = 'refresh';
}