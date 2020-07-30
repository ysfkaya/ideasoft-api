<?php

namespace Ysfkaya\IdeasoftApi\Traits;

trait StoreName
{
    /**
     * The idaseoft store name.
     *
     * @var string
     */
    protected $storeName;

    /**
     * Return string with replaced store name
     *
     * @param $arg
     *
     * @return string|string[]
     */
    public function withStoreName($arg)
    {
        return str_replace('{store}', $this->storeName, $arg);
    }
}
