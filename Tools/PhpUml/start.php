<?php
require_once('UML.php');

$t = new PHP_UML();
$t->parseDirectory('/var/www/prod/Twinmusic/');
$t->generateXMI(1);
$t->saveXMI('./Doonoyz.xmi');
