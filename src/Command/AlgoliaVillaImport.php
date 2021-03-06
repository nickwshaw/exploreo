<?php
// src/Command/CreateUserCommand.php
namespace Exploreo\Command;

use Exploreo\Client\VillaForYouClient;
use Exploreo\Service\AlgoliaIndexService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
// #[AsCommand(name: 'app:create-user')]
class AlgoliaVillaImport extends Command
{
    private $indexService;

    public function __construct(AlgoliaIndexService $indexService, string $name = null)
    {
        $this->indexService = $indexService;
        parent::__construct($name);
    }

    protected static $defaultName = 'exploreo:import-villas';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->indexService->updateIndex();
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
