<?php

namespace Ysfkaya\IdeasoftApi;

use ArrayAccess;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseFormat
 *
 * @package Ysfkaya\IdeasoftApi
 */
class ResponseFormat implements Jsonable, JsonSerializable, Arrayable, ArrayAccess
{
    /**
     * @var Response
     */
    protected $instance;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $items;

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * ResponseFormat constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response, Response $instance)
    {
        $this->response = $response;
        $this->instance = $instance;

        $this->items = $this->toEachCollection();

        $this->total = (int)$this->response->getHeaderLine('total_count') ?: 0;
    }

    /**
     * Get the collection items
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get the total result count
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Returns if there is any data
     *
     * @return boolean
     */
    public function hasAny()
    {
        return $this->total > 0;
    }

    /**
     * @param null $perPage
     * @param string $pageName
     * @param null $page
     *
     * @return LengthAwarePaginator
     */
    public function pagination($perPage = null, $pageName = 'page', $page = null)
    {
        $perPage = $perPage ?: $this->instance->limit;

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        return $this->paginator($this->items, $this->total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Convert collection for each response array
     *
     * @return \Illuminate\Support\Collection
     */
    protected function toEachCollection()
    {
        $collection = collect($this->toArray());

        return $collection->map(function ($items) {
            return is_array($items) ? collect($items) : $items;
        });
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param \Illuminate\Support\Collection $items
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param array $options
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->jsonSerialize();
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return $this->items->offsetExists($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        return $this->items->offsetSet($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        return $this->items->offsetUnset($offset);
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        return $this->items->{$name};
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        return $this->items->{$name}(...$arguments);
    }
}
