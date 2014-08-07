<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class PagesRessource extends AbstractRessource {
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Pages')
            ->setDescription('Deal with pages')
            ->editVerb('get', function(VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a page and all blocks')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('site')
                            ->setRequired()
                            ->setDescription('Host defined in Rubedo backoffice.')
                            ->setFilter('url')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('route')
                            ->setRequired()
                            ->setDescription('Route for this page')
                            ->setFilter('url')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('page')
                            ->setDescription('Informations about the page')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('site')
                            ->setDescription('Informations about the host')
                    )
                ;
            })
        ;
    }
    public function getAction($params) {
        $sitesServices = Manager::getService('Sites');
        $pagesServices = Manager::getService('Pages');
        $blocksServices = Manager::getService('Blocks');
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
        $lastMatchedNode['blocks'] = $blocksServices->getListByPage($lastMatchedNode['id'])['data'];
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