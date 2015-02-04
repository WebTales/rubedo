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
namespace Rubedo\Payment;

use Rubedo\Services\Manager;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class CheckPayment extends AbstractPayment
{
    public function __construct()
    {
        $this->paymentMeans = 'check';
        parent::__construct();
    }


    public function getOrderPaymentData($order)
    {
        $output = array();
        $content=null;
        if ($this->nativePMConfig["contentId"]) {
            $content = Manager::getService('Contents')->findById($this->nativePMConfig["contentId"], true, false);
        }
        if (!$content) {
            throw new \Rubedo\Exceptions\Server("Content not configured");
        }
        $price = $order['finalPrice'];
        $toReplace = array('%23', '###price###', '###orderId###');
        $replacedBy = array('#', number_format($price,2) . ' €', $order['orderNumber']);
        $output['richText'] = str_replace($toReplace, $replacedBy, $content['fields']['body']);
        $output['whatToDo']="displayRichText";
        return $output;
    }
}
