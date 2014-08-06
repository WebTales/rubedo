<?php


namespace RubedoAPI\Rest\V1;


class ContactRessource extends AbstractRessource {
    public function options() {
        return $this->getConfig();
    }
}