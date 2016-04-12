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

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;
use RubedoAPI\Entities\API\Language;
use RubedoAPI\Exceptions\APIEntityException;

/**
 * Class MenuResource
 * @package RubedoAPI\Rest\V1
 */
class SitestructureResource extends AbstractResource
{

    /**
     * Cache lifetime for api cache (only for get and getEntity)
     * @var int
     */
    public $cacheLifeTime=300;

    private $urlOptions=array(
        'encode' => true,
        'reset' => true
    );

    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Site structure')
            ->setDescription('Deal site structure')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a complete list of site pages for FO routing and menus')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('site')
                            ->setRequired()
                            ->setDescription('Host defined in Rubedo backoffice.')
                            ->setFilter('url')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('pages')
                            ->setDescription('The list of pages')
                    );
            });
    }


    public function getAction($params)
    {
        $site = $this->getSitesCollection()->findByHost($params['site']);
        if (empty($site)) {
            throw new APIEntityException('Site not found', 404);
        }
        if (isset($site['locStrategy']) && $site['locStrategy'] == 'fallback') {
            $params['lang'] = new Language(implode('|', array($params['lang']->getLocale(), $site['defaultLanguage'])));
            $this->getCurrentLocalizationAPIService()->refreshLocalization($params['lang']);
        }
        if(!$params['lang']){
            throw new APIEntityException('Unable to determine language', 404);
        }
        $filter=Filter::factory();
        $filter->addFilter(Filter::factory("Value")->setName("site")->setValue($site["id"]));
        $pages=Manager::getService("Pages")->getList($filter);
        foreach ($pages["data"] as &$page){
            $page["url"]=$this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $page['id'],
                'locale' => $params['lang']->getLocale()
            ), $this->urlOptions);
            if (isset($page['maskId'])) {
                $mask = Manager::getService('Masks')->findById($page['maskId']);
                if (isset($mask['mainColumnId']) && !empty($mask['mainColumnId'])) {
                    $page["hasMainColumn"]=true;
                }
            }
            $page=array_intersect_key($page, array_flip(array('title','parentId','hasMainColumn','description','excludeFromMenu', 'id', 'text','pages','url','eCTitle','eCDescription','eCImage','richTextId','includedRichText','taxonomy','orderValue')));
        }

        return [
            'success' => true,
            'pages' => $pages["data"],
        ];
    }

}