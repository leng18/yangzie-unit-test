<?php
namespace yangzie;
define("YZE_SCRIPT_LOGO", "
================================================================
		YANGZIE(V1.5.4) Generate Script
		易点互联®
================================================================");

define("YZE_METHED_HEADER", "
================================================================
		%s
================================================================");


global $language, $db;
$language = "zh-cn";

if(!preg_match("/cli/i",php_sapi_name())){
	echo wrap_output(sprintf(__("请在命令行下运行,进入到%s, 运行php generate.php",dirname(__FILE__))));die();
}

function script_locale(){
	global $language;
// 	echo $language;
	return $language;
}


chdir("./app/public_html");
include_once 'init.php';
include_once '../../scripts/generate-controller.php';
include_once '../../scripts/generate-model.php';
include_once '../../scripts/generate-module.php';

if(true){
	clear_terminal();
	while(($cmds = display_home_wizard())){
		$command = $cmds["cmd"];
		clear_terminal();
		echo get_colored_text(wrap_output(__("开始生成...")), "blue", "white")."\r\n";
		$class_name = "\yangzie\Generate_".ucfirst(strtolower($command))."_Script";
		$object = new $class_name($cmds);
		$object->generate();
		echo "\r\n".get_colored_text(wrap_output(__("生成结束.")), "blue", "white")."\r\n";
		//fgets(STDIN);
		die();
	}
}


function display_home_wizard(){
	clear_terminal();
	echo wrap_output(__(YZE_SCRIPT_LOGO."
  
伙计，你想要生成什么？
	
\t1.  生成代码：\t\tcontroller，view文件
\t2.  生成Model：\t\t根据表生成Model文件
			
\t3.  删除module：\t\t删除模块
\t4.  删除控制器：\t\t删除控制器及对应的验证器、视图
			
\t5.  把module打包成phar
			
\t6.  运行单元测试

\t7.  中文
\t8.  English

\t9.  检查url映射

\t0.  quit
请选择: "));
	
	while(!in_array(($input = fgets(STDIN)), array(0,1, 2, 3, 4, 5, 6, 7, 8, 9))){
		echo wrap_output(__("请选择操作对应的序号: "));
	}
	
	switch ($input){
		case 1: return display_mvc_wizard();
		case 2:  return display_model_wizard();
		case 3:  return display_delete_module_wizard();
		case 4:  return display_delete_controller_wizard();
		case 5:  return display_phar_wizard();
		case 6:  return _run_test();
		case 7:  return switch_to_zh();
		case 8:  return switch_to_en();
		case 9:  die(wrap_output("\r\n
TODO.\r\n
"));
		case 0:  die(wrap_output("\r\n
退出.\r\n
"));
		default: return array();
	}
}

function _run_test(){
	clear_terminal();
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."
	
选择要运行的单元测试，%s返回上一步

"), "运行单元测试", get_colored_text(" 0 ", "red", "white")));
	
	$index = 0;
	$tests = array();
	foreach(glob("../../tests/*")  as $f){
		if(is_dir($f)) {
			$index++;
			$test = basename($f);
			$tests[$index] = $f;
			echo "\t".($index).". {$test} \n";
		}
	}
	if($tests){
		$tests[0] = "";
		echo wrap_output(__("\t0. 运行全部单元测试\n:"));
	}else{
		echo wrap_output(__("\t没有单元测试\n"));
	}
	while (!array_key_exists(($selectedIndex = get_input()), $tests)){
		echo get_colored_text(wrap_output(__("\t选择的单元测试不存在，请重新选择:  ")), "red");
	}
	
	include "../../tests/config.php";
	$php = getenv("TEST_PHP_EXECUTABLE");
	if (empty($php) || !file_exists($php)) {
		echo get_colored_text(wrap_output(__("请修改tests/config.php中的TEST_PHP_EXECUTABLE为php.exe的全路径 ")), "red");die;
	}

	
	if($selectedIndex==="0"){
		system("php ../../tests/run-tests.php  ../../tests");
	}else{
		system("php ../../tests/run-tests.php ".$tests[$selectedIndex]);
	}
}

function switch_to_zh(){
	global $language;
	$language = "zh-cn";
	load_default_textdomain();
	display_home_wizard();
}

function switch_to_en(){
	global $language;
	$language = "en";
	load_default_textdomain();
	display_home_wizard();
}

function display_phar_wizard(){
	clear_terminal();
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."
	
打包phar，%s返回上一步
1. (1/2)请输入模块名:  ")), "打包Module", get_colored_text(" 0 ", "red", "white"));
	
	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\t命名遵守PHP变量命名规则，请重输:  ")), "red");
	}
	
	if( ! file_exists(dirname(dirname(__FILE__))."/app/modules/".$module)){
		echo wrap_output("模块不存在");
	}

	echo wrap_output(__("2. (2/2)phar 签名私钥路径，回车表示不签名 
  ［ 采用私钥进行签名后，只有使用对应的公钥才能正确使用phar包，
    生成私钥：openssl genrsa -out mykey.pem 1024
    生成公钥：openssl rsa -in mykey.pem -pubout -out mykey.pub］:"));
	while (!file_exists(($key_path = get_input()))){
		if(!$key_path)break;//回车
		echo get_colored_text(wrap_output(__("\t文件不存在，请输入绝对路径:  ")), "red");
	}

	phar_module($module, $key_path);
	echo wrap_output(sprintf(__("phar保持于tmp/%s.phar\r\n"),$module));
	if($key_path){
		echo wrap_output(sprintf(__("请把对应的公钥改名为%s.phar.pubkey并跟phar文件放在一起\r\n"),$module));
	}
	return array();
}

function phar_module($module, $key_path){
	@mkdir(dirname(dirname(__FILE__))."/tmp/");
	$phar = new \Phar(dirname(dirname(__FILE__))."/tmp/".$module.'.phar', 0, $module.'.phar');
	$phar->buildFromDirectory(dirname(dirname(__FILE__))."/app/modules/".$module);
	//$phar->setStub($phar->createDefaultStub('__module__.php'));
	$phar->compressFiles(\Phar::GZ);
	if($key_path){
		$private = openssl_get_privatekey(file_get_contents($key_path));
		$pkey = '';
		openssl_pkey_export($private, $pkey);
		$phar->setSignatureAlgorithm(\Phar::OPENSSL, $pkey);
	}
}

function display_delete_controller_wizard(){
	clear_terminal();
	echo sprintf(wrap_output(__( YZE_METHED_HEADER."

删除控制器及其控制器、视图，%s返回上一步
1. (1/2)所在功能模块: ")), "删除Controller",get_colored_text(" 0 ", "red", "white"));

	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\t命名遵守PHP变量命名规则，请重输:  ")), "red");
	}

	echo wrap_output(__("2. (2/2)控制器的名字:  "));
	while (!is_validate_name(($controller = get_input()))){
		echo get_colored_text(wrap_output(__("\t命名遵守PHP变量命名规则，请重输:  ")), "red");
	}
	
	if( ! file_exists(dirname(dirname(__FILE__))."/app/modules/{$module}/controllers/{$controller}_controller.class.php")){
		echo wrap_output(__("控制器不存在"));
	}else{
		unlink(dirname(dirname(__FILE__))."/app/modules/{$module}/controllers/{$controller}_controller.class.php");
		foreach (glob(dirname(dirname(__FILE__))."/app/modules/{$module}/views/{$controller}.*") as $file){
			unlink($file);
		}
		unlink(dirname(dirname(__FILE__))."/tests/{$module}/{$controller}_controller.class.phpt");
		echo wrap_output(__("控制器及视图、验证器、单元测试文件删除成功"));
	}

	return array();
}

function display_delete_module_wizard(){
	clear_terminal();
	echo sprintf(wrap_output(__( YZE_METHED_HEADER."
	
输入要删除的模块名，%s返回上一步:  ")), "删除整个Module", get_colored_text(" 0 ", "red", "white"));
	
	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\t命名遵守PHP变量命名规则，请重输:  ")), "red");
	}
	
	if( ! file_exists(dirname(dirname(__FILE__))."/app/modules/".$module)){
		echo wrap_output(__("模块不存在"));
	}else{
		rrmdir(dirname(dirname(__FILE__))."/app/modules/".$module);
		rrmdir(dirname(dirname(__FILE__))."/tests/".$module);
		echo wrap_output(__("模块删除成功"));
	}

	return array();
}

function display_mvc_wizard(){
	clear_terminal();
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."
  
你将生成VC代码结构，请根据提示进操作，%s返回上一步：
1. (1/8)所在功能模块:  "), "生成代码结构", get_colored_text(" 0 ", "red", "white")));
	
	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\t命名遵守PHP变量命名规则，请重输:  ")), "red");
	}
	
	echo wrap_output(__("2. (2/8)控制器的名字:  "));
	while (!is_validate_name(($controller = get_input()))){
		echo get_colored_text(wrap_output(__("\t命名遵守PHP变量命名规则，请重输:  ")), "red");
	}
	
	if(($uris = is_controller_exists($controller, $module))){
		echo wrap_output(__("3. (3/8)控制器已存在，映射URI的是:\n\n"));
		foreach ($uris as $index => $uri){
			echo "\t ".($index+1).". {$uri}\n";
		}
		echo wrap_output(__("\n\t选择一个或者输入新的, 回车表示不映射:"));
		$uri = get_input();
		
		if (is_numeric($uri)){
			$uri = $uris[$uri-1];
		}
	}else{
		echo wrap_output(__("3. (3/8)映射URI, 默认URI为/{$module}/{$controller}:  "));
		$uri = get_input();
	}
	
	
	
	echo wrap_output(__("4. (4/8)视图格式(如tpl, xml, json)，多个用空格分隔，默认是tpl:  "));
	$view_format = get_input();
	
	return @array(
		"cmd" => "controller",
		"controller"=>$controller,
        "uri"=>$uri,
        "module_name"=>$module,
        "view_format"=>$view_format ? $view_format : "tpl" ,
	        
// 		"model"=>$model,
// 		"view_tpl"=>$view_tpl
	);
}

function is_controller_exists($controller, $module){
	if(file_exists(YZE_APP_MODULES_INC.$module."/__module__.php")){
		include_once YZE_APP_MODULES_INC.$module."/__module__.php";
		$class = "\\app\\".$module."\\".ucfirst(strtolower($module))."_Module";
		$object = new $class();
		return $object->get_uris_of_controller($controller);
		
	}
	return false;
}

function display_model_wizard(){
    echo wrap_output(sprintf(__(YZE_METHED_HEADER."
    

\t1.  生成新Model
\t2.  刷新Model
    
%s 返回
请选择: "),"DB To Code", get_colored_text(" 0 ", "red", "white")));
    
    while(!in_array(($input = fgets(STDIN)), array(1, 2))){
        echo wrap_output(__("请选择操作对应的序号: "));
    }
    switch ($input){
        case 1:  return display_model_generate_wizard();
        case 2:  return display_model_refresh_wizard();
        default: return array();
    }
}
function display_model_refresh_wizard(){
    global $db;
    clear_terminal();
    echo wrap_output(sprintf(__( YZE_METHED_HEADER."
    
你将刷新Model代码，请根据提示进操作，%s返回上一步：
1. (1/2)Model类(全)名: "), "重新生成Model代码结构", get_colored_text(" 0 ", "red", "white")));
    while (true){
        $cls=get_input();
        if( !$cls ){
            echo get_colored_text(wrap_output(__("Model类(全)名:  ")), "red");
            continue;
        }
        if( ! class_exists($cls)){
            echo get_colored_text(wrap_output(__("$cls 不存在:  ")), "red");
            continue;
        } 
        
        return array(
        		"cmd" => "refreshmodel",
        		"base"=>"table",
        		"class_name"=>$cls,
        );
    }
    
   
}
function display_model_generate_wizard(){
    global $db;
	clear_terminal();
	
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."

你将生成Model代码，请根据提示进操作，%s返回上一步：
1. (1/2)表名: "), "生成Model代码结构", get_colored_text(" 0 ", "red", "white")));

	
	while (!is_validate_table(($table=get_input()))){
		echo get_colored_text(wrap_output(sprintf(__("\t表不存在(%s)，请重输:  "), mysqli_error($db))), "red");
	}

	echo wrap_output(__("2. (2/2)所在module模块名,  遵守PHP变量命名规则:  "));
	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\t功能模块名,  请重输:  ")), "red");
	}
	
	$model = rtrim($table,"s");
	
	if(class_exists("\\app\\$module\\{$model}_Model")){
	    echo wrap_output(__("\\app\\$module\\{$model}_Model 已存在，继续操作将覆盖已有文件：  
\t1.  继续
\t2.  返回
"));
	    
	    while(!in_array(($input = fgets(STDIN)), array(1, 2))){
	        echo wrap_output(__("请选择操作对应的序号: "));
	    }
	    
	    switch ($input){
	        case 1:  return array(
			"cmd" => "model",
			"base"=>"table",
			"module_name"=>$module,
			"class_name"=>$model,
			"table_name"=>$table,
	);
	        case 2:  display_home_wizard();return;
	        default: return array();
	    }
	}
	return array(
	        "cmd" => "model",
	        "base"=>"table",
	        "module_name"=>$module,
	        "class_name"=>$model,
	        "table_name"=>$table,
	);
	
}

function get_colored_text($text, $fgcolor=null, $bgcolor=null){
	if(PHP_OS=="WINNT")return $text;
	//return "\033[40m\033[31m some colored text \033[0m"; // red
	if(!$fgcolor && !$bgcolor)return $text;
	
	$_fgcolor = get_fgcolor($fgcolor);
	$_bgcolor = get_bgcolor($bgcolor);
	
	$colored_string = "";
	if ($_fgcolor) {
		$colored_string .= "\033[" . $_fgcolor . "m";
	}

	if ($_bgcolor) {
		$colored_string .= "\033[" . $_bgcolor . "m";
	}

	$colored_string .=  $text . "\033[0m";
	return $colored_string;
}

function get_bgcolor($color){
	switch(strtolower($color)){
	case 'black': return'0;30';
	case 'dark_gray': return'1;30';
	case 'blue': return'0;34';
	case 'light_blue': return'1;34';
	case 'green': return'0;32';
	case 'light_green': return'1;32';
	case 'cyan': return'0;36';
	case 'light_cyan': return'1;36';
	case 'red': return'0;31';
	case 'light_red': return'1;31';
	case 'purple': return'0;35';
	case 'light_purple': return'1;35';
	case 'brown': return'0;33';
	case 'yellow': return'1;33';
	case 'light_gray': return'0;37';
	case 'white': return'1;37';

		default: return null;
	}
}
function get_fgcolor($color){
	switch(strtolower($color)){
	case 'black': return'40';
	case 'red': return'41';
	case 'green': return'42';
	case 'yellow': return'43';
	case 'blue': return'44';
	case 'magenta': return'45';
	case 'cyan': return'46';
	case 'light_gray': return'47';
	default: return null;
	}
}

function get_input(){
	$input = strtolower(trim(fgets(STDIN)));
	is_back($input);
	return $input;
}

function is_back($input){
	if(strlen($input) >0 && $input=="0"){display_home_wizard();die;}
}

function is_validate_name($input){
	return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $input);
}

function is_validate_table($table){
    global $db;
	$app_module = new \app\App_Module();
	$db = mysqli_connect(
			$app_module->get_module_config("db_host"),
			$app_module->get_module_config("db_user"),
			$app_module->get_module_config("db_psw")
	);
	mysqli_select_db($db, $app_module->get_module_config("db_name"));
	return mysqli_query($db, "show full columns from `$table`");
}


function clear_terminal(){
	if(PHP_OS=="WINNT"){
		$clear = "cls";
	}else{
		$clear = "clear";
	}
	exec($clear);
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function wrap_output($msg){
	//if(PHP_OS=="WINNT"){
	//	return iconv("UTF-8", "GB2312//IGNORE", $msg);
	//}else{
		return $msg;
	//}
}

abstract class AbstractScript{
	protected $args = array();
	public function __construct($args){
		$this->args = $args;
	}
	public abstract function generate();

	public function check_dir($path){
		if(!file_exists($path)){
			$dir = mkdir($path);
			if(empty($dir)){
				die("\r\n\r\n\tcan not make dir: \r\n\r\n\t$path \r\n\r\n");
			}
			chmod($path, 0777);
		}
	}

	public function create_file($file_path,$content,$force=false){
		if(file_exists($file_path) && !$force){
			echo get_colored_text("file exists", "red", "white")."\r\n";return;
		}

		$f = fopen($file_path,'w+');
		if(empty($f)){
			echo get_colored_text("can not open file:{$file_path}");return;
		}
		chmod($file_path,0777);
		fwrite($f,$content);
		fclose($f);
		echo get_colored_text("OK.","blue","white")."\r\n";
	}

}
?>
