<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IStock;

/**
 * Special service to handle stock through aggregation or direct access (no workflow) to contents
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class Stock extends AbstractCollection implements IStock
{
    public function __construct()
    {
        $this->_collectionName = 'Contents';
        parent::__construct();
    }

    /**
     * @see \Rubedo\Interfaces\Collection\IStock::getStock
     */
    public function getStock($typeId, $workingLanguage)
    {
        $pipeline = array();
        $pipeline[] = array(
            '$match' => array(
                'typeId' => $typeId
            )
        );
        $pipeline[] = array(
            '$project' => array(
                'productId' => '$_id',
                '_id' => 0,
                'variation' => '$productProperties.variations',
                'outOfStockLimit' => '$productProperties.outOfStockLimit',
                'notifyForQuantityBelow' => '$productProperties.notifyForQuantityBelow',
                'title' => '$live.i18n.' . $workingLanguage . '.fields.text'
            )
        );
        $pipeline[] = array(
            '$unwind' => '$variation'
        );
        $response = $this->_dataService->aggregate($pipeline);
        if ($response['ok']) {
            foreach ($response['result'] as &$value) {
                $value['productId'] = (string)$value['productId'];
                $value = array_merge($value, $value['variation']);
                unset ($value['variation']);
            }
            return array(
                "data" => $response['result'],
                "total" => count($response['result']),
                "success" => true
            );
        } else {
            return array(
                "msg" => $response['errmsg'],
                "success" => false
            );
        }

    }

    /**
     * @see \Rubedo\Interfaces\Collection\IStock::increaseStock
     */
    public function increaseStock($productId, $variationId, $amount)
    {
        $product = $this->findById($productId);
        if (!$product) {
            return array(
                "msg" => "Product not found",
                "success" => false
            );
        }
        $newStock = 0;
        $notFound = true;
        foreach ($product['productProperties']['variations'] as $key => $variation) {
            if ($variation['id'] == $variationId) {
                $product['productProperties']['variations'][$key]['stock'] = $variation['stock'] + $amount;
                $newStock = $variation['stock'] + $amount;
                $notFound = false;
                break;
            }
        }
        if ($notFound) {
            return array(
                "msg" => "Variation not found",
                "success" => false
            );
        }
        $updatedProduct = $this->update($product);
        if (!$updatedProduct['success']) {
            return array(
                "msg" => "Error updating product",
                "success" => false
            );
        }
        return array(
            "newStock" => $newStock,
            "success" => true
        );

    }

    /**
     * @see \Rubedo\Interfaces\Collection\IStock::decreaseStock
     */
    public function decreaseStock($productId, $variationId, $amount)
    {
        $product = $this->findById($productId);
        if (!$product) {
            return array(
                "msg" => "Product not found",
                "success" => false
            );
        }
        $newStock = 0;
        $notFound = true;
        foreach ($product['productProperties']['variations'] as $key => $variation) {
            if ($variation['id'] == $variationId) {
                if ($variation['stock'] < $amount) {
                    return array(
                        "msg" => "Insufficient stock",
                        "success" => false
                    );
                }
                $product['productProperties']['variations'][$key]['stock'] = $variation['stock'] - $amount;
                $newStock = $variation['stock'] - $amount;
                $notFound = false;
                break;
            }
        }
        if ($notFound) {
            return array(
                "msg" => "Variation not found",
                "success" => false
            );
        }
        $updatedProduct = $this->update($product);
        if (!$updatedProduct['success']) {
            return array(
                "msg" => "Error updating product",
                "success" => false
            );
        }
        return array(
            "newStock" => $newStock,
            "success" => true
        );
    }
}
