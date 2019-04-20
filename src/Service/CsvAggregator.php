<?php


namespace App\Service;


use App\Entity\ComparableInterface;
use App\Entity\CsvFileInterface;
use App\Entity\CsvRowInterface;
use App\Entity\LargeCsvFile;

class CsvAggregator implements AggregatorInterface
{
    /**
     * @var string[]
     */
    private $columns = [];

    /**
     * @var string
     */
    private $csvRowClass;

    /**
     * CsvAggregator constructor.
     * @param array $columns
     * @param string $csvRowClassName
     */
    public function __construct(array $columns, string $csvRowClassName)
    {
        $this->csvRowClass = $csvRowClassName;
        $this->columns = $columns;
    }

    /**
     * @param CsvFileInterface $sourceCsv
     * @param string $resultFileNmae
     */
    public function aggregate(CsvFileInterface $sourceCsv, string $resultFileNmae): void
    {
        foreach ($sourceCsv->iterate() as $i => $data) {
            $isColumnNamesRow = $i == 0;
            if ($isColumnNamesRow) {
                continue;
            }
            try {
                $csvRow = $this->csvRowClass::fromArray($data);
                $resultFile = $this->openResultFile($resultFileNmae);
                $tmpCsv = $this->processRow($csvRow, $resultFile);
                $this->copyTmpToResult($resultFile, $tmpCsv);
            } catch (\Exception $exception) {
                echo $exception->getMessage() . "\n";

                continue;
            }

        }
    }

    /**
     * @param CsvRowInterface $newRow
     * @param CsvFileInterface $resultFile
     * @return CsvFileInterface
     * @throws \Exception
     */
    private function processRow(CsvRowInterface $newRow, CsvFileInterface $resultFile): CsvFileInterface
    {
        $tmpCsv = $this->createTmpFile();
        $resultFile->skipColumnNames();
        $tmpCsv->skipColumnNames();

        $newRowWritten = false;
        foreach ($resultFile->iterate() as $data) {
            if ($newRowWritten) {
                $tmpCsv->writeRow($data);

                continue;
            }

            switch ($newRow->compare($data)) {
                case ComparableInterface::EQUALS:
                    $newRow->reduce($data);
                    $tmpCsv->writeRow($newRow->toArray());
                    $newRowWritten = true;
                    break;
                case ComparableInterface::LT:
                    $tmpCsv->writeRow($newRow->toArray());
                    $tmpCsv->writeRow($data);
                    $newRowWritten = true;
                    break;
                case ComparableInterface::GT:
                    $tmpCsv->writeRow($data);
                    break;
            }
        }
        if (!$newRowWritten) {
            $tmpCsv->writeRow($newRow->toArray());
        }

        return $tmpCsv;
    }

    /**
     * @return LargeCsvFile
     * @throws \Exception
     */
    private function createTmpFile(): CsvFileInterface
    {
        $filename = '_tmp.csv';
        if (!file_exists($filename)) {
            touch($filename);
        }

        $csvFile = new LargeCsvFile($filename, 'r+');
        $csvFile->writeRow($this->columns);

        return $csvFile;
    }

    /**
     * @param string $filename
     * @return LargeCsvFile
     * @throws \Exception
     */
    private function openResultFile(string $filename): CsvFileInterface
    {
        if (!file_exists($filename)) {
            touch($filename);
        }

        $csvFile = new LargeCsvFile($filename, 'r');
        $csvFile->writeRow($this->columns);

        return $csvFile;
    }

    /**
     * @param CsvFileInterface $resultFile
     * @param CsvFileInterface $tmpCsv
     */
    private function copyTmpToResult(CsvFileInterface $resultFile, CsvFileInterface $tmpCsv): void
    {
        $resultFileName = $resultFile->getFileName();
        $resultFile->closeFile();
        unlink($resultFileName);
        $tmpName = $tmpCsv->getFileName();
        rename($tmpName, $resultFileName);
        $tmpCsv->closeFile();
    }
}