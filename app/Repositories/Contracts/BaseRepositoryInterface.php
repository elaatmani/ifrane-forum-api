<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    public function all();

    public function find($id);

    public function query();

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function paginate($per_page, array $options = []);

    public function search(array $cretiria, $get = true);
}
