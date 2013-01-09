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
                $nom = $params['name'];
				$prenom = $params['firstname'];
				$adresse = $params['address'];
				$codePostale = $params['postalCode'];
				$ville = $params['city'];
				$pays = $params['country'];
				$telCabinet = $params['officeTelNumber'];
				$telPortable = $params['mobilePhoneNumber'];
				$email = $params['email'];
				$activity = $params['activity'];
				$diplome = $params['diploma'];
				$anneeObtentiondiplome = $params['graduationYear'];
				$etudiant = $params['student'];
				$anneeObtentionEtudiant = $params['studentGraduationYear'];
				$adresseFacturation = $params['billingAddress'];
				$typeclient = $params['clientType'];
				$typePaiement = $params['paymentType'];
				
				//Control and backup, then if it's ok
				$this->_helper->redirector('payment', 'paybox');
            }
        }
		
    }

	public function paymentAction() {
		$serverUrl = $this->getRequest()->getScheme().'://'.$this->getRequest()->getHttpHost();
		
		$params = array(
		//mode d'appel
		     'PBX_MODE'        => '1',
		//identification
		     'PBX_SITE'        => '1999888',
		     'PBX_RANG'        => '98',
		     'PBX_IDENTIFIANT' => '3',
		//gestion de la page de connection : paramétrage "invisible"
		     'PBX_WAIT'        => '0',
		     'PBX_BOUTPI'      => "nul",
		     'PBX_BKGD'        => "white",
		//informations paiement (appel)
		     'PBX_TOTAL'       => '38000',
		     'PBX_DEVISE'      => '978',
		     'PBX_CMD'         => (string)rand(1, 10000),
		     'PBX_PORTEUR'     => "mickael.goncalves@webtales.fr",
		//informations nécessaires aux traitements (réponse)
		     'PBX_RETOUR'      => "montant:M;maref:R;auto:A;trans:T;abonnement:B ;paiement:P;carte:C;idtrans:S;pays:Y;erreur:E;validite:D;PPPS:U;IP:I;BIN6:N;digest:H;sign:K",
		     'PBX_EFFECTUE'    => $serverUrl.$this->_helper->url('back-payment','paybox','blocks'),
		     'PBX_REFUSE'      => $serverUrl.$this->_helper->url('refused','paybox','blocks'),
		     'PBX_ANNULE'      => $serverUrl.$this->_helper->url('canceled','paybox','blocks'),
			 'PBX_REPONDRE_A'  => $serverUrl.$this->_helper->url('back-payment','paybox','blocks'),
		//page en cas d'erreur
		     'PBX_ERREUR'      => $serverUrl.$this->_helper->url('error','paybox','blocks'),
		//en plus
		     'PBX_TYPECARTE'   => "CB",
		     'PBX_LANGUE'      => "FRA",
			 );
		
		foreach ($params as $key => $value) {
			$queryStringArray[] = "$key=$value";
		}

		$queryString = implode('&', $queryStringArray);

		/* Mettre le montant en session */

		//lancement paiement par URL
		$url = '/cgi-bin/modulev3.cgi?'.$queryString;

		$this->_redirect($url);
	}
	
	public function doneAction() {
		
	}
	
	public function refusedAction() {
		
	}

	public function canceledAction() {
		
	}
	
	public function errorAction() {
		
	}
	
	public function backPaymentAction() {
		$url = $this->getRequest()->getRequestUri();
		$stringArray = explode("?", $url);
		$url = $stringArray[1];
		
		$paramsKeyValue = array();
		
		foreach (explode("&", $url) as $value) {
			$keyValueArray = explode("=", $value);
			$params[$keyValueArray[0]] = $keyValueArray[1];
		}
		
		$pos = strrpos( $url, '&' );
    	$url = substr( $url, 0, $pos );
		
		$amount = "38000";
		
		if(isset($params['auto'])){
			if(isset($params['erreur'])) {
				if($params['erreur'] == "00000") {
					if(isset($params['sign'])){
						if(isset($params['montant']) && $amount==$params['montant']) {
							$file = fopen(APPLICATION_PATH."/../data/paybox/pubkey.pem", "r");
							$pubkey = fread($file, 1024);
							fclose($file);
							
							$sign = $params['sign'];
							$sign = urldecode($sign);
							$sign = base64_decode($sign);
							$result = openssl_verify($url, $sign, $pubkey);
							
							if($result == 1){
								//enregistrer payement
							}
						}
					}
				}
			}
		}
	}

}
