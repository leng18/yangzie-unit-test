--TEST--
YZE_SQL 测试（各种情况的Where测试）
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
class TestItemModel extends YZE_Model{
	const TABLE= "test_item";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\TestItemModel';

	const F_TITLE = "title";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'title'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'test_id'      => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
    protected $unique_key = array (
      'id' => 'PRIMARY',
      'test_id' => 'fk_test1_idx'
    );
}

$sql = new \yangzie\YZE_SQL();
$sql->clean()->from(TestModel::class, 'a')->where('a','id','=','1')->select('a', ['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class, 'a')->where('a','id','=','1')->where('a','id','=','2')->select('a', ['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class, 'a')->where('a','id','=','1')->or_where('a','id','=','2')->select('a', ['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class, 'a')->where('a','id','=','title', true)->or_where('a','id','=','2')->select('a', ['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'a')->native_Where("a.id = 1 AND a.test_id > 2")->select('a', ['id','test_id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'a')->where('a','id','=','1')->native_Where(" or a.id = 2")->select('a', ['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'b')->where_group([new YZE_Where('b','id','=','1'),new  YZE_Where('b','test_id','=','1')])->select('b', ['id','test_id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'b')->where_group([new YZE_Where('b','id','=','1'),new  YZE_Where('b','test_id','=','1','or')])->select('b', ['id','test_id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'b')->where('b','id','=','2')->where_group([new YZE_Where('b','id','=','1','or'),new  YZE_Where('b','test_id','=','1','or')])->select('b', ['id','test_id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'b')->or_where_group([new YZE_Where('b','id','=','1'),new  YZE_Where('b','test_id','=','2')])->select('b', ['id','test_id']);
echo $sql,"\r\n";

$sql->clean()->from(TestItemModel::class, 'b')->where('b','id','=','2')->or_where_group([new YZE_Where('b','id','=','1'),new  YZE_Where('b','test_id','=','2','OR')])->select('b', ['id','test_id']);
echo $sql,"\r\n";

?>
--EXPECT--
SELECT a.id AS a_id FROM `tests` AS a WHERE a.id = '1'
SELECT a.id AS a_id FROM `tests` AS a WHERE a.id = '1' AND a.id = '2'
SELECT a.id AS a_id FROM `tests` AS a WHERE a.id = '1' OR a.id = '2'
SELECT a.id AS a_id FROM `tests` AS a WHERE a.id = `title` OR a.id = '2'
SELECT a.id AS a_id,a.test_id AS a_test_id FROM `test_item` AS a WHERE a.id = 1 AND a.test_id > 2
SELECT a.id AS a_id FROM `test_item` AS a WHERE a.id = '1' or a.id = 2
SELECT b.id AS b_id,b.test_id AS b_test_id FROM `test_item` AS b WHERE ( b.id = '1' AND b.test_id = '1')
SELECT b.id AS b_id,b.test_id AS b_test_id FROM `test_item` AS b WHERE ( b.id = '1' or b.test_id = '1')
SELECT b.id AS b_id,b.test_id AS b_test_id FROM `test_item` AS b WHERE b.id = '2' AND ( b.id = '1' or b.test_id = '1')
SELECT b.id AS b_id,b.test_id AS b_test_id FROM `test_item` AS b WHERE ( b.id = '1' AND b.test_id = '2')
SELECT b.id AS b_id,b.test_id AS b_test_id FROM `test_item` AS b WHERE b.id = '2' OR ( b.id = '1' OR b.test_id = '2')


