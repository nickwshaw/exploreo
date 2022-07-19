<?php

use Algolia\AlgoliaSearch\SearchClient;
use Exploreo\Service\AlgoliaSearchService;

/**
 * Plugin Name:     Algolia Custom Integration
 * Description:     Add Algolia Search feature
 * Text Domain:     algolia-custom-integration
 * Version:         1.0.0
 *
 * @package         Algolia_Custom_Integration
 */

//require_once __DIR__ . '/api-client/autoload.php';
// If you're using Composer, require the Composer autoload
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/wp-cli.php';

global $algolia;
global $exploreoSearch;
$exploreoSearch= new AlgoliaSearchService(
    SearchClient::create($_ENV['ALGOLIA_APP_ID'], $_ENV['ALGOLIA_API_KEY'])
);

$algolia = SearchClient::create($_ENV['ALGOLIA_APP_ID'], $_ENV['ALGOLIA_API_KEY']);
