<?php

	/**
     * Parses a json file into a twig template file
     * 
	 * @param json json file to parse
	 * @param template template file to write
	 * @param id template_id in the json file
	 * 
	 * @return true if a template has been generated, or false is it hasn't.
     */	
	function parseJson($json, $template, $id)
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
			$out .= parseRows($rows,1);
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
	function parseRows($rows,$t=0) {
		$out = '';
		foreach ($rows as $key => $val) {
			$out .= tab($t).'<div class="row-fluid">'."\n";
			$t++;
			$cols = $val['columns'];
			foreach ($cols as $key => $val) {
				// in a column
				$out .= tab($t).'<div class="';
				$t++;
				if (isset($val['offset']) && $val['offset'] <> '0') {
					$out .= 'offset'.$val['offset'].' ';
				}
				$out .= 'span'.$val['span'].'">'."\n";
				if (isset($val['rows']) && $val['rows'] != null) {
					// are there any rows in this column ?
					$out .= parseRows($val['rows'],$t);
				} else {
					// put a block here
					// TODO : block identifier
					$out .= tab($t).'{% block zone_x %}{% endblock %}'."\n";
				}
				$t--;
				$out .= tab($t).'</div>'."\n";
			}
			$t--;
			$out .= tab($t).'</div>'."\n";
		}
		return $out;
	}
	
	function tab($n) {
		$out = '';
		for ($i = 0; $i < $n; $i++) $out.="\t";
		return $out;
	}
	
	
		

	