<?php

namespace App\Enums;

enum CostCenterType: string
{
    case BRAND = 'BRAND';
    case BRANCH = 'BRANCH';
    case DEPARTMENT = 'DEPARTMENT';
    case PROJECT = 'PROJECT';
    case REGION = 'REGION';

    public function label(): string
    {
        return match ($this) {
            self::BRAND => 'Brand',
            self::BRANCH => 'Branch',
            self::DEPARTMENT => 'Department',
            self::PROJECT => 'Project',
            self::REGION => 'Region',
        };
    }
}
