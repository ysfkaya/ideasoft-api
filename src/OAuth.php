<?php

namespace Ysfkaya\IdeasoftApi;

use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidStateException\InvalidStateException;
use Ysfkaya\IdeasoftApi\Traits\StoreName;

class OAuth
{
    use StoreName;

    const GRANT_AUTHORIZE = 'authorization_code';

    const GRANT_REFRESH = 'refresh_token';

    /**
     * The HTTP request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The custom Guzzle configuration options.
     *
     * @var array
     */
    protected $guzzle = [];

    /**
     * Create a new provider instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param $storeName
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param array $guzzle
     *
     */
    public function __construct(Request $request, $storeName, $clientId, $clientSecret, $redirectUrl, array $guzzle = [])
    {
        $this->guzzle = $guzzle;
        $this->request = $request;
        $this->storeName = $storeName;
        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     *
     * @return string
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->withStoreName('admin/user/auth'), $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    public function getTokenUrl()
    {
        return $this->withStoreName('oauth/v2/token');
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return AuthManifest
     */
    protected function manifest()
    {
        return new AuthManifest;
    }

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $this->request->session()->put('state', $state = $this->getState());

        return new RedirectResponse($this->getAuthUrl($state));
    }

    /**
     * Build the authentication URL for the provider from the given base URL.
     *
     * @param string $url
     * @param string $state
     *
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url . '?' . http_build_query($this->getCodeFields($state), '', '&');
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string|null $state
     *
     * @return array
     */
    protected function getCodeFields($state)
    {
        return [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'state' => $state
        ];
    }

    /**
     * Handle the callback request
     *
     * @return AuthManifest
     */
    public function authorize()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        return $this->handleResponse($response);
    }

    /**
     * Refresh authorization using by refresh token
     *
     * @return AuthManifest
     */
    public function reAuthorize($refreshToken)
    {
        $response = $this->getRefreshTokenResponse($refreshToken);

        return $this->handleResponse($response);
    }

    /**
     * Handle the auhorization response
     *
     * @param $response
     *
     * @return AuthManifest
     */
    protected function handleResponse($response)
    {
        $manifest = $this->manifest();

        $scopes = explode(' ', Arr::get($response, 'scope'));

        return $manifest->setToken(Arr::get($response, 'access_token'))
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setScopes($scopes)
            ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        $state = $this->request->session()->pull('state');

        return !(strlen($state) > 0 && $this->request->input('state') === $state);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param string $code
     *
     * @return array
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the access token response for the given refresh token.
     *
     * @param string $refreshToken
     *
     * @return array
     */
    public function getRefreshTokenResponse($refreshToken)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getRefreshTokenFields($refreshToken),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => self::GRANT_AUTHORIZE
        ];
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getRefreshTokenFields($token)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token,
            'grant_type' => self::GRANT_REFRESH
        ];
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->request->input('code');
    }

    /**
     * Set the redirect URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function redirectUrl($url)
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client($this->guzzle);
        }

        return $this->httpClient;
    }

    /**
     * Set the Guzzle HTTP client instance.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Set the request instance.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function getState()
    {
        return Str::random(40);
    }
}
