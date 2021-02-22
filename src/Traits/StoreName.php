<?php

namespace Ysfkaya\IdeasoftApi\Traits;

use Illuminate\Support\Str;

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
    public function withStoreName($endpoint = null, $useProtocol = true)
    {
        return ($useProtocol ? 'https://' : '') . $this->storeName . '.myideasoft.com' . ($endpoint ? Str::start($endpoint, '/') : $endpoint);
    }
}
