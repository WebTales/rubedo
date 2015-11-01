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
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;
/**
 * Class TaxonomyResource
 * @package RubedoAPI\Rest\V1
 */
class TaxonomyResource extends AbstractResource
{
    /**
     * @var static
     */
    private $taxonomyTermsService;
    /**
     * Cache lifetime for api cache (only for get and getEntity)
     * @var int
     */
    public $cacheLifeTime=600;
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Taxonomy')
            ->setDescription('Deal with taxonomy')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get taxonomy terms')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('vocabularies')
                            ->setRequired()
                            ->setDescription('Taxonomy vocabularies to use')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('taxo')
                            ->setDescription('Taxonomy terms array for selected vocabularies')
                    );
            });
        $this->taxonomyTermsService = Manager::getService('TaxonomyTerms');
    }
    /**
     * Get from /taxonomy
     *
     * @param $params
     * @return array
     */
    public function getAction($params)
    {
        $taxoArray=array();
        if (!is_array($params["vocabularies"])){
            throw new APIRequestException("Vocabularies array is required", 400);
        }
        $taxonomyGetConfig=$params["vocabularies"];
        foreach($taxonomyGetConfig as $value){
            $myTaxo=$this->getTaxonomyCollection()->findById($value);
            if ($myTaxo) {
                $filters = Filter::factory();
                $filters->addFilter(Filter::factory('Value')->setName('vocabularyId')->setValue($value));
                $taxonomyTerms = $this->taxonomyTermsService->getList($filters);
                foreach ($taxonomyTerms['data'] as &$term) {
                    $term = array_intersect_key(
                        $term,
                        array_flip(
                            array(
                                'id',
                                'text',
                                'parentId',
                                'orderValue',
                            )
                        )
                    );
                }
                $taxoArray[] = array(
                    "vocabulary" =>  array_intersect_key(
                        $myTaxo,
                        array_flip(
                            array(
                                'id',
                                'name',
                            )
                        )
                    ),
                    "terms" => $taxonomyTerms['data']
                );
            }
        }
        return [
            'success' => true,
            'taxo' => $taxoArray,
        ];
    }
}