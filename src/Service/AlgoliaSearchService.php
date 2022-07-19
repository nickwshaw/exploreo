<?php

namespace Exploreo\Service;

use Algolia\AlgoliaSearch\SearchClient;

class AlgoliaSearchService
{
    private $searchClient;

    private $index;
    /**
     * @var array
     */
    private $searchResult;

    public function __construct(SearchClient $angoliaClient)
    {
        $this->searchClient = $angoliaClient;
    }

    public function setIndex(string $index): AlgoliaSearchService
    {
        $this->index = $index;
        return $this;
    }

    public function getAll(): array
    {
        $index = $this->searchClient->initIndex($this->index);
        $this->searchResult = $index->search('', [
            'hitsPerPage' => 10
        ]);
        return $this->searchResult;
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     * Should be 4:3 ratio https://calculateaspectratio.com/
     */
    public static function getImagePath(int $width, int $height): string
    {
        return sprintf('https://media.villaforyou.net/photo/%d/%d/', $width, $height);
    }

    /**
     * @param string $houseCode
     * @return string
     */
    public static function getVillaLink(string $houseCode): string
    {
        return sprintf('https://villaforyou.com/en/sp-exploreo/%s', $houseCode);
    }

}
