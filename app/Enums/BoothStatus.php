<?php

namespace App\Enums;

enum BoothStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    
}
