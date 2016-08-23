<?php

namespace App\Http\Controllers;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as CacheStore;

class ResourceController extends Controller
{

    public function resolve($namespace,$path)
    {
        $rootPath=config("resourceNamespaces")[$namespace]["path"];
        $localAdapter = new Local($rootPath);
        $cacheStore = new CacheStore();
        $adapter = new CachedAdapter($localAdapter, $cacheStore);
        $filesystem = new Filesystem($adapter);
        if(!$filesystem->has($path)){
            abort(404);
        };
        return response($filesystem->read($path))
            ->withHeaders([
                'Content-Type' => $filesystem->getMimetype($path),
                'Content-Length' => $filesystem->getSize($path),
            ]);
    }
}