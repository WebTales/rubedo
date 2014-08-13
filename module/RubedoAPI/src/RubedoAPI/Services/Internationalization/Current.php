<?php

namespace RubedoAPI\Services\Internationalization;

use Rubedo\Collection\AbstractLocalizableCollection;
use RubedoAPI\Entities\API\Language;
use RubedoAPI\Exceptions\APIServiceException;

class Current extends \Rubedo\Internationalization\Current {
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