<?php 

/**
 * Action Helper for pop in module
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperPopIn extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;

    /**
     * Constructor: initialize plugin loader
     * 
     * @return void
     */
    public function __construct()
    {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }

    /**
     * Return array describing a pop in content
     * 
     * @return array
     */	
    public function getPopIn($block_id)
    {
    	switch($block_id) {
			case 1:
		    	$id = "about";
				$fr = array(
		    		'title' => 'A propos',
					'content' => '
					<div class="modal-body">
					<p>Rubedo est un logiciel open-source de gestion de contenus, développé et maintenu par la société WebTales.</p><p>Rubedo est en phase active de développement.</p><p>Le projet est soutenu par l\'incubateur de l\'Ecole Centrale Paris, et hébergé dans ses locaux.</p>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal">Fermer</a>
					</div>
					'
				);
				$en = array(
		    		'title' => 'About',
					'content' => '
					<div class="modal-body">
					<p>Rubedo is an open-source content management system, developped and supported by WebTales.</p><p>Rubedo is in an active phase of development.</p><p>This project is supported by Ecole Centrale Paris, and hosted in its offices.</p>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal">Close</a>
					</div>
					'
				);
				break;
			case 2:
		    	$id = "connect";
				$fr = array(
		    		'title' => 'Connexion',
					'content' => '
					<form class="form-horizontal" id="connect">
					  <div class="control-group">
					    <label class="control-label" for="inputEmail">E-mail</label>
					    <div class="controls">
					      <input type="text" id="inputEmail" placeholder="Email" value="julien.bourdin@webtales.fr">
					      <span class="help-inline" id="connect-msg"></span>
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="inputPassword">Mot de passe</label>
					    <div class="controls">
					      <input type="password" id="inputPassword" placeholder="Password" value="webtales">
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <label class="checkbox">
					        <input type="checkbox"> Maintenir la connexion
					      </label>
					    </div>
					  </div>
					  <div class="modal-footer">
		  				<button type="submit" class="btn btn-primary">Se connecter</button>
						<button type="button" class="btn" data-dismiss="modal">Annuler</button>
					  </div>
					</form>
					'
				);
				$en = array(
		    		'title' => 'Sign in',
					'content' => '
					<form class="form-horizontal" id="connect">
					  <div class="control-group">
					    <label class="control-label" for="inputEmail">E-mail</label>
					    <div class="controls">
					      <input type="text" id="inputEmail" placeholder="Email" value="julien.bourdin@webtales.fr">
					      <span class="help-inline" id="connect-msg"></span>
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="inputPassword">Password</label>
					    <div class="controls">
					      <input type="password" id="inputPassword" placeholder="Password" value="webtales">
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <label class="checkbox">
					        <input type="checkbox"> Remember me
					      </label>
					    </div>
					  </div>
					  <div class="modal-footer">
		  				<button type="submit" class="btn btn-primary">Sign in</button>
						<button type="button" class="btn" data-dismiss="modal">Cancel</button>
					  </div>
					</form>
					');
				break;
			case 3:
		    	$id = "confirm";
				$fr = array(
		    		'title' => 'Alerte',
					'content' => '
					<div class="modal-body">
					<p>Vous êtes sur le point de perdre toutes les modifications effectuées</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary" id="cancel-confirm" data-dismiss="modal">Confirmer</button>
						<a href="#" class="btn" data-dismiss="modal">Annuler</a>
					</div>
					'
				);
				$en = array(
		    		'title' => 'Alert',
					'content' => '
					<div class="modal-body">
					<p>You are about to loose all unsaved modifications</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary" id="cancel-confirm" data-dismiss="modal">Confirm</button>
						<a href="#" class="btn" data-dismiss="modal">Cancel</a>
					</div>
					'
				);
				break;
			case 2:
    	}
		
		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		
		$output = $$lang;
		$output['id'] = $id;

        return $output;

    }

    /**
     * Strategy pattern: call helper as broker method
     * 
	 * @param block_id block identifier
     * @return array
     */
    public function direct($block_id)
    {
        return $this->getPopIn($block_id);
    }
}