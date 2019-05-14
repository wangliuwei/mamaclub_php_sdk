<?php
namespace Mamaclub\Grant;

class ClientCredentials extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'client_credentials';
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredRequestParameters()
    {
        return [];
    }
}
