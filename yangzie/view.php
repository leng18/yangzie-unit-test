<?php

namespace yangzie;

/**
 * 表示一个请求的响应结果。可能是可查看的内容，比如html，xml，json，yaml等，
 * 也可以只是一些http响应头，比如 301 redirect，304 not modified等
 *
 * @access public
 * @author liizii, <libol007@gmail.com>
 */
interface YZE_IResponse {
    /**
     * 输出响应,
     * return 为true表示返回不输出
     */
    public function output($return = false);
    
    /**
     * 取得控制器设置在响应中的值
     * 
     * @package $key
     */
    public function get_data($key);
}
/**
 * 只输出http头，无message－body，表示请求的内容没有修改，客户端应该使用缓存的内容。
 * 
 * @author liizii
 *        
 */
class YZE_Response_304_NotModified implements YZE_IResponse {
    private $headers;
    public function __construct($headers, YZE_Resource_Controller $controller) {
        $this->headers = $headers;
        $this->controller = $controller;
    }
    public function output($return = false) {
        header ( "HTTP/1.1 304 Not Modified" );
        foreach ( ( array ) $this->headers as $name => $value ) {
            header ( "{$name}: {$value}" );
        }
    }
    public function add_header($header_name, $header_value) {
        // TODO 头中需要进行什么编码？
        $this->headers [$header_name] = $header_value;
    }
    public function get_data($key) {
        return $this->headers [$key];
    }
}
/**
 * HTTP Location:重定向，表示一次请求的处理输出是重定向到一个新地址
 * 根据请求返回的格式不同而有不同的输出，如果是html，输出为Header Location:
 * 如果是json，输出为
 *
 * 同时也时源控制器与目标控制器的纽带，
 * sourceURI: 源uri
 * sourceController: 源控制器
 *
 * destinationURI: 目标url
 * destinationController: 目标控制器
 *
 * @author liizii
 *        
 */
class YZE_Redirect implements YZE_IResponse {
    private $sourceURI;
    private $sourceController;
    private $destinationURI;
    private $destinationController;
    private $datas = array ();
    private $outgoing = false;
    private $url_components;
    private $innerRedirect;
    
    /**
     * <b>注意，如果内部重定向，则会出现一url对应多个控制器的情况</b>
     *
     * @param unknown $destination_uri            
     * @param YZE_Resource_Controller $source_controller            
     * @param array $datas
     *            传递给目标控制器的数据
     * @param boolean $innerRedirect
     *            true表示重定向不需要输出到客户端，直接处理
     *            
     */
    public function __construct($destination_uri, 
            YZE_Resource_Controller $source_controller, 
            array $datas = array(), $innerRedirect = false) {
        $this->destinationURI = $destination_uri;
        $this->sourceURI = YZE_Request::get_instance ()->the_full_uri ();
        $this->sourceController = $source_controller;
        $this->datas = $datas;
        $this->innerRedirect = $innerRedirect;
        
        $this->url_components = parse_url ( $this->destinationURI );
        
        if($this->innerRedirect){
        		try{
            $request = YZE_Request::get_instance();
            $request = $request->copy();
            $request->init ($destination_uri,null,null,"get");
            $this->destinationController = $request->controller();
            $request->remove();
            }catch(\Exception $e){}
        }
        
    }
	
    public function output($return=false){
        if ( ! $this->innerRedirect){
            if ( ! $return ){
                header("Location: $this->destinationURI");
                return ;
            }
            return $this->destinationURI;
        }
		
        if ($this->datas && $this->destinationController) {
            YZE_Session_Context::get_instance()->save_controller_datas($this->destinationURI, $this->datas);
        }
		
        $format = $this->sourceController->getRequest()->get_output_format();
        $target_uri = $this->destinationURI;
		
        if($format != "tpl"){
            $ext = pathinfo($this->url_components['path'], PATHINFO_EXTENSION   );
            $target_uri = @preg_replace('/\.'.$ext.'$/', "", $this->url_components['path']).".{$format}?".@$this->url_components['query'].
			(@$this->url_components['fragment'] ? "#".$this->url_components['fragment'] : "");
        }

        //内部重定向，不经过浏览器在请求一次
        return yze_go($target_uri, $this->sourceController->getRequest()->the_method(), true);
    }
	
    public function destinationURI(){
        return $this->destinationURI;
    }
	
    public function sourceURI(){
        return $this->sourceURI();
    }
    public function get_data($key){
        return @$this->datas[$key];
    }
}

