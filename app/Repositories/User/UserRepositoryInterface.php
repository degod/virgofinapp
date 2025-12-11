<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
}
