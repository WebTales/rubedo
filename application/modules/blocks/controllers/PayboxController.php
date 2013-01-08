<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

Use Rubedo\Services\Manager;

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_PayboxController extends Blocks_AbstractController
{

    public function indexAction() {

		$paybox = new Application_Form_ClientPayboxForm;
		
		$this->view->paybox = $paybox;
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
            if ($paybox->isValid($request->getPost())) {
                $params = $this->getRequest()->getParams();
				$titre = $params['gender'];
                $nom = $params['nom'];
				$prenom = $params['prenom'];
				$adresse = $params['adresse'];
				$codePostale = $params['codePostale'];
				$ville = $params['ville'];
				$pays = $params['pays'];
				$telCabinet = $params['telCabinet'];
				$telPortable = $params['telPortable'];
				$email = $params['email'];
				$activity = $params['activity'];
				$diplome = $params['diplome'];
				$anneeObtentiondiplome = $params['anneeObtentionDiplome'];
				$etudiant = $params['etudiant'];
				$anneeObtentionEtudiant = $params['anneeObtentionEtudiant'];
				$adresseFacturation = $params['adresseFacturation'];
				$typeclient = $params['typeClient'];
				$typePaiement = $params['typePaiement'];
				
				//Control and backup, then if it's ok
				$this->_helper->redirector('payment', 'paybox');
            }
        }
		
    }

	public function paymentAction() {
		//mode d'appel
		     $PBX_MODE        = '1';
		//identification
		     $PBX_SITE        = '1999888';
		     $PBX_RANG        = '98';
		     $PBX_IDENTIFIANT = '3';
		//gestion de la page de connection : paramétrage "invisible"
		     $PBX_WAIT        = '0';
		     $PBX_TXT         = " ";
		     $PBX_BOUTPI      = "nul";
		     $PBX_BKGD        = "white";
		//informations paiement (appel)
		     $PBX_TOTAL       = '37000';
		     $PBX_DEVISE      = '978';
		     $PBX_CMD         = "ref cmd";
		     $PBX_PORTEUR     = "mickael.goncalves@webtales.fr";
		//informations nécessaires aux traitements (réponse)
		     $PBX_RETOUR      = "montant:M;ref:R;auto:A;trans:T";
		     $PBX_EFFECTUE    = "http://rubedo.mickael/blocks/paybox/";
		     $PBX_REFUSE      = "http://rubedo.mickael/blocks/paybox/";
		     $PBX_ANNULE      = "http://rubedo.mickael/blocks/paybox/";
		//page en cas d'erreur
		     $PBX_ERREUR      = "http://rubedo.mickael/blocks/paybox/";
		//en plus
		     $PBX_TYPECARTE   = "CB";
		     $PBX_LANGUE      = "FRA";
		    
		//lancement paiement par URL
		$url = '/cgi-bin/modulev3.cgi?PBX_MODE='.$PBX_MODE.'&PBX_SITE='.$PBX_SITE.'&PBX_RANG='.$PBX_RANG.'&PBX_IDENTIFIANT='.$PBX_IDENTIFIANT.'&PBX_WAIT='.$PBX_WAIT.'&PBX_TXT='.$PBX_TXT.'&PBX_BOUTPI='.$PBX_BOUTPI.'&PBX_BKGD='.$PBX_BKGD.'&PBX_TOTAL='.$PBX_TOTAL.'&PBX_DEVISE='.$PBX_DEVISE.'&PBX_CMD='.$PBX_CMD.'&PBX_PORTEUR='.$PBX_PORTEUR.'&PBX_EFFECTUE='.$PBX_EFFECTUE.'&PBX_REFUSE='.$PBX_REFUSE.'&PBX_ANNULE='.$PBX_ANNULE.'&PBX_ERREUR='.$PBX_ERREUR.'&PBX_RETOUR='.$PBX_RETOUR.'&PBX_TYPECARTE='.$PBX_TYPECARTE.'&PBX_LANGUE='.$PBX_LANGUE.'';
	
		$this->_redirect($url);
	}
	
	public function accepteAction() {
		
	}
	
	public function refuseAction() {
		
	}

	public function annuleAction() {
		
	}
	
	public function erreurAction() {
		
	}

}