/**
 * 视图响应，表示响应的HTTP中有message-body。message-body的内容可能是
 * html，xml，json，yaml等，
 * 由于包含的message-body，视图响应是可缓存的
 */
abstract class YZE_View_Adapter extends YZE_Object implements YZE_IResponse,YZE_Cacheable{
	/**
	 * 响应视图上要显示的数据，具体是什么内容由响应视图自己决定
	 * @var array
	 */
	protected $data;
	/**
	 * 指定该view输出的layout，如果指定了，则优先级最高于controller设置的layout
	 * @var string
	 */
	public $layout;

	/**
	 * 指定视图的容器视图，当前视图的内容将在mater view的$this->content_of_view();中输出，
	 * master view的内容可以嵌套，最顶级的master view的内容将在layout的$this->content_of_view();输出
	 * master也支持content_of_section
	 *
	 * master view的默认查找路径是YZE_APP_VIEWS_INC、模块对应的views下面；你也可以指定决定路径
	 *
	 * 设置方式：$this->master_view = "master" 或者 $this->master_view = "mymaster/master"
	 *
	 * master view的格式使用请求环境的请求格式
	 * @var unknown
	 */
	public $master_view;
	
	/**
	 * 调用check master后找到的master view的绝对路径
	 * @var unknown
	 */
	protected $master_view_path;

	/**
	 * 视图响应的缓存控制
	 * @var YZE_HttpCache
	 */
	private $cache_ctl;
	
	/**
	 *
	 * @var YZE_Resource_Controller
	 */
	protected $controller;#生成Response的Controller
	
	
	public function content_of_section($section){
		return $this->data["content_of_section"][$section];
	}
	
	public function content_of_view(){
		return $this->data["content_of_view"];
	}
	
	/**
	 * 响应视图上要显示的数据，具体是什么内容由响应视图自己决定
	 *
	 * @param array $data 其中的view指当前请求处理时控制器设置的数据，cache指处理请求时之前缓存下来的数据
	 */
	public function __construct($data, YZE_Resource_Controller $controller){
		$this->data = (array)$data;
		$this->controller = $controller;
	}
	
	public function get_controller(){
		return $this->controller;
	}
	
	/**
	 * 没有设置master view，返回false；设置了master view但不存在，抛异常；存在master view，如果存在返回true
	 * master view可以放在模块的views下面或者vender的views下面
	 */
	protected function check_master(){
		//stub
	}
	

	protected function output_master($data, $return=false){
		$datas = $this->get_datas();

		$master = new YZE_Simple_View($this->master_view_path, array(), $this->controller);
		
		$datas['content_of_section'] = $this->view_sections();
		$datas['content_of_view']    = $data;
		$master->set_datas($datas);
		$output = $master->get_output();

		if($return){
			return $output;
		}else{
			echo $output;
		}
	}
	
	public final function output($return=false){

		ob_start();
		if($this->cache_ctl){
			$this->cache_ctl->output();
		}
		
		$this->display_self();
		$data = ob_get_clean();

		//display_self 中包含来view才能得到view中设置的master view数据
		if($this->check_master()){
			return $this->output_master($data, $return);
		}
		
		if($return)return $data;
		echo $data;
	}
	public function view_sections(){
	    return @$this->data['content_of_section'];
	}
	public function begin_section(){
	    ob_start();
	}
	public function end_section($section){
	    $this->data['content_of_section'][$section] = ob_get_clean();
	}
	
	/**
	 * 取得视图的输出内容
	 */
	public function get_output(){
    	return $this->output(true);
	}

	/**
	 * 视图响应显示自己，其布局由视图模块定义，位于views/controller name/action下
	 * 子类根据自己的需要实现视图的加载方式
	 */
	protected abstract function display_self();

	public function get_data($key){
	   return @$this->data[$key];
	}
	public function get_datas(){
	   return  $this->data;
	}
	public function set_data($key, $data){
	    $this->data[$key] = $data;
	}
	public function set_datas(array $datas){
	    $this->data = $datas;
	    return $this;
	}
	public function set_cache_config(YZE_HttpCache $cache=null){
		$this->cache_ctl = $cache;
	}

	/**
	 * 检查模板文件是否存在
	 */
	public function check_view()
	{
		return true;
	}
	
