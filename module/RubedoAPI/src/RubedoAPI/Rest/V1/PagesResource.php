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
use RubedoAPI\Entities\API\Language;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;

/**
 * Class PagesResource
 * @package RubedoAPI\Rest\V1
 */
class PagesResource extends AbstractResource
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
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('title')
                            ->setKey('title')
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
        $site = $this->getSitesCollection()->findByHost($params['site']);
        if (empty($site)) {
            throw new APIEntityException('Site not found', 404);
        }
        if (isset($site['locStrategy']) && $site['locStrategy'] == 'fallback') {
            $params['lang'] = new Language(implode('|', array($params['lang']->getLocale(), $site['defaultLanguage'])));
            $this->getCurrentLocalizationAPIService()->refreshLocalization($params['lang']);
        }
        $pages =  array();
        $url = '';
        if (empty($params['route'])) {
            $lastMatchedNode = $this->getPagesCollection()->findById($site['homePage']);
        } else {
            $urlSegments = explode('/', trim($params['route'], '/'));
            $lastMatchedNode = ['id' => 'root'];
            foreach ($urlSegments as $key => $value) {
                try {
                    $contentId = new \MongoId($value);
                    $content = $this->getContentsCollection()->findById($contentId, false, false);
                    break;
                } catch (\Exception $e) {}
                $matchedNode = $this->getPagesCollection()->matchSegment($value, $lastMatchedNode['id'], $site['id']);
                if (null === $matchedNode) {
                    break;
                } else {
                    if($key == 0){
                        $getUrl = $this->getEntityAction($matchedNode['id'],$params);
                        $url = $getUrl['url'];
                    } else {
                        $url = $url . '/' .$value;
                    }
                    $pages[]=array('title'=>$matchedNode['text'], 'url'=>$url);
                    $lastMatchedNode = $matchedNode;
                }
            }
        }
        if ($lastMatchedNode['id'] == 'root') {
            throw new APIEntityException('Page not found', 404);
        }
        $lastMatchedNode['blocks'] = $this->getBlocksCollection()->getListByPage($lastMatchedNode['id'])['data'];
        $languagesWithFlag = array();
        foreach($site['languages'] as $lang){
            $localeDetail =  $this->getLanguagesCollection()->findByLocale($lang);
            $languagesWithFlag[$lang]=array('lang'=>$lang,'flagCode'=>(isset($localeDetail['flagCode'])?$localeDetail['flagCode']:''));
        }
        $site['languages']=$languagesWithFlag;
        $wasFiltered = AbstractCollection::disableUserFilter();
        $mask = $this->getMasksCollection()->findById($lastMatchedNode['maskId']);
        AbstractCollection::disableUserFilter($wasFiltered);

        if (empty($mask)) {
            throw new APIEntityException('Mask not found', 404);
        }

        $blocksFound = array_merge($mask['blocks'], $lastMatchedNode['blocks']);
        $blocks = array();
        foreach ($blocksFound as $block) {
            if (isset($block['blockData'])) {
                $block = $block['blockData'];
            }
            if (!isset($block['orderValue'])) {
                throw new APIEntityException(sprintf('Missing orderValue for block %1$s', $block['id']), 404);
            }
            $blocks[$block['parentCol']][] = $block;
        }
        if (!empty($content)) {
            $mainColumn =  isset($mask['mainColumnId']) ? $mask['mainColumnId'] : null;
            if ($mainColumn) {
                $blocks[$mainColumn] = array($this->getSingleBlock($content['id']));
            }
        }
        $lastMatchedNode['rows'] = array_replace_recursive($mask['rows'], $this->getRowsInfos($blocks, $mask['rows']));

        $output = array(
            'success' => true,
            'site' => $this->outputSiteMask($site),
            'page' => $this->outputPageMask($lastMatchedNode),
            'mask' => $this->outputMaskMask($mask),
            'breadcrumb' => $pages,
        );
        return $output;
    }

    public function getEntityAction($id, $params) {
        $page = $this->getPagesCollection()->findById($id);
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
    protected function getRowsInfos(array &$blocks, array $rows = null)
    {
        if ($rows === null) {
            return null;
        }
        $returnArray = $rows;
        foreach ($rows as $key => $row) {
            $row = $this->localizeTitle($row);
            $returnArray[$key] = $row;

            if (is_array($row['columns'])) {
                $returnArray[$key]['columns'] = $this->getColumnsInfos($blocks, $row['columns']);
            } else {
                $returnArray[$key]['columns'] = null;
            }
        }
        return $returnArray;
    }

    protected function getColumnsInfos(array &$blocks, array $columns = null)
    {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            $column = $this->localizeTitle($column);
            $returnArray[$key] = $column;
            if (isset($blocks[$column['id']])) {
                $returnArray[$key]['blocks'] = $blocks[$column['id']];
            } else {
                $returnArray[$key]['rows'] = $this->getRowsInfos($blocks, $column['rows']);
            }
        }
        return $returnArray;
    }

    protected function getSingleBlock($id)
    {
        $block = array();
        $block['configBloc'] = array(
            'contentId' => $id,
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