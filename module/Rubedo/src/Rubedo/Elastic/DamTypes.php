<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2016, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr.
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 *
 * @copyright  Copyright (c) 2012-2016 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace Rubedo\Elastic;

/**
 * Service to handle Dam types mapping.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class DamTypes extends DataAbstract
{
    /**
     * Mapping.
     */
    protected static $_mapping = [
        'objectType' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'typeId' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'author' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'createUser' => [
            'type' => 'object',
            'store' => 'yes',
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => 'yes',
                ],
                'fullName' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => 'yes',
                ],
                'login' => [
                    'type' => 'string',
                    'index' => 'no',
                    'store' => 'no',
                ],
            ],
        ],
        'startPublicationDate' => [
            'type' => 'integer',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'endPublicationDate' => [
            'type' => 'integer',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'lastUpdateTime' => [
            'type' => 'date',
            'store' => 'yes',
        ],
        'autocomplete_nonlocalized' => [
            'type' => 'completion',
            'index_analyzer' => 'simple',
            'search_analyzer' => 'simple',
            'payloads' => true,
            'preserve_position_increments' => false,
        ],
        'text' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'target' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'writeWorkspace' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'availableLanguages' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'version' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'damType' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'mainFileType' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'fileSize' => [
            'type' => 'integer',
            'store' => 'yes',
        ],
        'file' => [
            'type' => 'binary',
        ],
    ];

    /**
     * Constructor.
     */
    public function init($indexName = null)
    {
        if ($indexName) {
            $this->_indexName = $indexName;
        } else {
            $this->_indexName = $this->getIndexNameFromConfig('damIndex');
        }
        parent::init();

        parent::getAnalyzers();
        parent::getLanguages();
    }

    /**
     * Build mapping for object.
     *
     * @param array $data
     *
     * @return array
     */
    public function getMapping(array $data)
    {
        $mapping = [];

        if (isset($data ['fields']) && is_array($data ['fields'])) {

            // get vocabularies
            $vocabularies = $this->getVocabularies($data);

            // add mapping for autocomplete in every active language
            foreach ($this->_activeLanguages as $lang) {
                $locale = !in_array($lang ['locale'], $this->_activeAnalysers) ? 'default' : $lang ['locale'];
                $mapping ['autocomplete_'.$lang ['locale']] = [
                    'type' => 'completion',
                    'index_analyzer' => $locale.'_analyzer',
                    'search_analyzer' => $locale.'_analyzer',
                    'payloads' => true,
                    'preserve_position_increments' => false,
                ];
            }

            // Add Taxonomies
            foreach ($vocabularies as $vocabularyName) {
                $mapping ['taxonomy.'.$vocabularyName] = [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => 'yes',
                ];
            }

            // Add system fields : title
            $fields = $data['fields'];

            $fields [] = [
                'cType' => 'system',
                'config' => [
                    'name' => 'title',
                    'fieldLabel' => 'text',
                    'searchable' => true,
                    'localizable' => true,
                ],
            ];

            // unmapped fields are not allowed in fields, i18n and productProperties
            $mapping ['fields'] = [
                'dynamic' => false,
                'type' => 'object',
            ];

            foreach ($this->_activeLanguages as $lang) {
                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] = [
                    'dynamic' => false,
                    'type' => 'object',
                ];
            }

            // add fields mappings
            foreach ($fields as $field) {
                $this->addFieldMapping($field, $mapping);
            }
        }

        return array_merge(self::$_mapping, $mapping);
    }

    /**
     * Set mapping for new or updated dam type.
     *
     * @param string $typeId
     * @param array  $data
     *                       dam type data
     *
     * @return array
     */
    public function setMapping($typeId, $data)
    {
        // Delete existing content type
        $this->deleteMapping($this->_indexName, $typeId);

        // Create mapping
        return $this->putMapping($typeId, $this->getMapping($data));
    }

    /**
     * Delete dam type mapping.
     *
     * @param string $typeId
     *                       dam type id
     *
     * @return array
     */
    public function delete($typeId)
    {
        return $this->deleteMapping($typeId);
    }

    /**
     * Index all existing assets from given type.
     *
     * @param string $typeId
     *                       object type id
     * @param string $id
     *                       object id
     */
    public function index($typeId)
    {
        return $this->indexByType('dam', $typeId);
    }
}
