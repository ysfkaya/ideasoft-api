<?php

namespace Ysfkaya\IdeasoftApi;

use GuzzleHttp\Client;
use Ysfkaya\IdeasoftApi\Traits\RequestParameters;

/**
 * Class Response
 *
 * @package Ysfkaya\IdeasoftApi
 */
class Response
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var RequestParameter
     */
    public $parameter;

    /**
     * @var string
     */
    public $endpoint;

    /**
     * Response constructor.
     *
     * @param Client $client
     * @param RequestParameter $parameter
     * @param string $endpoint
     */
    public function __construct(Client $client, RequestParameter $parameter, string $endpoint)
    {
        $this->client = $client;
        $this->parameter = $parameter;
        $this->endpoint = $endpoint;
    }

    /**
     * @return ResponseFormat
     */
    public function get()
    {
        $response = $this->client->get($this->endpoint, [
            'query' => $this->parameter->toArray()
        ]);

        return new ResponseFormat($response, $this);
    }

    /**
     * @return ResponseFormat
     */
    public function getById($id)
    {
        $response = $this->client->get($this->endpoint . '/' . $id);

        return new ResponseFormat($response, $this);
    }

    public function __get($name)
    {
        return $this->parameter->{$name};
    }
}
