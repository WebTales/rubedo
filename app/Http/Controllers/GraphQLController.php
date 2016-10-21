<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GraphQLController extends Controller
{

    public function handle(Request $request)
    {
        $params=$request->input();
        $requestString = isset($params['query']) ? $params['query'] : null;
        $operationName = isset($params['operation']) ? $params['operation'] : null;
        $variableValues = isset($params['variables']) ? $params['variables'] : null;
        $result=app()['GraphQLHandler']->execute($requestString,$variableValues,$operationName);
        if(!$result["data"]&&isset($result["errors"][0]["message"])){
            abort(500,$result["errors"][0]["message"]);
        }
        return response()->json($result);
    }
}