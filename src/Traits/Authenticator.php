<?php

namespace Ysfkaya\IdeasoftApi\Traits;

use Illuminate\Support\Carbon;
use Ysfkaya\IdeasoftApi\OAuth;

/**
 * Trait Authenticator
 *
 * @package Ysfkaya\IdeasoftApi\Traits
 */
trait Authenticator
{
    /**
     * @var OAuth
     */
    protected $authenticator;

    /**
     * Get the authenticator
     *
     * @return OAuth
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * Set the authenticator
     *
     * @param OAuth $authenticator
     *
     * @return $this
     */
    public function setAuthenticator(OAuth $authenticator)
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * @param Carbon $loggedIn
     * @param int $expireIn
     *
     * @return bool
     */
    public function authenticated(Carbon $loggedIn, $expireIn = 21600)
    {
        $expireDate = $loggedIn->addSeconds($expireIn);

        return Carbon::now()->diffInSeconds($expireDate, false) > 0;
    }
}
