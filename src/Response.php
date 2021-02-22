<?php

namespace Ysfkaya\IdeasoftApi;

use GuzzleHttp\Client;

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

    public function create($attributes)
    {
        $response = $this->client->post($this->endpoint, [
            'json' => $attributes,
        ]);

        return $this->format($response);
    }

    public function delete($id = null)
    {
        $requestUri = $id ? $this->endpoint . '/' . $id : $this->endpoint;

        $response = $this->client->delete($requestUri);

        return $this->format($response);
    }

    public function update($id, $attributes)
    {
        $response = $this->client->update($this->endpoint . '/' . $id, [
            'json' => $attributes,
        ]);

        return $this->format($response);
    }

    /**
     * @return ResponseFormat
     */
    public function get()
    {
        $response = $this->client->get($this->endpoint, [
            'query' => $this->parameter->toArray()
        ]);

        return $this->format($response);
    }

    /**
     * @return ResponseFormat
     */
    public function getById($id)
    {
        $response = $this->client->get($this->endpoint . '/' . $id);

        return $this->format($response);
    }

    /**
     * @return ResponseFormat
     */
    public function format($response)
    {
        return new ResponseFormat($response, $this);
    }

    public function __get($name)
    {
        return $this->parameter->{$name};
    }
}
