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

    public $async = false;

    public $asyncCallback;

    /**
     * Response constructor.
     *
     * @param Client $client
     * @param RequestParameter $parameter
     * @param string $endpoint
     */
    public function __construct(Client $client, RequestParameter $parameter, string $endpoint, $async = false, callable $asyncCallback = null)
    {
        $this->client = $client;
        $this->parameter = $parameter;
        $this->endpoint = $endpoint;
        $this->async = $async;
        $this->asyncCallback = $asyncCallback;
    }

    public function create($attributes)
    {
        $method = $this->requestMethod('post');

        $response = $this->client->{$method}($this->endpoint, [
            'json' => $attributes,
        ]);

        return $this->format($response);
    }

    public function delete($id = null)
    {
        $method = $this->requestMethod('delete');

        $requestUri = $id ? $this->endpoint . '/' . $id : $this->endpoint;

        $response = $this->client->{$method}($requestUri);

        return $this->format($response);
    }

    public function update($id, $attributes)
    {
        $method = $this->requestMethod('put');

        $response = $this->client->{$method}($this->endpoint . '/' . $id, [
            'json' => $attributes,
        ]);

        return $this->format($response);
    }

    /**
     * @return ResponseFormat
     */
    public function get()
    {
        $method = $this->requestMethod('get');

        $response = $this->client->{$method}($this->endpoint, [
            'query' => $this->parameter->toArray()
        ]);

        return $this->format($response);
    }

    /**
     * @return ResponseFormat
     */
    public function getById($id)
    {
        $method = $this->requestMethod('get');

        $response = $this->client->{$method}($this->endpoint . '/' . $id);

        return $this->format($response);
    }

    public function format($response)
    {
        if ($this->async && $this->asyncCallback && method_exists($response, 'then')) {
            return $response->then(function ($response) {
                call_user_func($this->asyncCallback, $this->format($response));
            });
        }

        return new ResponseFormat($response, $this);
    }

    protected function requestMethod($method)
    {
        return $this->async ? $method . 'Async' : $method;
    }

    public function __get($name)
    {
        return $this->parameter->{$name};
    }
}
