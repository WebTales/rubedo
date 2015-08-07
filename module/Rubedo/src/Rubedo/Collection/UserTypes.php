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
namespace Rubedo\Collection;

use Rubedo\Exceptions\Server;
use Rubedo\Interfaces\Collection\IUserTypes;
use Rubedo\Services\Events;
use Rubedo\Services\Manager;

/**
 * Service to handle UserTypes
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class UserTypes extends AbstractCollection implements IUserTypes
{

    public function __construct()
    {
        $this->_collectionName = 'UserTypes';
        parent::__construct();
    }

    public function findById($contentId, $forceReload = false) {
        $userType = parent::findById($contentId, $forceReload);

        return $this->localizeUserTypeFields($userType);
    }

    public function destroy(array $obj, $options = array())
    {
        if ((isset($obj["UTType"])) && (($obj["UTType"] == "default") || ($obj["UTType"] == "email"))) {
            $result = array(
                'success' => false,
                "msg" => 'Cannot destroy system user type'
            );
            return ($result);
        }
        $result = $this->_dataService->destroy($obj, $options);
        $args = $result;
        $args['data'] = $obj;
        Events::getEventManager()->trigger(self::POST_DELETE_COLLECTION, $this, $args);
        return $result;
    }

    /**
     * Localize fiels tooltip and label in user type
     *
     * @param $userTypeObj array User type object to localize
     * @return array Localized user type
     * @throws Server Throw an exception when the user type object is not well formated
     */
    private function localizeUserTypeFields($userTypeObj) {
        $almostOneSite = Manager::getService("Sites")->count();

        if($almostOneSite > 0 && self::$_isFrontEnd) {
            $site = Manager::getService("Sites")->getCurrent();

            $localizationStrategy = isset($site["locStrategy"]) ? $site["locStrategy"] : "onlyOne";

            if(!isset($site["defaultLanguage"]) || $site["defaultLanguage"] == "") {
                throw new Server("Missing key 'defaultLanguage' in site object");
            }

            $currentLanguage = Manager::getService("CurrentLocalization")->getCurrentLocalization();
            $fallbackLanguage = $site["defaultLanguage"];

            if(!isset($userTypeObj["fields"]) || !is_array($userTypeObj["fields"])) {
                $userTypeObj["fields"] = [];
            }

            foreach($userTypeObj["fields"] as &$field) {
                if(!isset($field["config"]["i18n"])) {
                    continue;
                }

                if($localizationStrategy == "onlyOne") {
                    if(isset($field["config"]["i18n"][$currentLanguage]["fieldLabel"]) && is_string($field["config"]["i18n"][$currentLanguage]["fieldLabel"])) {
                        $field["config"]["fieldLabel"] = $field["config"]["i18n"][$currentLanguage]["fieldLabel"];
                    }

                    if(isset($field["config"]["i18n"][$currentLanguage]["tooltip"]) && is_string($field["config"]["i18n"][$currentLanguage]["tooltip"])) {
                        $field["config"]["tooltip"] = $field["config"]["i18n"][$currentLanguage]["tooltip"];
                    }
                } elseif($localizationStrategy == "fallback") {
                    if(isset($field["config"]["i18n"][$currentLanguage]["fieldLabel"]) && is_string($field["config"]["i18n"][$currentLanguage]["fieldLabel"])) {
                        $field["config"]["fieldLabel"] = $field["config"]["i18n"][$currentLanguage]["fieldLabel"];
                    } elseif(isset($field["config"]["i18n"][$fallbackLanguage]["fieldLabel"]) && is_string($field["config"]["i18n"][$fallbackLanguage]["fieldLabel"])) {
                        $field["config"]["fieldLabel"] = $field["config"]["i18n"][$fallbackLanguage]["fieldLabel"];
                    }

                    if(isset($field["config"]["i18n"][$currentLanguage]["tooltip"]) && is_string($field["config"]["i18n"][$currentLanguage]["tooltip"])) {
                        $field["config"]["tooltip"] = $field["config"]["i18n"][$currentLanguage]["tooltip"];
                    } elseif(isset($field["config"]["i18n"][$fallbackLanguage]["tooltip"]) && is_string($field["config"]["i18n"][$fallbackLanguage]["tooltip"])) {
                        $field["config"]["tooltip"] = $field["config"]["i18n"][$fallbackLanguage]["tooltip"];
                    }
                }
            }
        }

        return $userTypeObj;
    }
}
