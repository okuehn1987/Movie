<?php

namespace App\Enums;

enum Status: string
{
    case Created = 'created';
    case Accepted = 'accepted';
    case Declined = 'declined';
}
