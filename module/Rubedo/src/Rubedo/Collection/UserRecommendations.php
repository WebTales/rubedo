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
use Rubedo\Services\Manager;

/**
 * Service to handle Item to User recommendations
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class UserRecommendations extends AbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'UserRecommendations';
        parent::__construct();
    }
    
    public function init()
    {
    }
    
    public function read($limit = 50){
        $fingerprint=Manager::getService("Session")->get("fingerprint");
        $pipeline=array();
        $pipeline[]=array(
            '$match'=>array(
                'userFingerprint'=> $fingerprint
            )
        );
        $pipeline[]=array(
            '$unwind'=>'$reco'
        );
        $pipeline[]=array(
            '$project'=>array(
            	'_id' => 0,
            	'id' => '$reco.cid',
            	'score' => '$reco.score'
            )
        );
        $pipeline[]=array(
        		'$sort'=>array(
        				'score'=>-1
        		)
        );
        $pipeline[]=array(
            '$limit'=> $limit
        );        
        
        $response=$this->_dataService->aggregate($pipeline);
        
        if ($response['ok']){
            return array(
                "data"=>$response['result'],
                "total"=>count($response['result']),
                "success"=>true
            );
        } else {
            return array(
                "msg"=>$response['errmsg'],
                "success"=>false
            );
        }

    }
    
    public function build() {
    
    	$code = "
			db.tmpRecommendations.drop();
			db.ContentViewLog.find().snapshot().forEach(function(foo) {
				var v = db.ItemRecommendations.findOne({_id:foo.contentId});
				if (v) {
					for (var content in v.value) {
						db.UserRecommendations.update(
							{ userFingerprint: foo.userFingerprint },
							{ \$addToSet : {reco: {cid: content, score:  v.value[content]}}},
							{ upsert: true }
						);
						db.UserRecommendations.update(
							{ userFingerprint: foo.userFingerprint, reco: { \$elemMatch: { cid: content } } },
							{ \$inc: {'reco.$.score' : v.value[content]}}
						);
						var action = {};
						action[foo.contentId] = '';
					}
					db.ContentViewLog.remove(foo);
					db.UserRecommendations.update({ userFingerprint: foo.userFingerprint },{\$pull: { reco: {'cid': foo.ContentId}}});
				}
			});";
    
    	$response = $this->_dataService->execute($code);
    
    	return $response;
    }
    
    public function flush() {
    	
    	$code = "db.UserRecommendations.remove();";
    	$response = $this->_dataService->execute($code);
    	
    	return $response;
    	
    }
}