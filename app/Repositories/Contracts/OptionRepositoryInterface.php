<?php

namespace App\Repositories\Contracts;

interface OptionRepositoryInterface extends BaseRepositoryInterface
{
    public function getOptionByName(string $name);
    public function setOption(string $name, string $value);


}
