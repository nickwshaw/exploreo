#!/usr/bin/env php
<?php
// console_app.php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use Exploreo\Command\AlgoliaVillaImport;
use GuzzleHttp\Client;
use Exploreo\Client\VillaForYouClient;
use Exploreo\Service\AlgoliaIndexService;
use Algolia\AlgoliaSearch\SearchClient;
use Exploreo\Command\AlgoliaSearchCommand;
use Exploreo\Service\AlgoliaSearchService;

$dotenv = new Dotenv();
$dotenv->usePutenv()->load(__DIR__.'/.env');

$application = new Application();

// ... register commands
$application->add(
    new AlgoliaVillaImport(
        new AlgoliaIndexService(
            new VillaForYouClient(
                new Client(),
                getenv('VILLA_API_USERNAME'),
                getenv('VILLA_API_PASSWORD')
            ),
            SearchClient::create(getenv('ALGOLIA_APP_ID'), getenv('ALGOLIA_API_KEY'))
        )
    )
);

$application->add(
    new AlgoliaSearchCommand(
        new AlgoliaSearchService(
            SearchClient::create(getenv('ALGOLIA_APP_ID'), getenv('ALGOLIA_API_KEY')),
            getenv('ANGOLIA_EN_INDEX')
        )
    )
);
$application->run();
