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
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;


/**
 * Controller providing CRUD API for the Orders JSON
 *
 * Receive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class OrdersController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        // init the data access service
        $this->_dataService = Manager::getService('Orders');
    }

    public function exportAction()
    {
        $params = $this->params()->fromQuery();
        $filters = Filter::factory();
        if (!empty($params['startDate'])) {
            $filters->addFilter(
                Filter::factory('OperatorTovalue')->setName('createTime')
                    ->setOperator('$gte')
                    ->setValue((int)$params['startDate'])
            );
        }
        if (!empty($params['endDate'])) {
            $filters->addFilter(
                Filter::factory('OperatorTovalue')->setName('createTime')
                    ->setOperator('$lte')
                    ->setValue((int)$params['endDate'])
            );
        }
        $orders = $this->_dataService->getList($filters);
        $fileName = 'export_rubedo_orders_' . time() . '.csv';
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        $csvResource = fopen($filePath, 'w+');

        $csvHeader = array(
            'OrderNumber', 'PaymentMean', 'Status', 'PriceInclTax', 'PriceExclTax',
            'BillingAddress1', 'BillingAddress2', 'BillingCity', 'BillingPostalCode', 'BillingCountry', 'BillingRegionState',
            'ShippingAddress1', 'ShippingAddress', 'ShippingCity', 'ShippingPostalCode', 'ShippingCountry', 'ShippingRegionState',
            'ProductName', 'ProductSubtitle', 'ProductAmount', 'UnitPriceInclTax', 'UnitPriceExclTax', 'ProductPriceInclTax', 'ProductPriceExclTax'
        );

        /** @var \Rubedo\Internationalization\Translate $translationService */
        $translationService = Manager::getService('Translate');
        $workingLanguage = $this->params()->fromQuery('workingLanguage', 'en');
        foreach ($csvHeader as &$head) {
            $newHead = 'Backoffice.Exports.Orders.' . $head;
            $translatedHead = $translationService->getTranslation($newHead, $workingLanguage);
            if ($translatedHead){
                $head=$translatedHead;
            }
        }

        fputcsv($csvResource, $csvHeader, ';');
        foreach ($orders['data'] as $order) {
            $billingA = &$order['billingAddress'];
            $shippingA = &$order['shippingAddress'];
            $product = array_shift($order['detailedCart']['cart']);
            $firstLine = array(
                $order['orderNumber'], $order['paymentMeans'], $order['status'], $order['finalPrice'], $order['finalTFPrice'],
                //Billing address
                isset($billingA['address1']) ? $billingA['address1'] : '',
                isset($billingA['address2']) ? $billingA['address2'] : '',
                isset($billingA['city']) ? $billingA['city'] : '',
                isset($billingA['postCode']) ? $billingA['postCode'] : '',
                isset($billingA['country']) ? $billingA['country'] : '',
                isset($billingA['regionState']) ? $billingA['regionState'] : '',
                //Shipping address
                isset($shippingA['address1']) ? $shippingA['address1'] : '',
                isset($shippingA['address2']) ? $shippingA['address2'] : '',
                isset($shippingA['city']) ? $shippingA['city'] : '',
                isset($shippingA['postCode']) ? $shippingA['postCode'] : '',
                isset($shippingA['country']) ? $shippingA['country'] : '',
                isset($shippingA['regionState']) ? $shippingA['regionState'] : '',
                $product['title'], $product['subtitle'], $product['amount'], $product['unitTaxedPrice'], $product['unitPrice'], $product['taxedPrice'], $product['price'],
            );
            fputcsv($csvResource, $firstLine, ';');
            foreach ($order['detailedCart']['cart'] as $product) {
                $line = array(
                    $order['orderNumber'], '', '', '', '',
                    '', '', '', '', '', '',
                    '', '', '', '', '', '',
                    $product['title'], $product['subtitle'], $product['amount'], $product['unitTaxedPrice'], $product['unitPrice'], $product['taxedPrice'], $product['price'],
                );
                fputcsv($csvResource, $line, ';');
            }
        }

        $content = file_get_contents($filePath);
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$fileName\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);
        return $response;
    }

}