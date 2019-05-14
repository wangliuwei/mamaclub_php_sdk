<?php
namespace Mamaclub\Grant;

class RefreshToken extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'refresh_token';
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredRequestParameters()
    {
        return [
            'refresh_token',
        ];
    }
}
