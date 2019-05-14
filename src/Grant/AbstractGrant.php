<?php
namespace Mamaclub\Grant;

abstract class AbstractGrant
{

    /**
     * Returns the name of this grant, eg. 'grant_name', which is used as the
     * grant type when encoding URL query parameters.
     *
     * @return string
     */
    abstract protected function getName();

    /**
     * Returns a list of all required request parameters.
     *
     * @return array
     */
    abstract protected function getRequiredRequestParameters();

    /**
     * Returns this grant's name as its string representation. This allows for
     * string interpolation when building URL query parameters.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Prepares an access token request's parameters by checking that all
     * required parameters are set, then merging with any given defaults.
     *
     * @param  array $defaults
     * @param  array $options
     * @return array
     */
    public function prepareRequestParameters(array $defaults, array $options)
    {
        $defaults['grant_type'] = $this->getName();

        $required = $this->getRequiredRequestParameters();
        $provided = array_merge($defaults, $options);

        $this->checkRequiredParameters($required, $provided);

        return $provided;
    }

    /**
     * Checks for a required parameter in a hash.
     *
     * @throws \BadMethodCallException
     * @param  string $name
     * @param  array  $params
     * @return void
     */
    private function checkRequiredParameter($name, array $params)
    {
        if (!isset($params[$name])) {
            throw new \BadMethodCallException(sprintf(
                'Required parameter not passed: "%s"',
                $name
            ));
        }
    }

    /**
     * Checks for multiple required parameters in a hash.
     *
     * @throws \InvalidArgumentException
     * @param  array $names
     * @param  array $params
     * @return void
     */
    private function checkRequiredParameters(array $names, array $params)
    {
        foreach ($names as $name) {
            $this->checkRequiredParameter($name, $params);
        }
    }
}
