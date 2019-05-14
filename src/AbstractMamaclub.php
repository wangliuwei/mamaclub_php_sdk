<?php
namespace Mamaclub;

use Mamaclub\Grant\AbstractGrant;
use Mamaclub\Grant\GrantFactory;

abstract class AbstractMamaclub{
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

    public $clientId;

    public $clientSecret;

    public $redirectUri;

    public $scopes;

    public $state;

    protected $grantFactory;

    public function __construct(){
        $this->setGrantFactory();
    }

    abstract function createAccessToken(array $response, AbstractGrant $grant);

    /**
     * Sets the grant factory instance.
     */
    public function setGrantFactory()
    {
        $this->grantFactory = new GrantFactory();
    }


    /**
     * @return string
     */
    public function getDefaultState(){
        return 'mmc_login';
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScopes()
    {
        return $this->scopes;
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator()
    {
        return ',';
    }


    /**
     * Builds the authorization URL's query string.
     *
     * @param  array $params Query parameters
     * @return string Query string
     */
    protected function getAuthorizationQuery(array $params)
    {
        return http_build_query($params, null, '&', \PHP_QUERY_RFC3986);
    }

    /**
     * Appends a query string to a URL.
     *
     * @param  string $url The URL to append the query to
     * @param  string $query The HTTP query string
     * @return string The resulting URL
     */
    protected function appendQuery($url, $query)
    {
        $query = trim($query, '?&');

        if ($query) {
            $glue = strstr($url, '?') === false ? '?' : '&';
            return $url . $glue . $query;
        }

        return $url;
    }

    /**
     * Checks that a provided grant is valid, or attempts to produce one if the
     * provided grant is a string.
     *
     * @param  AbstractGrant|string $grant
     * @return AbstractGrant
     */
    protected function verifyGrant($grant)
    {
        if (is_string($grant)) {
            return $this->grantFactory->getGrant($grant);
        }

        $this->grantFactory->checkGrant($grant);
        return $grant;
    }

    /**
     * Prepares an parsed access token response for a grant.
     *
     * Custom mapping of expiration, etc should be done here. Always call the
     * parent method when overloading this method.
     *
     * @param  mixed $result
     * @return array
     */
    protected function prepareAccessTokenResponse(array $result)
    {
        if ($this->getAccessTokenResourceOwnerId() !== null) {
            $result['resource_owner_id'] = $this->getValueByKey(
                $result,
                $this->getAccessTokenResourceOwnerId()
            );
        }
        return $result;
    }

    /**
     * Returns the key used in the access token response to identify the resource owner.
     *
     * @return string|null Resource owner identifier key
     */
    protected function getAccessTokenResourceOwnerId()
    {
        return static::ACCESS_TOKEN_RESOURCE_OWNER_ID;
    }

    /**
     * Returns a value by key using dot notation.
     *
     * @param  array      $data
     * @param  string     $key
     * @param  mixed|null $default
     * @return mixed
     */
    private function getValueByKey(array $data, $key, $default = null)
    {
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (!is_array($data) || !array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}