<?php


namespace App\Entity;

class CsvRow implements CsvRowInterface
{
    const DATE_FORMAT = 'Y-m-d';
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var float
     */
    private $a;

    /**
     * @var float
     */
    private $b;

    /**
     * @var float
     */
    private $c;

    /**
     * CsvLine constructor.
     * @param \DateTime $date
     * @param float $a
     * @param float $b
     * @param float $c
     */
    public function __construct(\DateTime $date, float $a, float $b, float $c)
    {
        $this->date = $date;
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    /**
     * @param array $data
     * @return CsvRowInterface
     * @throws \Exception
     */
    public static function fromArray(array $data): CsvRowInterface
    {

        $data = array_map('trim', $data);
        self::validate($data);
        list($date, $a, $b, $c) = $data;
        $date = \DateTime::createFromFormat(self::DATE_FORMAT, $date);
        $date->setTime(0, 0);

        return new self($date, floatval($a), floatval($b), floatval($c));
    }

    /**
     * @param array $data
     * @return bool
     */
    private static function isValid(array $data): bool
    {
        if (count($data) < 4) {
            return false;
        }
        list($date, $a, $b, $c) = $data;
        $date = \DateTime::createFromFormat(self::DATE_FORMAT, $date);
        $lastErrors = \DateTime::getLastErrors();
        if ($lastErrors['warning_count'] > 0 || $lastErrors['error_count'] > 0) {
            return false;
        }
        foreach ([$a, $b, $c] as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private static function validate(array $data): void
    {
        if (!self::isValid($data)) {
            throw new \Exception(sprintf('Invalid CSV row [%s]', join(';', $data)));
        }
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return float
     */
    public function getA(): float
    {
        return $this->a;
    }

    /**
     * @param float $a
     */
    public function setA(float $a): void
    {
        $this->a = $a;
    }

    /**
     * @return float
     */
    public function getB(): float
    {
        return $this->b;
    }

    /**
     * @param float $b
     */
    public function setB(float $b): void
    {
        $this->b = $b;
    }

    /**
     * @return float
     */
    public function getC(): float
    {
        return $this->c;
    }

    /**
     * @param float $c
     */
    public function setC(float $c): void
    {
        $this->c = $c;
    }

    public function toArray(): array
    {
        return [
            $this->getDate()->format(self::DATE_FORMAT),
            $this->getA(),
            $this->getB(),
            $this->getC(),
        ];
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function compare(array $data): int
    {
        self::validate($data);
        list($date) = $data;
        $date = \DateTime::createFromFormat(self::DATE_FORMAT, $date);
        $date->setTime(0, 0);

        if ($this->getDate() == $date) {
            return ComparableInterface::EQUALS;
        } elseif ($this->getDate() < $date) {
            return ComparableInterface::LT;
        } else {
            return ComparableInterface::GT;
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function reduce(array $data)
    {
        self::validate($data);
        list(, $a, $b, $c) = $data;
        $this->setA($this->getA() + floatval($a));
        $this->setB($this->getB() + floatval($b));
        $this->setC($this->getC() + floatval($c));
    }
}