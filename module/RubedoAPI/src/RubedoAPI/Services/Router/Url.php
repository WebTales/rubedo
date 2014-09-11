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

namespace RubedoAPI\Services\Router;

use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIServiceException;


/**
 * Class Url
 * @package RubedoAPI\Services\Router
 */
class Url extends \Rubedo\Router\Url
{

    /**
     * return URL from API
     *
     * @param $content
     * @param string $type
     * @param $site
     * @param $page
     * @param $locale
     * @param null $defaultPage
     * @return string
     * @throws \RubedoAPI\Exceptions\APIServiceException
     */
    public function displayUrlApi($content, $type = "default", $site, $page, $locale, $defaultPage = null)
    {
        $pageValid = false;

        $doNotAddSite = true;

        if (isset($content['taxonomy']['navigation']) && $content['taxonomy']['navigation'] !== "") {
            foreach ($content['taxonomy']['navigation'] as $pageId) {
                if ($pageId == 'all') {
                    continue;
                }
                $page = Manager::getService('Pages')->findById($pageId);
                if ($page && $page['site'] == $site['id']) {
                    $pageValid = true;
                    break;
                }
            }
        }

        if (!$pageValid) {
            if ($type == "default") {
                if ($defaultPage) {
                    $pageId = $defaultPage;
                } else {
                    $pageId = $page['id'];
                    if (isset($page['maskId'])) {
                        $mask = Manager::getService('Masks')->findById($page['maskId']);
                        if (!isset($mask['mainColumnId']) || empty($mask['mainColumnId'])) {
                            $pageId = $this->_getDefaultSingleBySiteID($site['id']);
                        }
                    }
                }
            } elseif ($type == "canonical") {
                $pageId = $this->_getDefaultSingleBySiteID($site['id']);
            } else {
                throw new APIServiceException("You must specify a good type of URL : default or canonical", 500);
            }
        }

        if ($pageId) {
            $data = array(
                'pageId' => $pageId,
                'content-id' => $content['id'],
                'locale' => $locale
            );

            if ($type == "default") {
                $pageUrl = $this->url($data, 'rewrite', true);
            } elseif ($type == "canonical") {
                // @todo refactor this
                $pageUrl = $this->url($data, null, true);
            } else {
                throw new APIServiceException("You must specify a good type of URL : default or canonical", 500);
            }

            if ($doNotAddSite) {
                return $pageUrl;
            } else {
                return 'http://' . $site['host'] . $pageUrl;
            }
        } else {
            return '#';
        }
    }
}