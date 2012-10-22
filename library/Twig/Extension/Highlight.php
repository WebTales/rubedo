<?php

class Twig_Extension_Highlight extends Twig_Extension
{

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'highlight' => new Twig_Filter_Method($this,'highlight'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'highlight';
    }
	
	/**
	 * Delegates translation to Zend_Translate
	 * 
	 * @param sentence content in which the expression is searched
	 * @param expr expression to highlight
	 * @return highlighted text
	 */
	public function highlight($sentence, $expr) {
		$words = explode(" ", $expr);
		foreach ($words as $word) {
			$word = htmlentities($word, ENT_NOQUOTES, 'UTF-8');
			$sentence = preg_replace('/(' . $word . ')/i','<span class="highlight">\1</span>', $sentence);
		}
        return $sentence;
    }
}