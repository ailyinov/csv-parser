<?php


namespace App\Service;


use App\Entity\CsvFileInterface;

interface AggregatorInterface
{
    public function aggregate(CsvFileInterface $sourceCsv, string $resultFileName): void;
}