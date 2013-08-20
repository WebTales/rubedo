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
Use Rubedo\Services\Manager;

/**
 * Front End Edition controller
 *
 * @author nduvollet
 * @category Rubedo
 * @package Rubedo
 */
class XhrEditController extends Zend_Controller_Action
{

    /**
     * variable for the Session service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_session;

    /**
     * variable for the Data service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_dataService;

    /**
     * Init the session service
     */
    public function init ()
    {
        $this->_dataService = Manager::getService('Contents');
    }

    /**
     * Allow to define the current theme
     */
    public function indexAction ()
    {
        $data = Zend_Json::decode($this->getParam("data", null));
        $errors = array();
        
        foreach ($data as $contentId => $value) {
            $contentId = explode("_", $contentId);
            $id = $contentId[0];
            $field = $contentId[1];
            $localizable = false;
            
            $locale = isset($value["locale"]) ? $value["locale"] : null;
            
            $field = explode("-", $field);
            $name = $field[0];
            
            if (count($field) > 1) {
                $index = $field[1];
            }
            if ($id === null || $data === null || $name === null) {
                throw new \Rubedo\Exceptions\Server("You must provide the concerned content id, the new value and the field which had to be updated in database", "Exception27");
            }
            // correcting value in case of false bool
            if ($data == 'false') {
                $data = false;
            }
            
            $content = $this->_dataService->findById($id, true, false);
            $contentType = Manager::getService("ContentTypes")->findById($content['typeId']);
            
            foreach ($contentType["fields"] as $fieldObj) {
                if($fieldObj["config"]["name"] === $name) {
                    $localizable = isset($fieldObj["config"]["localizable"]) ? $fieldObj["config"]["localizable"] : false;
                }
            }
            
            if($name == "text" || $name == "summary") {
                $localizable = true;
            }
            
            if (! $content) {
                throw new \Rubedo\Exceptions\Server('This content id does not exist: %1$s', "Exception28", $id);
            }
            
            if ($content["status"] !== 'published') {
                $errors[] = 'Content already have a draft version';
            } else {
                if($localizable) {
                    
                    if($locale !== null) {
                        //Create the translation if it doesn't exist
                        if(!isset($content["i18n"][$locale])) {
                            $nativeLanguage = $content["nativeLanguage"];
                        
                            $content["i18n"][$locale] = $content["i18n"][$nativeLanguage];
                            $content["i18n"][$locale]["locale"] = $locale;
                        }
                        
                        if (count($field) > 1) {
                            $content["i18n"][$locale]['fields'][$name][$index] = $value["newValue"];
                        } else {
                            $content["i18n"][$locale]['fields'][$name] = $value["newValue"];
                        }
                    } else {
                        $errors[] = "You must provide the current language of the content to update it (".$content['id'].")";
                    }
                } else {
                    if (count($field) > 1) {
                        $content['fields'][$name][$index] = $value["newValue"];
                    } else {
                        $content['fields'][$name] = $value["newValue"];
                    }
                }
                
                $updateResult = $this->_dataService->update($content, array(), false);
                
                if (! $updateResult['success']) {
                    $errors[] = "Failed to update the content \"" . $content["text"] . "\"";
                }
            }
        }
        
        if (count($errors) > 0) {
            return $this->_helper->json(array(
                "success" => false,
                "msg" => $errors
            ));
        } else {
            return $this->_helper->json(array(
                "success" => true
            ));
        }
    }
}
