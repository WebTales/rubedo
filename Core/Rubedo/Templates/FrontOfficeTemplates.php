<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Templates;

use Rubedo\Interfaces\Templates\IFrontOfficeTemplates;
/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FrontOfficeTemplates implements  IFrontOfficeTemplates
{

    /**
     * twig environnelent object
     * @var \Twig_Environment
     */
    protected $_twig;

    /**
     * Twig options array
     * @var array
     */
    protected $_options = array();

    /**
     * initialise Twig Context
     * @param string $lang current language
     */
    public function init($lang = 'default') {
        $this->_options = array('templateDir' => APPLICATION_PATH . "/../data/templates", 'cache' => APPLICATION_PATH . "/../cache/twig", 'debug' => true, 'auto_reload' => true);
        if(isset($this->_service)) {
            $this->_options = $this->_service->getCurrentOptions();
        }

        $loader = new \Twig_Loader_Filesystem($this->_options['templateDir']);
        $this->_twig = new \Twig_Environment($loader, $this->_options);
        $this->_twig->addExtension(new \Twig_Extension_Translate($lang));

        $this->_twig->addExtension(new \Twig_Extension_Highlight());

        $this->_twig->addExtension(new \Twig_Extension_Intl());
    }

    /**
     * render a twig template given an array of data
     * @param string $template template name
     * @param array $vars array of data to be rendered
     * @return string HTML produced by twig
     */
    public function render($template, array $vars) {
        $templateObj = $this->_twig->loadTemplate($template);
        return $templateObj->render($vars);
    }

}
