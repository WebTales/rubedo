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

/**
 * Class MenuResource
 * @package RubedoAPI\Rest\V1
 */
class MenuResource extends AbstractResource
{

    /**
     * Cache lifetime for api cache (only for get and getEntity)
     * @var int
     */
    public $cacheLifeTime=60;

    /**
     * @var array
     */
    private $excludeFromMenuCondition;

    /**
     * @var array
     */
    private $urlOptions;

    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Menu')
            ->setDescription('Deal with menu')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a menu tree from id')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('pageId')
                            ->setRequired()
                            ->setDescription('Id of the root page for the menu')
                            ->setFilter('\\MongoDB\\BSON\\ObjectID')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('menuLocale')
                            ->setRequired()
                            ->setDescription('Locale for the menu')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('menuLevel')
                            ->setDescription('Level limit')
                    )->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('includeRichText')
                            ->setDescription('Include rich text in pages')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('menu')
                            ->setDescription('The recursive menu')
                    );
            });
    }

    /**
     * Get from /menu
     *
     * @param $params
     * @return array
     */
    public function getAction($params)
    {
        $rootPage = $this->getPagesCollection()->findById($params['pageId']);
        $menu = array_intersect_key($rootPage, array_flip(array('title', 'id', 'text')));
        $levelLimit = isset($params["menuLevel"]) ? $params["menuLevel"] : 1;
        $this->excludeFromMenuCondition = Filter::factory('Not')->setName('excludeFromMenu')->setValue(true);
        $this->urlOptions = array(
            'encode' => true,
            'reset' => true
        );

        $menu["pages"] = $this->_getPagesByLevel($rootPage['id'], $levelLimit, 1, $params["menuLocale"],$params);

        $menu['url'] = $this->getContext()->url()->fromRoute('rewrite', array(
            'pageId' => $menu['id'],
            'locale' => $params['menuLocale']
        ), $this->urlOptions);

        return [
            'success' => true,
            'menu' => $menu,
        ];
    }

    /**
     * Get pages by level
     *
     * @param $rootPage
     * @param $targetLevel
     * @param int $currentLevel
     * @param $locale
     * @return array
     */
    protected function _getPagesByLevel($rootPage, $targetLevel, $currentLevel = 1, $locale,$params)
    {
        $pages = $this->getPagesCollection()->readChild($rootPage, $this->excludeFromMenuCondition);

        if ($currentLevel == $targetLevel) {
            foreach ($pages as $key => $page) {
                $pages[$key]["url"] = $this->getContext()->url()->fromRoute('rewrite', array(
                    'pageId' => $page['id'],
                    'locale' => $locale
                ), $this->urlOptions);
                if(isset($params["includeRichText"])&&isset($page["richTextId"])&&$page["richTextId"]&&$page["richTextId"]!=""){
                    $pages[$key]["includedRichText"]=$this->getContentsCollection()->findById($page["richTextId"], true, false);
                }
                $pages[$key]= array_intersect_key($pages[$key], array_flip(array('title','description' ,'id', 'text','pages','url','eCTitle','eCDescription','eCImage','richTextId','includedRichText','taxonomy','orderValue')));

            }

            return $pages;
        }

        foreach ($pages as $key => $page) {
            $pages[$key]["url"] = $this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $page['id'],
                'locale' => $locale
            ), $this->urlOptions);

            $nextLevel = $this->_getPagesByLevel($page['id'], $targetLevel, $currentLevel + 1, $locale,$params);

            if (is_array($nextLevel) && !empty($nextLevel)) {
                $pages[$key]["pages"] = $nextLevel;
            }
            if(isset($params["includeRichText"])&&isset($page["richTextId"])&&$page["richTextId"]&&$page["richTextId"]!=""){
                $pages[$key]["includedRichText"]=$this->getContentsCollection()->findById($page["richTextId"], true, false);
            }
            $pages[$key]= array_intersect_key($pages[$key], array_flip(array('title','description', 'id', 'text','pages','url','eCTitle','eCDescription','eCImage','richTextId','includedRichText','taxonomy','orderValue')));
        }

        return $pages;
    }
}
