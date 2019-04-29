<?php


namespace App\Entity;


use Exception;
use Generator;

class LargeCsvFile implements CsvFileInterface
{

    /**
     * @var resource
     */
    private $file;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string[]
     */
    private $delimiter;

    /**
     * CsvLargeFile constructor.
     * @param string $filename
     * @param string $mode
     * @param string $delimiter
     * @throws Exception
     */
    public function __construct(string $filename, string $mode = 'r', string $delimiter = ';')
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found");
        }

        $this->fileName = $filename;
        $this->file = fopen($filename, $mode);
        $this->delimiter = $delimiter;
    }

    /**
     * @return \Generator|int
     */
    protected function iterateCsv(): Generator
    {
        $count = 0;
        while (($data = fgetcsv($this->getFile(), 0, $this->getDelimiter())) !== false) {
            yield $data;
            $count++;
        }

        return $count;
    }

    public function iterate(): Generator
    {
        return $this->iterateCsv();
    }

    /**
     * @return resource
     */
    public function getFile()
    {
        return $this->file;
    }

    public function closeFile(): void
    {
        fclose($this->getFile());
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param array $columns
     */
    public function writeRow(array $columns): void
    {
        fputcsv($this->file, $columns, $this->getDelimiter(), chr(32));
    }


    public function skipColumnNames(): void
    {
        rewind($this->getFile());
        fgetcsv($this->getFile(), 0, $this->getDelimiter());
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}