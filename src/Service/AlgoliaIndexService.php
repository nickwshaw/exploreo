<?php

namespace Exploreo\Service;

use Algolia\AlgoliaSearch\SearchClient;
use Exploreo\Client\VillaForYouClient;
use Exploreo\VillaMetaData;

class AlgoliaIndexService
{
    private $villasClient;
    private $angoliaClient;

    private $chunkSize = 50;

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
        // TODO move batch getting of data into villasClient
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
                echo "\n Getting chunk of $this->chunkSize details for index $index";
                // Get details for chunk of ids
                $houseDetails = array_merge($houseDetails, $this->filterDetails($this->villasClient->getDataOfHouses($houseCodes[$index])));
                $index++;
            }

            if ($index > 5) {
                //break;
                //dump($houseCodes);die();
            }
        }

        // TODO merge remaining houses
        $houseDetails = array_merge($houseDetails, $this->filterDetails($this->villasClient->getDataOfHouses($houseCodes[$index])));

        //dump($houseDetails[0]);
        dump(count($houseDetails));
        $index = $this->angoliaClient->initIndex("test_index");
        echo "\n saving objects";
        $index->saveObjects($houseDetails);
        echo "\n done saving objects";
    }

    private function filterDetails(array $houseDetails): array
    {
        $details = [];
        $englishDescription = null;
        foreach ($houseDetails as $houseDetail) {
            foreach ($houseDetail[VillaMetaData::API_METHOD_DESCRIPTION] as $description) {
                if ($description[VillaMetaData::API_KEY_LANGUAGE] === 'EN') {
                    $englishDescription = $description;
                    break;
                }
            }
            $details[] = [
                "objectID" => $houseDetail[VillaMetaData::API_KEY_HOUSE_CODE],
                "name" => $houseDetail[VillaMetaData::API_METHOD_BASIC_INFORMATION][VillaMetaData::API_KEY_NAME],
                "province" => $houseDetail[VillaMetaData::API_METHOD_BASIC_INFORMATION][VillaMetaData::API_KEY_PROVINCE],
                "numberOfStars" => $houseDetail[VillaMetaData::API_METHOD_BASIC_INFORMATION][VillaMetaData::API_KEY_NUMBER_OF_STARS],
                "media" => $houseDetail[VillaMetaData::API_METHOD_MEDIA][0][VillaMetaData::API_KEY_MEDIA_PHOTOS],
                VillaMetaData::META_KEY_DESCRIPTION_TITLE => $englishDescription[VillaMetaData::API_KEY_DESCRIPTION_TITLE],
                VillaMetaData::META_KEY_DESCRIPTION => $englishDescription[VillaMetaData::API_KEY_DESCRIPTION],
            ];
        }
        //dump($details);die();
        return $details;
    }
}
