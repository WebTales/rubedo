<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/api/graphql', function () use ($app) {
    $myHandler = $app->make('GraphQLHandler');
    $params=$app["request"]->input();
    $requestString = isset($params['query']) ? $params['query'] : null;
    $operationName = isset($params['operation']) ? $params['operation'] : null;
    $variableValues = isset($params['variables']) ? $params['variables'] : null;
    $result=$myHandler->execute($requestString,$variableValues,$operationName,$params);
    header('Content-Type: application/json');
    echo json_encode($result);
    die();
});