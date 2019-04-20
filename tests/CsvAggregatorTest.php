<?php

namespace App\Tests;
require '../vendor/autoload.php';

use App\Entity\CsvRow;
use App\Entity\LargeCsvFile;
use App\Service\CsvAggregator;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class CsvAggregatorTest extends TestCase
{
    /**
     * @var CsvAggregator
     */
    protected $csvAggregator;

    protected function setUp()
    {
        if (file_exists('result.csv')) {
            unlink('result.csv');
        }
        $this->csvAggregator = new CsvAggregator(['date', 'A', 'B', 'C'], CsvRow::class);
    }

    public function testProcessRow()
    {
        $csvFile = new LargeCsvFile("data/test/test.csv");
        $this->csvAggregator->aggregate($csvFile, 'result.csv');

        $expectedFile = new \SplFileObject('data/expected/expected.csv');
        $expectedFile->setFlags(SplFileObject::READ_CSV);
        $expectedFile->setCsvControl(';');

        $actualFile = new \SplFileObject('result.csv');
        $actualFile->setFlags(SplFileObject::READ_CSV);
        $actualFile->setCsvControl(';');

        foreach ($expectedFile as $i => $expectedRow) {
            $actualFile->seek($i);
            $actualRow = $actualFile->current();
            $this->assertEquals($expectedRow, $actualRow);
        }
    }
}
