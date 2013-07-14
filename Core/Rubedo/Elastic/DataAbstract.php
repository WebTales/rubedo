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
namespace Rubedo\Elastic;

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataAbstract
{

    /**
     * Default value of hostname
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultHost;

    /**
     * Default transport value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultTransport;

    /**
     * Default port value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultPort;

    /**
     * Elastica Client
     *
     * @var \Elastica_Client
     */
    protected $_client;

    /**
     * Configuration options
     *
     * @var array
     */
    protected static $_options;

    /**
     * Object which represent the content ES index
     *
     * @var \Elastica_Index
     */
    protected static $_content_index;

    /**
     * Object which represent the default ES index param
     * @TODO : get param from config
     * 
     * @var \Elastica_Index
     */
    protected static $_content_index_param = array(
        "index" => array(
            "number_of_shards" => 1,
            "number_of_replicas" => 0,
        	"analysis" => array(
        		"filter"=> array(
        			"ar_stop_filter"=> array(
        				"type"=> "stop",
        				"stopwords"=> array("_arabic_")
        			),
	        		"bg_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_bulgarian_")
	        		),
	        		"ca_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_catalan_")
	        		),
	        		"cs_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_czech_")
	        		),
	        		"da_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_danish_")
	        		),
	        		"de_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_german_")
	        		),
	        		"de_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "minimal_german"
	        		),
	        		"el_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_greek_")
	        		),
	        		"en_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_english_")
	        		),
	        		"en_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "minimal_english"
	        		),
	        		"es_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_spanish_")
	        		),
	        		"es_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "light_spanish"
	        		),
	        		"eu_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_basque_")
	        		),
	        		"fa_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_persian_")
	        		),
	        		"fi_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_finnish_")
	        		),
	        		"fi_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "light_finish"
	        		),
	        		"fr_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_french_")
	        		),
	        		"fr_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "minimal_french"
	        		),
	        		"hi_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_hindi_")
	        		),
	        		"hu_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_hungarian_")
	        		),
	        		"hu_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "light_hungarian"
	        		),
	        		"hy_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_armenian_")
	        		),
	        		"id_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_indonesian_")
	        		),
	        		"it_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_italian_")
	        		),
	        		"it_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "light_italian"
	        		),
	        		"nl_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_dutch_")
	        		),
	        		"no_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_norwegian_")
	        		),
	        		"pt_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_portuguese_")
	        		),
	        		"pt_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "minimal_portuguese"
	        		),
	        		"ro_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_romanian_")
	        		),
	        		"ru_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_russian_")
	        		),
	        		"ru_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "light_russian"
	        		),
	        		"sv_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_swedish_")
	        		),
	        		"sv_stem_filter"=> array(
		        		"type"=> "stemmer",
		        		"name"=> "light_swedish"
	        		),
	        		"tr_stop_filter"=> array(
		        		"type"=> "stop",
		        		"stopwords"=> array("_turkish_")
	        		)
        		),
        		"analyzer"=> array(
	        		"ar_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "ar_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"bg_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "bg_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"ca_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "ca_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"cs_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "cs_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"da_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "da_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"de_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "de_stop_filter", "de_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"el_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "el_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"en_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "en_stop_filter", "en_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"es_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "es_stop_filter", "es_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"eu_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "eu_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"fa_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "fa_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"fi_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "fi_stop_filter", "fi_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"fr_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "fr_stop_filter", "fr_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"he_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "he_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"hi_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "hi_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"hu_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "hu_stop_filter", "hu_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"hy_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "hy_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"id_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "id_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"it_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "it_stop_filter", "it_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"ja_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "kuromoji_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"ko_analyzer"=> array(
		        		"type"=> "cjk"
	        		),
	        		"nl_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "nl_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"no_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "no_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"pt_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "pt_stop_filter", "pt_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"ro_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "ro_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"ru_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "ru_stop_filter", "ru_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"sv_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "sv_stop_filter", "sv_stem_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"tr_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer", "tr_stop_filter"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"zh_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "smartcn_sentence",
		        		"filter"=> array("icu_folding", "icu_normalizer", "smartcn_word"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"default"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "icu_tokenizer",
		        		"filter"=> array("icu_folding", "icu_normalizer"),
		        		"char_filter"=> array("html_strip")
	        		),
	        		"wp_raw_lowercase_analyzer"=> array(
		        		"type"=> "custom",
		        		"tokenizer"=> "keyword",
		        		"filter"=> array("lowercase")
	        		)
        		),
        		"tokenizer"=> array(
	        		"kuromoji"=> array(
		        		"type"=> "kuromoji_tokenizer",
		        		"mode"=> "search"
        			)
				)
        	)       	
        )
    );

    /**
     * Object which represent the dam ES index
     *
     * @var \Elastica_Index
     */
    protected static $_dam_index;

    /**
     * Object which represent the default dam ES index param
     * @TODO : get param from config
     * 
     * @var \Elastica_Index
     */
    protected static $_dam_index_param = array(
        'index' => array(
            'number_of_shards' => 1,
            'number_of_replicas' => 0
        )
    );

    /**
     * Initialize a search service handler to index or query Elastic Search
     *
     * @see \Rubedo\Interfaces\IDataIndex::init()
     * @param string $host
     *            http host name
     * @param string $port
     *            http port
     */
    public function init ($host = null, $port = null)
    {
        if (is_null($host)) {
            $host = self::$_options['host'];
        }
        
        if (is_null($port)) {
            $port = self::$_options['port'];
        }
        
        $this->_client = new \Elastica_Client(array(
            'port' => $port,
            'host' => $host
        ));
        
        self::$_content_index = $this->_client->getIndex(self::$_options['contentIndex']);
        
        // Create content index if not exists
        if (! self::$_content_index->exists()) {
            self::$_content_index->create(self::$_content_index_param, true);
        }
        self::$_dam_index = $this->_client->getIndex(self::$_options['damIndex']);
        
        // Create dam index if not exists
        if (! self::$_dam_index->exists()) {
            self::$_dam_index->create(self::$_dam_index_param, true);
        }
    }

    /**
     * Set the options for ES connection
     *
     * @param array $options            
     */
    public static function setOptions (array $options)
    {
        self::$_options = $options;
    }

    /**
     *
     * @return the $_options
     */
    public static function getOptions ()
    {
        return self::$_options;
    }

    /**
     * Set the options for the content-index
     *
     * @param string $host            
     */
    public static function setContentIndexOption (array $options)
    {
        self::$_content_index_param = $options;
    }

    /**
     * Return the ElasticSearch Server Version
     * 
     * @return string
     */
    public function getVersion ()
    {
        $data = $this->_client->request('/', 'GET')->getData();
        if (isset($data['version']) && isset($data['version']['number'])) {
            return $data['version']['number'];
        }
        return null;
    }
}
