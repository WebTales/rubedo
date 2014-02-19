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
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        $myCart = Manager::getService("ShoppingCart")->getCurrentCart();
        $processedCart=$this->addCartInfos($myCart);
        $output["cartItems"]=$processedCart['cart'];
        $output["totalAmount"]=$processedCart['totalAmount'];
        $output["totalItems"]=$processedCart['totalItems'];
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/shoppingCart.html.twig");
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
        $templateService = Manager::getService('FrontOfficeTemplates');
        $params=$this->params()->fromPost();
        $cartUpdate = Manager::getService("ShoppingCart")->addItemToCart($params["productId"], $params["variationId"], $params["amount"]);
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
        $results['success'] = true;
        return new JsonModel($results);

    }

    public function removeItemFromCartAction () {
        if ($this->getRequest()->isXmlHttpRequest()){
            $this->init();
        }
        $templateService = Manager::getService('FrontOfficeTemplates');
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
        $results['success'] = true;
        return new JsonModel($results);

    }

    private function addCartInfos ($cart) {
        $totalPrice=0;
        $totalItems=0;
        $contentsService=Manager::getService("Contents");
        foreach ($cart as &$value){
            $myContent=$contentsService->findById($value["productId"], true, false);
            if ($myContent){
                $value['title']=$myContent['text'];
                $price=0;
                foreach ($myContent["productProperties"]['variations'] as $variation){
                    if ($variation['id']==$value['variationId']){
                        $price=$variation["price"]*$value["amount"];
                        $totalPrice=$totalPrice+$price;
                        $totalItems=$totalItems+$value["amount"];
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
