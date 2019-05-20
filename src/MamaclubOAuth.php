<?php
namespace Mamaclub;

use Mamaclub\MamaclubClient;
use UnexpectedValueException;
use Mamaclub\Token\AccessToken;
class MamaclubOAuth extends AbstractMamaclub {
    private $client;

    /**
     * OAuth2 constructor.
     * @param array $options
     */
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
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []){
        $baseAuthorizationUrl = $this->client->getUrlAuthorize();
        $params = $this->getAuthorizationParameters($options);
        $query  = $this->getAuthorizationQuery($params);

        return $this->appendQuery($baseAuthorizationUrl, $query);
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
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return Response
     */
    public function getResourceOwner(AccessToken $token)
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $response;
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
}