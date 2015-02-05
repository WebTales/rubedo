<?php

namespace RubedoAPI\Rest\V1\Ecommerce\Payments;

use RubedoAPI\Rest\V1\AbstractResource;
use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;

class PaypalResource extends AbstractResource
{

    /**
     * native config for this payment means
     *
     * @var array
     */
    protected $nativePMConfig;

    public function __construct()
    {
        parent::__construct();
        $pmConfig=Manager::getService("PaymentConfigs")->getConfigForPM("paypal");
        $this->nativePMConfig=$pmConfig['data']['nativePMConfig'];
        $this
            ->definition
            ->setName('Paypal')
            ->setDescription('Deal with Paypal IPN')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Process IPN');
            });
    }

    public function postAction($params)
    {
// STEP 1: read POST data

// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
// Instead, read raw POST data from the input stream.
        Manager::getService("PaypalIPN")->create(array(
            "postData"=>$_POST,
            "source"=>"paypal",
        ));
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }


// STEP 2: POST IPN data back to PayPal to validate

        $ch = curl_init($this->nativePMConfig['customerRedirect']);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
// the directory path of the certificate as shown below:
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if( !($res = curl_exec($ch)) ) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        curl_close($ch);


// STEP 3: Inspect IPN validation result and act accordingly

        if (strcmp ($res, "VERIFIED") == 0) {
            // The IPN is verified, process it:
            // check whether the payment_status is Completed
            // check that txn_id has not been previously processed
            // check that receiver_email is your Primary PayPal email
            // check that payment_amount/payment_currency are correct
            // process the notification
            Manager::getService("PaypalIPN")->create(array(
                "postData"=>$_POST,
                "source"=>"paypal",
                "verified"=>true
            ));

            // assign posted variables to local variables
            $payment_status = $_POST['payment_status'];
            $payment_amount = $_POST['mc_gross'];
            $payment_currency = $_POST['mc_currency'];
            $receiver_email = $_POST['receiver_email'];
            $orderNumber=$_POST['custom'];

            if ($payment_status!="Completed"){
                return array("success"=>false);
            }
            $filter = Filter::factory()->addFilter(Filter::factory('Value')->setName('orderNumber')->setValue($orderNumber));
            $order=Manager::getService("Orders")->findOne($filter);
            if (!$order){
                return array("success"=>false);
            }
            if($order['status']!='pendingPayment'){
                return array("success"=>false);
            }
            if ((string) number_format($order['finalPrice'],2)!=$payment_amount){
                return array("success"=>false);
            }
            if($payment_currency!="EUR"){
                return array("success"=>false);
            }
            if($receiver_email!=$this->nativePMConfig['userEmail']){
                return array("success"=>false);
            }
            $order['status']="payed";
            $updatedOrder=Manager::getService("Orders")->update($order);
            if (!$updatedOrder['success']){
                return array("success"=>false);
            }
            return array("success"=>true);
        } else if (strcmp ($res, "INVALID") == 0) {
            // IPN invalid
            Manager::getService("PaypalIPN")->create(array(
                "postData"=>$_POST,
                "source"=>"paypal",
                "verified"=>false
            ));
            return array("success"=>false);
        }
    }
}