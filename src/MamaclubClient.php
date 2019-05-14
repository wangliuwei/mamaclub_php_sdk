<?php

class MamaclubClient {
    const UrlAuthorize = 'https://api.mamaclub.com/mmc_oauth/authorize.php';

    const urlAccessToken = 'https://api.mamaclub.com/mmc_oauth/token.php';

    const UrlResourceOwnerDetails = 'http://api.mamaclub.com/mmc_oauth/resource.php';

    public static function getBaseAuthorizationUrl(){

        return self::UrlAuthorize;
    }

//    public function getAccessToken($grant, array $options = [])
//    {
//        $grant = $this->verifyGrant($grant);
//
//        $params = [
//            'client_id'     => $this->clientId,
//            'client_secret' => $this->clientSecret,
//            'redirect_uri'  => $this->redirectUri,
//        ];
//
//        $params   = $grant->prepareRequestParameters($params, $options);
//        $request  = $this->getAccessTokenRequest($params);
//        $response = $this->getParsedResponse($request);
//        if (false === is_array($response)) {
//            throw new UnexpectedValueException(
//                'Invalid response received from Authorization Server. Expected JSON.'
//            );
//        }
//        $prepared = $this->prepareAccessTokenResponse($response);
//        $token    = $this->createAccessToken($prepared, $grant);
//
//        return $token;
//    }
}