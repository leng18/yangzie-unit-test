--TEST--
YZE_SQL 测试（各种情况的Clean测试）
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
$sql->from(TestModel::class,'t')->where('t','id','>','1')->where('t','title','is not null',null)->group_by('t','created_on')->limit(0,10)->select('t',['id','created_on']);
echo $sql,"\r\n";

$sql->clean_where('t','title');
echo $sql,"\r\n";

$sql->clean_where();
echo $sql,"\r\n";

$sql->clean_groupby();
echo $sql,"\r\n";

$sql->clean_limit();
echo $sql,"\r\n";

$sql->limit(1,10)->clean_select();
echo $sql,"\r\n";

$sql->count('t','id','total',true)->clean_select();
$sql->sum('t','id','sum')->clean_select();
$sql->max('t','id','max')->clean_select();
$sql->min('t','id','min')->clean_select();
echo $sql,"\r\n";

$sql->from(TestModel::class,'t')->where('t','id','>','1')->where('t','title','is not null',null)->group_by('t','created_on')->limit(0,10)->select('t',['id','created_on']);
$sql->clean();
echo $sql,"-\r\n";
?>
--EXPECT--
SELECT t.id AS t_id,t.created_on AS t_created_on FROM `tests` AS t WHERE t.id > '1' AND t.title IS NOT NULL GROUP BY t.created_on LIMIT 0 , 10
SELECT t.id AS t_id,t.created_on AS t_created_on FROM `tests` AS t WHERE t.id > '1' GROUP BY t.created_on LIMIT 0 , 10
SELECT t.id AS t_id,t.created_on AS t_created_on FROM `tests` AS t GROUP BY t.created_on LIMIT 0 , 10
SELECT t.id AS t_id,t.created_on AS t_created_on FROM `tests` AS t LIMIT 0 , 10
SELECT t.id AS t_id,t.created_on AS t_created_on FROM `tests` AS t
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t
SELECT t.id AS t_id,t.title AS t_title,t.created_on AS t_created_on,t.modified_on AS t_modified_on FROM `tests` AS t
SELECT * FROM -

