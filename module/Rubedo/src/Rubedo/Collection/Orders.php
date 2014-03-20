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
namespace Rubedo\Collection;
use Rubedo\Services\Manager;

/**
 * Service to handle Orders
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class Orders extends AbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'Orders';
        parent::__construct();
    }

    /**
     * Creates order, performs stock decrement
     *
     * @param $orderData
     * @return array
     */
    public function createOrder ($orderData)
    {
        $createdOrder=$this->create($orderData);
        if (!$createdOrder['success']){
            return $createdOrder;
        }
        $orderData=$createdOrder['data'];
        $contentTypesService=Manager::getService("ContentTypes");
        $contentsService=Manager::getService("Contents");
        $stockService=Manager::getService("Stock");
        foreach ($orderData['detailedCart']['cart'] as $value){
            $content=$contentsService->findById($value['productId'], true, false);
            $productType=$contentTypesService->findById($content['typeId']);
            if ($productType['manageStock']){
                $stockExtraction=$stockService->decreaseStock($value['productId'],$value['variationId'],$value['amount']);
                if (!$stockExtraction['success']){
                    $orderData['hasStockDecrementIssues']=true;
                    $orderData['stockDecrementIssues'][]=$value;
                }
            }
        }
        $updatedOrder=$this->update($orderData);
        return $updatedOrder;
    }

}
