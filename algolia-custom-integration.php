<?php

use Algolia\AlgoliaSearch\SearchClient;
use Exploreo\Service\AlgoliaSearchService;
use Dotenv\Dotenv;

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

/**
 * Exploreo
 */
(\Dotenv\Dotenv::createImmutable(__DIR__))->load();

global $algolia;
global $exploreoSearch;
//var_dump($_ENV['ALGOLIA_APP_ID'], $_ENV['ALGOLIA_API_KEY']);
$exploreoSearch = new AlgoliaSearchService(
    SearchClient::create($_ENV['ALGOLIA_APP_ID'], $_ENV['ALGOLIA_API_KEY'])
);

$algolia = SearchClient::create($_ENV['ALGOLIA_APP_ID'], $_ENV['ALGOLIA_API_KEY']);

// Create a hook for villas index cron job
//add_action('exploreo_villas_import', array($this, 'indexVillas'));

