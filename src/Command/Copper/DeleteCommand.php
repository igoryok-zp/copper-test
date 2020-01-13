<?php

namespace App\Command\Copper;

use App\Component\DataFormatter;
use App\Service\CopperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
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
    protected static $defaultName = 'app:copper:delete';

    public function __construct(CopperService $service, DataFormatter $formatter, string $name = null)
    {
        $this->service = $service;
        $this->formatter = $formatter;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Delete the entity');
        $this->addArgument('type', InputArgument::REQUIRED);
        $this->addArgument('id', InputArgument::REQUIRED);
        $this->addOption('fields', 'f', InputOption::VALUE_OPTIONAL, 'Output Fields');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->service->delete($input->getArgument('id'), $input->getArgument('type'));
        $response = $this->formatter->format($data, $input->getOption('fields'));
        $output->writeln($response);
        return 0;
    }
}
