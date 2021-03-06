<?php
namespace Mamaclub;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use Mamaclub\Exception\Exception;
class MamaclubClient {
    private $urlAuthorize = 'https://api.mamaclub.com/mmc_oauth/authorize.php';

    private $urlAccessToken = 'https://api.mamaclub.com/mmc_oauth/token.php';

    private $urlResourceOwnerDetails = 'http://api.mamaclub.com/mmc_oauth/resource.php';

    const METHOD_POST = 'POST';

    const METHOD_GET = 'GET';

    private $http_client;

    /**
     * @var string
     */
    private $responseError = 'error';

    /**
     * @var string
     */
    private $responseCode;


    /**
     * MamaclubClient constructor.
     */
    public function __construct()
    {
        $this->http_client = new HttpClient();
    }

    /**
     * Returns the method to use when requesting an access token.
     *
     * @return string HTTP method
     */
    public static function getAccessTokenMethod()
    {
        return self::METHOD_POST;
    }

    /**
     * @return string
     */
    public function getUrlAuthorize(){
        return $this->urlAuthorize;
    }

    /**
     * @return string
     */
    public function getUrlResourceOwnerDetails(){
        return $this->urlResourceOwnerDetails;
    }

    /**
     * Returns the default headers used by this provider.
     *
     * Typically this is used to set 'Accept' or 'Content-Type' headers.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return [];
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param  mixed|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Returns all headers used by this provider for a request.
     *
     * The request will be authenticated if an access token is provided.
     *
     * @param  mixed|null $token object or string
     * @return array
     */
    public function getHeaders($token = null)
    {
        if ($token) {
            return array_merge(
                $this->getDefaultHeaders(),
                $this->getAuthorizationHeaders($token)
            );
        }

        return $this->getDefaultHeaders();
    }

    /**
     * Returns a prepared request for requesting an access token.
     *
     * @param array $params Query string parameters
     * @return Request
     */
    public function getAccessTokenRequest(array $params)
    {
        $method  = $this->getAccessTokenMethod();
        $url     = $this->getAccessTokenUrl($params);
        $options = $this->getAccessTokenOptions($method, $params);
        return $this->getRequest($method, $url, $options);
    }

    /**
     * Returns the full URL to use when requesting an access token.
     *
     * @param array $params Query parameters
     * @return string
     */
    protected function getAccessTokenUrl(array $params)
    {
        $url = $this->urlAccessToken;

        return $url;
    }

    /**
     * @param $method
     * @param array $params
     * @return array
     */
    public function getAccessTokenOptions($method, array $params)
    {
        $options = ['headers' => ['content-type' => 'application/x-www-form-urlencoded']];

        if ($method === self::METHOD_POST) {
            $options['body'] = http_build_query($params, null, '&', \PHP_QUERY_RFC3986);
        }

        return $options;
    }

    /**
     * Returns a PSR-7 request instance that is not authenticated.
     *
     * @param  string $method
     * @param  string $url
     * @param  array $options
     * @return Request
     */
    public function getRequest($method, $url, array $options = [])
    {
        return $this->createRequest($method, $url, null, $options);
    }

    /**
     * Creates a PSR-7 request instance.
     *
     * @param  string $method
     * @param  string $url
     * @param  string|null $token
     * @param  array $options
     * @return Request
     */
    protected function createRequest($method, $url, $token, array $options)
    {
        $defaults = [
            'headers' => $this->getHeaders($token),
        ];

        if (empty($options['body']))
            $options['body'] = null;

        $options = array_merge_recursive($defaults, $options);

        return new Request($method, $url, $options['headers'], $options['body']);
    }


    /**
     * Returns an authenticated PSR-7 request instance.
     *
     * @param  string $method
     * @param  string $url
     * @param  string $token
     * @param  array $options Any of "headers", "body", and "protocolVersion".
     * @return Request
     */
    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {
        return $this->createRequest($method, $url, $token, $options);
    }

    /**
     * @param Request $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getResponse(Request $request){
        return $this->http_client->send($request);
    }

    /**
     * Sends a request and returns the parsed response.
     *
     * @param  Request $request
     * @throws BadResponseException
     * @return mixed
     */
    public function getParsedResponse(Request $request)
    {
        try {
            $response = $this->getResponse($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        $parsed = $this->parseResponse($response);
        $this->checkResponse($parsed);

        return $parsed;
    }

    /**
     * Parses the response according to its content-type header.
     *
     * @throws \UnexpectedValueException
     * @param  Response $response
     * @return array
     */
    protected function parseResponse(Response $response)
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);

        if (strpos($type, 'urlencoded') !== false) {
            parse_str($content, $parsed);
            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            return $this->parseJson($content);
        } catch (\UnexpectedValueException $e) {
            if (strpos($type, 'json') !== false) {
                throw $e;
            }

            if ($response->getStatusCode() == 500) {
                throw new \UnexpectedValueException(
                    'An OAuth server error was encountered that did not contain a JSON body',
                    0,
                    $e
                );
            }

            return $content;
        }
    }

    /**
     * Returns the content type header of a response.
     *
     * @param  Response $response
     * @return string Semi-colon separated join of content-type headers.
     */
    protected function getContentType(Response $response)
    {
        return join(';', (array) $response->getHeader('content-type'));
    }

    /**
     * Attempts to parse a JSON response.
     *
     * @param  string $content JSON content from response body
     * @return array Parsed JSON data
     * @throws \UnexpectedValueException if the content could not be parsed
     */
    protected function parseJson($content)
    {
        $content = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \UnexpectedValueException(sprintf(
                "Failed to parse JSON response: %s",
                json_last_error_msg()
            ));
        }

        return $content;
    }

    /**
     * @param $data
     * @throws Exception
     */
    protected function checkResponse($data)
    {
        if (!empty($data[$this->responseError])) {
            $error = $data[$this->responseError];
            if (!is_string($error)) {
                $error = var_export($error, true);
            }
            $code  = $this->responseCode && !empty($data[$this->responseCode])? $data[$this->responseCode] : 0;
            if (!is_int($code)) {
                $code = intval($code);
            }
            throw new Exception($error, $code);
        }
    }

}