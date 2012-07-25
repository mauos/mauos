<?php
error_reporting(E_ALL|E_STRICT);
define("DS",DIRECTORY_SEPARATOR);
if(!defined("IN_MAUOS")&&define("IN_MAUOS", TRUE));
if(!defined("MAUOS_ROOT")&&define("MAUOS_ROOT", dirname(__FILE__).DS));
require_once MAUOS_ROOT.'core.php';
require_once MAUOS_ROOT.'template.php';
$template=new template();
$template->parse_template("test.html","", "", "", "cache_test.php");

?>