<?php

namespace Ysfkaya\IdeasoftApi;

use GuzzleHttp\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
    const BASE_URL = 'http://{store}.myideasoft.com/api/';

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
        return new Client([
            'base_uri' => $this->getRequestUrl(),
            'headers' => [
                'Host' => $this->ideasoft->withStoreName('{store}.myideasoft.com'),
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->ideasoft->token
            ],
        ]);
    }

    /**
     * @return string|string[]
     */
    protected function getRequestUrl()
    {
        return $this->ideasoft->withStoreName(self::BASE_URL);
    }

    public function withAsync(callable $callback = null)
    {
        $this->async = true;
        $this->asyncCallback = $callback;

        return $this;
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
    public function pagination($perPage = 100, $page = null, $pageName = 'page')
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        return $this->page($page)->limit($perPage)->get()->pagination($perPage, $pageName, $page);
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
