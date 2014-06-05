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
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class ShoppingCartController extends AbstractController
{

    public function indexAction ()
    {
        $output = $this->params()->fromQuery();
        $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
        $processedCart=$this->addCartInfos($myCart);
        $output["cartItems"]=$processedCart['cart'];
        $output["totalAmount"]=$processedCart['totalAmount'];
        $output["totalItems"]=$processedCart['totalItems'];
        $output['cartDetailPage'] = isset($output['block-config']['cartDetailPage']) ? $output['block-config']['cartDetailPage'] : false;
        $output['checkoutPage'] = isset($output['block-config']['checkoutPage']) ? $output['block-config']['checkoutPage'] : false;
        if ($output["cartDetailPage"]) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $output['cartDetailPageUrl'] = $this->url()->fromRoute(null, array(
                'pageId' => $output["cartDetailPage"]
            ), $urlOptions);
        }
        if ($output["checkoutPage"]) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $output['checkoutPageUrl'] = $this->url()->fromRoute(null, array(
                'pageId' => $output["checkoutPage"]
            ), $urlOptions);
        }
        $output['displayMode'] = isset($output['block-config']['displayMode']) ? $output['block-config']['displayMode'] : "button";
        if ($output['displayMode']=="detail"){
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/shoppingCartDetail.html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/shoppingCart.html.twig");
        }
        $css = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("css/shoppingcart.css")
        );
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/shoppingcart.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }

    public function addItemToCartAction () {
        if ($this->getRequest()->isXmlHttpRequest()){
            $this->init();
        }
        $params = $this->params()->fromPost();
        $cartUpdate = Manager::getService("ShoppingCart")->addItemToCart($params["productId"], $params["variationId"], $params["amount"]);
        if ($cartUpdate === false){
            $result = array("success" => false);
            return new JsonModel($result);
        }
        $templateService = Manager::getService('FrontOfficeTemplates');
        $template = $templateService->getFileThemePath("blocks/shoppingCart/productList.html.twig");
        $output = array();
        $processedCart = $this->addCartInfos($cartUpdate);
        $output["cartItems"] = $processedCart['cart'];
        $output["totalAmount"] = $processedCart['totalAmount'];
        $output["totalItems"] = $processedCart['totalItems'];
        $results = array();
        $results['html'] = $templateService->render($template, $output);
        $results['totalItems'] = $output["totalItems"];
        $results['totalAmount'] = $output['totalAmount'];
        $results['success'] = true;
        return new JsonModel($results);

    }

    public function removeItemFromCartAction () {
        if ($this->getRequest()->isXmlHttpRequest()){
            $this->init();
        }
        $params=$this->params()->fromPost();
        $cartUpdate = Manager::getService("ShoppingCart")->removeItemFromCart($params["productId"], $params["variationId"], $params["amount"]);
        if ($cartUpdate===false){
            $result=array("success"=>false);
            return new JsonModel($result);
        }
        $templateService = Manager::getService('FrontOfficeTemplates');
        $template = $templateService->getFileThemePath("blocks/shoppingCart/productList.html.twig");
        $output = array();
        $processedCart=$this->addCartInfos($cartUpdate);
        $output["cartItems"]=$processedCart['cart'];
        $output["totalAmount"]=$processedCart['totalAmount'];
        $output["totalItems"]=$processedCart['totalItems'];
        $results=array();
        $results['html']=$templateService->render($template, $output);
        $results['totalItems']=$output["totalItems"];
        $results['totalAmount'] = $output['totalAmount'];
        $results['success'] = true;
        return new JsonModel($results);

    }

    protected function addCartInfos ($cart) {
        $totalPrice=0;
        $totalItems=0;
        $ignoredArray=array("price","amount","id","sku","stock");
        $contentsService=Manager::getService("Contents");
        foreach ($cart as &$value){
            $myContent=$contentsService->findById($value["productId"], true, false);
            if ($myContent){
                $value['title']=$myContent['text'];
                $value['product'] = &$myContent;
                $value['subtitle']="";
                $price=0;
                foreach ($myContent["productProperties"]['variations'] as $variation){
                    if ($variation['id']==$value['variationId']){
                        $price=$variation["price"]*$value["amount"];
                        $totalPrice=$totalPrice+$price;
                        $totalItems=$totalItems+$value["amount"];
                        foreach ($variation as $varkey => $varvalue){
                            if (!in_array($varkey,$ignoredArray)){
                                $value['subtitle']=$value['subtitle']." ".$varvalue;
                            }
                        }
                    }
                }
                $value['price']=$price;
            }
        }
        return (array(
            "cart"=>$cart,
            "totalAmount"=>$totalPrice,
            "totalItems"=>$totalItems
        ));
    }
}