	/**
	 * 
	 * @param YZE_Resource_Controller $controller
	 * @param unknown $data
	 * @param string $success
	 * @param number $errorcode
	 * @param string $msg
	 * 
	 * @return YZE_IResponse
	 */
	public static function build_view(YZE_Resource_Controller $controller, $format, $data, $data_type="data", $success=true, $errorcode=0, $msg=''){
		if($format=="json") return new YZE_JSON_View($controller, $data, $data_type);
		if($format=="xml") return new YZE_XML_View($controller, $data, $data_type);
		return new YZE_Notpl_View($data, $controller);
		
	}
}
/**
 * 视图响应实现，负责加载视图响应模板，视图模板位于views/controller name/action name.tpl.php
 * Simple_View根据请求信息加载对于模块下面的视图模块，并include 它，由于是在对象中include，
 * 在该模板中就可以通过$this->the_date等API取到控制器设置给view的数据
 *
 * 模板可以是生成html的模板，也可以是生成其它数据的模板，比如json，xml等，只是不同的模块对应不同的layout
 * 在view这里它们是一样的。
 */
class YZE_Simple_View extends YZE_View_Adapter {

	
	/**
	 * 通过模板、数据构建视图输出
	 * @param string $tpl 模板的路径全名。
	 * @param array $data
	 * @param YZE_Resource_Controller $controller
	 */
	public function __construct($tpl_name, $data, YZE_Resource_Controller $controller, $format=null){
		parent::__construct($data,$controller);
		$this->tpl 		= $tpl_name;
		$this->format 	= $format ? $format : $controller->getRequest()->get_output_format();
		
	}
	protected function check_master(){
		if( ! $this->master_view ) return false;
		
		$request = YZE_Request::get_instance();
		$module_view_path = $request->view_path();
		
		if( file_exists($module_view_path."/{$this->master_view}.{$this->format}.php")){
			$this->master_view_path = $module_view_path."/{$this->master_view}";
			return true;
		}
		
		if( file_exists(YZE_APP_VIEWS_INC."{$this->master_view}.{$this->format}.php")){
			$this->master_view_path = YZE_APP_VIEWS_INC."{$this->master_view}";
			return true;
		}
		
		if( file_exists("{$this->master_view}.{$this->format}.php")){
			$this->master_view_path = "{$this->master_view}";
			return true;
		}
		
		//如果不是默认的tpl格式，则换成tpl在找一遍，其他情况抛异常
        if($this->format == "tpl"){
            throw new YZE_Resource_Not_Found_Exception(" master view {$this->master_view}.{$this->format}.php not found from below path:
            <ul><li> {$module_view_path}/{$this->master_view}.{$this->format}.php</li>
            <li> ".YZE_APP_VIEWS_INC."{$this->master_view}.{$this->format}.php</li>
            <li> {$this->master_view}.{$this->format}.php</li></ul>");
        }else{
            $this->format = "tpl";
            $this->check_master();
        }
		return true;
	}
	
	public function check_view(){
		
		if( ! file_exists("{$this->tpl}.{$this->format}.php")){
            //if format not exist then use tpl
            if($this->format == "tpl"){
                throw new YZE_Resource_Not_Found_Exception(" view {$this->tpl}.{$this->format}.php not found");
            }else{
                $this->format = "tpl";
                $this->check_view();
            }
		}
	}

	protected function display_self(){

		$this->check_view();
		require "{$this->tpl}.{$this->format}.php";
	}
}
/**
 * 以class的方式来实现view
 * @author ydhlleeboo
 *
 */
abstract class YZE_View_Component extends YZE_View_Adapter{
    /**
     * 输出组件内容
     */
    public abstract function output_component();
    
    public function __construct($data, $controller){
        parent::__construct( $data, $controller);
    }
    
    protected function display_self(){
        $this->output_component();
    }
}
/**
 * 该response没有模板文件，只输出一些字符串，用于那些没有html模板只返回简单数据的地方如json，xml
 *
 */
class YZE_Notpl_View extends YZE_View_Adapter {
	private $html;
	public function __construct($html, YZE_Resource_Controller $controller){
		parent::__construct(array(),$controller);
		$this->html = $html;
	}
	protected function display_self(){
		echo $this->html;
	}
	public function return_html(){
		return $this->html;
	}
}

/**
 * 返回json，返回格式{errorcode, success,msg,data}
 * 
 * @author apple
 *
 */
class YZE_JSON_View extends YZE_View_Adapter {
	/**
	 * 
	 * @param YZE_Resource_Controller $controller
	 * @param unknown $data
	 * @param string $data_type data 为数据，redirect 为重定向
	 * @param string $success
	 * @param number $errorcode
	 * @param string $msg
	 */
	public function __construct(YZE_Resource_Controller $controller, $data){
		parent::__construct($data,$controller);
	}
	protected function display_self(){
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($this->data);
	}


