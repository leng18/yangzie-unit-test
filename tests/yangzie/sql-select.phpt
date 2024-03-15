--TEST--
YZE_SQL 测试（各种查询的测试）
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
$sql->clean()->from(TestModel::class)->select('m');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class)->select('*');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->select('*');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->select('t');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->select('t',['id','title']);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->select('t',['id'])->distinct('t','title');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->count('t','id','total',true);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->count('t','*','total');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->max('t','id','max_id');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->min('t','id','min_id');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->sum('t','id','sum_id');
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->limit(0,10)->select('t',['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->limit(0)->select('t',['id']);
echo $sql,"\r\n";

$sql->clean()->from(TestModel::class,'t')->limit(10)->select('t',['id']);
echo $sql,"\r\n";

?>
--EXPECT--
SELECT m.id AS m_id,m.title AS m_title,m.created_on AS m_created_on,m.modified_on AS m_modified_on FROM `tests` AS m
SELECT m.id AS m_id,m.title AS m_title,m.created_on AS m_created_on,m.modified_on AS m_modified_on FROM `tests` AS m
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t
SELECT t.id AS t_id,t.title AS t_title FROM `tests` AS t
SELECT distinct t.title AS t_title,t.id AS t_id FROM `tests` AS t
SELECT count(distinct t.id) AS t_total FROM `tests` AS t
SELECT count( *) AS t_total FROM `tests` AS t
SELECT max(t.id) AS t_max_id FROM `tests` AS t
SELECT min(t.id) AS t_min_id FROM `tests` AS t
SELECT sum(t.id) AS t_sum_id FROM `tests` AS t
SELECT t.id AS t_id FROM `tests` AS t LIMIT 0 , 10
SELECT t.id AS t_id FROM `tests` AS t
SELECT t.id AS t_id FROM `tests` AS t LIMIT 10
