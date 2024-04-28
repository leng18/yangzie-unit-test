--TEST--
YZE_SQL 测试（删除DELETE的测试）
--FILE--
<?php
namespace  yangzie;
chdir(dirname(dirname(dirname(__FILE__)))."/app/public_html");
include "init.php";

class TestModel extends YZE_Model{
	const TABLE= "tests";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\TestModel';

	const F_TITLE = "title";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'title'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
}

$sql = new YZE_SQL();
$sql->clean()->delete();
echo $sql,"-\r\n";

$sql->clean()->delete()->from(TestModel::class);
echo $sql,"\r\n";

$sql->clean()->delete()->from(TestModel::class,'t');
echo $sql,"\r\n";

$sql->clean()->delete()->from(TestModel::class,'t')->where('t','id','=','1');
echo $sql,"\r\n";

?>
--EXPECT--
DELETE FROM -
DELETE FROM `tests`
DELETE FROM `tests`
DELETE FROM `tests` 
WHERE `id` = '1'
