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
namespace Rubedo\Blocks\Controller;

use Alb\OEmbed;
use Rubedo\Services\Cache;
use Rubedo\Services\Manager;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class UserProfileController extends AbstractController
{

    public function indexAction()
    {
        $output = $this->params()->fromQuery();
        $site = $this->params()->fromQuery('site');
        if ((isset($output['userprofile'])) && (!empty($output['userprofile']))) {
            $currentUser = Manager::getService("Users")->findById($output['userprofile']);
        } else {
            $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        }

        if (!$currentUser) {
            $output['errorMessage'] = "Blocks.UserProfile.error.noUser";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/userProfile/error.html.twig");
            return $this->_sendResponse($output, $template);
        }

        $user = $currentUser;
        $userType = Manager::getService("UserTypes")->findById($user['typeId']);
        $output['user'] = $user;
        $output['fieldTypes'] = $userType['fields'];
        $frontOfficeTemplatesService = Manager::getService('FrontOfficeTemplates');
        $cTypeArray = array();
        $CKEConfigArray = array();
        $contentTitlesArray = array();
        $data = $user['fields'];
        $data["id"] = $user['id'];
        $data['locale'] = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        $contentsReader = Manager::getService('Contents');
        foreach ($userType["fields"] as $value) {

            $cTypeArray[$value['config']['name']] = $value;
            if ($value["cType"] == "Rubedo.view.DCEField") {
                if (is_array($data[$value['config']['name']])) {
                    $contentTitlesArray[$value['config']['name']] = array();
                    foreach ($data[$value['config']['name']] as $intermedValue) {
                        $intermedContent = $contentsReader->findById($intermedValue, true, false);
                        $contentTitlesArray[$value['config']['name']][] = $intermedContent['fields']['text'];
                    }
                } else {
                    if (is_string($data[$value['config']['name']]) && preg_match('/[\dabcdef]{24}/', $data[$value['config']['name']]) == 1) {
                        $intermedContent = $contentsReader->findById($data[$value['config']['name']], true, false);
                        $contentTitlesArray[$value['config']['name']] = $intermedContent['fields']['text'];
                    }
                }
            } else
                if (($value["cType"] == "Rubedo.view.CKEField") && (isset($value["config"]["CKETBConfig"]))) {
                    $CKEConfigArray[$value['config']['name']] = $value["config"]["CKETBConfig"];
                } else
                    if (($value["cType"] == "Rubedo.view.externalMediaField") && (isset($data[$value["config"]["name"]]))) {
                        $mediaConfig = $data[$value["config"]["name"]];

                        if (isset($mediaConfig['url'])) {

                            $oembedParams['url'] = $mediaConfig['url'];

                            $cache = Cache::getCache('oembed');

                            $options = array();

                            if (isset($blockConfig['maxWidth'])) {
                                $oembedParams['maxWidth'] = $blockConfig['maxWidth'];
                                $options['maxWidth'] = $blockConfig['maxWidth'];
                            } else {
                                $oembedParams['maxWidth'] = 0;
                            }

                            if (isset($blockConfig['maxHeight'])) {
                                $oembedParams['maxHeight'] = $blockConfig['maxHeight'];
                                $options['maxHeight'] = $blockConfig['maxHeight'];
                            } else {
                                $oembedParams['maxHeight'] = 0;
                            }

                            $cacheKey = 'oembed_item_' . md5(serialize($oembedParams));
                            $loaded = false;
                            $item = $cache->getItem($cacheKey, $loaded);

                            if (!$loaded) {
                                // If the URL come from flickr, we check the URL
                                if (stristr($oembedParams['url'], 'www.flickr.com')) {
                                    $decomposedUrl = explode("/", $oembedParams['url']);

                                    $end = false;

                                    // We search the photo identifiant and we remove all parameters after it
                                    foreach ($decomposedUrl as $key => $value) {
                                        if (is_numeric($value) && strlen($value) === 10) {
                                            $end = true;
                                            continue;
                                        }

                                        if ($end) {
                                            unset($decomposedUrl[$key]);
                                        }
                                    }

                                    $oembedParams['url'] = implode("/", $decomposedUrl);
                                }

                                $response = OEmbed\Simple::request($oembedParams['url'], $options);

                                $item['width'] = $oembedParams['maxWidth'];
                                $item['height'] = $oembedParams['maxHeight'];
                                if (!stristr($oembedParams['url'], 'www.flickr.com')) {
                                    $item['html'] = $response->getHtml();
                                } else {
                                    $raw = $response->getRaw();
                                    if ($oembedParams['maxWidth'] > 0) {
                                        $width_ratio = $raw->width / $oembedParams['maxWidth'];
                                    } else {
                                        $width_ratio = 1;
                                    }
                                    if ($oembedParams['maxHeight'] > 0) {
                                        $height_ratio = $raw->height / $oembedParams['maxHeight'];
                                    } else {
                                        $height_ratio = 1;
                                    }

                                    $size = "";
                                    if ($width_ratio > $height_ratio) {
                                        $size = "width='" . $oembedParams['maxWidth'] . "'";
                                    }
                                    if ($width_ratio < $height_ratio) {
                                        $size = "height='" . $oembedParams['maxHeight'] . "'";
                                    }
                                    $item['html'] = "<img src='" . $raw->url . "' " . $size . "' title='" . $raw->title . "'>";
                                }

                                $cache->setItem($cacheKey, $item);
                            }

                            $output['item'] = $item;
                        }
                    }
        }

        $output["type"] = $cTypeArray;
        $output["CKEFields"] = $CKEConfigArray;
        $output["contentTitles"] = $contentTitlesArray;
        $hasCustomLayout = false;
        $customLayoutRows = array();
        if ((isset($userType['layouts'])) && (is_array($userType['layouts']))) {
            foreach ($userType['layouts'] as $key => $value) {
                if (($value['type'] == "Detail") && ($value['active']) && ($value['site'] == $site['id'])) {
                    $hasCustomLayout = true;
                    $customLayoutRows = $value['rows'];
                }
            }
        }

        $output["data"] = $data;
        $output["customLayoutRows"] = $customLayoutRows;
        $blockConfig = $this->params()->fromQuery('block-config');
        $output["blockConfig"] = $blockConfig;

        if ($hasCustomLayout) {
            $template = $frontOfficeTemplatesService->getFileThemePath("blocks/userProfile/customLayout.html.twig");
        } else {
            $template = $frontOfficeTemplatesService->getFileThemePath("blocks/userProfile.html.twig");
        }
        $css = array(
            "/components/jquery/timepicker/jquery.ui.timepicker.css",
            "/components/jquery/jqueryui/themes/base/jquery-ui.css"
        );
        $js = array(
            $this->getRequest()->getBasePath() . '/' . $frontOfficeTemplatesService->getFileThemePath("js/rubedo-map.js"),
            $this->getRequest()->getBasePath() . '/' . $frontOfficeTemplatesService->getFileThemePath("js/map.js"),
            $this->getRequest()->getBasePath() . '/' . $frontOfficeTemplatesService->getFileThemePath("js/rating.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
