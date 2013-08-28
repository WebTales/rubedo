<?php
// $result = array();
// $bocontrollerDirectory = realpath(APPLICATION_PATH . '/module/Rubedo/src/Rubedo/Backoffice/Controller');

// $iterator = new DirectoryIterator($bocontrollerDirectory);
// foreach ($iterator as $item) {
// if ($item->isDot()) {
// continue;
// }
// $fileName = $item->getFilename();
// $controllerName = str_replace('Controller.php', '', $fileName);
// $result['Rubedo\Backoffice\Controller\\' . $controllerName] = 'Rubedo\Backoffice\Controller\\' . $controllerName . 'Controller';
// }

// ksort($result);
// var_export($result);
// die();
// return $result;
$backControllers = include 'backoffice.controllers.config.php';
$frontControllers = include 'frontoffice.controllers.config.php';
$installControllers = include 'install.controllers.config.php';
return array_merge($backControllers, $frontControllers, $installControllers);