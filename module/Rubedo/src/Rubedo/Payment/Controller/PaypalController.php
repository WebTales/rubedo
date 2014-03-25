<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Payment\Controller;
use Zend\Json\Json;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class PaypalController extends AbstractController
{

    public function __construct()
    {
        $this->paymentMeans = 'paypal';
        parent::__construct();
    }


    public function indexAction ()
    {
        $this->initOrder();
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$this->nativePMConfig['endpoint']);
        $payload=array(
            "actionType"=>"PAY",
            "currencyCode"=>"EUR",
            "receiverList"=>array(
                "receiver"=>array(
                    array(
                        "amount"=>$this->getOrderPrice(),
                        "email"=>$this->nativePMConfig['userEmail']
                    )
                )
            ),
            "returnUrl"=>"http://".$_SERVER['HTTP_HOST']."/payment/paypal/success",
            "cancelUrl"=>"http://".$_SERVER['HTTP_HOST']."/payment/paypal/cancel",
            "requestEnvelope"=>array(
                "errorLanguage"=>"en_US",
                "detailLevel"=>"ReturnAll"
            )
        );
        $payload=Json::encode($payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-type: application/json',
                'Content-Length: ' . strlen($payload),
                'X-PAYPAL-SECURITY-USERID: '.$this->nativePMConfig['userID'],
                'X-PAYPAL-SECURITY-PASSWORD: '.$this->nativePMConfig['userPassword'],
                'X-PAYPAL-SECURITY-SIGNATURE: '.$this->nativePMConfig['userSignature'],
                'X-PAYPAL-APPLICATION-ID: '.$this->nativePMConfig['applicationID'],
                'X-PAYPAL-REQUEST-DATA-FORMAT: JSON',
                'X-PAYPAL-RESPONSE-DATA-FORMAT: JSON'
        ));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result=curl_exec($curl);
        curl_close($curl);
        $result=Json::decode($result,Json::TYPE_ARRAY);
        if ($result['responseEnvelope']['ack']!="Success"){
            throw new \Rubedo\Exceptions\Server("Paykey retrieval error");
        }
        $this->currentOrder['paypalPayKey']=$result["payKey"];
        $updatedOrder=$this->ordersService->update($this->currentOrder);
        if (!$updatedOrder['success']){
            throw new \Rubedo\Exceptions\Server("Error updating order");
        }
        $this->redirect()->toUrl($this->nativePMConfig['customerRedirect']."?cmd=_ap-payment&paykey=".$result["payKey"]);
        return FALSE;
    }

    public function successAction ()
    {
        Debug::dump($this->params()->fromQuery());
        Debug::dump($this->params()->fromPost());
        die("test paypal payment success");
    }

    public function cancelAction ()
    {
        Debug::dump($this->params()->fromQuery());
        Debug::dump($this->params()->fromPost());
        die("test paypal payment cancel");
    }

    protected function extractParamsFromString($string)
    {
        $explodedString=explode("&",$string);
        $resultArray=array();
        foreach($explodedString as $value){
            $exploded=explode("=",$value);
            $resultArray[$exploded[0]]=urldecode($exploded[1]);
        }
        return($resultArray);
    }

}
