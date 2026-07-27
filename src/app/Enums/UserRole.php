<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'USER';
    case OPERATOR = 'OPERATOR';
    case ADMINISTRATOR = 'ADMINISTRATOR';
}
