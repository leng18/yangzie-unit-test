<?php
namespace yangzie;

class YZE_Object{
	private static $loaded_modules = array();
	
	public static function set_loaded_modules($module_name, $module_info){
		self::$loaded_modules[strtolower($module_name)] = $module_info;
	}
	
	public static function loaded_module($module_name){
		return @self::$loaded_modules[strtolower($module_name)];
	}
	
	//subclass impl this.
	public function output(){

	}

	
	
	/**
	 * 取得一个变量的值，该方法主要是增加了默认值处理，如果变量为假值，返回默认值
	 *
	 */
	public static function the_val($val,$default){
		return $val ? $val : $default;
	}

	/**
	 *
	 * aa_bb_cc格式化成Aa_Bb_Cc_suffix
	 * @param unknown_type $class_name
	 * @param unknown_type $suffix
	 */
	public static function format_class_name($class_name,$suffix){
		foreach(explode("_", trim($class_name)) as $word){
			$class[] = ucfirst(strtolower($word));
		}
		return join("_", $class).($suffix ? "_{$suffix}" : "");
	}


	/**
	 * 过滤掉html符号
	 *
	 * @param array $array
	 * @param $type INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, INPUT_ENV, INPUT_SESSION, or INPUT_REQUEST.
	 * @return unknown
	 */
	public static function filter_special_chars(array $array=array(),$type){
        if(php_sapi_name()=="cli"){
            switch($type){
                case INPUT_GET: return self::filter_vars($_GET);
                case INPUT_POST: return self::filter_vars($_POST);
                case INPUT_COOKIE: return self::filter_vars($_COOKIE);
                case INPUT_SERVER: return self::filter_vars($_SERVER);
                case INPUT_SESSION: return self::filter_vars($_SESSION);
                case INPUT_REQUEST: return self::filter_vars($_REQUEST);
                case INPUT_ENV: return self::filter_vars($_ENV);
            }
            return $array;
        }
        
        //非cli模式下才有$_POST这些全局变量
		$definition = array();
		foreach($array as $name=>$value){
			$definition[$name]['filter'] 	= FILTER_CALLBACK;
			$definition[$name]['options']	= 'htmlspecialchars';
		}
		return filter_input_array($type, $definition);
	}

	public static function filter_vars(array $array){
		$definition = array();
		foreach($array as $name=>$value){
			$definition[$name]['filter'] 	= FILTER_CALLBACK;
			$definition[$name]['options']	= 'htmlspecialchars';
		}
		return filter_var_array($array, $definition);
	}
	public static function filter_var($var){
		return filter_var($var, FILTER_CALLBACK,array('options' => 'htmlspecialchars'));
	}
	public static function defilter_var($var){
		return filter_var($var, FILTER_CALLBACK,array('options' => 'htmlspecialchars_decode'));
	}
	public static function unescape($str,$charcode="UTF-8"){
		$text = preg_replace_callback("/[%|\\\\]u(?P<c>[0-9A-Za-z]{4})/",function($matches){
			$c = '';
			$val = intval($matches['c'],16);
			if($val < 0x7F){        // 0000-007F
				$c .= chr($val);
			}elseif($val < 0x800) { // 0080-0800
				$c .= chr(0xC0 | ($val / 64));
				$c .= chr(0x80 | ($val % 64));
			}else{                // 0800-FFFF
				$c .= chr(0xE0 | (($val / 64) / 64));
				$c .= chr(0x80 | (($val / 64) % 64));
				$c .= chr(0x80 | ($val % 64));
			}
			return $c;
		},$str);
		return mb_convert_encoding($text, $charcode, 'utf-8');
	}
}

?>
