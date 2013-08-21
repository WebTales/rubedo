<?php
// $result = array();
// $bocontrollerDirectory = realpath(APPLICATION_PATH.'/module/Rubedo/src/Rubedo/Backoffice/Controllers');

// $iterator = new DirectoryIterator($bocontrollerDirectory);
// foreach ($iterator as $item){
//     if($item->isDot()){
//         continue;
//     }
//     $fileName = $item->getFilename();
//     $controllerName = str_replace('Controller.php', '', $fileName);
//     $result['Rubedo\Backoffice\Controller\\'.$controllerName]='Rubedo\Backoffice\Controller\\'.$controllerName.'Controller';
// }

// ksort($result);
// var_export($result);die();
// return $result;
return array(
    'Rubedo\Backoffice\Controller\Index' => 'Rubedo\Backoffice\Controller\IndexController',
    'Rubedo\Backoffice\Controller\Login' => 'Rubedo\Backoffice\Controller\LoginController',
    'Rubedo\Backoffice\Controller\Logout' => 'Rubedo\Backoffice\Controller\LogoutController',
    'Rubedo\Backoffice\Controller\XhrAuthentication' => 'Rubedo\Backoffice\Controller\XhrAuthenticationController',
    'Rubedo\Backoffice\Controller\Icons' => 'Rubedo\Backoffice\Controller\IconsController',
    'Rubedo\Backoffice\Controller\Acl' => 'Rubedo\Backoffice\Controller\AclController',
    'Rubedo\Backoffice\Controller\CurrentUser' => 'Rubedo\Backoffice\Controller\CurrentUserController',
    'Rubedo\Backoffice\Controller\Workspaces' => 'Rubedo\Backoffice\Controller\WorkspacesController',
    'Rubedo\Backoffice\Controller\Languages' => 'Rubedo\Backoffice\Controller\LanguagesController',
    'Rubedo\Backoffice\Controller\Cache' => 'Rubedo\Backoffice\Controller\CacheController',
    'Rubedo\Backoffice\Controller\Acl' => 'Rubedo\Backoffice\Controller\AclController',
);