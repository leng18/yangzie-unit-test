--TEST--
YZE_SQL 测试（各种情况的INSERT测试）
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
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'])->from(TestModel::class,'t');
echo $sql,"\r\n";
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_NORMAL)->from(TestModel::class,'t');
echo $sql,"\r\n";

//有唯一健冲突时先删除原来的，再插入
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_ON_DUPLICATE_KEY_REPLACE)->from(TestModel::class,'t');
echo $sql,"\r\n";

//忽略唯一健冲突;数据将不写入数据库
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_ON_DUPLICATE_KEY_IGNORE)->from(TestModel::class,'t');
echo $sql,"\r\n";

//有唯一键冲突时进行更新
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_ON_DUPLICATE_KEY_UPDATE,['id'])->from(TestModel::class,'t');
echo $sql,"\r\n";

//指定的$checkSql条件查询出数据时才插入
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_EXIST)->from(TestModel::class,'t');
echo $sql,"\r\n";
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_EXIST)->from(TestModel::class,'t')->where('t','title','!=','test2');
echo $sql,"\r\n";
$checkSql = new YZE_SQL();
$checkSql->from(TestModel::class,'m')->where('m','id','=',0)->select('m',['id']);
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_EXIST,$checkSql)->from(TestModel::class,'t');
echo $sql,"\r\n";

//指定的条件不存在时插入
$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_NOT_EXIST)->from(TestModel::class,'t')->where('t','title','!=','test1');
echo $sql,"\r\n";

$sql->clean()->insert('t',['id'=>1,'title'=>'test1'],YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE)->from(TestModel::class,'t')->where('t','title','!=','test1');
echo $sql,"\r\n";

?>
--EXPECT--
INSERT INTO `tests` (`id`,`title`) VALUES(1,'test1')
INSERT INTO `tests` (`id`,`title`) VALUES(1,'test1')
REPLACE INTO `tests` SET `id`=1,`title`='test1'
INSERT IGNORE INTO `tests` (`id`,`title`) VALUES(1,'test1')
INSERT INTO `tests` (`id`,`title`) VALUES(1,'test1')  ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), `title`=VALUES(`title`)
INSERT INTO `tests` (`id`,`title`) SELECT 1,'test1' FROM dual WHERE EXISTS (SELECT id FROM `tests` WHERE )
INSERT INTO `tests` (`id`,`title`) SELECT 1,'test1' FROM dual WHERE EXISTS (SELECT id FROM `tests` WHERE `title` != 'test2')
INSERT INTO `tests` (`id`,`title`) SELECT 1,'test1' FROM dual WHERE EXISTS (SELECT m.id AS m_id FROM `tests` AS m WHERE m.id = 0)
INSERT INTO `tests` (`id`,`title`) SELECT 1,'test1' FROM dual WHERE NOT EXISTS (SELECT id FROM `tests` WHERE `title` != 'test1')
INSERT INTO `tests` (`id`,`title`) SELECT 1,'test1' FROM dual WHERE NOT EXISTS (SELECT id FROM `tests` WHERE `title` != 'test1')
