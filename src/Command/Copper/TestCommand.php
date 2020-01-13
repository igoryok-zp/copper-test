<?php

namespace App\Command\Copper;

use App\Component\DataFinder;
use App\Service\CopperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
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
     * @var string
     */
    protected static $defaultName = 'app:copper:test';

    public function __construct(CopperService $service, DataFinder $finder, string $name = null)
    {
        $this->service = $service;
        $this->finder = $finder;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addOption('company', null, InputOption::VALUE_OPTIONAL, '', 'Dunder Mifflin');
        $this->addOption('person', null, InputOption::VALUE_OPTIONAL, '', 'Pam Beesley');
        $this->addOption('person-update', null, InputOption::VALUE_OPTIONAL, '', 'Pam Halpert');
        $this->addOption('opportunity', null, InputOption::VALUE_OPTIONAL, '', '20,000 post-it notes');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyName = $input->getOption('company');
        $personName = $input->getOption('person');
        $personNameUpdate = $input->getOption('person-update');
        $opportunityName = $input->getOption('opportunity');
        try {
            $page = 1;
            $company = $this->finder->find('name', $companyName, function () use (&$page) {
                return $this->service->search(CopperService::ENTITY_TYPE_COMPANY, [
                    'page_number' => $page++,
                ]);
            });
            if ($company === null) {
                throw new \Exception(sprintf('Company "%s" not found', $companyName));
            }
            $output->writeln(sprintf('1. Found company "%s", ID: %d', $company['name'], $company['id']));

            $person = $this->service->create(CopperService::ENTITY_TYPE_PERSON, [
                'name' => $personName,
            ]);
            $output->writeln(sprintf('2. Created person "%s", ID: %d', $person['name'], $person['id']));

            $this->service->addRelatedItem($person['id'], CopperService::ENTITY_TYPE_PERSON, $company['id'], CopperService::ENTITY_TYPE_COMPANY);
            $output->writeln('3. Person is now related with companies:');
            $relatedItems = $this->service->getRelatedItems($person['id'], CopperService::ENTITY_TYPE_PERSON, CopperService::ENTITY_TYPE_COMPANY);
            foreach ($relatedItems as $relatedItem) {
                $output->writeln($relatedItem['id']);
            }

            $person = $this->service->update($person['id'], CopperService::ENTITY_TYPE_PERSON, [
                'name' => $personNameUpdate,
            ]);
            $output->writeln(sprintf('4. Person %d name is updated. New name: %s', $person['id'], $person['name']));

            $opportunity = $this->service->create(CopperService::ENTITY_TYPE_OPPORTUNITY, [
                'name' => $opportunityName,
                'primary_contact_id' => $person['id'],
            ]);
            $output->writeln(sprintf('5. Created an opportunity "%s" for %s, ID: %d', $opportunity['name'], $person['name'], $opportunity['id']));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>ERROR: %s</error>', $e->getMessage()));
        }
        return 0;
    }
}