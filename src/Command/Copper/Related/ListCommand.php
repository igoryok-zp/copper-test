<?php

namespace App\Command\Copper\Related;

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
    protected static $defaultName = 'app:copper:related:list';

    public function __construct(CopperService $service, DataFormatter $formatter, string $name = null)
    {
        $this->service = $service;
        $this->formatter = $formatter;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('List related entities');
        $this->addArgument('type', InputArgument::REQUIRED);
        $this->addArgument('id', InputArgument::REQUIRED);
        $this->addOption('related', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('fields', 'f', InputOption::VALUE_OPTIONAL, 'Output Fields');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $items = $this->service->getRelatedItems($input->getArgument('id'), $input->getArgument('type'), $input->getOption('related'));
        foreach ($items as $item) {
            $response = $this->formatter->format($item, $input->getOption('fields'));
            $output->writeln($response);
        }
        return 0;
    }
}