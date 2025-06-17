<?php

namespace App\Repositories;

use App\Models\Booth;

class BoothRepository
{
    public function all($perPage = 15)
    {
        return Booth::paginate($perPage);
    }

    public function find(int $id): ?Booth
    {
        return Booth::find($id);
    }

    public function create(array $data): Booth
    {
        return Booth::create($data);
    }

    public function update(Booth $booth, array $data): Booth
    {
        $booth->update($data);
        return $booth;
    }

    public function delete(Booth $booth): void
    {
        $booth->delete();
    }

    public function isBoothBooked(Booth $booth): bool
    {
        return $booth->bookings()->exists();
    }

}