<?php

namespace Exploreo\Service;

use Algolia\AlgoliaSearch\SearchClient;
use Exploreo\Client\VillaForYouClient;
use Exploreo\VillaMetaData;

class AlgoliaIndexService
{
    private VillaForYouClient $villasClient;
    private SearchClient $angoliaClient;

    private $chunkSize = 20;

    private $env;

    // env Real or Test

    public function __construct(VillaForYouClient $villasClient, SearchClient $angoliaClient, $env='Real')
    {
        $this->villasClient = $villasClient;
        $this->angoliaClient = $angoliaClient;
        $this->env = $env;
    }

    public function updateIndex()
    {
        //dump($this->villasClient->getListOfHouses());
        $houseCodes = [];
        $index = 0;
        $houseDetails = [];
        foreach ($this->villasClient->getListOfHouses() as $house) {
            //dump($house[VillaMetaData::API_REAL_OR_TEST]);
            if ($house[VillaMetaData::API_REAL_OR_TEST] !== $this->env) {
                continue;
            }

            $houseCodes[$index][] = $house[VillaMetaData::API_KEY_HOUSE_CODE];
            //dump(count($houseCodes[$index]));
            //dump($houseCodes);

            if (count($houseCodes[$index]) >= $this->chunkSize) {
                // Get details for chunk of ids
                $houseDetails = array_merge($houseDetails, $this->filterDetails($this->villasClient->getDataOfHouses($houseCodes[$index])));
                $index++;
            }

            if ($index > 5) {
                break;
                //dump($houseCodes);die();
            }
        }
        
        // TODO merge remaining houses
        
        dump($houseDetails[0]);
        dump(count($houseDetails));
        $index = $this->angoliaClient->initIndex("test_index");
        $index->saveObjects($houseDetails);
    }

    private function filterDetails(array $houseDetails): array
    {
        $details = [];
        foreach ($houseDetails as $houseDetail) {
            $details[] = [
                "objectID" => $houseDetail[VillaMetaData::API_KEY_HOUSE_CODE],
                "name" => $houseDetail[VillaMetaData::API_METHOD_BASIC_INFORMATION][VillaMetaData::API_KEY_NAME],
                "province" => $houseDetail[VillaMetaData::API_METHOD_BASIC_INFORMATION][VillaMetaData::API_KEY_PROVINCE],
                "NumberOfStars" => $houseDetail[VillaMetaData::API_METHOD_BASIC_INFORMATION][VillaMetaData::API_KEY_NUMBER_OF_STARS]
            ];
        }
        //dump($details);die();
        return $details;
    }
}
