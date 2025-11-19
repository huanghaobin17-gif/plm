<?php
/**
 * Created by PhpStorm.
 * User: 邓锦龙
 * Date: 2017/7/17
 * Time: 20:54
 */
namespace Admin\Model;
use Think\Model;
use Think\Model\RelationModel;
import('@.ORG.Util.TableTree'); //Thinkphp导入方法
class SmsModel extends CommonModel
{
    protected $tablePrefix = 'sb_';
    protected $tableName = 'sms';



}