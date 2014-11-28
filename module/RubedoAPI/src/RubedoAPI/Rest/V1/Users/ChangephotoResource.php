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

namespace RubedoAPI\Rest\V1\Users;


use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Rest\V1\AbstractResource;
use Zend\Debug\Debug;

/**
 * Class ChangephotoResource
 * @package RubedoAPI\Rest\V1\Users
 */
class ChangephotoResource extends AbstractResource
{
    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Post action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @throws \RubedoAPI\Exceptions\APIAuthException
     */
    public function postAction($params)
    {
        //for now change only your own photo
        if ($this->getCurrentUserAPIService()->getCurrentUser()['id'] != $params['userId']) {
            throw new APIAuthException('You have insufficient rights', 403);
        }
        $user = $this->getUsersCollection()->findById($params['userId']);
        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        $newFileId=$this->uploadFile($params['file']);
        if (isset($user['photo'])){
            $oldFile=$this->getFilesCollection()->findById($user['photo']);
            if ($oldFile){
                $this->getFilesCollection()->destroy(array(
                    "id"=>$user['photo'],
                    "version"=>1
                ));
            }
        }
        $user['photo']=$newFileId;
        $updatedUser=$this->getUsersCollection()->update($user);
        if (!$updatedUser['success']){
            return($updatedUser);
        }
        $userPhotoUrl=$this->getUrlAPIService()->userAPIAvatar($user, 100, 100, 'boxed');
        return(array(
            "success"=>true,
            "photoUrl"=>$userPhotoUrl
        ));

    }


    /**
     * Upload a file
     *
     * @param $file
     * @return mixed
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    protected function uploadFile($file)
    {
        $mimeType = mime_content_type($file['tmp_name']);
        $fileToCreate = array(
            'serverFilename' => $file['tmp_name'],
            'text' => $file['name'],
            'filename' => $file['name'],
            'Content-Type' => isset($mimeType) ? $mimeType : $file['type'],
            'mainFileType' => "Image"
        );
        $result = $this->getFilesCollection()->create($fileToCreate);
        if (!$result['success']) {
            throw new APIEntityException('Failed to create file', 500);
        }
        return $result['data']['id'];

    }


    /**
     * Define
     */
    protected function define()
    {
        $this
            ->definition
            ->setDescription('Change user photo')
            ->setName('Change photo')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $this->definePost($entity);
            });
    }

    /**
     * Define post
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function definePost(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Change photo')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('User ID')
                    ->setKey('userId')
                    ->setFilter('\MongoId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Photo file')
                    ->setKey('file')
                    ->setRequired()
            )->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('photoUrl')
                    ->setDescription('Url of the new user photo')
            );
    }
} 