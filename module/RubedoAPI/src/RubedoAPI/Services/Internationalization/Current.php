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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Services\Internationalization;

use Rubedo\Collection\AbstractLocalizableCollection;
use RubedoAPI\Entities\API\Language;
use RubedoAPI\Exceptions\APIServiceException;

/**
 * Class Current
 * @package RubedoAPI\Services\Internationalization
 */
class Current extends \Rubedo\Internationalization\Current
{
    /**
     * Inject localization in low level class
     *
     * @param $params
     * @throws \RubedoAPI\Exceptions\APIServiceException
     */
    public function injectLocalization($params)
    {
        if (!isset($params['lang'])) {
            return;
        }
        $lang = $params['lang'];

        if (!($lang instanceof Language))
            throw new APIServiceException('"lang" must be a Language entity', 400);

        if ($lang->hasFallback()) {
            AbstractLocalizableCollection::setLocalizationStrategy('fallback');
            AbstractLocalizableCollection::setFallbackLocale($lang->getFallback());
        }

        AbstractLocalizableCollection::setWorkingLocale($lang->getLocale());
        AbstractLocalizableCollection::setIncludeI18n(false);
    }
}