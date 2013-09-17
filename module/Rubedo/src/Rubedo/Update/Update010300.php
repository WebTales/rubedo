<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
namespace Rubedo\Update;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Rubedo\Collection\AbstractCollection;

/**
 * Methods
 * for
 * update
 * tool
 *
 * @author jbourdin
 *        
 */
class Update010300 extends Update
{

    protected static $toVersion = '1.4.0';

    /**
     * do
     * the
     * upgrade
     *
     * @return boolean
     */
    public static function upgrade()
    {
        static::ressourceUpdate();
        return true;
    }

    public static function ressourceUpdate()
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        // introduction
        $service = Manager::getService('Blocks');
        
        $filters = Filter::factory();
        $filters->addFilter(Filter::factory('In')->setName('blockData.bType')
            ->setValue(array(
            'resource',
            'protectedResource'
        )));
        $filters->addFilter(Filter::factory('OperatorToValue')->setName('blockData.configBloc.introduction')
            ->setValue('')
            ->setOperator('$ne'));
        
        $list = $service->getList($filters);
        if ($list['count'] > 0) {
            $contentService = Manager::getService('Contents');
            $pageService = Manager::getService('Pages');
            foreach ($list['data'] as $block) {
                $introduction = $block['blockData']['configBloc']['introduction'];
                if (! is_string($introduction) || preg_match('/[\dabcdef]{24}/', $introduction) !== 1) {
                    $page = $pageService->findById($block['pageId']);
                    if (isset($page['nativeLanguage'])) {
                        $nativeLanguage = $page['nativeLanguage'];
                    } else {
                        $site = Manager::getService('Sites')->findById($page['site']);
                        if ($site) {
                            $nativeLanguage = $page['defaultLanguage'];
                        } else {
                            continue;
                        }
                    }
                    $richtext = array(
                        'text' => 'resource',
                        'fields' => array(
                            'fields' => array(
                                'body' => $introduction,
                                'text' => 'resource',
                                'summary' => ''
                            )
                        ),
                        'typeId' => '520b8644c1c3dad506000036',
                        'status' => 'published',
                        'version' => '',
                        'online' => true,
                        'pageId' => $block['pageId'],
                        'maskId' => '',
                        'blockId' => $block['id'],
                        'locale' => '',
                        'target' => 'global',
                        'i18n' => array(
                            $nativeLanguage => array(
                                'fields' => array(
                                    'body' => $introduction,
                                    'text' => 'resource',
                                    'summary' => ''
                                )
                            )
                        ),
                        'nativeLanguage' => $nativeLanguage
                    );
                    
                    $result = $contentService->create($richtext);
                    $contentId = $result['data']['id'];
                    $block['blockData']['configBloc']['introduction'] = $contentId;
                    $result = $service->update($block);
                }
            }
        }
        
        AbstractCollection::disableUserFilter($wasFiltered);
        
        return true;
    }
}