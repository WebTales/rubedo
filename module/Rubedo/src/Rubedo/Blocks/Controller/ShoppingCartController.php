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
