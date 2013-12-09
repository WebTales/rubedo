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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IMailingList;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle Mailing list
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class MailingList extends AbstractCollection implements IMailingList
{

    public function __construct()
    {
        $this->_collectionName = 'MailingList';
        parent::__construct();
    }

    /**
     * Add a user into a specified mailing list
     *
     * @param string $mailingListId
     * @param string $email
     * @param boolean $doNotDuplicate
     *
     * @throws \Rubedo\Exceptions\User
     * @return array
     *
     * @see \Rubedo\Interfaces\Collection\IMailingList::subscribe()
     */
    public function subscribe($mailingListId, $email, $doNotDuplicate = true, $userName = null)
    {
        // Get mailing list
        $mailingList = $this->findById($mailingListId);

        // Test if the mailing list exist in database
        if ($mailingList === null) {
            throw new \Rubedo\Exceptions\User('Invalid newsletter id', "Exception43");
        }

        // Get the user
        $wasFiltered = AbstractCollection::disableUserFilter();
        $user = Manager::getService("Users")->findByEmail($email);
        AbstractCollection::disableUserFilter($wasFiltered);

        // Create hash
        $hash = Manager::getService("Hash")->generateRandomString(24);

        // Get current time
        $date = Manager::getService("CurrentTime")->getCurrentTime();

        // Check if the user exist
        if ($user != null) {
            // Check if the user is already registered
            $isRegistered = false;

            if (isset($user["mailingLists"]) && isset($user["mailingLists"][$mailingList["id"]]) && $user["mailingLists"][$mailingList["id"]]['status'] == true) {
                $isRegistered = true;
            }

            if ($isRegistered === false) {
                // Attribute hash to the user
                if (!isset($user['mailingListHash']) || empty($user['mailingListHash'])) {
                    $user['mailingListHash'] = $hash;
                }

                // Add new mailing list to the user
                $user["mailingLists"][$mailingList["id"]] = array(
                    "id" => $mailingList["id"],
                    "status" => true,
                    "date" => $date
                );

                // Update user
                $wasFiltered = AbstractCollection::disableUserFilter();
                $updateResult = Manager::getService("Users")->update($user);
                AbstractCollection::disableUserFilter($wasFiltered);

                // Check the result of the update
                if ($updateResult["success"]) {
                    $response = array(
                        "success" => true,
                        "msg" => "Inscription réussie"
                    );
                } else {
                    throw new \Rubedo\Exceptions\User("Failed to update user", "Exception44");
                }
            } else {
                $success = $doNotDuplicate ? false : true;
                $response = array(
                    "success" => $success,
                    "msg" => "Vous êtes déjà inscrit à cette newsletter"
                );
            }
        } else {
            // Make the default skeleton for the user if it's a new user
            $filters = Filter::factory();
            $filters->addFilter(Filter::factory('Value')->setName('UTType')
                ->setValue("email"));
            $emailUserType = Manager::getService("UserTypes")->findOne($filters);
            $newName=$email;
            if (($userName)&&(!empty($userName))){
                $newName=$userName;
            }
            $user = array(
                "login" => $email,
                "typeId" => $emailUserType['id'],
                "fields" => array(),
                "taxonomy" => array(),
                "email" => $email,
                "name" => $newName,
                "workspace" => $mailingList["workspaces"],
                "mailingListHash" => $hash,
                "mailingLists" => array(
                    $mailingList["id"] => array(
                        "id" => $mailingList["id"],
                        "status" => true,
                        "date" => $date
                    )
                )
            );

            // Create the new user
            $createResult = Manager::getService("Users")->create($user);

            // Check the result of the creation
            if ($createResult["success"]) {
                $response = array(
                    "success" => true,
                    "msg" => "Inscription réussie"
                );
            } else {
                throw new \Rubedo\Exceptions\User("Failed to create the user", "Exception45");
            }
        }

        return $response;
    }

    /**
     * Remove a user from a specified mailing list
     *
     * @param string $mailingListId
     * @param string $email
     *
     * @return array
     *
     * @see \Rubedo\Interfaces\Collection\IMailingList::unSubscribe()
     */
    public function unSubscribe($mailingListId, $email)
    {
        $mailingList = $this->findById($mailingListId);
        if ($mailingList === null) {
            throw new \Rubedo\Exceptions\User('Invalid newsletter id', "Exception43");
        }
        $wasFiltered = AbstractCollection::disableUserFilter();
        $user = Manager::getService("Users")->findByEmail($email);
        AbstractCollection::disableUserFilter($wasFiltered);
        $user["mailingLists"][$mailingList["id"]]['status']=false;
        $updateResult=Manager::getService("Users")->update($user);
        if ($updateResult["success"]) {
            $response = true;
        } else {
            throw new \Rubedo\Exceptions\User("Failed to update user", "Exception44");
        }
        return($response);
    }

    public function getNewMessage($mailingListId)
    {
        $mailingList = $this->findById($mailingListId);
        if (!$mailingList) {
            throw new \Rubedo\Exceptions\Server('Unknown mailing list', "Exception46");
        }
        $mailService = Manager::getService('Mailer');
        $message = $mailService->getNewMessage();
        if (isset($mailingList['replyToAddress']) && !empty($mailingList['replyToAddress'])) {
            $replyTo = array();

            $replyTo[$mailingList['replyToAddress']] = isset($mailingList['replyToName']) ? $mailingList['replyToName'] : $mailingList['replyToAddress'];

            $message->setReplyTo($replyTo);
        }

        if (isset($mailingList['fromAddress']) && !empty($mailingList['fromAddress'])) {
            $from = array();

            $from[$mailingList['fromAddress']] = isset($mailingList['fromName']) ? $mailingList['fromName'] : $mailingList['fromAddress'];

            $message->setFrom($from);
        }

        if (isset($mailingList['returnPathAddress']) && !empty($mailingList['returnPathAddress'])) {
            $returnPath = $mailingList['returnPathAddress'];
            $message->setReturnPath($returnPath);
        }

        return $message;
    }
}
