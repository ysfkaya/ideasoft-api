<?php

namespace Ysfkaya\IdeasoftApi;

use InvalidStateException\InvalidServiceException;
use Ysfkaya\IdeasoftApi\Traits\Authenticator;
use Ysfkaya\IdeasoftApi\Traits\StoreName;

/**
 * Class Ideasoft
 *
 * @package Ysfkaya\IdeasoftApi
 *
 * @property Service $orders
 * @property Service $products
 * @property Service $product_images
 */
class Ideasoft
{
    use Authenticator, StoreName;

    /**
     * The access token
     *
     * @var string
     */
    public $token;

    /**
     * @var array
     */
    public $services = [];

    /**
     * Ideasoft constructor.
     *
     * @param string $storeName
     */
    public function __construct(string $storeName)
    {
        $this->storeName = $storeName;

        $this->setupDefaultServices();
    }

    /**
     * @param mixed $token
     *
     * @return Ideasoft
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Setup the default services
     *
     * @return void
     */
    protected function setupDefaultServices()
    {
        foreach (['orders', 'products', 'product_images', 'order_details'] as $service) {
            $this->setService(
                new Service($this, $service)
            );
        }
    }

    /**
     * @param Service $service
     *
     * @return $this
     */
    public function setService(Service $service)
    {
        $this->services[$service->endpoint] = $service;

        return $this;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getService($name)
    {
        if (!isset($this->services[$name])) {
            throw new InvalidServiceException("The [$name] service is invalid in ideasoft api");
        }

        return $this->services[$name];
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->services[$name])) {
            return $this->getService($name);
        }

        throw new \InvalidArgumentException;
    }
}