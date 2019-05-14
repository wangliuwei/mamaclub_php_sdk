<?php
namespace Mamaclub;

abstract class AbstractMamaclub{
    public $clientId;

    public $clientSecret;

    public $redirectUri;

    public $scopes;

    public $state;

    public function __construct(){}

    abstract function getBaseAuthorizationUrl();

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
}