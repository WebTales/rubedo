<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Internationalization;

use Rubedo\Services\Manager;
use Rubedo\Interfaces\Internationalization\ICurrent;
use Rubedo\Collection\AbstractLocalizableCollection;

/**
 * Determine current localization
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Current implements ICurrent
{
    public function resolveLocalization($siteId=null,$browserArray = array()){
        $locale = 'fr';
        AbstractLocalizableCollection::setWorkingLocale($locale);
        return $locale;
    }
    
    public function getCurrentLocalization(){
        return AbstractLocalizableCollection::getWorkingLocale();
    }
    
}