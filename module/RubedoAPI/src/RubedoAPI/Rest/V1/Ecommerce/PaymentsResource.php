<?php

namespace RubedoAPI\Rest\V1\Ecommerce;

use RubedoAPI\Rest\V1\AbstractResource;

class PaymentsResource extends AbstractResource {
    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getIPN());
    }

    protected function define()
    {
        $this->definition
            ->setName('Payments IPN')
            ->setDescription('Payments IPN');
    }

    protected function getIPN()
    {
        $IPNs = array('paypal');
        return array(
            'IPN' => $IPNs,
        );
    }
}