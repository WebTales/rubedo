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
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use Rubedo\Update\Install;


class ReloadconfigResource extends AbstractResource
{
    private $installObject;


    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Reload config')
            ->setDescription('Reload DB config')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Reload DB config');
            });

    }

    public function postAction($params)
    {
        $this->installObject = new Install();
        $rconfig=Manager::getService("MongoConf")->getRubedoConf();
        if($rconfig){
            $this->installObject->saveLocalConfig($rconfig);
            return [
                'success' => true
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No config found in DB'
            ];
        }

    }
}