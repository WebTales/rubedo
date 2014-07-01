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
use Rubedo\Exceptions\User as EUser;
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
    /**
     * @var \Rubedo\Interfaces\Templates\IFrontOfficeTemplates
     */
    protected $frontOfficeTemplatesService;

    /**
     * @var \Rubedo\Interfaces\Collection\IUserTypes
     */
    protected $userTypesService;

    /**
     * @var \Rubedo\Interfaces\Collection\IUsers
     */
    protected $usersService;

    /**
     * @var \Rubedo\Interfaces\User\ICurrentUser
     */
    protected $currentUserService;

    /**
     * @var \Rubedo\Interfaces\Collection\IContents
     */
    protected $contentsService;

    /**
     * @var \Rubedo\Interfaces\Internationalization\ICurrent
     */
    protected $currentLocalizationService;


    function __construct()
    {
        $this->frontOfficeTemplatesService = Manager::getService('FrontOfficeTemplates');
        $this->userTypesService = Manager::getService('UserTypes');
        $this->usersService = Manager::getService('Users');
        $this->currentUserService = Manager::getService('CurrentUser');
        $this->contentsService = Manager::getService('Contents');
        $this->currentLocalizationService = Manager::getService('CurrentLocalization');
    }

    public function indexAction()
    {
        $output = $this->params()->fromQuery();

        if (
            !(
            $user =
                !empty($output['userprofile'])
                    ? $this->usersService->findById($output['userprofile'])
                    : $user = $this->currentUserService->getCurrentUser()
            )
        ) {
            $output['errorMessage'] = 'Blocks.UserProfile.error.noUser';
            $template = $this->frontOfficeTemplatesService->getFileThemePath('blocks/userProfile/error.html.twig');
            return $this->_sendResponse($output, $template);
        }
        $output['canEdit'] = $this->currentUserService->getCurrentUser() == $user;
        $userType = $this->userTypesService->findById($user['typeId']);
        if ($this->getRequest()->isPost() && $output['canEdit']) {
            $post = $this->params()->fromPost();
            if (isset($output['editProfile'])){
                $user['fields'] = $this->filterFields($userType['fields'], $post); //todo make an intelligent merge !
                $this->usersService->update($user);
                $output['success'] = true;
            } elseif (isset($output['editLogin'])) {
                if (!empty($post['password']) && !empty($post['password-old']) && !empty($post['password-confirm'])) {
                    if ($post['password-confirm'] != $post['password']) {
                            $output['editLogin']['validation']['password-confirm'][] = 'Blocks.UserProfile.Error.PasswordConfirmNotMatch';
                    } else {
                        try {
                            $this->currentUserService->changePassword($post['password-old'], $post['password']);
                            $output['editLogin']['success']['password'] = true;
                        } catch(EUser $e) {
                            $output['editLogin']['validation']['password-old'][] = 'Blocks.UserProfile.Error.OldPasswordIsWrong';
                        }
                    }
                }
                if (!empty($post['email']) && $user['email'] != $post['email']) {
                    if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
                        $output['editLogin']['validation']['email'][] = 'Blocks.UserProfile.Error.EmailNotValid';
                    } else {
                        $searchUser = $this->usersService->findByEmail($post['email']);
                        if (!empty($searchUser)) {
                            $output['editLogin']['validation']['email'][] = 'Blocks.UserProfile.Error.EmailAlreadyInDB';
                        } else {
                            $user['email'] = $post['email'];
                            $this->usersService->update($user);
                            $output['editLogin']['success']['email'] = true;
                        }
                    }
                }
            }
        }
        $output['user'] = &$user;
        $output['fieldTypes'] = &$userType['fields'];

        $data = $user['fields'];
        $data['id'] = $user['id'];
        $data['locale'] = $this->currentLocalizationService->getCurrentLocalization();

        list(
            $output['type'],
            $output['CKEFields'],
            $output['contentTitles']
            ) = $this->renderFields($userType['fields'], $data);

        $hasCustomLayout = false;
        $customLayoutRows = array();
        if (isset($userType['layouts']) && is_array($userType['layouts'])) {
            foreach ($userType['layouts'] as $layout) {
                if ($layout['type'] == 'Detail' && $layout['active'] && $layout['site'] == $output['site']['id']) {
                    $hasCustomLayout = true;
                    $customLayoutRows = $layout['rows'];
                }
            }
        }

        $output['data'] = $data;
        $output['customLayoutRows'] = $customLayoutRows;
        $blockConfig = $this->params()->fromQuery('block-config');
        $output['blockConfig'] = $blockConfig;

        $template = $this
            ->frontOfficeTemplatesService
            ->getFileThemePath(
                $hasCustomLayout
                    ? 'blocks/userProfile/customLayout.html.twig'
                    : 'blocks/userProfile.html.twig'
            );
        $css = array(
            '/components/jquery/timepicker/jquery.ui.timepicker.css',
            '/components/jquery/jqueryui/themes/base/jquery-ui.css'
        );
        $js = array(
            "https://maps.googleapis.com/maps/api/js?sensor=false",
            $this->getRequest()->getBasePath() . '/' . $this->frontOfficeTemplatesService->getFileThemePath('js/rubedo-map.js'),
            $this->getRequest()->getBasePath() . '/' . $this->frontOfficeTemplatesService->getFileThemePath('js/map.js'),
            $this->getRequest()->getBasePath() . '/' . $this->frontOfficeTemplatesService->getFileThemePath('js/rating.js'),
            $this->getRequest()->getBasePath() . '/' . $this->frontOfficeTemplatesService->getFileThemePath('js/fields.js'),
            $this->getRequest()->getBasePath() . '/' . $this->frontOfficeTemplatesService->getFileThemePath('js/userfields.js'),
        );
        foreach($output['type'] as $typeName => $type){
            if (!empty($type['config']['i18n'][$data['locale']])){
                $output['type'][$typeName]['config']['fieldLabel'] = $type['config']['i18n'][$data['locale']]['fieldLabel'];
            }
        }
        return $this->_sendResponse($output, $template, $css, $js);
    }

    protected function renderFields($userTypefields, $userFields)
    {
        $cTypeArray = array();
        $CKEConfigArray = array();
        $contentTitlesArray = array();

        foreach ($userTypefields as $value) {
            $cTypeArray[$value['config']['name']] = $value;
            if ($value['cType'] == 'Rubedo.view.DCEField') {
                if (is_array($userFields[$value['config']['name']])) {
                    $contentTitlesArray[$value['config']['name']] = array();
                    foreach ($userFields[$value['config']['name']] as $intermedValue) {
                        $intermedContent = $this->contentsService->findById($intermedValue, true, false);
                        $contentTitlesArray[$value['config']['name']][] = $intermedContent['fields']['text'];
                    }
                } elseif (
                    is_string($userFields[$value['config']['name']])
                    && preg_match('/[\dabcdef]{24}/', $userFields[$value['config']['name']]) == 1
                ) {
                    $intermedContent = $this->contentsService->findById($userFields[$value['config']['name']], true, false);
                    $contentTitlesArray[$value['config']['name']] = $intermedContent['fields']['text'];
                }
            } elseif ($value['cType'] == 'Rubedo.view.CKEField' && isset($value['config']['CKETBConfig'])) {
                $CKEConfigArray[$value['config']['name']] = $value['config']['CKETBConfig'];
            } elseif ($value['cType'] == 'Rubedo.view.externalMediaField' && isset($data[$value['config']['name']])) {
                $mediaConfig = $data[$value['config']['name']];

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
                            $decomposedUrl = explode('/', $oembedParams['url']);
                            $end = false;
                            // We search the photo identifiant and we remove all parameters after it
                            foreach ($decomposedUrl as $key => $value) {
                                if ($end) {
                                    unset($decomposedUrl[$key]);
                                } elseif (is_numeric($value) && strlen($value) === 10) {
                                    $end = true;
                                    continue;
                                }
                            }
                            $oembedParams['url'] = implode('/', $decomposedUrl);
                        }

                        $response = OEmbed\Simple::request($oembedParams['url'], $options);
                        $item['width'] = $oembedParams['maxWidth'];
                        $item['height'] = $oembedParams['maxHeight'];
                        if (!stristr($oembedParams['url'], 'www.flickr.com')) {
                            $item['html'] = $response->getHtml();
                        } else {
                            $raw = $response->getRaw();
                            $width_ratio = $oembedParams['maxWidth'] > 0 ? $raw->width / $oembedParams['maxWidth'] : 1;
                            $height_ratio = $oembedParams['maxHeight'] > 0 ? $raw->height / $oembedParams['maxHeight'] : 1;
                            if ($width_ratio > $height_ratio) {
                                $size = 'width="' . $oembedParams['maxWidth'] . '"';
                            } elseif ($width_ratio < $height_ratio) {
                                $size = 'height="' . $oembedParams['maxHeight'] . '"';
                            } else {
                                $size = '';
                            }
                            $item['html'] = '<img src="' . $raw->url . '" ' . $size . ' title="' . $raw->title . '">';
                        }

                        $cache->setItem($cacheKey, $item);
                    }
                    $output['item'] = $item;
                }
            }
        }
        return array($cTypeArray, $CKEConfigArray, $contentTitlesArray);
    }

    protected function filterFields($userTypefields, $fields)
    {
        foreach ($fields as $fieldName => &$field) {
            $config = $this->isInConfigName($userTypefields, $fieldName);
            if (!$config) {
                unset($field);
            } elseif ($config['cType'] == 'Ext.form.field.Date') {
                if (is_array($field)) {
                    foreach($field as &$date) {
                        $date = strtotime($date);
                    }
                } else {
                    $field = strtotime($field);
                }
            }
        }
        if (isset($fields['position'])){
            $fields['position']['location']=array(
                "type"=>"Point",
                "coordinates"=>array((float) $fields['position']['lon'], (float) $fields['position']['lat'])
            );
        }
        return $fields;
    }

    /**
     * Search a value in array config
     *
     * @param $fields Array to scan
     * @param $name value searched
     * @return bool
     */
    protected function isInConfigName($fields, $name)
    {
        foreach ($fields as $field) {
            if ($field['config']['name'] == $name) return $field;
        }
        return false;
    }
}
