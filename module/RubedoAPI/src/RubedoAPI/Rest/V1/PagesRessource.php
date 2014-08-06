<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;

class PagesRessource extends AbstractRessource {
    public function get(&$params) {
        $sitesServices = Manager::getService('Sites');
        $pagesServices = Manager::getService('Pages');
        $site = $sitesServices->findByHost($params['site']);
        $page = $pagesServices->findByNameAndSite($params['route'], $site['id']);
        return [
            'success' => true,
            'site' => $this->outputSiteMask($site),
            'page' => $this->outputPageMask($page),
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