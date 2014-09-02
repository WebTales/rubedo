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

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;

/**
 * Class PagesRessource
 * @package RubedoAPI\Rest\V1
 */
class PagesRessource extends AbstractRessource
{
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Pages')
            ->setDescription('Deal with pages')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
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
                            ->setDescription('Route for this page, if not, use homepage')
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
                    );
            });
        $this
            ->entityDefinition
            ->setName('Pages entity')
            ->setDescription('Deal with pages entity')
            ->editVerb('get', function (VerbDefinitionEntity &$entity){
                $entity
                    ->setDescription('Get URL from pageId')

                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Url')
                            ->setKey('url')
                            ->setFilter('string')
                            ->setRequired()
                    );
            });
    }

    /**
     * Get from pages
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function getAction($params)
    {
        $sitesServices = Manager::getService('Sites');
        $pagesServices = Manager::getService('Pages');
        $blocksServices = Manager::getService('Blocks');
        $site = $sitesServices->findByHost($params['site']);
        if ($site == null)
            throw new APIEntityException('Site not found', 404);
        if (empty($params['route'])) {
            $lastMatchedNode = $pagesServices->findById($site['homePage']);
        } else {
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
        }
        if ($lastMatchedNode['id'] == 'root') {
            throw new APIEntityException('Page not found', 404);
        }
        $lastMatchedNode['blocks'] = $blocksServices->getListByPage($lastMatchedNode['id'])['data'];
        return [
            'success' => true,
            'site' => $this->outputSiteMask($site),
            'page' => $this->outputPageMask($lastMatchedNode),
        ];
    }

    public function getEntityAction($id, $params) {
        $urlOptions = array(
            'encode' => true,
            'reset' => true,
        );
        $url = $this->getContext()->url()->fromRoute('rewrite', array(
            'pageId' => $id,
            'locale' => $params['lang']->getLocale(),
        ), $urlOptions);
        return array(
            'success' => true,
            'url' => $url,
        );
    }

    /**
     * Filter site with mask
     *
     * @param $output
     * @return array
     */
    protected function outputSiteMask($output)
    {
        $output['host'] = $output['text'];
        $mask = ['id', 'host', 'alias', 'description', 'keywords', 'defaultLanguage', 'languages', 'locale', 'locStrategy', 'homePage', 'author'];
        return array_intersect_key($output, array_flip($mask));
    }

    /**
     * Filter page with mask
     *
     * @param $output
     * @return mixed
     */
    protected function outputPageMask($output)
    {
        return $output;
    }
}