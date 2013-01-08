<?php

class Application_Form_ClientPayboxForm extends Zend_Form
{
    public function init()
    {
       	$this->setMethod('post');
		$this->setAttrib('action', '/blocks/paybox/');
		
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
	
		$this->addElement(	'text', 'nom', array(
            				'label'      => 'Nom :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'prenom', array(
            				'label'      => 'Prenom :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'adresse', array(
            				'label'      => 'Adresse :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'codePostale', array(
            				'label'      => 'Code postale :',
            				'decorators' => $decorateur,
		));
	
		$this->addElement(	'text', 'ville', array(
            				'label'      => 'Ville :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'pays', array(
            				'label'      => 'Pays :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'telCabinet', array(
            				'label'      => 'Telephone cabinet :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'telPortable', array(
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
		
		$this->addElement(	'text', 'diplome', array(
            				'label'      => 'Diplome :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'anneeObtentionDiplome', array(
            				'label'      => 'Annee d\'obtention :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'etudiant', array(
            				'label'      => 'Si vous etes etudiant, nom faculte :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'text', 'anneeObtentionEtudiant', array(
            				'label'      => 'Annee d\'obtention :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'checkbox', 'adresseFacturation', array(
            				'label'      => 'Merci de cocher cette case si l\'adresse de facturation est differente de celle renseignee ci-dessus',
            				'decorators' => $decorateur,
		));
		
		$this->addElement(	'select', 'typeClient', array(
            				'label'      => 'Etes vous ?',
            				'decorators' => $decorateur,
            				'multiOptions' => array(
            					'client' => 'Client Zimmer Dental', 
            					'visiteur' => 'Visiteur',
							)
		));
		
		$this->addElement(	'select', 'typePaiement', array(
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