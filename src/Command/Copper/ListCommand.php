<?php

namespace App\Command\Copper;

use App\Component\DataFormatter;
use App\Service\CopperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    /**
     * @var CopperService
     */
    private $service;

    /**
     * @var DataFormatter
     */
    private $formatter;

    /**
     * @var string
     */
    protected static $defaultName = 'app:copper:list';

    public function __construct(CopperService $service, DataFormatter $formatter, string $name = null)
    {
        $this->service = $service;
        $this->formatter = $formatter;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('List entities');
        $this->addArgument('type', InputArgument::REQUIRED);
        $this->addOption('fields', 'f', InputOption::VALUE_OPTIONAL, 'Output Fields');
        $this->addOption('page', 'p', InputOption::VALUE_OPTIONAL, 'Page', '1');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entities = $this->service->search($input->getArgument('type'), [
            'page_number' => $input->getOption('page'),
        ]);
        foreach ($entities as $entity) {
            $response = $this->formatter->format($entity, $input->getOption('fields'));
            $output->writeln($response);
        }
        return 0;
    }
}