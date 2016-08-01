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
 * Service to handle Contents indexing and searching.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class Dam extends DataAbstract
{
    protected $service = 'DamTypes';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_indexName = $this->getIndexNameFromConfig('damIndex');
        parent::init();
    }

    /**
     * Create or update index for existing dam.
     *
     * @param obj  $data dam data
     * @param bool $bulk
     *
     * @return array
     */
    public function index($data, $bulk = false)
    {
        if (!isset($data['typeId']) || isset($data['mainFileType']) && $data['mainFileType'] === 'Resource') {
            return;
        }

        $typeId = $data['typeId'];

        // get available languages
        $availableLanguages = array_keys($data['i18n']);

        // Initialize data array to push into index
        $indexData = [
            'objectType' => 'dam',
            'damType' => $typeId,
            'text' => $data['title'],
            'fields' => isset($data['fields']) ? $data['fields'] : null,
            'mainFileType' => isset($data['mainFileType']) ? $data['mainFileType'] : null,
            'i18n' => $data['i18n'],
            'writeWorkspace' => $data['writeWorkspace'],
            'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string) ($data['lastUpdateTime'] *
                1000) : 0,
            'createUser' => $data['createUser'],
            'availableLanguages' => array_keys($data['i18n']),
            'fileSize' => isset($data['fileSize']) ? (integer) $data['fileSize'] : 0,
            'version' => $data['version'],
        ];

        // Normalize date fields
        $damType = $this->_getType('DamTypes', $typeId);
        foreach ($damType['fields'] as $field) {
            if ($field['cType'] == 'datefield' or $field['cType'] == 'Ext.form.field.Date') {
                $fieldName = $field['config']['name'];
                if (isset($indexData['fields'][$fieldName])) {
                    $ts = intval($indexData['fields'][$fieldName]);
                    $indexData['fields'][$fieldName] = mktime(0, 0, 0, date('m', $ts), date('d', $ts), date('Y', $ts)) * 1000;
                }
            }
        }

        // Add taxonomy
        if (isset($data['taxonomy'])) {
            $taxonomyService = $this->_getService('Taxonomy');
            $taxonomyTermsService = $this->_getService('TaxonomyTerms');

            foreach ($data['taxonomy'] as $vocabulary => $terms) {
                if (!is_array($terms)) {
                    $terms = [$terms];
                }
                $taxonomy = $taxonomyService->findById($vocabulary);
                $termsArray = [];

                foreach ($terms as $term) {
                    if ($term == 'all') {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);

                    if (!$term) {
                        continue;
                    }

                    if (!isset($termsArray[$term['id']])) {
                        $termsArray[$term['id']] = $taxonomyTermsService->getAncestors(
                            $term);
                        $termsArray[$term['id']][] = $term;
                    }

                    foreach ($termsArray[$term['id']] as $tempTerm) {
                        $indexData['taxonomy'][$taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }

        // Add read workspace
        $indexData['target'] = [];
        if (isset($data['target'])) {
            foreach ($data['target'] as $target) {
                $indexData['target'][] = (string) $target;
            }
        }

        // Add autocompletion
        $mediaThumbnail = $this->_getService('Url')->mediaThumbnailUrl($data['id']);
        foreach ($availableLanguages as $lang) {
            $title = isset($data['i18n'][$lang]['fields']['title']) ? $data['i18n'][$lang]['fields']['title'] : $data['title'];
            $indexData['autocomplete_'.$lang] = [
                'input' => $title,
                'output' => $title,
                'payload' => '{ "type" : "dam",  "id" : "'.$data['id'].'",  "thumbnail" : "'.$mediaThumbnail.'"}',
            ];
        }

        // Add document
        if (isset($data['originalFileId']) && $data['originalFileId'] != '') {
            $indexedFiles = [
                'application/pdf',
                'application/rtf',
                'text/html',
                'text/plain',
                'text/richtext',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.oasis.opendocument.text',
                'application/vnd.oasis.opendocument.spreadsheet',
                'application/vnd.oasis.opendocument.presentation',
            ];
            $mime = explode(';', $data['Content-Type']);

            if (in_array($mime[0], $indexedFiles)) {
                $mongoFile = $this->_getService('Files')->FindById($data['originalFileId']);
                $indexData['file'] = base64_encode($mongoFile->getBytes());
            }
        }

        // Add dam to dam type index
        $body = [
            ['index' => ['_id' => $data['id']]],
            $indexData,
        ];
        if (!$bulk) {
            $params = [
                'index' => $this->_indexName,
                'type' => $typeId,
                'body' => $body,
            ];
            $this->_client->bulk($params);

            $this->_client->indices()->refresh(['index' => $this->_indexName]);
        } else {
            return $body;
        }
    }

    /**
     * Delete existing dam from index.
     *
     * @param string $typeId
     *                       dam type id
     * @param string $id
     *                       dam id
     */
    public function delete($typeId, $id)
    {
        $params = [
            'index' => $this->_indexName,
            'type' => $typeId,
            'id' => $id,
        ];
        $this->_client->delete($params);
    }
}
