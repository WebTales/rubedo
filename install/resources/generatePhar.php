 <?php
$stub =
'<?php
require_once "phar://ciphp.phar/App/App.php";
__HALT_COMPILER();
?>';
$phar = new Phar($argv[1]);
$phar->setAlias('ciphp.phar');
$phar->buildFromDirectory($argv[2]);
$phar->setStub($stub);