	public static function error($controller, $message =null, $code =null) {
	    return new YZE_JSON_View($controller,  array (
	            'success' => false,
	            "data" => null,
	            "code" => $code,
	            "msg" => $message
	    ) );
	}
	public static function success($controller, $data = null) {
	    return new YZE_JSON_View($controller,  array (
	            'success' => true,
	            "data" => $data,
	            "msg" => null
	    ) );
	}
}
/**
 * 把数据转换成xml输出，输出格式<?xml version="1.0"?>
 * <root><success>1</success><errorcode>0</errorcode><msg></msg><data>your data</data><data_type>data</data_type></root>
 * 
 * @author apple
 *
 */
class YZE_XML_View extends YZE_View_Adapter {
	/**
	 * 
	 * @param YZE_Resource_Controller $controller
	 * @param unknown $data
	 * @param string $data_type data 为数据，redirect 为重定向
	 * @param string $success
	 * @param number $errorcode
	 * @param string $msg
	 */
	public function __construct(YZE_Resource_Controller $controller, $data){
		parent::__construct($data, $controller);
	}
	protected function display_self(){
		$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
		$this->array_to_xml($this->data,$xml);
		
		echo $xml->asXML();
	}
	
	private function array_to_xml($data, &$xml) {
		foreach($data as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml->addChild("$key");
					$this->array_to_xml($value, $subnode);
				}
				else{
					$subnode = $xml->addChild("item$key");
					$this->array_to_xml($value, $subnode);
				}
			}
			else {
				$xml->addChild("$key","$value");
			}
		}
	}
	
	public static function error($controller, $message =null, $code =null) {
	    return new YZE_XML_View($controller, array (
	            'success' => false,
	            "data" => null,
	            "code" => $code,
	            "msg" => $message
	    ) );
	}
	public static function success($controller, $data = null) {
	    return new YZE_XML_View($controller, array (
	            'success' => true,
	            "data" => $data,
	            "msg" => null
	    ) );
	}
}

/**
 * layout指定义视图响应的数据定义格式，比如输出html是<html>....</html>，
 * 输出xml的格式是<xml>...</xml>，json是{}等等，
 *
 * layout也是视图响应，也包含模板，它在定义的响应数据格式中加上请求的视图的内容，这其中有一些约定：
 * layout模板中的content_for_layout指的是请求的视图输出内容。
 * content_for_layout是固定的、表示视图内容的变量
 * 其它的需要在layout中显示的变量，可以在controller中通过set_view_data设置后，
 * 在layout模板中通过$this->view->get_data()取出来。
 *
 * @author liizii
 *
 */
class YZE_Layout extends YZE_View_Adapter{
  	/**
  	 * 
  	 * @var YZE_View_Adapter
  	 */
	private $view;
	
	public function __construct($layout,YZE_View_Adapter $view,  YZE_Resource_Controller $controller){
		parent::__construct($view->get_datas(),$controller);
		$this->view 	= $view;
		$this->layout 	= $layout;
	}
    

	protected function display_self(){
		$this->data = $this->view->get_datas();
		$this->data['content_of_view'] = $this->view->get_output();
		$this->data['content_of_section'] = $this->view->view_sections();

		if(isset($this->view->layout)){
			$this->layout = $this->view->layout;
		}

		if(@$_SERVER['HTTP_X_PJAX']){//pjax 请求，不返回layout
			echo "<title>".$this->get_data("yze_page_title")."</title>";//pjax 加载时设置页面标题
			$this->layout = "";
		}
		if ($this->layout){
		    if(YZE_Request::get_instance()->is_mobile_client()){
		        $moblayoutfile = YZE_APP_LAYOUTS_INC."{$this->layout}.moblayout.php";
		        if( file_exists($moblayoutfile) ){
		            include $moblayoutfile;
		            return;
		        }   
		    }
		    $layoutfile = YZE_APP_LAYOUTS_INC."{$this->layout}.layout.php";
		    if( file_exists($layoutfile) ){
		        include $layoutfile;
		        return;
		    }
		    throw new YZE_Resource_Not_Found_Exception(" layout {$moblayoutfile} not found");
		}else{
			echo $this->data['content_of_view'];
		}
	}
	

}
?>