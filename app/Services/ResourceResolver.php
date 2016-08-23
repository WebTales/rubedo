<?php

namespace App\Services;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as CacheStore;

class ResourceResolver
{

    protected $builtFS = [];
    protected $resourceNamespaceConfig = [];

    public function __construct($resourceNamespaceConfig)
    {
        $this->resourceNamespaceConfig=$resourceNamespaceConfig;
    }

    public function resolve($namespace,$path){
        if(!isset($this->resourceNamespaceConfig[$namespace])){
            return false;
        }
        if(!isset($this->builtFS[$namespace])){
            $this->buildFS($namespace);
        }
        if($this->builtFS[$namespace]->has($path)){
            return [
                "content"=>$this->builtFS[$namespace]->read($path),
                "mimeType"=>$this->builtFS[$namespace]->getMimeType($path),
                "size"=>$this->builtFS[$namespace]->getSize($path)
            ];
        }
        if(!isset($this->resourceNamespaceConfig[$namespace]['fallbackNamespace'])){
            return false;
        } else {
            return $this->resolve($this->resourceNamespaceConfig[$namespace]['fallbackNamespace'],$path);
        }
    }

    public function buildFS($namespace){
        $rootPath=$this->resourceNamespaceConfig[$namespace]["path"];
        $localAdapter = new Local($rootPath);
        $cacheStore = new CacheStore();
        $adapter = new CachedAdapter($localAdapter, $cacheStore);
        $this->builtFS[$namespace] = new Filesystem($adapter);
    }
}
