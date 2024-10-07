<?php

namespace Drupal\filmbuff\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\filmbuff\MovieAPIConnector;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the movie listings.
 */
class MovieListing extends ControllerBase {

  /**
   * The MovieAPIConnector service.
   *
   * @var \Drupal\filmbuff\MovieAPIConnector
   */
  protected MovieAPIConnector $movieApiConnector;

  /**
   * Inject the MovieAPIConnector service.
   */
  public function __construct(MovieAPIConnector $movie_api_connector) {
    $this->movieApiConnector = $movie_api_connector;
  }

  /**
   * Implements the static create method to inject services.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('filmbuff.movie_api_connector')
    );
  }

  /**
   * Display movie listings.
   *
   * @return array
   *   A render array for the movie listing page.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function view(): array {
    // Get the list of movies.
    $movies = $this->listMovies();

    // Construct the render array for the page content.
    return [
      '#theme' => 'filmbuff_template',
      '#movies' => $movies,
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Retrieve a list of movies.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function listMovies(): array {
    try {
      $movie_list = $this->movieApiConnector->discoverMovies();
    }
    catch (GuzzleException $e) {
      \Drupal::logger('filmbuff')->error($e->getMessage());
      // Or handle the error as needed.
      return [];
    }
    return $movie_list;
  }

}
