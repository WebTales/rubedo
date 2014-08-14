<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 07/08/14
 * Time: 14:42
 */

namespace RubedoAPI\Interfaces;


interface IRessource {
    public function handler($method, $params);
    public function handlerEntity($id, $method, $params);
    public function setContext($controller);
} 