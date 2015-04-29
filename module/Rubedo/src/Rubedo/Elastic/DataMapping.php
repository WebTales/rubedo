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
namespace Rubedo\Elastic;

use Rubedo\Services\Manager;

/**
 * Class implementing the Rubedo API to Elastic Search mapping services using
 * PHP elasticsearch API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataMapping extends DataAbstract
{

	/**
	 * Common system mapping for contents, dam and users
	 */
	protected $_systemMapping = [
		'objectType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'typeId' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'author' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'createUser' => [
			'type' => 'object',
			'store' => 'yes',
			'properties' => [
				'id' => [
				'type' => 'string',
				'index' => 'not_analyzed',
				'store' => 'yes'
				],
				'fullName' => [
					'type' => 'string',
					'index' => 'not_analyzed',
					'store' => 'yes'
				],
				'login' => [
					'type' => 'string',
					'index' => 'no',
					'store' => 'no'
				]
			]
		],
		'startPublicationDate' => [
			'type' => 'integer',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'endPublicationDate' => [
			'type' => 'integer',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'lastUpdateTime' => [
			'type' => 'date',
			'store' => 'yes'
		],
		'autocomplete_nonlocalized' => [
			'type' => 'completion',
			'index_analyzer' => 'simple',
			'search_analyzer' => 'simple',
			'payloads' => true,
			'preserve_position_increments' => false
		]
	];
	
	/**
	 * Common mapping for contents & dam
	 */	
	protected $_contentAndDamMapping = [
		'text' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'target' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'writeWorkspace' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'availableLanguages' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'version' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		]
			
	];
	
	/**
	 * Specific mapping for contents
	 */
	protected $_contentMapping = [
		'summary' => [
			'type' => 'string',
			'store' => 'yes'
		],
		'contentType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'online' => [
			'type' => 'boolean',
			'store' => 'yes'
		]	
	];

	/**
	 * Specific mapping for dam
	 */
	protected $_damMapping = [
		'damType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'fileSize' => [
			'type' => 'integer',
			'store' => 'yes'
		],
		'file' => [
			'type' => 'attachment'
		]	
	];

	/**
	 * Specific mapping for users
	 */
	protected $_userMapping = [
		'email' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'photo' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'userType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		]	
	];

	/**
	 * Active languages
	 */	
	protected $_activeLanguages;
	
	/**
	 * Active analyzers
	 */
	protected $_activeAnalysers;
	
	public function __construct()
	{
		parent::__construct();
		
		// get active languages
		$languages = Manager::getService('Languages');
		$this->_activeLanguages = $languages->getActiveLanguages();
		
		// get active analyzers
		$this->_activeAnalysers = array_keys($this::$_index_settings['analysis'] ['analyzer']);
	}
	
    /**
     * Returns mapping from content or dam type data
     *
     * @param array $data
     *            string $type = 'content' or 'dam'
     *
     * @return array
     */
    public function getIndexMapping(array $data, $type)
    {
        $mapping = [];

        if (isset ($data ['fields']) && is_array($data ['fields'])) {

            // get vocabularies
            $vocabularies = $this->_getVocabularies($data);

            // Set mapping
            if ($type == 'content') {
            	$specificMapping = $this->_contentMapping;
            }
            if ($type == 'dam') {
            	$specificMapping = $this->_damMapping;
            }
            $mapping = array_merge($this->_systemMapping,$this->_contentAndDamMapping, $specificMapping);

            // add mapping for autocomplete in every active language
            foreach ($this->_activeLanguages as $lang) {
                $locale = !in_array($lang ['locale'], $this->_activeAnalysers) ? 'default' : $lang ['locale'];
                $mapping ['autocomplete_' . $lang ['locale']] = [
                    'type' => 'completion',
                    'index_analyzer' => $locale . '_analyzer',
                    'search_analyzer' => $locale . '_analyzer',
                    'payloads' => true,
                    'preserve_position_increments' => false
                ];
            }

            // Add Taxonomies
            foreach ($vocabularies as $vocabularyName) {
                $mapping ["taxonomy." . $vocabularyName] = [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => 'yes'
                ];
            }

            // Add system fields : text and summary for contents, title for dam
            $fields = $data['fields'];
            if ($type == 'content') {
                $fields [] = [
                    'cType' => 'system',
                    'config' => [
                        'name' => 'text',
                        'fieldLabel' => 'text',
                        'searchable' => true,
                        'localizable' => true
                    ]
                ];
                $fields [] = [
                    'cType' => 'system',
                    'config' => [
                        'name' => 'summary',
                        'fieldLabel' => 'summary',
                        'searchable' => true,
                        'localizable' => true
                    ]
                ];
            }

            if ($type == 'dam') {
                $fields [] = [
                    'cType' => 'system',
                    'config' => [
                        'name' => 'title',
                        'fieldLabel' => 'text',
                        'searchable' => true,
                        'localizable' => true
                    ]
                ];
            }

            // unmapped fields are not allowed in fields, i18n and productProperties
            $mapping ['fields'] = [
                'dynamic' => false,
                'type' => 'object'
            ];

            foreach ($this->_activeLanguages as $lang) {
                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] = [
                    'dynamic' => false,
                    'type' => 'object'
                ];
            }

            // Add properties for product only
            if (isset($data['productType']) && $data['productType'] != 'none') {
                $mapping ['productProperties'] = [
                    'dynamic' => true,
                    'store' => 'yes',
                    'type' => 'object',
                    'properties'=>[
                        'variations'=>[
                            'dynamic' => true,
                            'type' => 'object',
                            'properties'=>[]
                        ]
                    ]

                ];
                $mapping['encodedProductProperties']=[
                    'store'=>'yes',
                    'type'=>'string',
                    'index'=>'no'
                ];

                $mapping['isProduct'] = [
                    'type' => 'boolean',
                    'store' => 'yes'
                ];
            }

            // add fields mappings
            foreach ($fields as $field) {
				$this->_addFieldMapping($field,$mapping);
            }
        }

        return $mapping;
    }

    /**
     * Returns mapping from user type data
     *
     * @param array $data
     *
     * @return array
     */
    public function getUserIndexMapping(array $data)
    {
        $mapping = [];

        if (isset($data['fields']) && is_array($data['fields'])) {

            // get vocabularies
            $vocabularies = $this->_getVocabularies($data);

            // Set mapping for user
            $mapping = array_merge($this->_systemMapping, $this->_userMapping);

            // Add Taxonomies
            foreach ($vocabularies as $vocabularyName) {
                $mapping["taxonomy." . $vocabularyName] = [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => 'yes'
                ];
            }

            // Add Fields

            $fields = $data['fields'];

            // Add system fields : email and name for user
            $fields[] = [
                'cType' => 'system',
                'config' => [
                    'name' => 'email',
                    'fieldLabel' => 'email',
                    'searchable' => false
                ]
            ];
            $fields[] = [
                'cType' => 'system',
                'config' => [
                    'name' => 'name',
                    'fieldLabel' => 'name',
                    'searchable' => true,
                    'notAnalyzed' => false
                ]
            ];

            // unmapped fields are not allowed in fields and i18n
            $mapping['fields'] = [
                'dynamic' => false,
                'type' => 'object'
            ];

            // add fields mappings
            foreach ($fields as $field) {
            	$this->_addFieldMapping($field,$mapping);
            }
            
        }
        return $mapping;
    }

    /**
     * Return all the vocabularies contained in the id list
     *
     * @param array $data
     *            contain vocabularies id of the current object
     * @return array
     */
    protected function _getVocabularies($data)
    {
        $vocabularies = [];
        foreach ($data['vocabularies'] as $vocabularyId) {
            $vocabulary = Manager::getService('Taxonomy')->findById(
                $vocabularyId);
            $vocabularies[] = $vocabulary['id'];
        }

        return $vocabularies;
    }
    
    protected function _addFieldMapping($field,&$mapping) {
    	
    	// Only searchable fields get indexed
    	if ($field ['config'] ['searchable']) {
    	
    		$name = $field ['config'] ['name'];
    		$store = (isset ($field ['config'] ['returnInSearch']) && $field ['config'] ['returnInSearch'] == FALSE) ? 'no' : 'yes';
    		$notAnalyzed = (isset ($field ['config'] ['notAnalyzed']) && $field ['config'] ['notAnalyzed']) ? TRUE : FALSE;
    	
    		// For classical fields
    		if (!isset($field ['config'] ['useAsVariation']) or ($field ['config'] ['useAsVariation'] == false)) {
    	
    			switch ($field ['cType']) {
    				case 'datefield' :
    					$config = [
    						'type' => 'string',
    						'store' => $store
    					];
    					if ($notAnalyzed) {
    						$config ['index'] = 'not_analyzed';
    					}
    					if (!$field ['config'] ['localizable']) {
    						$mapping ['fields'] ['properties'] [$name] = $config;
    					} else {
    						foreach ($this->_activeLanguages as $lang) {
    							$mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
    						}
    					}
    					break;
    				case 'numberfield' :
    					$config = [
    						'type' => 'float',
    						'store' => $store
    					];
    					if ($notAnalyzed) {
    						$config ['index'] = 'not_analyzed';
    					}
    					if (!$field ['config'] ['localizable']) {
    						$mapping ['fields'] ['properties'] [$name] = $config;
    					} else {
    						foreach ($this->_activeLanguages as $lang) {
    							$mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
    						}
    					}
    					break;
    				case 'document' :
    					$config = [
    						'type' => 'attachment',
    						'store' => $store
    					];
    					if ($notAnalyzed) {
    						$config ['index'] = 'not_analyzed';
    					}
    					if (!$field ['config'] ['localizable']) {
    						$mapping ['fields'] ['properties'] [$name] = $config;
    					} else {
    						foreach ($this->_activeLanguages as $lang) {
    							$mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
    						}
    					}
    					break;
    				case 'localiserField' :
    					$config = [
    						'properties' => [
    							'location' => [
    								'properties' => [
    									'coordinates' => [
    										'type' => 'geo_point',
    										'store' => 'yes'
    									]
    								]
    							],
    							'address' => [
    								'type' => 'string',
    								'store' => 'yes'
    							]
    						]
    					];
    					if (!$field ['config'] ['localizable']) {
    							$mapping ['fields'] ['properties'] [$name] = $config;
    					} else {
    						foreach ($this->_activeLanguages as $lang) {
    							$mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
    						}
    					}
    					break;
    				default :
    					// Default mapping for non localizable fields
    					if (!isset($field ['config'] ['localizable']) || !$field ['config'] ['localizable']) {
    						$config = [
    							'type' => 'string',
    							'index' => (!$notAnalyzed) ? 'analyzed' : 'not_analyzed',
    							'copy_to' => ['all_nonlocalized'],
    							'store' => $store
    						];
    						// User name particular case
    						if ($name == 'name') {
    							$config['fields']['first_letter'] = [
    								'type' => 'string',
    								'analyzer' => 'first_letter'
    							];
    						}
    						$mapping ['fields'] ['properties'] [$name] = $config;
    					} else {
    						// Mapping for localizable fields
    						foreach ($this->_activeLanguages as $lang) {
    							$locale = $lang ['locale'];
    							$fieldName = $name . '_' . $locale;
    							$_all = 'all_' . $locale;
    							$_autocomplete = 'autocomplete_' . $locale;
    							if (in_array($locale . '_analyzer', $this->_activeAnalysers)) {
    								$lg_analyser = $locale . '_analyzer';
    							} else {
    								$lg_analyser = 'default';
    							}
    							$config = [
    								'type' => 'string',
    								'index' => (!$notAnalyzed) ? 'analyzed' : 'not_analyzed',
    								'analyzer' => $lg_analyser,
    								'copy_to' => [
    									$_all
    								],
    								'store' => $store
    							];
    	
    							$mapping [$fieldName] = $config;
    							$mapping ['i18n'] ['properties'] [$locale] ['properties'] ['fields'] ['properties'] [$name] = $config;
    						}
    					}
    			}
    		} else { // Product variation field
    			$_all = 'all_nonlocalized';
    			$config = [
    				'type' => 'string',
    				'index' => 'not_analyzed',
    				'copy_to' => [
    					$_all
    				],
    				'store' => $store
    			];
    			$mapping ['productProperties']['properties']['variations']['properties'][$name] = $config;
    		}
    	}    	
    }
    
    /**
     * Index ES type for new or updated content type
     *
     * @param string $id
     *            content type id
     * @param array $data
     *            new content type
     * @return array
     */
    public function indexContentType($id, $data)
    {

        // Delete existing content type
        $this->deleteContentType($id);

        // Create mapping
        $indexMapping = $this->getIndexMapping($data, 'content');

        // Create new ES type if not empty
        if (!empty($indexMapping)) {

            // Create new type
            
        	$indexParams = [
        		'index' => self::$_content_index['name'],
        		'type' => $id,
        		'body' => [
        			$id => ['properties' => $indexMapping]
        		]
        	];
        	
            $this->_client->indices()->putMapping($indexParams);

            // Return indexed field list
            return array_flip(array_keys($indexMapping));
        } else {
            // If there is no searchable field, the new type is not created
            return [];
        }
    }

    /**
     * Index ES type for new or updated dam type
     *
     * @param string $id
     *            dam type id
     * @param array $data
     *            new content type
     * @return array
     */
    public function indexDamType($id, $data)
    {

        // Delete existing dam type
        $this->deleteDamType($id);
        
        // Create mapping
        $indexMapping = $this->getIndexMapping($data, 'dam');
       
        // If there is no searchable field, the new type is not created
        if (!empty($indexMapping)) {
            
            // Create new type           
        	$indexParams = [
        		'index' => self::$_dam_index['name'],
        		'type' => $id,
        		'body' => [
        			$id => [    
        				'_source' => ['enabled' => false],
        				'properties' => $indexMapping
            		]
        		]
        	];
        	
        	$this->_client->indices()->putMapping($indexParams);
        	
            // Return indexed field list
            return array_flip(array_keys($indexMapping));
        } else {
            return [];
        }
    }

    /**
     * Index ES type for new or updated duser type
     *
     * @param string $id
     *            user type id
     * @param array $data
     *            user type data
     * @return array
     */
    public function indexUserType($id, $data)
    {

        // Delete existing user type
        $this->deleteUserType($id);

        // Create mapping
        $indexMapping = $this->getUserIndexMapping($data);

        // If there is no searchable field, the new type is not created
        if (!empty($indexMapping)) {
        	
        	// Create new type        	
        	$indexParams = [
        		'index' => self::$_user_index['name'],
        		'type' => $id,
        		'body' => [
        			$id => [
        				'_source' => ['enabled' => false],
        				'properties' => $indexMapping
        			]
        		]
        	];

        	$this->_client->indices()->putMapping($indexParams);
        	
            // Return indexed field list
            return array_flip(array_keys($indexMapping));
        } else {
            return [];
        }
    }

    /**
     * Delete content type mapping
     *
     * @param string $typeId
     *            content type id
     * @return array
     */
    public function deleteContentType($typeId)
    {
      	$params = [
    		'index' => self::$_content_index['name'],
    		'type' => $typeId
    	];
    	if ($this->_client->indices()->existsType($params)) {
    		$this->_client->indices()->deleteMapping($params);
    	}
    }

    /**
     * Delete dam type mapping
     *
     * @param string $typeId
     *            dam type id
     * @return array
     */
    public function deleteDamType($typeId)
    {   	
    	$params = [
    		'index' => self::$_dam_index['name'],
    		'type' => $typeId
    	];
    	if ($this->_client->indices()->existsType($params)) {
    		$this->_client->indices()->deleteMapping($params);
    	}
    }

    /**
     * Delete user type mapping
     *
     * @param string $typeId
     *            user type id
     * @return array
     */
    public function deleteUserType($typeId)
    {
        $params = [
    		'index' => self::$_user_index['name'],
    		'type' => $typeId
    	];
    	if ($this->_client->indices()->existsType($params)) {
    		$this->_client->indices()->deleteMapping($params);
    	}
    }
    
}
