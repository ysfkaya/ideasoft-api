<?php

namespace Ysfkaya\IdeasoftApi;

use GuzzleHttp\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

/**
 * Class Service
 *
 * @package Ysfkaya\IdeasoftApi\Services
 *
 * @method $this limit(int $limit)
 * @method $this ids($ids)
 * @method $this page(int $page)
 * @method $this sinceId($sinceId)
 * @method $this with(array $parameters = [])
 * @method $this sort(string $sort, $direction = 'asc')
 * @method $this filter($column,$term)
 * @method $this latest()
 * @method $this direction(string $direction)
 */
class Service
{
    /**
     * @var string
     */
    const API_ENDPOINT = 'api';

    /**
     * @var string
     */
    public $endpoint;

    /**
     * @var Ideasoft
     */
    public $ideasoft;

    /**
     * @var RequestParameter
     */
    public $parameters;

    public $async = false;

    public $asyncCallback;

    /**
     * Service constructor.
     *
     * @param Ideasoft $ideasoft
     * @param $endpoint
     */
    public function __construct(Ideasoft $ideasoft, $endpoint)
    {
        $this->ideasoft = $ideasoft;
        $this->endpoint = $endpoint;
        $this->parameters = new RequestParameter;
    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        $defaultOptions = [
            'base_uri' => $this->getRequestUrl(),
            'headers' => [
                'Host' => $this->ideasoft->withStoreName(null, false),
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->ideasoft->token
            ],
        ];

        $options = array_merge(config('ideasoft.httpOptions', []), $defaultOptions);

        return new Client($options);
    }

    /**
     * @return string|string[]
     */
    protected function getRequestUrl()
    {
        $url = $this->ideasoft->withStoreName(self::API_ENDPOINT);

        return Str::finish($url, '/');
    }

    /**
     * @return ResponseFormat
     */
    public function get()
    {
        return $this->getResponse()->get();
    }

    /**
     * @return ResponseFormat
     */
    public function delete($id = null)
    {
        return $this->getResponse()->delete($id);
    }

    /**
     * @return ResponseFormat
     */
    public function getById($id)
    {
        return $this->getResponse()->getById($id);
    }

    public function create($attributes)
    {
        return $this->getResponse()->create($attributes);
    }

    /**
     *
     * @param  $id
     * @param $attributes
     *
     * @return ResponseFormat
     */
    public function update($id, $attributes)
    {
        return $this->getResponse()->update($id, $attributes);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function pagination($page = null, $perPage = 100, $pageName = 'page')
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $response = $this->page($page)->limit($perPage)->get();

        return $response->pagination($perPage, $pageName, $page);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->parameters->{$name}(...$arguments);

        return $this;
    }

    public function getFullRequestUrl()
    {
        return $this->getRequestUrl() . $this->endpoint . '?' . http_build_query($this->parameters->toArray());
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return new Response(
            $this->getHttpClient(),
            $this->parameters,
            $this->endpoint,
            $this->async,
            $this->asyncCallback
        );
    }
}
