<?php

namespace RubedoAPI\Rest\V1\Ecommerce;

use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class PaymentsResource
 * @package RubedoAPI\Rest\V1\Ecommerce
 */
class PaymentsResource extends AbstractResource
{
    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Redefine options action
     *
     * @return array
     */
    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getIPN());
    }

    /**
     * define name and description
     */
    protected function define()
    {
        $this->definition
            ->setName('Payments IPN')
            ->setDescription('Payments IPN');
    }

    /**
     * Return IPN
     *
     * @return array
     */
    protected function getIPN()
    {
        $IPNs = array('paypal');
        return array(
            'IPN' => $IPNs,
        );
    }
}