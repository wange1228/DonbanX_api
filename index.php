<?php
ini_set("display_errors", "0");     # don"t show any errors...
error_reporting(E_ALL | E_STRICT);  # ...but do log them

define("BASEPATH", realpath(dirname(__FILE__))."/");
define("APPPATH", realpath(dirname(__FILE__))."");

include_once(BASEPATH."core/Common.php");
include_once(BASEPATH."controller/Api.php");

function get_config(){}

function &load_database($params = "", $active_record_override = false)
{
    $database =& DB($params, $active_record_override);
    return $database;
}

$main = new Api();
$method = isset($_GET['method']) ? $_GET['method'] : NULL;
$main->$method();
