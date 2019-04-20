<?php

namespace App\Tests;
require '../vendor/autoload.php';

use App\Entity\ComparableInterface;
use App\Entity\CsvRow;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class CsvRowTest extends TestCase
{
    /**
     * @dataProvider fromArrayData
     */
    public function testFromArraySuccess($data)
    {
        $csvRow = CsvRow::fromArray($data);
        $date = \DateTime::createFromFormat('Y-m-d', $data[0]);
        $date->setTime(0, 0);
        $this->assertTrue($date == $csvRow->getDate());
        $this->assertSame(floatval($data[1]), $csvRow->getA());
        $this->assertSame(floatval($data[2]), $csvRow->getB());
        $this->assertSame(floatval($data[3]), $csvRow->getC());
    }

    /**
     * @dataProvider fromArrayDataErr
     */
    public function testFromArrayFail($data)
    {
        $this->expectException(\Exception::class);
        $csvRow = CsvRow::fromArray($data);
    }

    public function fromArrayData()
    {
        return [
            [['2013-03-15', '0.5', '1.2', '4']],
        ];
    }

    public function fromArrayDataErr()
    {
        return [
            [['20133-03-15', '0.5', '1.2', '4']],
            [['2013-030-15', '0.5', '1.2', '4']],
            [['2013-03-154', '0.5', '1.2', '4']],
            [['2013-03-15', 'err', '1.2', '4']],
            [['2013-03-15', '0.5', 'err', '4']],
            [['2013-03-15', '0.5', '1.2', 'err']],
        ];
    }

    /**
     * @dataProvider fromArrayData
     */
    public function testToArray($data)
    {
        $csvRow = CsvRow::fromArray($data);
        $arr = $csvRow->toArray();
        $this->assertSame($data[0], $arr[0]);
        $this->assertSame(floatval($data[1]), $arr[1]);
        $this->assertSame(floatval($data[2]), $arr[2]);
        $this->assertSame(floatval($data[3]), $arr[3]);
    }

    /**
     * @dataProvider compareData
     */
    public function testCompare($data1, $data2, $expected)
    {
        $csvRow1 = CsvRow::fromArray($data1);
        $this->assertSame($expected, $csvRow1->compare($data2));
    }

    public function compareData()
    {
        return [
            [['2013-03-15', '1.5', '2', '4.1'], ['2013-03-16', '4.5', '2', '-4'], ComparableInterface::LT],
            [['2015-03-15', '-0.5', '1.2', '3.6'], ['2013-03-16', '-6.5', '1', '6.1'], ComparableInterface::GT],
            [['2013-03-16', '-1.5', '5.02', '3'], ['2013-03-16', '2.5', '3', '3.4'], ComparableInterface::EQUALS],
        ];
    }

    public function operationData()
    {
        return [
            [['2013-03-15', '1.5', '2', '4.1'], ['2013-03-16', '4.5', '2', '-4'], [6.0, 4.0, 0.1]],
        ];
    }

    /**
     * @dataProvider operationData
     */
    public function testPerformOperation($data1, $data2, $expected)
    {
        $csvRow = CsvRow::fromArray($data1);
        $csvRow->reduce($data2);
        list($a, $b, $c) = $expected;
        $this->assertSame($a, $csvRow->getA());
        $this->assertSame($b, $csvRow->getB());
        $this->assertSame($c, $csvRow->getC());
    }
}
