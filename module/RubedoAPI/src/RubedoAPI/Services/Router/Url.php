<?php

namespace RubedoAPI\Services\Router;

use Rubedo\Services\Manager;

class Url extends \Rubedo\Router\Url {

    public function displayUrlApi($content, $type = "default", $site , $page , $locale, $defaultPage = null)
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
                        if (! isset($mask['mainColumnId']) || empty($mask['mainColumnId'])) {
                            $pageId = $this->_getDefaultSingleBySiteID($site['id']);
                        }
                    }
                }
            } elseif ($type == "canonical") {
                $pageId = $this->_getDefaultSingleBySiteID($site['id']);
            } else {
                throw new Server("You must specify a good type of URL : default or canonical", "Exception94");
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
                throw new Server("You must specify a good type of URL : default or canonical", "Exception94");
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