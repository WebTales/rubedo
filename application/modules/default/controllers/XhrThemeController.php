<?php

class XhrThemeController extends AbstractController {
    /**
     * @param 	Rubedo\Interfaces\User\ISession
     *
     */
    protected $_session;

    public function init() {
        $this->_session = Rubedo\Services\Manager::getService('Session');
    }

    public function defineThemeAction() {

        $theme = $this->getRequest()->getParam('theme', "default");
        $this->_session->set('themeCSS', $theme);

        $response['theme'] = $this->_session->get('themeCSS');
		
        return $this->_helper->json($response);
    }

}
