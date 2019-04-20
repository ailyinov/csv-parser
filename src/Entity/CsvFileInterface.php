<?php


namespace App\Entity;


use Generator;

interface CsvFileInterface
{
    public function iterate(): Generator;
    public function getFileName(): string;
    public function writeRow(array $columns): void;
    public function skipColumnNames();
    public function closeFile(): void;
    public function getDelimiter(): string;
}