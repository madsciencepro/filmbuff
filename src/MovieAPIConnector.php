<?php

namespace Drupal\filmbuff;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * MovieAPIConnector class to connect to the external movie database API.
 */
class MovieAPIConnector {

  private Client $client;
  private array $query = [];
  private $logger;

  /**
   * Constructs the MovieAPIConnector object.
   *
   * @param \Drupal\Core\Http\ClientFactory $client_factory
   *   The HTTP client factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(ClientFactory $client_factory, ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    $movie_api_config = $config_factory->get('filmbuff.movie_api_config');
    $api_base_url = $movie_api_config->get('api_base_url');
    $api_key = $movie_api_config->get('api_key');

    if (empty($api_base_url)) {
      $this->logger->critical('Movie API base URL is not set. Please configure the base URL in the module settings.');
      throw new \RuntimeException('Movie API base URL is missing.');
    }

    if (empty($api_key)) {
      $this->logger->critical('Movie API key is not set. Please configure the API key in the module settings.');
      throw new \RuntimeException('Movie API key is missing.');
    }

    $this->client = $client_factory->fromOptions(['base_uri' => $api_base_url]);
    $this->logger = $logger;
    $this->query = ['api_key' => $api_key];

  }

  /**
   * Discover movies using the movie API.
   *
   * @return array
   *   The data returned from the API or an empty array if the request fails.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function discoverMovies() {
    $this->logger->info('MovieAPIConnector: discoverMovies method started.');

    $data = [];
    $endpoint = '/3/discover/movie';
    $options = [
      'query' => $this->query,
      'headers' => ['Accept' => 'application/json'],
    ];

    try {
      // Log the full request URL for debugging.
      $this->logger->info('Requesting Movie API: @url', ['@url' => $this->client->getConfig('base_uri') . $endpoint]);

      $response = $this->client->get($endpoint, $options);
      $results = $response->getBody()->getContents();

      // Log the raw response for debugging.
      $this->logger->info('Movie API response: @response', ['@response' => $results]);

      // Decode JSON response.
      $data = json_decode($results, TRUE);
      if (json_last_error() !== JSON_ERROR_NONE) {
        $this->logger->error('Error decoding Movie API response: @error', ['@error' => json_last_error_msg()]);
        return [];
      }
    }
    catch (RequestException $e) {
      $this->logger->error('Movie API request failed: @message', ['@message' => $e->getMessage()]);
    }
    catch (\Exception $e) {
      $this->logger->error('Unexpected error during Movie API request: @message', ['@message' => $e->getMessage()]);
    }

    $this->logger->info('MovieAPIConnector: discoverMovies method finished.');
    // Check if the 'results' key exists in the response.
    return $data['results'] ?? [];
  }

}
