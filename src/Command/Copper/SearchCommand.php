<?php

namespace App\Command\Copper;

use App\Component\DataFinder;
use App\Component\DataFormatter;
use App\Service\CopperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    /**
     * @var CopperService
     */
    private $service;

    /**
     * @var DataFinder
     */
    private $finder;

    /**
     * @var DataFormatter
     */
    private $formatter;

    /**
     * @var string
     */
    protected static $defaultName = 'app:copper:search';

    public function __construct(CopperService $service, DataFinder $finder, DataFormatter $formatter, string $name = null)
    {
        $this->service = $service;
        $this->finder = $finder;
        $this->formatter = $formatter;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Search an entity');
        $this->addArgument('type', InputArgument::REQUIRED);
        $this->addArgument('value', InputArgument::REQUIRED);
        $this->addOption('field', null, InputOption::VALUE_OPTIONAL, '', 'name');
        $this->addOption('fields', 'f', InputOption::VALUE_OPTIONAL, 'Output Fields');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $field = $input->getOption('field');
        $value = $input->getArgument('value');
        $page = 1;
        $company = $this->finder->find($field, $value, function () use ($type, &$page) {
            return $this->service->search($type, [
                'page_number' => $page++,
            ]);
        });
        if ($company !== null) {
            $response = $this->formatter->format($company, $input->getOption('fields'));
            $output->writeln($response);
        } else {
            $output->writeln(sprintf('<error>The %s with %s "%s" is not found</error>', $type, $field, $value));
        }
        return 0;
    }
}