<?php
namespace Mamaclub;

use Mamaclub\MamaclubClient;
use UnexpectedValueException;
use Mamaclub\Grant\AbstractGrant;
use Mamaclub\Token\AccessToken;
class OAuth2 extends AbstractMamaclub {
    private $client;

    public function __construct(array $options = [])
    {
        $this->assertRequiredOptions($options);

        $possible   = $this->getRequiredOptions();
        $configured = array_intersect_key($options, array_flip($possible));
        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }

        $this->client = new MamaclubClient();

        parent::__construct();
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            'clientId',
            'clientSecret',
            'redirectUri',
        ];
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }


    public function getAuthorizationUrl(array $options = []){
        $baseAuthorizationUrl = $this->client->getUrlAuthorize();
        $params = $this->getAuthorizationParameters($options);
        $query  = $this->getAuthorizationQuery($params);

        return $this->appendQuery($baseAuthorizationUrl, $query);
    }

    /**
     * Returns authorization parameters based on provided options.
     *
     * @param  array $options
     * @return array Authorization parameters
     */
    protected function getAuthorizationParameters(array $options)
    {
        if (empty($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }

        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }

        $options += [
            'response_type'   => 'code',
            'approval_prompt' => 'auto'
        ];

        // Store the state as it may need to be accessed later on.
        $this->state = $options['state'];

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirectUri'])) {
            $options['redirect_uri'] = $this->redirectUri;
        }

        $options['client_id'] = $this->clientId;

        return $options;
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     * @throws UnexpectedValueException
     * @return AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->client->getAccessTokenRequest($params);
        $response = $this->client->getParsedResponse($request);
        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);

        return $token;
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param  array $response
     * @param  AbstractGrant $grant
     * @return AccessToken
     */
    public function createAccessToken(array $response, AbstractGrant $grant)
    {
        return new AccessToken($response);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return Response
     */
    public function getResourceOwner(AccessToken $token)
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $response;
//        return $this->createResourceOwner($response, $token);
    }

    /**
     * Requests resource owner details.
     *
     * @param  AccessToken $token
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $url = $this->client->getUrlResourceOwnerDetails();

        $request = $this->client->getAuthenticatedRequest(MamaclubClient::METHOD_GET, $url, $token);

        $response = $this->client->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        return $response;
    }

    /**
     * Returns authorization headers for the 'bearer' grant.
     *
     * @param  string|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}