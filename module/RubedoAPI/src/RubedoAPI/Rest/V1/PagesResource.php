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

use Rubedo\Collection\AbstractCollection;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Entities\API\Language;
use RubedoAPI\Exceptions\APIEntityException;

/**
 * Class PagesResource
 * @package RubedoAPI\Rest\V1
 */
class PagesResource extends AbstractResource
{

    /**
     * Cache lifetime for api cache (only for get and getEntity)
     * @var int
     */
    public $cacheLifeTime=60;
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
                            ->setKey('mask')
                            ->setDescription('Informations about the mask')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('content')
                            ->setDescription('Informations about the content')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('breadcrumb')
                            ->setDescription('Breadcrumb')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('site')
                            ->setDescription('Informations about the host')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Block types in the page')
                            ->setKey('blockTypes')
                            ->setFilter('string')
                            ->setRequired()
                            ->setMultivalued()
                    );
            });
        $this
            ->entityDefinition
            ->setName('Pages entity')
            ->setDescription('Deal with pages entity')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get URL from pageId')
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Url')
                            ->setKey('url')
                            ->setFilter('string')
                            ->setRequired()
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Title for the page')
                            ->setKey('title')
                            ->setRequired()
                    )->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('SEO and e-commerce page data for link display')
                            ->setKey('pageData')
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
        $site = $this->getSitesCollection()->findByHost($params['site']);
        if (empty($site)) {
            throw new APIEntityException('Site not found', 404);
        }
        if (isset($site['locStrategy']) && $site['locStrategy'] == 'fallback') {
            $params['lang'] = new Language(implode('|', array($params['lang']->getLocale(), $site['defaultLanguage'])));
            $this->getCurrentLocalizationAPIService()->refreshLocalization($params['lang']);
        }
        $pages = array();
        $url = '';
        if (empty($params['route'])) {
            $lastMatchedNode = $this->getPagesCollection()->findById($site['homePage']);
        } else {
            $nbMatched = 0;
            $urlSegments = explode('/', trim($params['route'], '/'));
            $lastMatchedNode = ['id' => 'root'];
            foreach ($urlSegments as $key => $value) {
                try {
                    $contentId = new \MongoId($value);
                    $content = $this->getContentsCollection()->findById($contentId, false, false);
                    break;
                } catch (\Exception $e) {
                }
                $matchedNode = $this->getPagesCollection()->matchSegment($value, $lastMatchedNode['id'], $site['id']);
                if (null === $matchedNode) {
                    break;
                } else {
                    if ($key == 0) {
                        $getUrl = $this->getEntityAction($matchedNode['id'], $params);
                        $url = $getUrl['url'];
                    } else {
                        $url = $url . '/' . $value;
                    }
                    $matchedPageTitle=$matchedNode['text'];
                        if (isset($matchedNode['i18n'][$params['lang']->getLocale()]['text'])){
                            $matchedPageTitle=$matchedNode['i18n'][$params['lang']->getLocale()]['text'];
                        } elseif (isset($matchedNode['i18n'][$params['lang']->getFallback()]['text'])){
                            $matchedPageTitle=$matchedNode['i18n'][$params['lang']->getFallback()]['text'];
                        }
                    $pages[] = array('title' => $matchedPageTitle, 'url' => $url);
                    $lastMatchedNode = $matchedNode;
                    $nbMatched++;
                }
            }
            if ($lastMatchedNode['id'] == 'root'||(empty($content)&&($nbMatched<count($urlSegments)))) {
                if (isset($site['defaultNotFound'])&&$site['defaultNotFound']!="") {
                    $matchedNode=$this->getPagesCollection()->findById($site['defaultNotFound']);
                    if (!$matchedNode){
                        throw new APIEntityException('Page not found', 404);
                    }
                    $lastMatchedNode["id"] = $site['defaultNotFound'];
                    $matchedPageTitle=$matchedNode['text'];
                    if (isset($matchedNode['i18n'][$params['lang']->getLocale()]['text'])){
                        $matchedPageTitle=$matchedNode['i18n'][$params['lang']->getLocale()]['text'];
                    } elseif (isset($matchedNode['i18n'][$params['lang']->getFallback()]['text'])){
                        $matchedPageTitle=$matchedNode['i18n'][$params['lang']->getFallback()]['text'];
                    }
                    $lastMatchedNode = $matchedNode;
                    $getUrl = $this->getEntityAction($matchedNode['id'], $params);
                    $url = $getUrl['url'];
                    $pages[] = array('title' => $matchedPageTitle, 'url' => $url);
                } else {
                    throw new APIEntityException('Page not found', 404);
                }
            }
        }

        $lastMatchedNode['blocks'] = $this->getBlocksCollection()->getListByPage($lastMatchedNode['id'])['data'];

        //Translate block titles
        foreach ($lastMatchedNode["blocks"] as $keyBlock => $block) {
            $lastMatchedNode["blocks"][$keyBlock]["blockData"] = $this->localizeTitle($block["blockData"]);
        }

        $languagesWithFlag = array();
        foreach ($site['languages'] as $lang) {
            $localeDetail = $this->getLanguagesCollection()->findByLocale($lang);

            if (isset($localeDetail['ownLabel']) && !empty($localeDetail['ownLabel'])) {
                $label = $localeDetail['ownLabel'];
            } elseif (isset($localeDetail['label']) && !empty($localeDetail['label'])) {
                $label = $localeDetail['label'];
            } else {
                $label = $lang;
            }

            $languagesWithFlag[$lang] = array('lang' => $lang, 'label' => $label, 'flagCode' => (isset($localeDetail['flagCode']) ? $localeDetail['flagCode'] : ''));
        }
        $site['languages'] = $languagesWithFlag;
        $wasFiltered = AbstractCollection::disableUserFilter();
        $mask = $this->getMasksCollection()->findById($lastMatchedNode['maskId']);
        AbstractCollection::disableUserFilter($wasFiltered);

        if (empty($mask)) {
            throw new APIEntityException('Mask not found', 404);
        }

        $blocksFound = array_merge($mask['blocks'], $lastMatchedNode['blocks']);
        $blocks = array();
        $blockTypes = array();
        foreach ($blocksFound as $block) {
            if (isset($block['blockData'])) {
                $block = $block['blockData'];
            }
            if (!isset($block['orderValue'])) {
                throw new APIEntityException(sprintf('Missing orderValue for block %1$s', $block['id']), 404);
            }
            if (!in_array($block['bType'], $blockTypes)) {
                $blockTypes[] = $block['bType'];
            }
            $blocks[$block['parentCol']][] = $block;
        }
        if (!empty($content)) {
            $mainColumn = isset($mask['mainColumnId']) ? $mask['mainColumnId'] : null;
            if ($mainColumn) {
                $blocks[$mainColumn] = array($this->getSingleBlock($content['id']));
                if (!in_array("contentDetail", $blockTypes)) {
                    $blockTypes[] = "contentDetail";
                }
            }
        }
        $termColumn = (!empty($mainColumn)) ? $mainColumn : null;
        $lastMatchedNode['rows'] = array_replace_recursive($mask['rows'], $this->getRowsInfos($blocks, $mask['rows'], $termColumn));
        if (isset($lastMatchedNode['i18n'][$params['lang']->getLocale()])){
            $lastMatchedNode['locale']=$params['lang']->getLocale();
        } elseif (isset($lastMatchedNode['i18n'][$params['lang']->getFallback()])){
            $lastMatchedNode['locale']=$params['lang']->getFallback();
        }
        if (!isset($lastMatchedNode['title'])) {
            if (isset($lastMatchedNode['i18n'][$params['lang']->getLocale()]['title'])){
                $lastMatchedNode['title']=$lastMatchedNode['i18n'][$params['lang']->getLocale()]['title'];
            } elseif (isset($lastMatchedNode['i18n'][$params['lang']->getFallback()]['title'])){
                $lastMatchedNode['title']=$lastMatchedNode['i18n'][$params['lang']->getFallback()]['title'];
            }
        }
        if (!isset($lastMatchedNode['eCTitle'])) {
            if (isset($lastMatchedNode['i18n'][$params['lang']->getLocale()]['eCTitle'])){
                $lastMatchedNode['eCTitle']=$lastMatchedNode['i18n'][$params['lang']->getLocale()]['eCTitle'];
            } elseif (isset($lastMatchedNode['i18n'][$params['lang']->getFallback()]['eCTitle'])){
                $lastMatchedNode['eCTitle']=$lastMatchedNode['i18n'][$params['lang']->getFallback()]['eCTitle'];
            }
        }
        if (!isset($lastMatchedNode['description'])) {
            if (isset($lastMatchedNode['i18n'][$params['lang']->getLocale()]['description'])){
                $lastMatchedNode['description']=$lastMatchedNode['i18n'][$params['lang']->getLocale()]['description'];
            } elseif (isset($lastMatchedNode['i18n'][$params['lang']->getFallback()]['description'])){
                $lastMatchedNode['description']=$lastMatchedNode['i18n'][$params['lang']->getFallback()]['description'];
            }
        }
        if (!isset($lastMatchedNode['keywords'])) {
            if (isset($lastMatchedNode['i18n'][$params['lang']->getLocale()]['keywords'])){
                $lastMatchedNode['keywords']=$lastMatchedNode['i18n'][$params['lang']->getLocale()]['keywords'];
            } elseif (isset($lastMatchedNode['i18n'][$params['lang']->getFallback()]['keywords'])){
                $lastMatchedNode['keywords']=$lastMatchedNode['i18n'][$params['lang']->getFallback()]['keywords'];
            }
        }
        if (isset($lastMatchedNode["i18n"])){
            $urlOptions = array(
                'encode' => true,
                'reset' => true,
            );
            foreach($lastMatchedNode["i18n"] as &$alternative){
                $alternative["fullUrl"]=$this->getContext()->url()->fromRoute('rewrite', array(
                    'pageId' => $lastMatchedNode["id"],
                    'locale' => $alternative["locale"],
                ), $urlOptions);
            }
        }
        $output = array(
            'success' => true,
            'site' => $this->outputSiteMask($site),
            'page' => $this->outputPageMask($lastMatchedNode),
            'mask' => $this->outputMaskMask($mask),
            'breadcrumb' => $pages,
            'blockTypes' => $blockTypes,
        );
        return $output;
    }

    /**
     * Get entity action
     *
     * @param $id
     * @param $params
     * @return array
     */
    public function getEntityAction($id, $params)
    {
        $page = $this->getPagesCollection()->findById($id);
        if (!$page) {
            throw new APIEntityException('Page not found', 404);
        }
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
            'title' => $page['title'],
            'pageData'=>array_intersect_key($page, array_flip([ 'title','description','eCTitle','eCDescription','eCImage','richTextId','includedRichText']))
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
        $mask = ['id', 'host', 'alias', 'description', 'keywords', 'defaultLanguage', 'languages', 'locale', 'locStrategy', 'homePage', 'author', 'disqusKey', 'iframelyKey','optimizedRender'];
        return array_intersect_key($output, array_flip($mask));
    }

    /**
     * Mask output mask
     * @param $output
     * @return array
     */
    protected function outputMaskMask($output)
    {
        $mask = array('pageProperties', 'mainColumnId');
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

    /**
     * Get rows infos
     *
     * @param array $blocks
     * @param array $rows
     * @param null $termColumn
     * @return array|null
     */
    protected function getRowsInfos(array &$blocks, array $rows = null, $termColumn = null)
    {
        if ($rows === null) {
            return null;
        }
        $returnArray = $rows;
        foreach ($rows as $key => $row) {
            $row = $this->localizeTitle($row);
            $returnArray[$key] = $row;

            if (is_array($row['columns'])) {
                $returnArray[$key]['columns'] = $this->getColumnsInfos($blocks, $row['columns'], $termColumn);
            } else {
                $returnArray[$key]['columns'] = null;
            }
        }
        return $returnArray;
    }

    /**
     * Get columns infos
     *
     * @param array $blocks
     * @param array $columns
     * @param null $termColumn
     * @return array|null
     */
    protected function getColumnsInfos(array &$blocks, array $columns = null, $termColumn = null)
    {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            $column = $this->localizeTitle($column);
            $returnArray[$key] = $column;
            if ($termColumn == $column['id']) {
                $returnArray[$key]['isTerminal'] = true;
            } else {
                $returnArray[$key]['rows'] = $this->getRowsInfos($blocks, $column['rows'], $termColumn);
            }
            if (isset($blocks[$column['id']])) {
                $returnArray[$key]['blocks'] = $this->sortBlocks($blocks[$column['id']]);
            }
        }
        return $returnArray;
    }

    /**
     * Sort blocks
     *
     * @param $blocks
     * @return array
     */
    protected function sortBlocks($blocks)
    {
        $newBlocks = array();
        foreach ($blocks as &$block) {
            $block['id'] = (string)$block['id'];
            if (isset($block['orderValue']) && !isset($newBlocks[$block['orderValue']])) {
                $newBlocks[$block['orderValue']] = &$block;
            } else {
                $newBlocks[] = &$block;
            }
        }
        ksort($newBlocks);
        return array_values($newBlocks);
    }

    /**
     * Get single block
     *
     * @param $id
     * @return array
     */
    protected function getSingleBlock($id)
    {
        $block = array();
        $block['configBloc'] = array(
            'contentId' => $id,
            'isAutoInjected'=>true, //used to differentiate auto-injected detail block from purposely added block
        );
        $block['bType'] = 'contentDetail';
        $block['id'] = 'single';
        $block['responsive'] = array(
            'tablet' => true,
            'desktop' => true,
            'phone' => true
        );

        return $block;
    }

    /**
     * Localize title
     *
     * @param array $item
     * @return array
     */
    protected function localizeTitle(array $item)
    {
        if (isset($item['i18n'])) {
            if (isset($item['i18n'][$this->getCurrentLocalizationAPIService()->getCurrentLocalization()])) {
                if (isset($item['i18n'][$this->getCurrentLocalizationAPIService()->getCurrentLocalization()]['eTitle'])) {
                    $item['eTitle'] = $item['i18n'][$this->getCurrentLocalizationAPIService()->getCurrentLocalization()]['eTitle'];
                } else {
                    $item['title'] = $item['i18n'][$this->getCurrentLocalizationAPIService()->getCurrentLocalization()]['title'];
                }
            }
            unset($item['i18n']);
        }
        return $item;
    }
}
