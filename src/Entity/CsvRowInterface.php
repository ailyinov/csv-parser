<?php


namespace App\Entity;


interface CsvRowInterface extends ComparableInterface, ReduceInterface
{
    public static function fromArray(array $data): CsvRowInterface;
    public function toArray(): array;
}