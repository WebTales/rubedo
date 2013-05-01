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

/**
 * Contact Form
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *
 */

class Blocks_Model_catalogueform extends Zend_Form
{
	protected $_captcha;
	
	public function __construct($options = null, $captcha = false)
	{
		$this->_captcha = $captcha;
		parent::__construct($options);
	}
	
	public function init()
    {
    	$request = new Zend_Controller_Request_Http();
    	
    	$this->setMethod('post');
    	$this->setAttrib('action', $this->getView()->baseUrl().$request->getPathInfo());
    	$this->setAttrib('class','form-horizontal');
    	
    	$checkboxDecorator = array(
    	    'ViewHelper',
    	    'Errors',
    	    'Label',
    	    array('HtmlTag', array('tag' => 'div', 'class' => 'controls')),
    	    array('decorator' => array('Holder' => 'HtmlTag'), 'options' => array('tag' => 'div', 'class' => 'control-group')),
    	);
    	
    	$pdf = new Zend_Form_Element_Checkbox("pdf");
    	$pdf->setLabel("Je souhaite recevoir le catalogue au format PDF");
    	$pdf->setAttrib("id", "checkbox-pdf-format");
    	$pdf->setDecorators($checkboxDecorator);
    	
    	$papier = new Zend_Form_Element_Checkbox("papier");
    	$papier->setLabel("Je souhaite recevoir le catalogue au format papier");
    	$papier->setAttrib("id", "checkbox-paper-format");
    	$papier->setDecorators($checkboxDecorator);
    	
    	$civilite = new Zend_Form_Element_Select("civilite");
    	$civilite->setLabel("Civilité *");
    	$civilite->setMultiOptions(array(
    	    ""             => "",
    	    "Madame"       => "Madame",
    	    "Monsieur"     => "Monsieur",
    	));
    	$civilite->setRequired(true);
    	$civilite->setAttrib("required", "");
    	$civilite->removeDecorator('HtmlTag');
    	if ($civilite->getDecorator('label')) {
    	    $civilite->removeDecorator('Label');
    	    $civilite->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $civilite->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $civilite->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group'));
    	}
    	
    	
    	$titre = new Zend_Form_Element_Select("titre");
    	$titre->setLabel("Titre");
    	$titre->setMultiOptions(array(
    	    ""             => "",
    	    "Dr."          => "Dr.",
    	));
    	$titre->removeDecorator('HtmlTag');
    	if ($titre->getDecorator('label')) {
    	    $titre->removeDecorator('Label');
    	    $titre->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $titre->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $titre->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group'));
    	}
    	
    	$fonction = new Zend_Form_Element_Select("fonction");
    	$fonction->setLabel("Fonction");
    	$fonction->setMultiOptions(array(
    	    ""             => "",
    	    "Assistante"   => "Assistante",
    	    "Prothésiste"  => "Prothésiste",
    	));
    	$fonction->removeDecorator('HtmlTag');
    	if ($fonction->getDecorator('label')) {
    	    $fonction->removeDecorator('Label');
    	    $fonction->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $fonction->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $fonction->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group'));
    	}
    	
    	$nom = new Zend_Form_Element_Text("nom");
    	$nom->setLabel("Nom *");
    	$nom->setRequired(true);
    	$nom->setAttrib("required", "");
    	$nom->setAttrib("placeholder", "Nom");
    	$nom->removeDecorator('HtmlTag');
    	if ($nom->getDecorator('label')) {
    	    $nom->removeDecorator('Label');
    	    $nom->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $nom->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $nom->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group'));
    	}
    	
    	$prenom = new Zend_Form_Element_Text("prenom");
    	$prenom->setLabel("Prenom *");
    	$prenom->setRequired(true);
    	$prenom->setAttrib("required", "");
    	$prenom->setAttrib("placeholder", "Prenom");
    	$prenom->removeDecorator('HtmlTag');
    	if ($prenom->getDecorator('label')) {
    	    $prenom->removeDecorator('Label');
    	    $prenom->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $prenom->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $prenom->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group'));
    	}
    	
    	$raisonSociale = new Zend_Form_Element_Text("raison-sociale");
    	$raisonSociale->setLabel("Raison sociale");
    	$raisonSociale->setAttrib("placeholder", "Raison sociale");
    	$raisonSociale->removeDecorator('HtmlTag');
    	if ($raisonSociale->getDecorator('label')) {
    	    $raisonSociale->removeDecorator('Label');
    	    $raisonSociale->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $raisonSociale->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $raisonSociale->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group'));
    	}
    	
    	$email = new Zend_Form_Element_Text('email');
    	$email->setLabel('E-mail *');
    	$email->setAttrib("class", "required");
    	$email->addValidator('EmailAddress');
    	$email->setAttrib("placeholder", "E-mail");
    	$email->removeDecorator('HtmlTag');
    	if ($email->getDecorator('label')) {
    	    $email->removeDecorator('Label');
    	    $email->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $email->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $email->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group catalogue-pdf', "style" => "display:none;"));
    	}
    	
    	$adresse = new Zend_Form_Element_Text("adresse");
    	$adresse->setLabel("Adresse *");
    	$adresse->setAttrib("class", "required");
    	$adresse->setAttrib("placeholder", "Adresse");
    	$adresse->removeDecorator('HtmlTag');
    	if ($adresse->getDecorator('label')) {
    	    $adresse->removeDecorator('Label');
    	    $adresse->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $adresse->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $adresse->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group catalogue-papier', "style" => "display:none;"));
    	}
    	
    	$codePostale = new Zend_Form_Element_Text("code-postale");
    	$codePostale->setLabel("Code postale *");
    	$codePostale->setAttrib("class", "required");
    	$codePostale->setAttrib("placeholder", "Code postale");
    	$codePostale->removeDecorator('HtmlTag');
    	if ($codePostale->getDecorator('label')) {
    	    $codePostale->removeDecorator('Label');
    	    $codePostale->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $codePostale->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $codePostale->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group catalogue-papier', "style" => "display:none;"));
    	}
    	
    	$ville = new Zend_Form_Element_Text("ville");
    	$ville->setLabel("Ville *");
    	$ville->setAttrib("class", "required");
    	$ville->setAttrib("placeholder", "ville");
    	$ville->removeDecorator('HtmlTag');
    	if ($ville->getDecorator('label')) {
    	    $ville->removeDecorator('Label');
    	    $ville->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $ville->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $ville->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group catalogue-papier', "style" => "display:none;"));
    	}
    	
    	$pays = new Zend_Form_Element_Text("pays");
    	$pays->setLabel("Pays *");
    	$pays->setAttrib("class", "required");
    	$pays->setAttrib("placeholder", "Pays");
    	$pays->removeDecorator('HtmlTag');
    	if ($pays->getDecorator('label')) {
    	    $pays->removeDecorator('Label');
    	    $pays->addDecorator(array('controls'=>'HTMLTag'),array('tag'=>'div','class'=>'controls'));
    	    $pays->addDecorator('Label',array('tag'=>'div','class'=>'control-label'));
    	    $pays->addDecorator('HTMLTag',array('tag'=>'div','class'=>'control-group catalogue-papier', "style" => "display:none;"));
    	}
    	
    	$this->addElements(array($pdf, $papier, $civilite, $titre, $fonction, $nom, $prenom, $raisonSociale, $email, $adresse, $codePostale, $ville, $pays));
    	
    	if($this->_captcha){
			$captcha = new Zend_Form_Element_Captcha ('captcha',
				array(
					'label' => "Merci de saisir le code ci-dessous :",
					'required' => true,
					'captcha'=> array(
						'captcha' => 'image',
						'wordLen' => 6,
						'font' => APPLICATION_PATH."/../data/fonts/fonts-japanese-gothic.ttf",
						'height' => 100,
						'width' => 300,
						'fontSize' => 50,
						'imgDir' => APPLICATION_PATH."/../public/captcha/",
						'imgUrl' => Zend_Controller_Front::getInstance()->getBaseUrl()."/captcha",
						'dotNoiseLevel' => 200,
						'lineNoiseLevel' => 20,
					)
				)
			);
			$captcha->addErrorMessage("Le code que vous avez saisi ne correspond pas avec l'image");
			
			$this->addElement($captcha);
    	}
		
		$submit = new Zend_Form_Element_Submit('Envoyer');
		$submit->setAttrib('class', 'btn btn-primary');
		    	
    	$this->addElement($submit);
    	
    	$this->addDisplayGroup(array(
    			$submit
    	), 'button');
    	
    	foreach ($this->getDisplayGroups() as $group) {
    		foreach ($group->getElements() as $element) {
    			$element->removeDecorator('HtmlTag');
    			$element->removeDecorator('Label');
    			$element->removeDecorator('Tooltip');
    			$element->removeDecorator('DtDdWrapper');
    	
    		}
    	}
    	
    	$this->getDisplayGroup('button')->setDecorators(array(
    	     
    	    'FormElements',
    	    array(
    	        'HtmlTag',
    	        array(
    	            'tag' => 'div',
    	            'class' => 'form-actions'
    	        )
    	    )
    	));
    	
    	$this->setDecorators(array(
            'FormElements',
            'HtmlTag',
            'Form',
        ));
    }
}