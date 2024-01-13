<?php

namespace JohnPetersonG17\OAuthTokenManagement\Token;

enum TokenPurpose: string
{
    case ACCESS = 'access';
    case REFRESH = 'refresh';
}