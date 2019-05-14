<?php
namespace Mamaclub\Grant;

class AuthorizationCode extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'authorization_code';
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredRequestParameters()
    {
        return [
            'code',
        ];
    }
}
