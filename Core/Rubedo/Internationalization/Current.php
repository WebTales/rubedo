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

Use Rubedo\Services\Manager, Rubedo\Interfaces\Internationalization\ICurrent;

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
    public function resolveLocalization($siteId,$browserArray = array()){
        AbstractLocalizableCollection::setWorkingLocale('en');
    }
    
    public function getCurrentLocalization(){
        AbstractLocalizableCollection::getWorkingLocale();
    }
    
}