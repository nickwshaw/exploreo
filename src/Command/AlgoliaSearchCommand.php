<?php

namespace Exploreo\Command;

use Exploreo\Service\AlgoliaSearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaSearchCommand extends Command
{
    private AlgoliaSearchService $searchService;

    public function __construct(AlgoliaSearchService $searchService, string $name = null)
    {
        $this->searchService = $searchService;
        parent::__construct($name);
    }

    protected static $defaultName = 'exploreo:search-villas:get-all';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->searchService->getAll();
        // ... put here the code to create the user

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
