<?php

namespace App\DataTransferObjects;

class DashboardCustomerDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public float $total_spent
    ) {}
}
