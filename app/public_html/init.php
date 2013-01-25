<?php
/**
 * 该文件的职责：
 * 1.定义系统目录常量
 * 2.设置文件包含查找路径
 *
 * @category Framework
 * @package  Yangzie
 * @author   liizii <libol007@gmail.com>
 * @license  http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version  SVN: $Id$
 * @link     http://www.yangzie.net
 *
 */

define("YANGZIE", "../../yangzie");
define("DS", DIRECTORY_SEPARATOR);
define("PS", PATH_SEPARATOR);

define("APP_INC", "../");//应用代码目录名称
define("APP_MODULES_INC", "../modules/");//应用代码目录名称
define("APP_LAYOUTS_INC", "../components/layouts/");
define("APP_VIEWS_INC", "../components/views/");
define("APP_MODELS_INC", "../components/models/");
define("APP_VALIDATES_INC", "../components/validates/");

define("INSTALL_PATH", dirname(__FILE__)."/../../");//安装的目录路径
define("APP_PATH", INSTALL_PATH."app".DS);//应用代码路径
define("APP_CACHES_PATH", INSTALL_PATH."app".DS."caches".DS);//应用代码路径

ini_set('include_path', get_include_path().PS."../..");

ini_set('error_reporting', E_ALL);

date_default_timezone_set('Asia/Chongqing');
?>