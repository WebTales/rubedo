<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;

class PagesRessource extends AbstractRessource {
    public function getAction(&$params) {
        $sitesServices = Manager::getService('Sites');
        $pagesServices = Manager::getService('Pages');
        $site = $sitesServices->findByHost($params['site']);
        $urlSegments = explode('/', trim($params['route'], '/'));
        $lastMatchedNode = ['id' => 'root'];
        foreach ($urlSegments as $value) {
            $matchedNode = $pagesServices->matchSegment($value, $lastMatchedNode['id'], $site['id']);
            if (null === $matchedNode) {
                break;
            } else {
                $lastMatchedNode = $matchedNode;
            }
        }
        return [
            'success' => true,
            'site' => $this->outputSiteMask($site),
            'page' => $this->outputPageMask($lastMatchedNode),
        ];
    }

    protected function outputSiteMask($output)
    {
        $output['host'] = $output['text'];
        $mask = ['id', 'host', 'alias', 'description', 'keywords', 'defaultLanguage', 'languages', 'locale', 'locStrategy', 'homePage', 'author'];
        return array_intersect_key($output, array_flip($mask));
    }

    protected function outputPageMask($output)
    {
        return $output;
    }
}