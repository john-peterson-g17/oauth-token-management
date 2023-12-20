<?php

namespace JohnPetersonG17\JwtAuthentication;

enum TokenType
{
    case ACCESS_TOKEN;
    case REFRESH_TOKEN;
}