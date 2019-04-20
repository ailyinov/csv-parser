<?php


namespace App\Entity;


interface ComparableInterface
{
    const EQUALS = 0;
    const GT = 1;
    const LT = -1;

    public function compare(array $data): int;
}