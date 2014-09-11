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

namespace RubedoAPI\Traits;


use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIControllerException;

/**
 * Class LazyServiceManager
 * @package RubedoAPI\Traits
 * @method \RubedoAPI\Collection\UserTokens getUserTokensAPICollection() Return UserTokens collection
 * @method \RubedoAPI\Services\Security\Authentication getAuthAPIService() Return Authentication service
 * @method \RubedoAPI\Services\Router\Url getUrlAPIService() Return Router URL service
 * @method \RubedoAPI\Services\User\CurrentUser getCurrentUserAPIService() Return current User service
 * @method \RubedoAPI\Services\Internationalization\Current getCurrentLocalizationAPIService() Return current localization service
 * @method \Rubedo\Interfaces\Collection\IContents getContentsCollection() Return current localization service
 * @method \Rubedo\Interfaces\Collection\IContentTypes getContentTypesCollection() Return ContentTypes collection
 * @method \Rubedo\Interfaces\Collection\IDamTypes getDamTypesCollection() Return Damtypes collection
 * @method \Rubedo\Interfaces\Collection\ITaxonomy getTaxonomyCollection() Return Taxonomy collection
 * @method \Rubedo\Interfaces\Collection\IPages getPagesCollection() Return current localization service
 * @method \Rubedo\Interfaces\Collection\ISites getSitesCollection() Return current localization service
 * @method \Rubedo\Interfaces\Collection\IQueries getQueriesCollection() Return current localization service
 * @method \Rubedo\Interfaces\Security\IHash getHashService() Return current localization service
 * @method \Zend\Mvc\Application getApplicationService() Return current application
 * @method \Zend\Config\Config getConfigService() Return config service
 * @method \Rubedo\Interfaces\Collection\IUsers getUsersCollection() Return Users collection
 * @method \Rubedo\Interfaces\Security\IAcl getAclService() Return ACL service
 * @method \RubedoAPI\Services\Security\Token getTokenAPIService() Return ACL service
 * @method \Rubedo\Collection\MailingList getMailingListCollection() Return MLists collection
 * @method \Rubedo\Collection\UserTypes getUserTypesCollection() Return UserTypes collection
 * @method \Rubedo\Interfaces\Time\ICurrentTime getCurrentTimeService() Return CurrentTime service
 * @method \Rubedo\Interfaces\Templates\IFrontOfficeTemplates getFrontOfficeTemplatesService() Return FrontOfficeTemplates service
 * @method \Rubedo\Interfaces\Internationalization\ITranslate getTranslateService() Return FrontOfficeTemplates service
 * @method \Rubedo\Interfaces\Mail\IMailer getMailerService() return Mailer service
 * @method \Rubedo\Interfaces\Collection\IDam getDamCollection() return DAM collection
 * @method \Rubedo\Interfaces\Collection\IFiles getFilesCollection() return Files collection
 * @method \Rubedo\Interfaces\Collection\ITinyUrl getTinyUrlCollection() return TinyUrl collection
 * @method \Rubedo\Interfaces\Elastic\IDataSearch getElasticDataSearchService() return DataSearch service
 * @method \Rubedo\Collection\Orders getOrdersCollection() return Orders collection
 * @method \Rubedo\Time\Date getDateService() return Date service
 * @method \Rubedo\Collection\ShoppingCart getShoppingCartCollection() return ShoppingCart service
 * @method \Rubedo\Collection\ContentViewLog getContentViewLogCollection() return ContentViewLog service
 */
trait LazyServiceManager
{
    protected $callCache = array();

    public function __call($method, $arguments)
    {
        if (!isset($this->callCache[$method])) {
            $matches = array();
            if (preg_match('/^get(.+)APICollection$/', $method, $matches)) {
                $this->callCache[$method] = Manager::getService('API\\Collection\\' . $matches[1]);
            } elseif (preg_match('/^get(.+)APIService$/', $method, $matches)) {
                $this->callCache[$method] = Manager::getService('API\\Services\\' . $matches[1]);
            } elseif (preg_match('/^get(.+)(Service|Collection)$/', $method, $matches)) {
                $this->callCache[$method] = Manager::getService($matches[1]);
            } else {
                throw new APIControllerException('method "' . $method . " not found.", 500);
            }
        }
        return $this->callCache[$method];
    }
} 