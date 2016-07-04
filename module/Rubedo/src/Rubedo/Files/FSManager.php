<?php

namespace Rubedo\Files;

use League\Flysystem\GridFS\GridFSAdapter;
use League\Flysystem\Filesystem;
use Rubedo\Services\Manager;

class FSManager
{
    public function getFS($adapterConfig = null){
        $adapter= $this->getGridFSAdapter($adapterConfig);
        return (new Filesystem($adapter));
    }

    protected function getGridFSAdapter($adapterConfig = null){
        $mongoService=Manager::getService("MongoDataAccess");
        $mongoAdapter=$mongoService->getAdapter();
        $dbName=$mongoService->getDefaultDb();
        $gridFS=$mongoAdapter->$dbName->getGridFS();
        return(new GridFSAdapter($gridFS));
    }
}