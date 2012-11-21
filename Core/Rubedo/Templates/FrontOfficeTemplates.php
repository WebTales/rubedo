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
		
		$this->_twig->addExtension(new \Twig_Extension_Debug());
		
		
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
	
	public static function parseJson($json, $template, $id)
    {
    	$masque = json_decode(file_get_contents($json), TRUE);
		
		$found = false;
		foreach ($masque as $key => $val) {
			if ($val['id'] == $id) {
				$found = true;
				$rows = $val['rows'];
				break;
			}
		}
		
		if ($found) {
			$out = '<div class="container-fluid">'."\n";
			$out .= self::parseRows($rows,1);
			$out .= '</div>'."\n";
			
			$file = fopen($template, 'w+');
	 
			fputs($file, $out);
			 
			fclose($file);
			return true;
		} else {
			return false;
		}
	}
	
	/**
     * Parses a rows
     * 
	 * @param rows rows to parse
	 * @param t number of tabulations
	 * @return parsed content
     */	
	public static function parseRows($rows,$t=0) {
		$out = '';
		foreach ($rows as $key => $val) {
			$out .= self::tab($t).'<div class="row-fluid">'."\n";
			$t++;
			$cols = $val['columns'];
			foreach ($cols as $key => $val) {
				// in a column
				$out .= self::tab($t).'<div class="';
				$t++;
				if (isset($val['offset']) && $val['offset'] <> '0') {
					$out .= 'offset'.$val['offset'].' ';
				}
				$out .= 'span'.$val['span'].'">'."\n";
				if (isset($val['rows']) && $val['rows'] != null) {
					// are there any rows in this column ?
					$out .= self::parseRows($val['rows'],$t);
				} else {
					// put a block here
					// TODO : block identifier
					$out .= self::tab($t).'{% block zone_x %}{% endblock %}'."\n";
				}
				$t--;
				$out .= self::tab($t).'</div>'."\n";
			}
			$t--;
			$out .= self::tab($t).'</div>'."\n";
		}
		return $out;
	}
	
	public static function tab($n) {
		$out = '';
		for ($i = 0; $i < $n; $i++) $out.="\t";
		return $out;
	}

}
