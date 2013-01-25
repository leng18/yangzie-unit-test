<?php
class YZE_Form extends YZE_Object{
	private $form_name;
	private $model;
	private $method = "post";
	private $acl;
	private $view;


	public function __construct(View_Adapter $view,$form_name,Model $model=null){
		$this->form_name = $form_name;
		$this->model = $model;
		$this->view = $view;
		$this->acl = YZE_ACL::get_instance();
	}

	public function begin_form(array $attrs=array(),$is_delete=false){
		ob_start();
		$name = $this->form_name;
		$model = $this->model;
		$html = $modify = '';
		foreach ($attrs as $n=>$value){
			$html .= "$n = '$value' ";
		}
		$token = Session::get_instance()->get_request_token(Request::get_instance()->the_uri());
		if($model){
			$modify = "<input type='hidden' name='yze_modify_version' value='".$model->get_version_value()."'/>
					<input type='hidden' name='yze_model_id' value='".$model->get_key()."'/>
							<input type='hidden' name='yze_model_name' value='".get_class($model)."'/>
									<input type='hidden' name='yze_module_name' value='".$model->get_module_name()."'/>
											<input type='hidden' name='yze_method' value='".($is_delete ? "delete" : "put")."'/>";
		}
		echo "<form name='$name' method='{$this->method}' $html>
		<input type='hidden' name='yze_request_token' value='{$token}'/>
		$modify";
	}
	public function end_form(){
		echo '</form>';
		$form = ob_get_clean();
		$app_auth = new  App_Auth();
		$aroname = $app_auth->get_request_aro_name();
		if($this->acl->check_byname($aroname, $this->form_name)){
			echo $form;
		}
	}
}

function get_default_value($object, $name, $uri=null)
{
	if (Session::post_cache_has($name, $uri)){
		return Session::get_cached_post($name, $uri);
	}
	if ($object){
		return $object->get($name);
	}
	return "";
}
function get_post_error()
{
	$session = Session::get_instance();
	$uri = Request::get_instance()->the_uri();
	if ($session->has_exception($uri)) {
		return nl2br($session->get_uri_exception($uri)->getMessage());
	}
}
?>