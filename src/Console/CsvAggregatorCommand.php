<?php


namespace App\Console;


use App\Entity\LargeCsvFile;
use App\Service\AggregatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CsvAggregatorCommand extends Command
{
    /**
     * @var AggregatorInterface
     */
    private $csvAggregator;

    protected static $defaultName = 'app:csv-run';

    /**
     * CsvAggregatorCommand constructor.
     * @param AggregatorInterface $csvAggregator
     */
    public function __construct(AggregatorInterface $csvAggregator)
    {
        $this->csvAggregator = $csvAggregator;
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('dir');
        $this->addArgument('resultFileName', null, '', 'result.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('dir');
        $resultFileName = $input->getArgument('resultFileName');

        $finder = new Finder();
        $finder->files()->name('*.csv')->in($dir);
        foreach ($finder as $csvFile) {
            try {
                $this->csvAggregator->aggregate(new LargeCsvFile($csvFile->getPathname()), $resultFileName);
                echo $csvFile->getPathname() ."\n";
            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }
        }
    }
}