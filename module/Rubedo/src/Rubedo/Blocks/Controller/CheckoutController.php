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
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class CheckoutController extends AbstractController
{
    public function indexAction()
    {
        $postConfirmer = $this->params()->fromPost('isConfirmOrderPost');
        if (($this->getRequest()->isPost()) && (isset($postConfirmer))) {
            $params = $this->params()->fromPost();
            if ((!isset($params['shippingMethod'])) || (!isset($params['paymentMeans']))) {
                throw new \Rubedo\Exceptions\User('Missing required parameters');
            }
            $config = Manager::getService('config');
            $pmConfig = $config['paymentMeans'];
            if (!isset($pmConfig[$params['paymentMeans']])) {
                throw new \Rubedo\Exceptions\User('Unknown payment method');
            }
            $myPaymentMeans = $pmConfig[$params['paymentMeans']];
            $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
            if (empty($myCart)) {
                throw new \Rubedo\Exceptions\User('Shopping cart is empty');
            }
            $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
            if (!$currentUser) {
                throw new \Rubedo\Exceptions\User('No authenticated user');
            }
            if ((!isset($currentUser['shippingAddress'])) || (!isset($currentUser['shippingAddress']['country']))) {
                throw new \Rubedo\Exceptions\User('Missing shipping address country');
            }
            $order = array();
            $items = 0;
            foreach ($myCart as $value) {
                $items = $items + $value['amount'];
            }
            $myShippers = Manager::getService("Shippers")->getApplicableShippers($currentUser['shippingAddress']['country'], $items);
            $shippingPrice = 0;
            $shippingTaxedPrice = 0;
            $shippingTax = 0;
            $shipperNotFound = true;
            $usedShipper = array();
            foreach ($myShippers['data'] as $shipper) {
                if ($shipper['shipperId'] == $params['shippingMethod']) {
                    $shippingPrice = $shipper['rate'];
                    $shippingTax = $shippingPrice * ($shipper['tax'] / 100);
                    $shippingTaxedPrice = $shippingPrice + $shippingTax;
                    $shipperNotFound = false;
                    $usedShipper = $shipper;
                }
            }
            if ($shipperNotFound) {
                if ((!isset($currentUser['shippingAddress'])) || (!isset($currentUser['shippingAddress']['country']))) {
                    throw new \Rubedo\Exceptions\User('Shipper not found');
                }
            }
            $order['detailedCart'] = $this->addCartInfos($myCart, $currentUser['typeId'], $currentUser['shippingAddress']['country'], $currentUser['shippingAddress']['regionState'], $currentUser['shippingAddress']['postCode']);
            $order['shippingPrice'] = $shippingPrice;
            $order['shippingTaxedPrice'] = $shippingTaxedPrice;
            $order['shippingTax'] = $shippingTax;
            $order['finalTFPrice'] = $order['detailedCart']['totalPrice'] + $order['shippingPrice'];
            $order['finalTaxes'] = $order['detailedCart']['totalTaxedPrice'] - $order['detailedCart']['totalPrice'] + $order['shippingTax'];
            $order['finalPrice'] = $order['detailedCart']['totalTaxedPrice'] + $order['shippingTaxedPrice'];
            $order['shipper'] = $usedShipper;
            $order['userId'] = $currentUser['id'];
            $order['userName'] = $currentUser['name'];
            $order['billingAddress'] = $currentUser['billingAddress'];
            $order['shippingAddress'] = $currentUser['shippingAddress'];
            $order['hasStockDecrementIssues'] = false;
            $order['stockDecrementIssues'] = array();
            $order['paymentMeans'] = $params['paymentMeans'];
            $order['status'] = "pendingPayment";

            $registeredOrder = Manager::getService("Orders")->createOrder($order);
            if (!$registeredOrder['success']) {
                throw new \Rubedo\Exceptions\Server('Order creation failed');
            }
            Manager::getService("ShoppingCart")->setCurrentCart(array());
            $postParams = $this->getRequest()->getPost();
            $postParams->set("order-id", $registeredOrder['data']['id']);
            return $this->forward()->dispatch($myPaymentMeans['controller'], array(
                'action' => 'index'
            ));
        }
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
        if (empty($myCart)) {
            $output['errorText'] = "Your cart is empty.";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/genericError.html.twig");
            return $this->_sendResponse($output, $template);
        }
        if ((isset($blockConfig["signupContentId"])) && (!empty($blockConfig["signupContentId"]))) {
            $introContent = Manager::getService('Contents')->findById($blockConfig["signupContentId"], true, false);
            if ($introContent) {
                $output['introContentId'] = $blockConfig["signupContentId"];
                $output['introContentText'] = $introContent["fields"]["body"];
            }
        }
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            $output['currentStep'] = 1;
            if (!isset($blockConfig['userType'])) {
                return $this->_sendResponse(array(), "block.html.twig");
            }
            $output['userTypeId'] = $blockConfig['userType'];
        } else {
            $output['currentStep'] = 2;
            $output['currentUser'] = $currentUser;
            $output['userTypeId'] = $currentUser['typeId'];
        }
        $output["tCPage"] = isset($blockConfig["tCPage"]) ? $blockConfig["tCPage"] : false;
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

        $mailingListArray = array();
        if ((!isset($blockConfig['mailingListId'])) || (!is_array($blockConfig['mailingListId']))) {
            $mailingListArray = false;
        }
        if ($mailingListArray !== false) {
            $mailingListService = Manager::getService("MailingList");
            foreach ($blockConfig['mailingListId'] as $value) {
                $myList = $mailingListService->findById($value);
                if ($myList) {
                    $mailingListArray[] = array(
                        "label" => $myList['name'],
                        "value" => $value
                    );
                }
            }
        }
        $output['mailingListArray'] = $mailingListArray;
        $output['countries'] = Manager::getService("Countries")->getList();
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
        $params = $this->params()->fromPost('data', '[ ]');
        $params = Json::decode($params, Json::TYPE_ARRAY);
        if ((!isset($params['name'])) || (!isset($params['email'])) || (!isset($params['userTypeId']))) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Missing params"
            ));
        }

        if ((!isset($params['password'])) || (!isset($params['confirmPassword']))) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Missing password"
            ));
        }

        if ($params['password'] != $params['confirmPassword']) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Passwords do not match"
            ));
        }

        $alreadyExistingUser = Manager::getService("Users")->findByEmail($params['email']);
        if ($alreadyExistingUser) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Email already used"
            ));
        }

        $userType = Manager::getService('UserTypes')->findById($params['userTypeId']);
        if ($userType['signUpType'] == "none") {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Unknown user type"
            ));
        }

        $useSameAddressBilling = isset($params['useSameAddressBilling']);
        $useSameAddressDelivery = isset($params['useSameAddressDelivery']);
        unset($params['useSameAddressBilling']);
        unset($params['useSameAddressDelivery']);
        unset($params['readTermsAndConds']);
        $mailingListsToSubscribe = array();
        $userAddress = array();
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
        foreach ($params as $key => $value) {
            if (strpos($key, "chkmlSubscribe_") !== FALSE) {
                $mailingListsToSubscribe[] = str_replace("chkmlSubscribe_", "", $key);
                unset($params[$key]);
            }
        }
        foreach ($params as $key => $value) {
            if (strpos($key, "address_") !== FALSE) {
                $userAddress[str_replace("address_", "", $key)] = $value;
                unset($params[$key]);
            }
        }
        $newUser['address'] = $userAddress;
        $newUser['billingAddress'] = array();
        $newUser['shippingAddress'] = array();
        if ($useSameAddressBilling) {
            $newUser['billingAddress'] = $userAddress;
        }
        if ($useSameAddressDelivery) {
            $newUser['shippingAddress'] = $userAddress;
        }
        $newUser['fields'] = $params;
        $newUser['status'] = "approved";
        $createdUser = Manager::getService('Users')->create($newUser);
        if ($createdUser['success']) {
            Manager::getService('Users')->changePassword($newPassword, $createdUser['data']['version'], $createdUser['data']['id']);
            if ($mailingListsToSubscribe) {
                $mailingListService = Manager::getService("MailingList");
                foreach ($mailingListsToSubscribe as $mailingListId) {
                    $mailingListService->subscribe($mailingListId, $newUser['email'], false);
                }
            }
            try {
                /** @var \Rubedo\Interfaces\User\IAuthentication $authService */
                $authService = Manager::getService('Authentication');
                $authService->authenticate($newUser['login'], $newPassword);
            } catch (\Exception $e) {
                return new JsonModel(array(
                    "success" => false,
                    "msg" => "Account created but login failed",
                ));
            }
            return new JsonModel(array(
                "success" => true,
                "msg" => "Account created",
            ));
        } else {
            return new JsonModel(array(
                "success" => false,
                "msg" => "User creation failed",
            ));
        }
    }

    public function xhrUpdateBillingAction()
    {
        $params = $this->params()->fromPost('data', '[ ]');
        $data = Json::decode($params, Json::TYPE_ARRAY);
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Unable to get current user"
            ));
        }
        $currentUser['billingAddress'] = $data;
        $result = Manager::getService("Users")->update($currentUser);
        return new JsonModel($result);

    }

    public function xhrUpdateShippingAction()
    {
        $params = $this->params()->fromPost('data', '[ ]');
        $data = Json::decode($params, Json::TYPE_ARRAY);
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Unable to get current user"
            ));
        }
        $currentUser['shippingAddress'] = $data;
        $result = Manager::getService("Users")->update($currentUser);
        return new JsonModel($result);

    }

    public function xhrUpdateAccountDataAction()
    {
        $params = $this->params()->fromPost('data', '[ ]');
        $data = Json::decode($params, Json::TYPE_ARRAY);
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Unable to get current user"
            ));
        }
        $currentUser['name'] = $data['name'];
        unset ($data['name']);
        $userAddress = array();
        foreach ($data as $key => $value) {
            if (strpos($key, "address_") !== FALSE) {
                $userAddress[str_replace("address_", "", $key)] = $value;
                unset($data[$key]);
            } else {
                $currentUser['fields'][$key] = $value;
            }
        }
        $currentUser['address'] = $userAddress;
        $currentUser['shippingAddress'] = $data;
        $result = Manager::getService("Users")->update($currentUser);
        return new JsonModel($result);

    }

    public function xhrGetShippingOptionsAction()
    {
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        $currentChoice = $this->params()->fromPost("current-choice", "");
        if (!$currentUser) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Unable to get current user"
            ));
        }
        if ((isset($currentUser['shippingAddress'])) && (isset($currentUser['shippingAddress']['country']))) {
            $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
            $items = 0;
            foreach ($myCart as $value) {
                $items = $items + $value['amount'];
            }
            $myShippers = Manager::getService("Shippers")->getApplicableShippers($currentUser['shippingAddress']['country'], $items);
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/checkout/shippingOptions.html.twig");
            $myShippers['currentChoice'] = $currentChoice;
            $html = Manager::getService('FrontOfficeTemplates')->render($template, $myShippers);
            return new JsonModel(array(
                "success" => true,
                "html" => $html
            ));
        } else {
            return new JsonModel(array(
                "success" => true,
                "html" => ""
            ));
        }

    }

    public function xhrGetSummaryAction()
    {
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        $currentChoice = $this->params()->fromPost("current-choice", "");
        if (!$currentUser) {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Unable to get current user"
            ));
        }
        if (isset($currentUser['shippingAddress'])) {
            $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
            $items = 0;
            foreach ($myCart as $value) {
                $items = $items + $value['amount'];
            }
            $myShippers = Manager::getService("Shippers")->getApplicableShippers($currentUser['shippingAddress']['country'], $items);
            $shippingPrice = 0;
            $shippingTaxedPrice = 0;
            $shippingTax = 0;
            foreach ($myShippers['data'] as $shipper) {
                if ($shipper['shipperId'] == $currentChoice) {
                    $shippingPrice = $shipper['rate'];
                    $shippingTax = $shippingPrice * ($shipper['tax'] / 100);
                    $shippingTaxedPrice = $shippingPrice + $shippingTax;
                }
            }
            $twigVars = $this->addCartInfos($myCart, $currentUser['typeId'], $currentUser['shippingAddress']['country'], $currentUser['shippingAddress']['regionState'], $currentUser['shippingAddress']['postCode']);
            $twigVars['shippingPrice'] = $shippingPrice;
            $twigVars['shippingTaxedPrice'] = $shippingTaxedPrice;
            $twigVars['shippingTax'] = $shippingTax;
            $twigVars['billingAddress'] = $currentUser['billingAddress'];
            $twigVars['shippingAddress'] = $currentUser['shippingAddress'];
            $activePaymentMeans = Manager::getService("PaymentConfigs")->getActivePMConfigs();
            $twigVars['paymentMeans'] = array();
            $twigVars['shippingMethod'] = $currentChoice;
            $twigVars['shipper'] = Manager::getService("Shippers")->findById($currentChoice);
            foreach ($activePaymentMeans['data'] as $value) {
                $twigVars['paymentMeans'][] = array(
                    "displayName" => $value['displayName'],
                    "paymentMeans" => $value['paymentMeans'],
                    "logo" => $value['logo']
                );
            }
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/checkout/checkoutSummary.html.twig");
            $html = Manager::getService('FrontOfficeTemplates')->render($template, $twigVars);
            return new JsonModel(array(
                "success" => true,
                "html" => $html
            ));
        } else {
            return new JsonModel(array(
                "success" => true,
                "html" => ""
            ));
        }

    }

    protected function addCartInfos($cart, $userTypeId, $country, $region, $postalCode)
    {
        $totalPrice = 0;
        $totalTaxedPrice = 0;
        $totalItems = 0;
        $ignoredArray = array('price', 'amount', 'id', 'sku', 'stock');
        $contentsService = Manager::getService('Contents');
        $taxService = Manager::getService('Taxes');
        foreach ($cart as &$value) {
            $myContent = $contentsService->findById($value['productId'], true, false);
            if ($myContent) {
                $value['title'] = $myContent['text'];
                $value['subtitle'] = '';
                $unitPrice = 0;
                $taxedPrice = 0;
                $unitTaxedPrice = 0;
                $price = 0;
                foreach ($myContent['productProperties']['variations'] as $variation) {
                    if ($variation['id'] == $value['variationId']) {
                        $unitPrice = $variation['price'];
                        $unitTaxedPrice = $taxService->getTaxValue($myContent['typeId'], $userTypeId, $country, $region, $postalCode, $unitPrice);
                        $price = $unitPrice * $value['amount'];
                        $taxedPrice = $unitTaxedPrice * $value['amount'];
                        $totalTaxedPrice = $totalTaxedPrice + $taxedPrice;
                        $totalPrice = $totalPrice + $price;
                        $totalItems = $totalItems + $value['amount'];
                        foreach ($variation as $varkey => $varvalue) {
                            if (!in_array($varkey, $ignoredArray)) {
                                $value['subtitle'] .= ' ' . $varvalue;
                            }
                        }
                    }
                }
                $value['price'] = $price;
                $value['unitPrice'] = $unitPrice;
                $value['unitTaxedPrice'] = $unitTaxedPrice;
                $value['taxedPrice'] = $taxedPrice;
            }
        }
        return array(
            'cart' => $cart,
            'totalPrice' => $totalPrice,
            'totalTaxedPrice' => $totalTaxedPrice,
            'totalItems' => $totalItems
        );
    }

}
