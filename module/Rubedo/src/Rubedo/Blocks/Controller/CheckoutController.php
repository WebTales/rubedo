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
namespace Rubedo\Blocks\Controller;

use Rubedo\Services\Manager;
use Zend\Debug\Debug;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class CheckoutController extends AbstractController
{
    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
        if (empty($myCart)) {
            $output['errorText'] = "Your cart is empty.";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/genericError.html.twig");
            return $this->_sendResponse($output, $template);
        }
        if ((isset($blockConfig["signupContentId"]))&&(!empty($blockConfig["signupContentId"]))) {
            $introContent = Manager::getService('Contents')->findById($blockConfig["signupContentId"], true, false);
            if ($introContent){
                $output['introContentId'] = $blockConfig["signupContentId"];
                $output['introContentText'] = $introContent["fields"]["body"];
            }
        }
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser){
            $output['currentStep']=1;
            if (!isset($blockConfig['userType'])) {
                return $this->_sendResponse(array(), "block.html.twig");
            }
            $output['userTypeId'] = $blockConfig['userType'];
        } else {
            $output['currentStep']=2;
            $output['currentUser']=$currentUser;
            $output['userTypeId'] = $currentUser['typeId'];
        }
        $output["tCPage"]=isset($blockConfig["tCPage"]) ? $blockConfig["tCPage"] : false;
        if ($output["tCPage"]) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $output['tCPageUrl'] = $this->url()->fromRoute(null, array(
                'pageId' => $output["tCPage"]
            ), $urlOptions);
        }
        $userType = Manager::getService('UserTypes')->findById($output['userTypeId']);
        $output['fields'] = $userType['fields'];

        $mailingListArray=array();
        if ((! isset($blockConfig['mailingListId']))||(!is_array($blockConfig['mailingListId']))) {
            $mailingListArray=false;
        }
        if ($mailingListArray!==false){
            $mailingListService = Manager::getService("MailingList");
            foreach ($blockConfig['mailingListId'] as $value){
                $myList=$mailingListService->findById($value);
                if ($myList){
                    $mailingListArray[]=array(
                        "label"=>$myList['name'],
                        "value"=>$value
                    );
                }
            }
        }
        $output['mailingListArray']=$mailingListArray;
        $output['countries']=Manager::getService("Countries")->getList();
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/checkout.html.twig");
        $css = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("css/checkout.css")
        );
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/checkout.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }

    public function xhrCreateAccountAction()
    {
        $params = $this->params()->fromPost('data','[ ]');
        $params=Json::decode($params, Json::TYPE_ARRAY);
        if ((!isset($params['name'])) || (!isset($params['email'])) || (!isset($params['userTypeId']))) {
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Missing params"
            ));
        }

            if ((!isset($params['password'])) || (!isset($params['confirmPassword']))) {
                return new JsonModel(array(
                    "success"=>false,
                    "msg"=>"Missing password"
                ));
            }
            if ($params['password'] != $params['confirmPassword']) {
                return new JsonModel(array(
                    "success"=>false,
                    "msg"=>"Passwords do not match"
                ));
            }

        $alreadyExistingUser = Manager::getService("Users")->findByEmail($params['email']);
        if ($alreadyExistingUser) {
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Email already used"
            ));
        }
        $userType = Manager::getService('UserTypes')->findById($params['userTypeId']);
        if ($userType['signUpType'] == "none") {
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Unknown user type"
            ));
        }
        $useSameAddress=isset($params['useSameAddress']) ? true : false;
        unset($params['useSameAddress']);
        unset($params['readTermsAndConds']);
        $mailingListsToSubscribe=array();
        $userAddress=array();
        $newUser = array();
        $newUser['name'] = $params['name'];
        $newUser['email'] = $params['email'];
        $newUser['login'] = $params['email'];
        $newUser['typeId'] = $params['userTypeId'];
        $newUser['defaultGroup'] = $userType['defaultGroup'];
        $newUser['groups'] = array($userType['defaultGroup']);
        $newUser['taxonomy'] = array();
        unset($params['name']);
        unset($params['email']);
        unset($params['userTypeId']);
        $newPassword = $params['password'];
        unset($params['password']);
        unset($params['confirmPassword']);
        foreach ($params as $key => $value){
            if (strpos($key,"chkmlSubscribe_")!==FALSE){
                $mailingListsToSubscribe[]=str_replace("chkmlSubscribe_","",$key);
                unset($params[$key]);
            }
        }
        foreach ($params as $key => $value){
            if (strpos($key,"address_")!==FALSE){
                $userAddress[str_replace("address_","",$key)]=$value;
                unset($params[$key]);
            }
        }
        $newUser['address']= $userAddress;
        $newUser['billingAddress']=array();
        $newUser['shippingAddress']=array();
        if ($useSameAddress){
            $newUser['billingAddress']=$userAddress;
            $newUser['shippingAddress']=$userAddress;
        }
        $newUser['fields'] = $params;
        $newUser['status'] = "approved";
        $createdUser = Manager::getService('Users')->create($newUser);
        if ($createdUser['success']) {
            Manager::getService('Users')->changePassword($newPassword, $createdUser['data']['version'], $createdUser['data']['id']);
        }
        if (($createdUser['success']) && ($mailingListsToSubscribe)) {
            $mailingListService=Manager::getService("MailingList");
            foreach ($mailingListsToSubscribe as $mailingListId){
                $mailingListService->subscribe($mailingListId, $newUser['email'], false);
            }
        }
        if (!$createdUser['success']) {
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"User creation failed"
            ));
        } else {
            return new JsonModel(array(
                "success"=>true,
                "msg"=>"Account created"
            ));
        }
    }

    public function xhrUpdateBillingAction()
    {
        $params = $this->params()->fromPost('data','[ ]');
        $data=Json::decode($params, Json::TYPE_ARRAY);
        $currentUser=Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser){
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Unable to get current user"
            ));
        }
        $currentUser['billingAddress']=$data;
        $result=Manager::getService("Users")->update($currentUser);
        return new JsonModel($result);

    }

    public function xhrUpdateShippingAction()
    {
        $params = $this->params()->fromPost('data','[ ]');
        $data=Json::decode($params, Json::TYPE_ARRAY);
        $currentUser=Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser){
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Unable to get current user"
            ));
        }
        $currentUser['shippingAddress']=$data;
        $result=Manager::getService("Users")->update($currentUser);
        return new JsonModel($result);

    }

    public function xhrUpdateAccountDataAction()
    {
        $params = $this->params()->fromPost('data','[ ]');
        $data=Json::decode($params, Json::TYPE_ARRAY);
        $currentUser=Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser){
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Unable to get current user"
            ));
        }
        $currentUser['name']=$data['name'];
        unset ($data['name']);
        $userAddress=array();
        foreach ($data as $key => $value){
            if (strpos($key,"address_")!==FALSE){
                $userAddress[str_replace("address_","",$key)]=$value;
                unset($data[$key]);
            } else {
                $currentUser['fields'][$key]=$value;
            }
        }
        $currentUser['address']=$userAddress;
        $currentUser['shippingAddress']=$data;
        $result=Manager::getService("Users")->update($currentUser);
        return new JsonModel($result);

    }

    public function xhrGetShippingOptionsAction()
    {
        $currentUser=Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser){
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Unable to get current user"
            ));
        }
        if ((isset($currentUser['shippingAddress']))&&(isset($currentUser['shippingAddress']['country']))){
            $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
            $items=0;
            foreach($myCart as $value){
                $items=$items+$value['amount'];
            }
            $myShippers=Manager::getService("Shippers")->getApplicableShippers($currentUser['shippingAddress']['country'],$items);
            return new JsonModel(array(
                "success"=>true,
                "html"=>$myShippers
            ));
        } else {
            return new JsonModel(array(
                "success"=>true,
                "html"=>""
            ));
        }

    }

}
