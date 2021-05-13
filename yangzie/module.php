<?php
namespace yangzie;
abstract class YZE_Base_Module{
	/**
	 * 模块的名字
	 * @var string
	 */
	public $name = "";

	/**
	 * 该模块自定义的uri路由，如array('/something/\d+'=>array("controller"=>'quote',"args"=>array()))
	 * controller string 资源控制器名
	 * args 带到action中去的参数:<br/>
	 * 例如：array('/something/(?P<order_id>\d+)'=>array("controller"=>'quote',"args"=>array('r:order_id')))
	 * <br/>控制器便可以通过get_var("order_id")得到地址上的order id, 这里的r:order_id表示正则匹配地址中的order_id<br/>
	 * 也可以写固定的值，比如array('/something/(?P<order_id>\d+)'=>array("controller"=>'quote',"args"=>array('foo'=>'bar')))
	 * <br/>控制器便可以通过get_var("foo")得到bar
	 *
	 * @var array
	 */
	public $routers = array();

	/**
	 * 该模块中需要认证访问的资源及其访问方法,格式：
	 *     array(
	 *         'resouce_controller_name'=>"get|post|put|delete|*",
	 *         'resouce_controller_name2'=>"get|post|put|delete|*",
	 *     )
	 * 表示某个资源控制器的某个请求求认证。如
	 *     array(
	 *         "deals" => "*"
	 *         "deals" => "post|put|delete"
	 *     )
	 * 表示deals控制器中的所有请求都要认证
	 * deals控制器中只有（post,put,delete）在调用前都要认证,get请求不需要
	 *
	 * @var array
	 */
	public $auths = array();

	/**
	 * 同auths，只是其它的定义不做验证，优先级比auths高
	 *
	 * @var unknown_type
	 */
	public $no_auths = array();


	public function get_module_config($name=null){
		$config = get_class_vars(get_class($this));
		$config = array_merge($config,$this->_config());
		return $name ? @$config[strtolower($name)] : $config;
	}

	public function get_uris_of_controller($controller){
		$controller = rtrim(strtolower($controller), "_controller");
		$config = $this->_config();
		$_ = array();
		foreach ($config['routers'] as $uri => $mapping){
			if(strtolower($mapping['controller']) == $controller){
				$_[] = $uri;
			}
		}
		return $_;
	}
	/**
	 * 加载该模块之间做检查, 出错则抛出异常
	 *
	 * @author leeboo
	 *
	 * @return boolean
	 *
	 * @return
	 *
	 * @throws YZE_RuntimeException
	 */
	public function check(){
		return true;
	}
	/**
	 * 初始化一些配置项的值，返回数组，键为配置名
	 * @return array
	 */
	protected abstract function _config();
    /**
     * js资源分组，在加载时方便直接通过分组名一次性加载所有文件，并支持http缓存机制;<br/><br/>
     * 如果是项目级的资源：<br/>
     *   路径以web 绝对路径/开始，/指的上public_html目录
     *   在layouts中通过接口yze_js_bundle("foo,bar")一次打包加载这里指定的资源<br/><br/>
     * 如果是模块的资源：<br/>
     *   路径以web 绝对路径/开始，/指的上模块下的public_html目录
     *   在layouts中通过接口yze_module_js_bundle("foo,bar")一次打包加载这里指定的资源<br/><br/>
	 * 实现该函数决定如何返回要打包下载的资源
     * @return array(资源路径1，资源路径2)
     */
    public abstract function js_bundle($bundle);
    /**
     * css资源分组，在加载时方便直接通过分组名一次性加载所有文件，并支持http缓存机制;<br/><br/>
     * 如果是项目级的资源：<br/>
     * 资源路径以web 绝对路径/开始，/指的上public_html目录
     * 在layouts中通过接口yze_css_bundle("yangzie,foo,bar")一次打包加载这里指定的资源<br/><br/>
     * 如果是模块的资源：<br/>
     *   路径以web 绝对路径/开始，/指的上模块下的public_html目录
     *   在layouts中通过接口yze_module_css_bundle("yangzie,foo,bar")一次打包加载这里指定的资源<br/><br/>
     * 实现该函数决定如何返回要打包下载的资源
     * @return array(资源路径1，资源路径2)
     */
	public abstract function css_bundle($bundle);
}
?>
