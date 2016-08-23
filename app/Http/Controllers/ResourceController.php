<?php

namespace App\Http\Controllers;


class ResourceController extends Controller
{

    public function resolve($namespace,$path)
    {
        $result=app()['ResourceResolver']->resolve($namespace,$path);
        if(!$result){
            abort(404);
        };
        return response($result["content"])
            ->withHeaders([
                'Content-Type' => $result["mimeType"],
                'Content-Length' => $result["size"],
            ]);
    }
}