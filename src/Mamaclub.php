<?php
namespace Mamaclub;

use MamaclubClient;
class OAuth2 extends AbstractMamaclub {

    private $urlAuthorize = 'https://api.mamaclub.com/mmc_oauth/authorize.php';

    private $urlAccessToken = 'https://api.mamaclub.com/mmc_oauth/token.php';

    private $urlResourceOwnerDetails = 'http://api.mamaclub.com/mmc_oauth/resource.php';

    public function __construct(array $options = [])
    {
        $this->assertRequiredOptions($options);

        $possible   = $this->getRequiredOptions();
        $configured = array_intersect_key($options, array_flip($possible));
        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }

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
        $baseAuthorizationUrl = $this->getBaseAuthorizationUrl();

        $query  = $this->getAuthorizationParameters($options);

        return $this->appendQuery($baseAuthorizationUrl, $query);
    }

    public function getBaseAuthorizationUrl(){
        return $this->urlAuthorize;
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
}