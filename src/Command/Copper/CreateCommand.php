<?php

namespace App\Command\Copper;

use App\Component\DataFormatter;
use App\Component\DataParser;
use App\Service\CopperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
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
     * @var DataParser
     */
    private $parser;

    /**
     * @var string
     */
    protected static $defaultName = 'app:copper:create';

    public function __construct(CopperService $service, DataFormatter $formatter, DataParser $parser, string $name = null)
    {
        $this->service = $service;
        $this->formatter = $formatter;
        $this->parser = $parser;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Create an entity');
        $this->addArgument('type', InputArgument::REQUIRED);
        $this->addArgument('data', InputArgument::REQUIRED, 'Semicolon-separated data, e.g. "field1=Value1;field2=Value2"');
        $this->addOption('fields', 'f', InputOption::VALUE_OPTIONAL, 'Output Fields');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $this->service->create($input->getArgument('type'), $this->parser->parse($input->getArgument('data')));
        $response = $this->formatter->format($entity, $input->getOption('fields'));
        $output->writeln($response);
        return 0;
    }
}