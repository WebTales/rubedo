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
namespace Rubedo\Templates;

Use Rubedo\Services\Manager;

/**
 * Twig extension to handle label translation based on user language.
 *
 * @author jbourdin
 *        
 */
class BackOfficeTranslate extends \Twig_Extension
{

    /**
     * current language
     *
     * @var string
     */
    protected $lang;
    
    /**
     * Default langage
     * 
     * @var string
     */
    protected $defaultLang = "en";

    /**
     * init class fetching current User Language
     */
    public function __construct ()
    {
        $this->lang = Manager::getService('CurrentUser')->getLanguage();
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters ()
    {
        return array(
            'botrans' => new \Twig_Filter_Method($this, 'translate')
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName ()
    {
        return 'BackOfficeTranslate';
    }

    /**
     * Delegates translation to translate service
     *
     * @param
     *            text to translate
     * @return translated text
     */
    public function translate ($text)
    {
        
        $label = Manager::getService('Translate')->getTranslation($text, $this->lang);
        
        if($label != "") {
            return $label;
        } else {
            return Manager::getService('Translate')->getTranslation($text, $this->defaultLang);
        }
    }
}