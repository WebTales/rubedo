<?php

class Application_Form_ClientPayboxForm extends Zend_Form
{
    public function init()
    {
       	$this->setMethod('post');
		//$this->setAttrib('action', '/blocks/paybox/');
		
		//Modification du décorateur
		$this->setDecorators(
		    array(
		        'FormElements',
		        array('HtmlTag', array('tag' => 'table')),
		        'Form'
		    )
		);

		$decorateur = array(
		    'ViewHelper',
		    'Errors',
		    array('Description', array('tag' => 'p', 'class' => 'description')),
		    array('HtmlTag', array('tag' => 'td')),
		    array('Label', array('tag' => 'th')),
		    array(array('tr' => 'HtmlTag'), array('tag' => 'tr'))
		);

		$this->addElement(	'select', 'gender', array(
            				'label'      => 'Titre :',
            				'decorators' => $decorateur,
            				'multiOptions' => array(
            					'mister' => 'Monsieur', 
            					'madam' => 'Madame',
							)
		));
	
		$this->addElement(	'text', 'name', array(
            				'label'      => 'Nom :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'first name', array(
            				'label'      => 'Prenom :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'address', array(
            				'label'      => 'Adresse :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'postalCode', array(
            				'label'      => 'Code postale :',
            				'decorators' => $decorateur,
		));
	
		$this->addElement(	'text', 'city', array(
            				'label'      => 'Ville :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'country', array(
            				'label'      => 'Pays :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'officeTelNumber', array(
            				'label'      => 'Telephone cabinet :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'mobilePhoneNumber', array(
            				'label'      => 'Telephone portable :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'email', array(
            				'label'      => 'E-mail :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'select', 'activity', array(
            				'label'      => 'Activite :',
            				'decorators' => $decorateur,
            				'multiOptions' => array(
            					'assitance dentaire' => 'Assistance Dentaire', 
            					'dentiste' => 'Dentiste',
							)
		));
		
		$this->addElement(	'text', 'diploma', array(
            				'label'      => 'Diplome :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'graduationYear', array(
            				'label'      => 'Annee d\'obtention :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'student', array(
            				'label'      => 'Si vous etes etudiant, nom faculte :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'studentGraduationYear', array(
            				'label'      => 'Annee d\'obtention :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'checkbox', 'billingAddress', array(
            				'label'      => 'Merci de cocher cette case si l\'adresse de facturation est differente de celle renseignee ci-dessus',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'select', 'clientType', array(
            				'label'      => 'Etes vous ?',
            				'decorators' => $decorateur,
            				'multiOptions' => array(
            					'client' => 'Client Zimmer Dental', 
            					'visiteur' => 'Visiteur',
							)
		));
		
		$this->addElement(	'select', 'paymentType', array(
            				'label'      => 'Mode de paiement :',
            				'decorators' => $decorateur,
            				'multiOptions' => array(
            					'cb' => 'Carte bancaire',
            					'cheque' => 'Cheque de banque',
							)
		));

		$this->addElement('submit', 'submit', array(
            'label'    => 'Valider',
            'name'	   => 'submit',
            'decorators' => array(
	            'ViewHelper',
	            array(array('td' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array(array('tr' => 'HtmlTag'), array('tag' => 'tr'))
	        ),
        ));
    }
}