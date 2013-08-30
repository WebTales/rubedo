<?php
namespace Rubedo\Templates;

class Translate extends \Twig_Extension
{

    protected $lang;

    public function __construct ($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters ()
    {
        return array(
            'trans' => new \Twig_Filter_Method($this, 'translate')
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName ()
    {
        return 'translate';
    }

    /**
     * Delegates translation to Zend_Translate
     *
     * @param
     *            text to translate
     * @return translated text
     */
    public function translate ($text)
    {
        throw \Exception('obsolete method');
    }
}