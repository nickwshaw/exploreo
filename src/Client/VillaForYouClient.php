<?php

declare(strict_types=1);

namespace Exploreo\Client;

use GuzzleHttp\ClientInterface;
use Exploreo\VillaMetaData;

class VillaForYouClient
{
    /**
     * @var ClientInterface
     */
    private $client;
    private $username;
    private $password;

    public function __construct(ClientInterface $client, string $username, string $password)
    {
        dump($username);
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
    }

    public function getListOfHouses(): array
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'ListOfHousesV1',
            'params' => [
                'PartnerCode' => $this->username,
                'PartnerPassword' => $this->password
            ],
            'id' => '38114532'
        ];

        $response = $this->client->request(
            'POST',
            'https://listofhousesv1.villaforyou.biz/cgi/jsonrpc-partner/listofhousesv1',
            [
                'auth' => [$this->username, $this->password],
                'json' => $data
            ]
        );
        return  json_decode((string) $response->getBody(), true);
    }

    public function getDataOfHouses(array $houseIds): array
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'DataOfHousesV1',
            'params' => [
                'PartnerCode' => $this->username,
                'PartnerPassword' => $this->password,
                'HouseCodes' => $houseIds,
                'Items' => [
                    VillaMetaData::API_METHOD_BASIC_INFORMATION,
                    VillaMetaData::API_METHOD_DESCRIPTION,
                    VillaMetaData::API_METHOD_MEDIA
                ]
            ],
        ];

        $response = $this->client->request(
            'POST',
            'https://dataofhousesv1.villaforyou.biz/cgi/jsonrpc-partner/dataofhousesv1',
            [
                'auth' => [$this->username, $this->password],
                'json' => $data
            ]
        );
        return  json_decode((string) $response->getBody(), true);
    }

}
