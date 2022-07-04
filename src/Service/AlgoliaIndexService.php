<?php

namespace Exploreo\Service;

use Exploreo\Client\VillaForYouClient;

class AlgoliaIndexService
{
    private $client;

    private $chunkSize = 10;

    public function __construct(VillaForYouClient $client)
    {
        $this->client = $client;
    }

    public function updateIndex()
    {
        dump($this->client->getListOfHouses());
    }
}
