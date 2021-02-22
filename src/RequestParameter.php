<?php

namespace Ysfkaya\IdeasoftApi;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class RequestParameter
 *
 * @package Ysfkaya\IdeasoftApi
 */
class RequestParameter implements Arrayable, Jsonable
{
    /**
     * @var array
     */
    public $parameters = [];

    /**
     * @var string
     */
    public $sort = 'id';

    /**
     * @var string
     */
    public $direction = 'asc';

    /**
     * @var int
     */
    public $limit = 100;

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var null
     */
    public $sinceId = null;

    /**
     * Search paramater
     *
     * @var array
     */
    public $q = [];

    /**
     * @var
     */
    public $ids;

    /**
     * @param string $column
     * @param string $term
     *
     * @return RequestParameter
     */
    public function filter($column, $term)
    {
        $this->q = array_filter(array_merge_recursive($this->q, ['q' => [$column => $term]]));

        $this->with($this->q);

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return RequestParameter
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int $page
     *
     * @return RequestParameter
     */
    public function page(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param null $sinceId
     *
     * @return RequestParameter
     */
    public function sinceId($sinceId)
    {
        $this->sinceId = $sinceId;

        return $this;
    }

    /**
     * @param mixed $ids
     *
     * @return RequestParameter
     */
    public function ids($ids)
    {
        if (is_array($ids)) {
            $ids = array_unique($ids);

            $ids = implode(',', $ids);
        }

        $this->ids = $ids;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return RequestParameter
     * @return RequestParameter
     */
    public function with(array $parameters = [])
    {
        $this->parameters = array_filter(array_merge($this->parameters, $parameters));

        return $this;
    }

    /**
     * @param string $sort
     *
     * @param string $direction
     *
     * @return RequestParameter
     */
    public function sort(string $sort, $direction = 'asc')
    {
        $this->sort = $sort;
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return $this
     */
    public function latest()
    {
        return $this->sort('id', 'desc');
    }

    /**
     * @return string
     */
    protected function sortBy()
    {
        $sortBy = $this->sort;

        if ($this->direction === 'desc') {
            $sortBy = '-' . $sortBy;
        }

        return $sortBy;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_filter(array_merge($this->parameters, [
            'sort' => $this->sortBy(),
            'limit' => $this->limit,
            'ids' => $this->ids,
            'page' => $this->page,
            'sinceId' => $this->sinceId,
        ]));
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
