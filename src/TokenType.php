<?php

namespace JohnPetersonG17\JwtAuthentication;

enum TokenType
{
    case ACCESS;
    case REFRESH;
}