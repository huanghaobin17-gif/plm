/*
 Navicat Premium Dump SQL

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50743 (5.7.43-log)
 Source Host           : localhost:3306
 Source Schema         : tecev_test_data

 Target Server Type    : MySQL
 Target Server Version : 50743 (5.7.43-log)
 File Encoding         : 65001

 Date: 10/01/2025 16:22:02
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sb_approve
-- ----------------------------
DROP TABLE IF EXISTS `sb_approve`;
CREATE TABLE `sb_approve` (
  `apprid` int(10) NOT NULL AUTO_INCREMENT,
  `repid` int(10) DEFAULT NULL COMMENT '维修单ID，与巡查ID（patrid），报废ID（scrapid）三填一',
  `allotid` int(11) DEFAULT '0' COMMENT '附属设备分配id',
  `atid` int(11) DEFAULT NULL COMMENT '转科记录表ID',
  `patrid` int(10) DEFAULT NULL COMMENT '巡查ID,与维修单ID（repid）、报废ID（scrapid）三填一',
  `scrapid` int(10) DEFAULT NULL COMMENT '报废ID,与维修单ID（repid）、巡查ID（patrid）三填一',
  `borid` int(10) DEFAULT NULL COMMENT '借调id',
  `outid` int(11) DEFAULT NULL COMMENT '外调id',
  `purchases_plans_id` int(11) DEFAULT NULL COMMENT '年度采购计划',
  `depart_apply_id` int(11) DEFAULT NULL COMMENT '科室申请',
  `approve_num` varchar(50) NOT NULL DEFAULT '' COMMENT '审批编码，记录维修编码、巡查编码等',
  `approve_class` varchar(50) DEFAULT NULL COMMENT '审批类型，维修（repair）、巡查（patorl）、报废（scrap）',
  `process_node_level` tinyint(3) DEFAULT '0' COMMENT '流程节点顺序',
  `process_node` varchar(50) NOT NULL COMMENT '流程节点，申报审批(applicant_approve)、保修申请审批(guarantee_approve)，或者以后巡查的其他审批流程节点等',
  `processid` int(11) DEFAULT NULL COMMENT '流程ID',
  `inventory_plan_id` int(10) DEFAULT NULL COMMENT '盘点计划id',
  `proposer` varchar(50) NOT NULL COMMENT '申请人，审批流程申请人',
  `proposer_time` int(10) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `approver` varchar(50) NOT NULL COMMENT '审批人',
  `approve_time` int(10) NOT NULL COMMENT '审批时间',
  `is_adopt` tinyint(1) DEFAULT '3' COMMENT '是否审批，1为同意，2为不同意，3为未审核',
  `remark` text COMMENT '原因',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0=''未删除'',1=''已删除''】',
  PRIMARY KEY (`apprid`) USING BTREE,
  KEY `process_node` (`process_node`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='审批业务——各个业务流程的审批记录表';

-- ----------------------------
-- Records of sb_approve
-- ----------------------------


-- ----------------------------
-- Table structure for sb_approve_process
-- ----------------------------
DROP TABLE IF EXISTS `sb_approve_process`;
CREATE TABLE `sb_approve_process` (
  `processid` int(11) NOT NULL AUTO_INCREMENT,
  `typeid` int(3) NOT NULL DEFAULT '0' COMMENT '审批类型ID',
  `condition_type` varchar(20) DEFAULT NULL COMMENT '流程条件【egt大于等于，lt小于，between区间】',
  `approve_name` varchar(60) DEFAULT NULL COMMENT '审核名称',
  `start_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格区间(始)',
  `end_price` decimal(20,2) DEFAULT NULL,
  `adduser` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(30) DEFAULT NULL COMMENT '修改人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '审批流程备注',
  PRIMARY KEY (`processid`) USING BTREE,
  KEY `type` (`typeid`) USING BTREE COMMENT '类型索引'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='审批设置——审批流程表';

-- ----------------------------
-- Records of sb_approve_process
-- ----------------------------


-- ----------------------------
-- Table structure for sb_approve_process_user
-- ----------------------------
DROP TABLE IF EXISTS `sb_approve_process_user`;
CREATE TABLE `sb_approve_process_user` (
  `puid` int(11) NOT NULL AUTO_INCREMENT,
  `processid` int(11) NOT NULL COMMENT '审批流程ID',
  `listorder` int(11) DEFAULT NULL COMMENT '审批排序',
  `approve_user` varchar(20) DEFAULT NULL COMMENT '主审核人',
  `approve_user_aux` varchar(20) DEFAULT NULL COMMENT '审核辅助人',
  PRIMARY KEY (`puid`) USING BTREE,
  KEY `type` (`processid`,`approve_user`) USING BTREE COMMENT '索引'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='审批设置——审批流程用户表';

-- ----------------------------
-- Records of sb_approve_process_user
-- ----------------------------


-- ----------------------------
-- Table structure for sb_approve_type
-- ----------------------------
DROP TABLE IF EXISTS `sb_approve_type`;
CREATE TABLE `sb_approve_type` (
  `typeid` int(11) NOT NULL AUTO_INCREMENT COMMENT '(1:维修 3:报废)',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `approve_type` varchar(60) DEFAULT NULL COMMENT '审批类型',
  `type_name` varchar(60) DEFAULT NULL COMMENT '审批类型名称',
  `status` tinyint(3) DEFAULT '1' COMMENT '是否启用【1启用0未启用】',
  `is_mandatory` tinyint(1) DEFAULT '0' COMMENT '是否必需设置关联部门项 1->必需',
  `count` int(11) DEFAULT '0' COMMENT '0：不限制数量 其他：则需限制数量',
  PRIMARY KEY (`typeid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='审批设置——审批类型';

-- ----------------------------
-- Records of sb_approve_type
-- ----------------------------
BEGIN;
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (1, 1, 'repair_approve', '维修审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (2, 1, 'transfer_approve', '转科审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (3, 1, 'scrap_approve', '报废审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (4, 1, 'outside_approve', '外调审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (5, 1, 'purchases_plans_approve', '采购计划审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (6, 1, 'depart_apply_approve', '科室计划审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (7, 1, 'subsidiary_approve', '附属设备分配审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (8, 1, 'patrol_approve', '巡查保养审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (10, 2, 'repair_approve', '维修审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (11, 2, 'transfer_approve', '转科审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (12, 2, 'scrap_approve', '报废审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (13, 2, 'outside_approve', '外调审批', 0, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (14, 2, 'purchases_plans_approve', '采购计划审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (15, 2, 'depart_apply_approve', '科室计划审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (16, 2, 'subsidiary_approve', '附属设备分配审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (17, 2, 'patrol_approve', '巡查保养审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (18, 3, 'repair_approve', '维修审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (19, 3, 'transfer_approve', '转科审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (20, 3, 'scrap_approve', '报废审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (21, 3, 'outside_approve', '外调审批', 0, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (22, 3, 'purchases_plans_approve', '采购计划审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (23, 3, 'depart_apply_approve', '科室计划审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (24, 3, 'subsidiary_approve', '附属设备分配审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (25, 3, 'patrol_approve', '巡查保养审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (26, 1, 'inventory_plan_approve', '盘点审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (27, 2, 'inventory_plan_approve', '盘点审批', 1, 0, 0);
INSERT INTO `sb_approve_type` (`typeid`, `hospital_id`, `approve_type`, `type_name`, `status`, `is_mandatory`, `count`) VALUES (28, 3, 'inventory_plan_approve', '盘点审批', 1, 0, 0);
COMMIT;

-- ----------------------------
-- Table structure for sb_archives_box
-- ----------------------------
DROP TABLE IF EXISTS `sb_archives_box`;
CREATE TABLE `sb_archives_box` (
  `box_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '档案盒ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `box_name` varchar(60) DEFAULT NULL COMMENT '档案盒名称',
  `box_num` varchar(60) NOT NULL DEFAULT '' COMMENT '档案盒编号',
  `code_url` varchar(255) DEFAULT NULL COMMENT '二维码图片地址',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0未1已删】',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`box_id`) USING BTREE,
  UNIQUE KEY `box_num` (`box_num`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='档案盒管理';

-- ----------------------------
-- Records of sb_archives_box
-- ----------------------------


-- ----------------------------
-- Table structure for sb_archives_emergency_category
-- ----------------------------
DROP TABLE IF EXISTS `sb_archives_emergency_category`;
CREATE TABLE `sb_archives_emergency_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL COMMENT '分类名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='应急预案分类';

-- ----------------------------
-- Records of sb_archives_emergency_category
-- ----------------------------


-- ----------------------------
-- Table structure for sb_archives_emergency_plan
-- ----------------------------
DROP TABLE IF EXISTS `sb_archives_emergency_plan`;
CREATE TABLE `sb_archives_emergency_plan` (
  `arempid` int(10) NOT NULL AUTO_INCREMENT COMMENT '预案自增ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `category` varchar(50) NOT NULL COMMENT '预案分类',
  `emergency` varchar(255) NOT NULL COMMENT '应急预案名称',
  `content` text COMMENT '预案正文',
  `add_date` date NOT NULL COMMENT '添加日期',
  `add_userid` int(10) NOT NULL COMMENT '添加者ID',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_userid` int(11) DEFAULT NULL COMMENT '修改人ID',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1是0否】',
  PRIMARY KEY (`arempid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='档案管理——应急预案';

-- ----------------------------
-- Records of sb_archives_emergency_plan
-- ----------------------------


-- ----------------------------
-- Table structure for sb_archives_emergency_plan_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_archives_emergency_plan_file`;
CREATE TABLE `sb_archives_emergency_plan_file` (
  `aremfid` int(10) NOT NULL AUTO_INCREMENT COMMENT '应急预案文件自增ID',
  `arempid` int(10) NOT NULL COMMENT '应急预案ID',
  `file_name` varchar(255) NOT NULL COMMENT '文件名称',
  `save_name` varchar(255) NOT NULL COMMENT '文件保存名称',
  `file_type` varchar(10) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,0) DEFAULT '0' COMMENT '文件大小',
  `file_url` varchar(255) NOT NULL COMMENT '文件地址',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `add_user` varchar(30) NOT NULL DEFAULT '0' COMMENT '添加人',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1是0否】',
  PRIMARY KEY (`aremfid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='档案管理——应急预案相关文件';

-- ----------------------------
-- Records of sb_archives_emergency_plan_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_archives_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_archives_file`;
CREATE TABLE `sb_assets_archives_file` (
  `arc_id` int(11) NOT NULL AUTO_INCREMENT,
  `box_id` int(11) DEFAULT NULL COMMENT '档案盒ID',
  `assid` int(11) DEFAULT NULL COMMENT '所属设备ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `file_type` varchar(10) DEFAULT NULL COMMENT '文件类型【images,pdf,doc】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '上传人',
  `add_time` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `archive_time` date DEFAULT NULL COMMENT '档案时间',
  `expire_time` date DEFAULT NULL COMMENT '过期时间',
  `identifier` varchar(10) DEFAULT NULL COMMENT '身份标识码',
  `unplan_class` varchar(16) DEFAULT NULL COMMENT '设备质控记录（ZK），设备计量计量（JL），设备不良事件记录（BLSJ）',
  PRIMARY KEY (`arc_id`) USING BTREE,
  KEY `assid` (`assid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备档案资料文件表';

-- ----------------------------
-- Records of sb_assets_archives_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_benefit
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_benefit`;
CREATE TABLE `sb_assets_benefit` (
  `benefitid` int(10) NOT NULL AUTO_INCREMENT,
  `assnum` varchar(25) NOT NULL COMMENT '设备编号',
  `departid` int(10) NOT NULL COMMENT '设备科室id',
  `entryDate` varchar(12) DEFAULT NULL COMMENT '录入月份',
  `income` decimal(10,2) DEFAULT '0.00' COMMENT '月收入',
  `work_number` int(10) DEFAULT '0' COMMENT '诊疗次数',
  `depreciation_cost` decimal(10,2) DEFAULT '0.00' COMMENT '折旧费用',
  `material_cost` decimal(10,2) DEFAULT '0.00' COMMENT '材料费用',
  `maintenance_cost` decimal(10,2) DEFAULT '0.00' COMMENT '维保费用',
  `management_cost` decimal(10,2) DEFAULT '0.00' COMMENT '管理费用',
  `comprehensive_cost` decimal(10,2) DEFAULT '0.00' COMMENT '综合费用',
  `interest_cost` decimal(10,2) DEFAULT '0.00' COMMENT '利息支出',
  `operator` int(10) DEFAULT '0' COMMENT '操作人员数量',
  `work_day` int(10) DEFAULT '0' COMMENT '工作天数',
  `positive_rate` int(10) DEFAULT '0' COMMENT '诊疗阳性次数',
  `adddate` int(10) DEFAULT NULL COMMENT '记录时间',
  `adduser` varchar(25) DEFAULT NULL COMMENT '记录人员',
  `edituser` varchar(25) DEFAULT '' COMMENT '修改人员',
  `editdate` int(10) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`benefitid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='效益分析——设备效益表';

-- ----------------------------
-- Records of sb_assets_benefit
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_benefit_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_benefit_upload_temp`;
CREATE TABLE `sb_assets_benefit_upload_temp` (
  `tempid` varchar(32) NOT NULL DEFAULT '' COMMENT '临时ID',
  `assets` varchar(255) DEFAULT '' COMMENT '资产名称',
  `assnum` varchar(32) NOT NULL COMMENT '设备编号',
  `model` varchar(25) DEFAULT NULL COMMENT '规格/型号',
  `department` varchar(255) DEFAULT NULL COMMENT '科室',
  `departid` int(10) NOT NULL COMMENT '设备科室id',
  `entryDate` varchar(12) DEFAULT NULL COMMENT '录入月份',
  `income` decimal(10,2) DEFAULT '0.00' COMMENT '月收入',
  `work_number` int(10) DEFAULT '0' COMMENT '诊疗次数',
  `depreciation_cost` decimal(10,2) DEFAULT '0.00' COMMENT '折旧费用',
  `material_cost` decimal(10,2) DEFAULT '0.00' COMMENT '材料费用',
  `maintenance_cost` decimal(10,2) DEFAULT '0.00' COMMENT '维保费用',
  `management_cost` decimal(10,2) DEFAULT '0.00' COMMENT '管理费用',
  `comprehensive_cost` decimal(10,2) DEFAULT '0.00' COMMENT '综合费用',
  `interest_cost` decimal(10,2) DEFAULT '0.00' COMMENT '利息支出',
  `operator` int(10) DEFAULT '0' COMMENT '操作人员',
  `work_day` int(10) DEFAULT '0' COMMENT '工作天数',
  `positive_rate` int(10) DEFAULT '0' COMMENT '诊疗阳性次数',
  `adddate` int(10) DEFAULT NULL COMMENT '记录时间',
  `adduser` varchar(25) DEFAULT NULL COMMENT '上传人',
  `editdate` int(10) DEFAULT NULL COMMENT '最后修改时间',
  `edituser` varchar(25) DEFAULT NULL COMMENT '最后修改人',
  `is_save` tinyint(1) DEFAULT '0' COMMENT '是否已入库保存【1是0否】',
  PRIMARY KEY (`tempid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='效益分析——设备效益上传excel临时表';

-- ----------------------------
-- Records of sb_assets_benefit_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_borrow
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_borrow`;
CREATE TABLE `sb_assets_borrow` (
  `borid` int(11) NOT NULL AUTO_INCREMENT COMMENT '借调申请自增ID',
  `assid` int(11) NOT NULL COMMENT '设备ID',
  `borrow_num` varchar(50) NOT NULL COMMENT '借调流水号',
  `apply_userid` int(11) NOT NULL COMMENT '申请人ID',
  `apply_departid` int(11) NOT NULL COMMENT '申请科室ID',
  `borrow_reason` text NOT NULL COMMENT '借调原因',
  `supplement` text COMMENT '清单补充',
  `estimate_back` int(10) NOT NULL COMMENT '预计归还时间',
  `apply_time` int(10) DEFAULT NULL COMMENT '借调申请时间',
  `not_apply_time` int(11) DEFAULT NULL COMMENT '不借入时间',
  `borrow_in_time` int(10) DEFAULT NULL COMMENT '确认借入时间(借入验收的时间)',
  `retrial_status` tinyint(1) DEFAULT '0' COMMENT '审批不通过后是否申请重审【1等待操作2重审中3直接结束】',
  `borrow_in_userid` int(10) DEFAULT NULL COMMENT '借入验收用户',
  `give_back_time` int(10) DEFAULT NULL COMMENT '实际归还时间/归还验收时间',
  `give_back_userid` int(10) DEFAULT NULL COMMENT '归还验收用户id',
  `end_reason` text COMMENT '不借入的原因',
  `score_value` int(11) DEFAULT NULL COMMENT '归还评分',
  `score_remark` text COMMENT '归还描述',
  `examine_status` tinyint(1) DEFAULT '0' COMMENT '是否通过审核=【''-1''=''不需审核'',''0''=''未审核'',''1''=''通过'',''2''=''不通过''】',
  `status` tinyint(1) DEFAULT '0' COMMENT '借调状态 -1：审核失败  0:待审核 1:待借入验收 2:待归还 3:完成 4:不借入',
  `examine_time` int(11) DEFAULT NULL COMMENT '审核结束时间',
  PRIMARY KEY (`borid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——借调管理';

-- ----------------------------
-- Records of sb_assets_borrow
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_borrow_approve
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_borrow_approve`;
CREATE TABLE `sb_assets_borrow_approve` (
  `bor_app_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '借调审批自增ID',
  `borid` int(11) NOT NULL COMMENT '借调申请ID',
  `level` tinyint(1) NOT NULL COMMENT '审批级别',
  `approve_userid` int(11) NOT NULL COMMENT '审批人ID',
  `approve_time` int(11) NOT NULL COMMENT '审批时间',
  `approve_status` tinyint(1) DEFAULT NULL COMMENT '审批状态：2不通过，1通过',
  `remark` varchar(255) DEFAULT NULL COMMENT '审批意见',
  PRIMARY KEY (`bor_app_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——借调管理——审批记录';

-- ----------------------------
-- Records of sb_assets_borrow_approve
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_borrow_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_borrow_detail`;
CREATE TABLE `sb_assets_borrow_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `borid` int(10) DEFAULT NULL COMMENT '借调id',
  `subsidiary_assid` int(10) DEFAULT NULL COMMENT '附属设备id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=gbk ROW_FORMAT=FIXED COMMENT='资产管理——借调附属设备信息';

-- ----------------------------
-- Records of sb_assets_borrow_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_borrow_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_borrow_file`;
CREATE TABLE `sb_assets_borrow_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `borid` int(11) NOT NULL COMMENT '借调id',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT '0.00' COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——借调附件表';

-- ----------------------------
-- Records of sb_assets_borrow_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_contract
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_contract`;
CREATE TABLE `sb_assets_contract` (
  `acid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资产合同ID',
  `assid` int(10) unsigned NOT NULL COMMENT '资产ID',
  `contract` varchar(255) NOT NULL COMMENT '合同名称',
  `con_date` int(10) unsigned NOT NULL COMMENT '签订日期',
  `price` int(10) NOT NULL COMMENT '合同价格',
  `buy_date` int(10) unsigned NOT NULL COMMENT '购入日期',
  `standard_date` int(10) unsigned NOT NULL COMMENT '验收合格日期',
  `guarantee_date` int(10) unsigned NOT NULL COMMENT '保修到期日期',
  `adddate` int(10) unsigned NOT NULL COMMENT '添加日期',
  `editdate` int(10) unsigned NOT NULL COMMENT '修改日期',
  PRIMARY KEY (`acid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——合同表';

-- ----------------------------
-- Records of sb_assets_contract
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_depreciation
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_depreciation`;
CREATE TABLE `sb_assets_depreciation` (
  `depid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '折旧ID',
  `assid` int(10) unsigned NOT NULL COMMENT '资产ID',
  PRIMARY KEY (`depid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='资产管理——折旧表';

-- ----------------------------
-- Records of sb_assets_depreciation
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_factory
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_factory`;
CREATE TABLE `sb_assets_factory` (
  `afid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资产厂商ID',
  `assid` int(11) NOT NULL DEFAULT '0' COMMENT '资产ID组',
  `ols_facid` int(10) DEFAULT '0' COMMENT '对应生产厂商ID',
  `factory` varchar(255) DEFAULT NULL COMMENT '生产厂商',
  `factory_user` varchar(50) DEFAULT NULL COMMENT '生产厂商联系人',
  `factory_tel` varchar(20) DEFAULT NULL COMMENT '生产厂商联系电话',
  `ols_supid` int(10) DEFAULT '0' COMMENT '对应供应商ID',
  `supplier` varchar(255) DEFAULT NULL COMMENT '供应商',
  `supp_user` varchar(50) DEFAULT NULL COMMENT '供应商联系人',
  `supp_tel` varchar(20) DEFAULT NULL COMMENT '供应商联系电话',
  `ols_repid` int(10) DEFAULT '0' COMMENT '对应维修商ID',
  `repair` varchar(255) DEFAULT NULL COMMENT '维修公司',
  `repa_user` varchar(50) DEFAULT NULL COMMENT '维修公司联系人',
  `repa_tel` varchar(20) DEFAULT NULL COMMENT '维修公司联系电话',
  `ols_insurid` int(11) DEFAULT '0' COMMENT '维保厂家id',
  `insurance` varchar(150) DEFAULT NULL COMMENT '维保厂家',
  `insur_user` varchar(50) DEFAULT NULL COMMENT '维保公司联系人',
  `insur_tel` varchar(20) DEFAULT NULL COMMENT '维保公司联系人电话',
  `adddate` int(10) unsigned NOT NULL COMMENT '添加日期',
  `editdate` int(10) DEFAULT '0' COMMENT '修改日期',
  PRIMARY KEY (`afid`) USING BTREE,
  KEY `assid` (`assid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——厂商信息表（维修、供应、生产）';

-- ----------------------------
-- Records of sb_assets_factory
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_factory_qualification_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_factory_qualification_file`;
CREATE TABLE `sb_assets_factory_qualification_file` (
  `quali_id` int(11) NOT NULL AUTO_INCREMENT,
  `assid` int(11) DEFAULT NULL COMMENT '所属设备ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `file_type` varchar(10) DEFAULT NULL COMMENT '文件类型【images,pdf,doc】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '上传人',
  `add_time` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`quali_id`) USING BTREE,
  KEY `assid` (`assid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备厂商资质文件表';

-- ----------------------------
-- Records of sb_assets_factory_qualification_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_increment
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_increment`;
CREATE TABLE `sb_assets_increment` (
  `aiid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附属设备自增ID',
  `assid` int(10) unsigned NOT NULL COMMENT '设备ID',
  `incre_catid` int(10) unsigned NOT NULL COMMENT '附属设备分类ID',
  `increnum` varchar(50) NOT NULL DEFAULT '' COMMENT '附属设备编号',
  `increname` varchar(50) NOT NULL DEFAULT '' COMMENT '附属设备名称',
  `brand` varchar(50) DEFAULT NULL COMMENT '附属设备品牌',
  `model` varchar(50) DEFAULT NULL COMMENT '附属设备规格型号',
  `incre_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
  `increprice` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `addtime` int(10) unsigned NOT NULL COMMENT '添加时间',
  `adduser` varchar(60) NOT NULL DEFAULT '0' COMMENT '录入人员',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `edittime` int(10) DEFAULT '0' COMMENT '修改时间',
  `edituser` varchar(60) NOT NULL DEFAULT '0' COMMENT '修改人员',
  PRIMARY KEY (`aiid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——附属设备';

-- ----------------------------
-- Records of sb_assets_increment
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_info
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_info`;
CREATE TABLE `sb_assets_info` (
  `assid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资产ID',
  `main_assid` int(11) DEFAULT '0' COMMENT '所属主设备id',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `code` varchar(10) NOT NULL COMMENT '医院代码',
  `acid` int(10) NOT NULL COMMENT '资产合同ID',
  `afid` int(10) NOT NULL COMMENT '资产厂商ID',
  `catid` int(10) NOT NULL COMMENT '68分类ID',
  `assnum` varchar(25) DEFAULT '' COMMENT '资产编码',
  `assorignum` varchar(25) DEFAULT NULL COMMENT '资产原编号',
  `barcore` varchar(25) NOT NULL COMMENT '资产条形码',
  `assets` varchar(255) NOT NULL DEFAULT '' COMMENT '资产名称',
  `assets_level` tinyint(3) DEFAULT NULL COMMENT '医疗器械类别【Ⅰ类=1、Ⅱ类=2、Ⅲ类=3】',
  `common_name` varchar(255) DEFAULT NULL COMMENT '设备常用名',
  `main_assets` varchar(255) DEFAULT NULL COMMENT '所属主设备名称',
  `helpcatid` tinyint(1) DEFAULT NULL COMMENT '辅助分类',
  `subsidiary_helpcatid` tinyint(1) DEFAULT NULL COMMENT '附属设备辅助分类',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '资产状态[0=''在用'',1=''维修中'',2=''已报废'',3=''已外调'',4=''外调中'',5=''报废中'',6=''转科中'']',
  `financeid` tinyint(1) DEFAULT NULL COMMENT '财务分类',
  `brand` varchar(255) NOT NULL COMMENT '品牌',
  `model` varchar(255) NOT NULL COMMENT '规格/型号',
  `unit` varchar(10) NOT NULL COMMENT '单位',
  `serialnum` varchar(50) NOT NULL COMMENT '资产序列号',
  `assetsrespon` varchar(20) NOT NULL COMMENT '资产负责人',
  `departid` int(10) unsigned NOT NULL COMMENT '所属科室',
  `address` varchar(255) NOT NULL COMMENT '存放地点',
  `managedepart` varchar(50) NOT NULL COMMENT '管理科室',
  `factorynum` varchar(50) DEFAULT NULL COMMENT '出厂编号',
  `factorydate` date DEFAULT NULL COMMENT '出厂日期',
  `opendate` date DEFAULT NULL COMMENT '开机日期',
  `storage_date` date DEFAULT NULL COMMENT '入库日期',
  `capitalfrom` tinyint(1) DEFAULT NULL COMMENT '资金来源',
  `assfromid` tinyint(1) DEFAULT NULL COMMENT '设备来源',
  `invoicenum` varchar(50) DEFAULT NULL COMMENT '发票编号',
  `buy_price` decimal(10,2) DEFAULT '0.00' COMMENT '设备原值',
  `paytime` date DEFAULT NULL COMMENT '付款时间',
  `pay_status` tinyint(3) DEFAULT '3' COMMENT '设备付款是否付清【1已付清0未付清】',
  `expected_life` int(11) DEFAULT '0' COMMENT '预计使用年限',
  `residual_value` varchar(10) DEFAULT NULL COMMENT '残净值率',
  `is_firstaid` tinyint(3) DEFAULT '0' COMMENT '是否急救资产【1是0否】',
  `is_special` tinyint(3) DEFAULT '0' COMMENT '是否特种设备【1是0否】',
  `is_metering` tinyint(3) DEFAULT '0' COMMENT '是否计量资产【1是0否】',
  `is_qualityAssets` tinyint(3) DEFAULT '0' COMMENT '是否质控设备【1是0否】',
  `is_benefit` tinyint(1) DEFAULT '0' COMMENT '是否效益分析【1是0否】',
  `is_lifesupport` tinyint(3) DEFAULT '0' COMMENT '是否生命支持类设备【0否1是】',
  `is_subsidiary` tinyint(1) DEFAULT '0' COMMENT '是否附属设备【0否1是】',
  `guarantee_date` date DEFAULT NULL COMMENT '报修截止日期',
  `depreciation_method` tinyint(3) DEFAULT '0' COMMENT '折旧方式',
  `depreciable_lives` int(11) DEFAULT NULL COMMENT '折旧年限',
  `net_assets` decimal(10,2) DEFAULT NULL COMMENT '资产净额',
  `impairment_provision` decimal(10,2) DEFAULT NULL COMMENT '减值准备',
  `net_asset_value` decimal(10,2) DEFAULT NULL COMMENT '资产净值',
  `depreciable_quota_count` decimal(10,2) DEFAULT NULL COMMENT '累计折旧额',
  `depreciable_quota_m` decimal(10,2) DEFAULT NULL COMMENT '月折旧额',
  `pic_url` text COMMENT '图片地址',
  `code_url` varchar(255) DEFAULT NULL COMMENT '二维码图片地址',
  `adduser` varchar(60) NOT NULL COMMENT '录入员工',
  `adddate` int(10) NOT NULL DEFAULT '0' COMMENT '录入时间',
  `edituser` varchar(255) DEFAULT NULL COMMENT '最后修改人',
  `editdate` int(10) DEFAULT NULL COMMENT '最后修改时间',
  `lastrepairtime` int(10) DEFAULT '0' COMMENT '上一次维修时间',
  `insuredsum` int(10) NOT NULL DEFAULT '0' COMMENT '参保次数',
  `lasttesttime` timestamp NULL DEFAULT NULL COMMENT '上次检测时间',
  `lasttestuser` varchar(60) DEFAULT NULL COMMENT '上次检测人',
  `lasttestresult` tinyint(3) DEFAULT NULL COMMENT '上次检测结果',
  `quality_in_plan` tinyint(3) DEFAULT '0' COMMENT '质控计划中【1是0否】',
  `patrol_in_plan` tinyint(3) DEFAULT '0' COMMENT '巡查计划中【1是0否】',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1为已删除0未删除】',
  `is_patrol` tinyint(3) DEFAULT NULL COMMENT '是否保养设备【1是0否】',
  `is_domestic` tinyint(3) DEFAULT '3' COMMENT '设备是否国产【1国产2进口】',
  `remark` varchar(255) DEFAULT NULL COMMENT '设备备注',
  `assorignum_spare` varchar(60) DEFAULT NULL COMMENT '设备原编码（备用）',
  `patrol_xc_cycle` varchar(60) DEFAULT NULL COMMENT '巡查周期',
  `patrol_pm_cycle` varchar(60) DEFAULT NULL COMMENT '保养周期',
  `quality_cycle` varchar(60) DEFAULT NULL COMMENT '质控周期',
  `metering_cycle` varchar(60) DEFAULT NULL COMMENT '计量周期',
  `patrol_nums` int(10) DEFAULT '0' COMMENT '总巡查次数',
  `maintain_nums` int(10) DEFAULT '0' COMMENT '总保养次数',
  `patrol_dates` text COMMENT '历史巡查日期',
  `maintain_dates` text COMMENT '历史保养日期',
  `pre_patrol_date` date DEFAULT NULL COMMENT '上一次巡查日期',
  `pre_patrol_executor` varchar(60) DEFAULT NULL COMMENT '上一次巡查执行人',
  `pre_patrol_result` varchar(30) DEFAULT NULL COMMENT '上一次巡查结果',
  `pre_maintain_date` date DEFAULT NULL COMMENT '上一次保养日期',
  `pre_maintain_executor` varchar(60) DEFAULT NULL COMMENT '上一次保养执行人',
  `pre_maintain_result` varchar(30) DEFAULT NULL COMMENT '上一次保养结果',
  `print_status` tinyint(3) DEFAULT '0' COMMENT '标签打印状态【0初始状态，1已核实，2已核实无法贴标】',
  `box_num` varchar(60) DEFAULT NULL COMMENT '档案盒编号',
  `registration` varchar(255) DEFAULT NULL COMMENT '注册证编号',
  `inventory_label_id` varchar(256) DEFAULT NULL COMMENT '盘点标签id',
  PRIMARY KEY (`assid`) USING BTREE,
  UNIQUE KEY `assnum` (`assnum`) USING BTREE,
  KEY `assnum_assets_catid_departid_status` (`assnum`,`assets`,`catid`,`departid`,`status`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备基本信息表';

-- ----------------------------
-- Records of sb_assets_info
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_info_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_info_upload_temp`;
CREATE TABLE `sb_assets_info_upload_temp` (
  `tempid` varchar(32) NOT NULL DEFAULT '' COMMENT '临时ID',
  `hospital_code` varchar(20) DEFAULT NULL COMMENT '医院代码',
  `cate` varchar(30) DEFAULT NULL COMMENT '设备分类',
  `assorignum` varchar(25) DEFAULT NULL COMMENT '资产原编号',
  `assets` varchar(255) NOT NULL DEFAULT '' COMMENT '资产名称',
  `assets_level` tinyint(3) DEFAULT NULL COMMENT '医疗器械类别【Ⅰ类=1、Ⅱ类=2、Ⅲ类=3】',
  `helpcat` varchar(20) DEFAULT NULL COMMENT '辅助分类',
  `finance` varchar(20) DEFAULT NULL COMMENT '财务分类',
  `brand` varchar(255) NOT NULL COMMENT '品牌',
  `model` varchar(255) NOT NULL COMMENT '规格/型号',
  `unit` varchar(10) NOT NULL COMMENT '单位',
  `serialnum` varchar(50) NOT NULL COMMENT '资产序列号',
  `assetsrespon` varchar(20) NOT NULL COMMENT '资产负责人',
  `department` varchar(20) DEFAULT NULL COMMENT '所属科室',
  `address` varchar(255) NOT NULL COMMENT '存放地点',
  `managedepart` varchar(50) NOT NULL COMMENT '管理科室',
  `factorynum` varchar(50) NOT NULL DEFAULT '' COMMENT '出厂编号',
  `factorydate` date DEFAULT NULL COMMENT '出厂日期',
  `opendate` date DEFAULT NULL COMMENT '开机日期',
  `storage_date` date DEFAULT NULL COMMENT '入库日期',
  `capitalfrom` varchar(20) DEFAULT '0' COMMENT '资金来源',
  `assfrom` varchar(20) NOT NULL DEFAULT '0' COMMENT '设备来源',
  `invoicenum` varchar(50) DEFAULT NULL COMMENT '发票编号',
  `buy_price` decimal(10,2) DEFAULT '0.00' COMMENT '设备原值',
  `paytime` date DEFAULT NULL COMMENT '付款时间',
  `pay_status` tinyint(3) DEFAULT '3' COMMENT '设备付款是否付清【1已付清0未付清】',
  `expected_life` int(11) DEFAULT '0' COMMENT '预计使用年限',
  `residual_value` varchar(10) DEFAULT NULL COMMENT '残净值率',
  `is_firstaid` tinyint(3) DEFAULT NULL COMMENT '是否急救资产【1是0否】',
  `is_special` tinyint(3) DEFAULT NULL COMMENT '是否特种设备【1是0否】',
  `is_metering` tinyint(3) DEFAULT NULL COMMENT '是否计量资产【1是0否】',
  `is_qualityAssets` tinyint(3) DEFAULT '0' COMMENT '是否质控设备【1是0否】',
  `is_benefit` tinyint(1) DEFAULT '0' COMMENT '是否效益分析【1是0否】',
  `is_lifesupport` tinyint(1) DEFAULT '0' COMMENT '是否生命支持类设备【0否，1是】',
  `guarantee_date` date DEFAULT NULL COMMENT '报修截止日期',
  `depreciation_method` varchar(20) DEFAULT '0' COMMENT '折旧方式',
  `depreciable_lives` int(11) DEFAULT NULL COMMENT '折旧年限',
  `factory` varchar(100) DEFAULT NULL COMMENT '生产厂家',
  `factory_user` varchar(30) DEFAULT NULL COMMENT '生产厂家联系人',
  `factory_tel` varchar(255) DEFAULT NULL COMMENT '厂家电话',
  `supplier` varchar(100) DEFAULT NULL COMMENT '供应商',
  `supp_user` varchar(105) DEFAULT NULL COMMENT '供应商联系人',
  `supp_tel` varchar(255) DEFAULT NULL COMMENT '供应商电话',
  `repair` varchar(255) DEFAULT NULL COMMENT '维修公司',
  `repa_user` varchar(255) DEFAULT NULL COMMENT '维修公司联系人',
  `repa_tel` varchar(255) DEFAULT NULL COMMENT '维修公司电话',
  `adduser` varchar(60) NOT NULL DEFAULT '' COMMENT '上传人',
  `adddate` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `edituser` varchar(255) DEFAULT NULL COMMENT '最后修改人',
  `editdate` timestamp NULL DEFAULT NULL COMMENT '最后修改时间',
  `is_save` tinyint(3) DEFAULT '0' COMMENT '是否已入库保存【1是0否】',
  `is_patrol` tinyint(3) DEFAULT NULL COMMENT '是否保养设备【1是0否】',
  `is_domestic` tinyint(3) DEFAULT '3' COMMENT '设备是否国产【1国产2进口】',
  `remark` varchar(255) DEFAULT NULL COMMENT '设备备注',
  `assorignum_spare` varchar(60) DEFAULT NULL COMMENT '设备原编码（备用）',
  `patrol_xc_cycle` varchar(60) DEFAULT NULL COMMENT '巡查周期',
  `patrol_pm_cycle` varchar(60) DEFAULT NULL COMMENT '保养周期',
  `quality_cycle` varchar(60) DEFAULT NULL COMMENT '质控周期',
  `metering_cycle` varchar(60) DEFAULT NULL COMMENT '计量周期',
  `registration` varchar(255) DEFAULT NULL COMMENT '注册证编号',
  `inventory_label_id` varchar(256) DEFAULT NULL COMMENT '盘点标签id',
  PRIMARY KEY (`tempid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备基本信息表批量入库暂存表';

-- ----------------------------
-- Records of sb_assets_info_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_insurance
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_insurance`;
CREATE TABLE `sb_assets_insurance` (
  `insurid` int(11) NOT NULL AUTO_INCREMENT COMMENT '维保自增ID',
  `assid` int(11) NOT NULL COMMENT '资产ID',
  `fileid` int(11) DEFAULT NULL COMMENT '维保合同&附件ID',
  `nature` tinyint(1) NOT NULL DEFAULT '0' COMMENT '维保性质 0-原厂 1-第三方',
  `buydate` int(11) NOT NULL COMMENT '维保购买日期',
  `startdate` int(11) NOT NULL COMMENT '维保开始日期',
  `overdate` int(11) NOT NULL COMMENT '维保结束日期',
  `company` varchar(255) NOT NULL COMMENT '维保公司名称',
  `company_id` int(11) DEFAULT NULL COMMENT '保修公司id',
  `cost` decimal(11,2) DEFAULT '0.00' COMMENT '维保费用',
  `contacts` varchar(60) NOT NULL COMMENT '联系人',
  `telephone` varchar(60) NOT NULL COMMENT '联系电话',
  `content` text NOT NULL COMMENT '维保内容',
  `remark` text COMMENT '备注',
  `adduser` varchar(50) DEFAULT NULL COMMENT '添加者',
  `adddate` int(11) DEFAULT NULL COMMENT '添加日期',
  `edituser` varchar(50) DEFAULT NULL COMMENT '修改者',
  `editdate` int(11) DEFAULT NULL COMMENT '修改日期',
  `status` tinyint(1) DEFAULT '1' COMMENT '维保使用状态  1-在用 2-已过期 3-未开始',
  PRIMARY KEY (`insurid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备维保信息表';

-- ----------------------------
-- Records of sb_assets_insurance
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_insurance_contract
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_insurance_contract`;
CREATE TABLE `sb_assets_insurance_contract` (
  `contract_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '合同自增ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `insurid` varchar(100) DEFAULT NULL COMMENT '维保ID',
  `contract_num` varchar(100) DEFAULT NULL COMMENT '合同编号',
  `contract_name` varchar(100) DEFAULT NULL COMMENT '合同名称',
  `supplier_id` tinyint(3) DEFAULT NULL COMMENT '供货商ID',
  `supplier_name` varchar(100) DEFAULT NULL COMMENT '供货商名称',
  `contract_type` tinyint(1) DEFAULT '3' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_contacts` varchar(60) DEFAULT NULL COMMENT '供货商联系人',
  `supplier_phone` varchar(30) DEFAULT NULL COMMENT '供货商联系电话',
  `sign_date` date DEFAULT NULL COMMENT '签订日期',
  `end_date` date DEFAULT NULL COMMENT '合同截止日期',
  `guarantee_date` date DEFAULT NULL COMMENT '合同设备保修截止日期',
  `contract_amount` decimal(10,2) DEFAULT NULL COMMENT '合同金额',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `archives_num` varchar(100) DEFAULT NULL COMMENT '档案编号',
  `archives_manager` varchar(60) DEFAULT NULL COMMENT '档案管理人员',
  `hospital_manager` varchar(60) DEFAULT NULL COMMENT '院方负责人',
  `contract_content` text COMMENT '合同内容',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人员',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '编辑人员',
  `edit_time` datetime DEFAULT NULL COMMENT '编辑时间',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `is_confirm` tinyint(1) DEFAULT '0' COMMENT '已确认【0否1是】',
  PRIMARY KEY (`contract_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维保管理——维保合同';

-- ----------------------------
-- Records of sb_assets_insurance_contract
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_insurance_contract_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_insurance_contract_file`;
CREATE TABLE `sb_assets_insurance_contract_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维保管理——维保合同附件';

-- ----------------------------
-- Records of sb_assets_insurance_contract_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_insurance_contract_pay
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_insurance_contract_pay`;
CREATE TABLE `sb_assets_insurance_contract_pay` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `pay_period` int(11) DEFAULT NULL COMMENT '付款期数',
  `estimate_pay_date` date DEFAULT NULL COMMENT '预计付款日期',
  `real_pay_date` date DEFAULT NULL COMMENT '实际付款日期',
  `pay_amount` decimal(10,2) DEFAULT NULL COMMENT '付款金额',
  `pay_status` tinyint(3) DEFAULT '0' COMMENT '付款状态【0未付款，1已付款】',
  `pay_user` varchar(60) DEFAULT NULL COMMENT '付款人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `supplier_id` int(10) DEFAULT NULL COMMENT '乙方单位id',
  `contract_type` tinyint(1) DEFAULT '3' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '乙方单位',
  `contract_name` varchar(255) DEFAULT NULL COMMENT '合同名称',
  `contract_num` varchar(150) DEFAULT NULL COMMENT '合同编号',
  PRIMARY KEY (`pay_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维保管理——维保合同付款信息';

-- ----------------------------
-- Records of sb_assets_insurance_contract_pay
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_insurance_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_insurance_file`;
CREATE TABLE `sb_assets_insurance_file` (
  `fileid` int(11) NOT NULL AUTO_INCREMENT COMMENT '维保合同&附件自增ID',
  `insurid` int(11) NOT NULL COMMENT '维保ID',
  `name` varchar(60) NOT NULL COMMENT '文件名称',
  `url` varchar(255) NOT NULL COMMENT '文件地址',
  `adddate` int(11) NOT NULL COMMENT '上传日期',
  PRIMARY KEY (`fileid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备维保信息表——合同&附件文件表';

-- ----------------------------
-- Records of sb_assets_insurance_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_outside
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_outside`;
CREATE TABLE `sb_assets_outside` (
  `outid` int(11) NOT NULL AUTO_INCREMENT COMMENT '外调自增ID',
  `assid` int(11) NOT NULL COMMENT '设备ID',
  `order_price` decimal(10,2) DEFAULT '0.00' COMMENT '外调单总值',
  `apply_type` varchar(50) NOT NULL COMMENT '申请类型：调出、捐赠、外售',
  `price` varchar(50) DEFAULT NULL COMMENT '金额',
  `reason` varchar(255) NOT NULL COMMENT '外调原因',
  `accept` varchar(255) NOT NULL COMMENT '外调目的地（设备接受单位）',
  `person` varchar(100) DEFAULT NULL COMMENT '联系人',
  `phone` varchar(100) DEFAULT NULL COMMENT '联系电话',
  `outside_date` int(11) NOT NULL COMMENT '预计调出日期',
  `apply_userid` int(11) NOT NULL COMMENT '申请人ID',
  `apply_time` int(11) NOT NULL COMMENT '申请时间',
  `retrial_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '审批不通过后是否申请重审【1等待操作2重审中3直接结束】',
  `check_person` varchar(255) DEFAULT NULL COMMENT '验收人',
  `check_phone` varchar(255) DEFAULT NULL COMMENT '验收人联系电话',
  `check_date` int(11) NOT NULL COMMENT '验收日期',
  `check_remark` varchar(255) DEFAULT NULL COMMENT '验收备注',
  `status` tinyint(1) DEFAULT '0' COMMENT '外调状态0:申请中 1：审批通过 2:验收单录入完成',
  `approve_status` tinyint(3) DEFAULT '0' COMMENT '审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` timestamp NULL DEFAULT NULL COMMENT '最后审核时间',
  `current_approver` varchar(100) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '所有审批人',
  PRIMARY KEY (`outid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——外调管理';

-- ----------------------------
-- Records of sb_assets_outside
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_outside_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_outside_detail`;
CREATE TABLE `sb_assets_outside_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `outid` int(10) DEFAULT NULL COMMENT '外调id',
  `subsidiary_assid` int(10) DEFAULT NULL COMMENT '附属设备id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=gbk ROW_FORMAT=FIXED COMMENT='资产管理——外调附属设备信息';

-- ----------------------------
-- Records of sb_assets_outside_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_outside_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_outside_file`;
CREATE TABLE `sb_assets_outside_file` (
  `fileid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `outid` int(11) NOT NULL COMMENT '外调id',
  `type` tinyint(1) DEFAULT '2' COMMENT '文件种类 0：申请外调附件 1:验收单附件 2:报告附件',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT '0.00' COMMENT '文件大小',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  PRIMARY KEY (`fileid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——外调附件表';

-- ----------------------------
-- Records of sb_assets_outside_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_print_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_print_temp`;
CREATE TABLE `sb_assets_print_temp` (
  `temp_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `printer_type` varchar(30) DEFAULT NULL COMMENT '打印机类型(zebra斑马打印机,brother兄弟打印机)',
  `system_default` tinyint(3) DEFAULT '0' COMMENT '是否系统标配模板【1是0用户自定义】',
  `temp_name` varchar(60) DEFAULT NULL COMMENT '模板名称',
  `show_fields` text COMMENT '要显示的字段',
  `temp_content` text COMMENT '模本内容',
  `is_select` tinyint(3) DEFAULT '0' COMMENT '是否当前在用【1是0否】',
  `pic_width` int(11) DEFAULT '70' COMMENT '图片快递px',
  `font_size` tinyint(3) DEFAULT '16' COMMENT '字体大小',
  `add_user` varchar(60) DEFAULT NULL COMMENT '设计人',
  `add_time` datetime DEFAULT NULL COMMENT '设计时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`temp_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——设备打印——模板设计';

-- ----------------------------
-- Records of sb_assets_print_temp
-- ----------------------------
BEGIN;
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (1, 1, 'zebra', 1, 'system_1', '{\"assets\":\"设备名称\",\"assnum\":\"设备编码\",\"model\":\"设备型号\",\"department\":\"所属科室\",\"factorynum\":\"出厂编号\",\"opendate\":\"开机日期\"}', '<div class=\"system_1\"><div class=\"hospital_name\"></div><div class=\"hospital_info\"><table class=\"show_table\"><tbody><tr><td colspan=\"2\" class=\"line_height\"></td></tr><tr><td colspan=\"2\" class=\"line_height\"></td></tr><tr><td colspan=\"2\" class=\"line_height\"></td></tr><tr><td class=\"row_td line_height\"></td><td class=\"td_img img_line_height\" rowspan=\"3\"><img src=\"/Public/images/show_qrcode_1.png\"></td></tr><tr><td class=\"row_td line_height\"></td></tr><tr><td class=\"row_td line_height\"></td></tr></tbody></table></div></div><div class=\"layui-btn-group\" style=\"width:100%;text-align:center\"><button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin:0 0 0 60px\"><i class=\"layui-icon layui-icon-zsave\"></i>设为默认</button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin:0 0 0 60px\"><i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size:16px\"></i>打印测试</button></div>', 0, 70, 16, '牛年', NULL, NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (2, 1, 'zebra', 1, 'system_2', '{\"assets\":\"设备名称\",\"model\":\"设备型号\",\"assnum\":\"设备编码\",\"factorynum\":\"出厂编号\",\"opendate\":\"开机日期\",\"department\":\"所属科室\"}', '<div class=\"system_2\"> <div class=\"hospital_name\"></div> <div class=\"hospital_info\"> <table class=\"show_table\"> <tbody> <tr> <td colspan=\"2\" class=\"line_height\"></td> </tr> <tr> <td colspan=\"2\" class=\"line_height\"></td> </tr> <tr> <td class=\"row_td line_height\"></td> <td class=\"td_img img_line_height\" rowspan=\"4\"> <img src=\"/Public/images/show_qrcode_1.png\"/> </td> </tr> <tr> <td class=\"row_td line_height\"></td> </tr> <tr> <td class=\"row_td line_height\"></td> </tr> <tr> <td class=\"row_td line_height\"></td> </tr> </tbody> </table> </div></div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button></div>', 0, 100, 16, '牛年', NULL, NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (20, 1, 'brother', 1, 'system_3', '{\"assets\":\"设备名称\",\"model\":\"规格型号\",\"assnum\":\"设备编号\",\"department\":\"使用科室\",\"opendate\":\"启用日期\"}', '<div class=\"system_3\"> <div class=\"hospital_name\" style=\"font-size:16px;height:18px;line-height:18px;padding-right: 10px;font-family: 宋体;\">郴州市第三人民医院</div> <div class=\"hospital_info\"> <table class=\"czsy_table\"> <tbody> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> </tbody> </table> </div> <div class=\"czsy_barcode\"> <img src=\"/Public/images/show_qrcode_1.png\"/> <div style=\"color: #000000;font-size: 11px;width: 105px;overflow: hidden;\">6858011371866</div> </div></div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;margin-top:10px;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button></div>', 0, 70, 16, '牛年', NULL, NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (25, 1, 'brother', 1, 'system_4', '{\"zidingyi\":\"固定资产管理卡\",\"hos_name\":\"天成医疗测试医院\",\"assets\":\"设备名称\",\"model\":\"规格型号\",\"assnum\":\"设备编号\",\"department\":\"使用科室\",\"opendate\":\"启用日期\"}', '<div class=\"system_4\"> <table class=\"czsy_table\"> <tbody> <tr> <td colspan=\"2\"></td> <td rowspan=\"6\"> <img class=\"czsy_barcode\" src=\"/Public/images/show_qrcode_1.png\"/> </td> </tr> <tr> <td colspan=\"2\"></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> <td><div class=\"bot_assnum\">68560211933081</div></td> </tr> </tbody> </table></div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;margin-top:10px;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button></div>', 0, 90, 12, '牛年', NULL, NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (50, 1, 'brother', 0, 'user_3', '{\"assets\":\"设备名称\",\"model\":\"规格型号\",\"serialnum\":\"序 列 号\",\"department\":\"使用科室\",\"opendate\":\"启用日期\"}', 'div class=\"user_3\"> <div class=\"hospital_name\" style=\"font-size:16px;height:18px;line-height:18px;padding-right: 10px;font-family: 宋体;\">郴州市第三人民医院</div> <div class=\"hospital_info\"> <table class=\"czsy_table\"> <tbody> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> <tr> <td class=\"czsy_title\"></td> <td><div class=\"czsy_con\"></div></td> </tr> </tbody> </table> </div> <div class=\"czsy_barcode\"><img src=\"/Public/images/show_qrcode_1.png\" style=\"width: 95px;margin-top: 0px;\"/> <div style=\"color: #000000;font-size: 12px;width: 105px;overflow: hidden;\">6858011371866</div> </div></div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;margin-top:10px;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-danger delete\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon\">&#xe640;</i>删除标签</button></div>', 1, 95, 12, '牛年', '2024-08-22 15:39:25', NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (35, 1, 'other', 1, 'system_5', NULL, '<div class=\"system_5\"> <div class=\"other_barcode\"> <img src=\"/Public/images/show_qrcode_1.png\"/> </div></div><div>（兄弟打印机，纸张大小36mm,长度40mm）</div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button></div>', 0, 70, 16, NULL, NULL, NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (36, 1, 'other', 1, 'system_6', NULL, '<div class=\"system_6\"> <div class=\"other_barcode\"> <img src=\"/Public/images/show_qrcode_1.png\"/> </div></div><div>（兄弟打印机，纸张大小24mm,长度30mm）</div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button></div>', 0, 70, 16, NULL, NULL, NULL, NULL);
INSERT INTO `sb_assets_print_temp` (`temp_id`, `hospital_id`, `printer_type`, `system_default`, `temp_name`, `show_fields`, `temp_content`, `is_select`, `pic_width`, `font_size`, `add_user`, `add_time`, `edit_user`, `edit_time`) VALUES (37, 1, 'other', 1, 'system_7', NULL, '<div class=\"system_7\"> <div class=\"other_barcode\"> <img src=\"/Public/images/show_qrcode_1.png\"/> </div> <div class=\"xbh\">6854010904360</div></div><div>（纸张大小18mm,长度24mm,浏览器最小字体设置为8）</div><div class=\"layui-btn-group\" style=\"width:100%;text-align: center;\"> <button type=\"button\" class=\"layui-btn layui-btn-sm default\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zsave\"></i>设为默认 </button> <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-normal print_test\" style=\"margin: 0 0 0 60px;\"> <i class=\"layui-icon layui-icon-zprinter-l\" style=\"font-size: 16px;\"></i>打印测试 </button></div>', 0, 70, 16, NULL, NULL, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for sb_assets_record_contract
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_record_contract`;
CREATE TABLE `sb_assets_record_contract` (
  `contract_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '合同自增ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `assid` varchar(100) DEFAULT NULL COMMENT '设备ID',
  `contract_num` varchar(100) DEFAULT NULL COMMENT '合同编号',
  `contract_name` varchar(100) DEFAULT NULL COMMENT '合同名称',
  `supplier_id` tinyint(3) DEFAULT NULL COMMENT '供货商ID',
  `supplier_name` varchar(100) DEFAULT NULL COMMENT '供货商名称',
  `contract_type` tinyint(1) DEFAULT '4' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_contacts` varchar(60) DEFAULT NULL COMMENT '供货商联系人',
  `supplier_phone` varchar(30) DEFAULT NULL COMMENT '供货商联系电话',
  `sign_date` date DEFAULT NULL COMMENT '签订日期',
  `end_date` date DEFAULT NULL COMMENT '合同截止日期',
  `guarantee_date` date DEFAULT NULL COMMENT '合同设备保修截止日期',
  `contract_amount` decimal(10,2) DEFAULT NULL COMMENT '合同金额',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `archives_num` varchar(100) DEFAULT NULL COMMENT '档案编号',
  `archives_manager` varchar(60) DEFAULT NULL COMMENT '档案管理人员',
  `hospital_manager` varchar(60) DEFAULT NULL COMMENT '院方负责人',
  `contract_content` text COMMENT '合同内容',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人员',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '编辑人员',
  `edit_time` datetime DEFAULT NULL COMMENT '编辑时间',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `is_confirm` tinyint(1) DEFAULT '0' COMMENT '已确认【0否1是】',
  PRIMARY KEY (`contract_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——设备合同(补录)合同';

-- ----------------------------
-- Records of sb_assets_record_contract
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_record_contract_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_record_contract_file`;
CREATE TABLE `sb_assets_record_contract_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——设备合同(补录)附件';

-- ----------------------------
-- Records of sb_assets_record_contract_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_record_contract_pay
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_record_contract_pay`;
CREATE TABLE `sb_assets_record_contract_pay` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `pay_period` int(11) DEFAULT NULL COMMENT '付款期数',
  `estimate_pay_date` date DEFAULT NULL COMMENT '预计付款日期',
  `real_pay_date` date DEFAULT NULL COMMENT '实际付款日期',
  `pay_amount` decimal(10,2) DEFAULT NULL COMMENT '付款金额',
  `pay_status` tinyint(3) DEFAULT '0' COMMENT '付款状态【0未付款，1已付款】',
  `pay_user` varchar(60) DEFAULT NULL COMMENT '付款人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `supplier_id` int(10) DEFAULT NULL COMMENT '乙方单位id',
  `contract_type` tinyint(1) DEFAULT '4' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '乙方单位',
  `contract_name` varchar(255) DEFAULT NULL COMMENT '合同名称',
  `contract_num` varchar(150) DEFAULT NULL COMMENT '合同编号',
  PRIMARY KEY (`pay_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——设备合同(补录)付款信息';

-- ----------------------------
-- Records of sb_assets_record_contract_pay
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_scrap
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_scrap`;
CREATE TABLE `sb_assets_scrap` (
  `scrid` int(10) NOT NULL AUTO_INCREMENT COMMENT '报废自增ID，同scrapid',
  `assid` int(10) NOT NULL COMMENT '主设备ID',
  `order_price` decimal(10,2) DEFAULT '0.00' COMMENT '报废单总值',
  `scrapnum` varchar(25) NOT NULL COMMENT '报废编号',
  `scrapdate` date DEFAULT NULL COMMENT '报废日期',
  `apply_user` varchar(20) DEFAULT NULL COMMENT '报废申请人',
  `scrap_reason` text NOT NULL COMMENT '报废原因',
  `cleardate` date DEFAULT NULL COMMENT '处置日期（清理日期）',
  `clear_time` timestamp NULL DEFAULT NULL COMMENT '处置时间',
  `clear_cross_user` varchar(20) DEFAULT NULL COMMENT '处置经手人',
  `clear_company` varchar(255) DEFAULT NULL COMMENT '清理公司',
  `clear_contacter` varchar(10) DEFAULT NULL COMMENT '联系人',
  `clear_telephone` varchar(11) DEFAULT NULL COMMENT '联系电话',
  `clear_remark` text COMMENT '清理备注',
  `approve_status` tinyint(1) DEFAULT '0' COMMENT '报废审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` timestamp NULL DEFAULT NULL COMMENT '最后审核时间',
  `retrial_status` tinyint(3) DEFAULT NULL COMMENT '审批不通过后是否申请重审【1等待操作2重审中3直接结束】',
  `add_time` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `add_user` varchar(20) DEFAULT NULL COMMENT '添加人',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `update_user` varchar(20) DEFAULT NULL COMMENT '更新人',
  `current_approver` varchar(255) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已完成审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '所有审批人',
  PRIMARY KEY (`scrid`) USING BTREE,
  KEY `assid_approvestatus` (`assid`,`approve_status`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备报废';

-- ----------------------------
-- Records of sb_assets_scrap
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_scrap_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_scrap_detail`;
CREATE TABLE `sb_assets_scrap_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `scrid` int(10) DEFAULT NULL COMMENT '报废单id',
  `subsidiary_assid` int(10) DEFAULT NULL COMMENT '附属设备id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=gbk ROW_FORMAT=FIXED COMMENT='资产管理——外调附属设备信息';

-- ----------------------------
-- Records of sb_assets_scrap_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_scrap_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_scrap_report`;
CREATE TABLE `sb_assets_scrap_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `scrid` int(11) DEFAULT NULL COMMENT '报废ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——设备报废报告';

-- ----------------------------
-- Records of sb_assets_scrap_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_state_change
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_state_change`;
CREATE TABLE `sb_assets_state_change` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assid` int(11) DEFAULT NULL COMMENT '设备ID',
  `old_status` tinyint(3) DEFAULT NULL COMMENT '原状态',
  `new_status` tinyint(3) DEFAULT NULL COMMENT '现状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `change_user` varchar(60) DEFAULT NULL COMMENT '变更人',
  `change_time` timestamp NULL DEFAULT NULL COMMENT '变更时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备状态变更记录表';

-- ----------------------------
-- Records of sb_assets_state_change
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_technical_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_technical_file`;
CREATE TABLE `sb_assets_technical_file` (
  `tech_id` int(11) NOT NULL AUTO_INCREMENT,
  `assid` int(11) DEFAULT NULL COMMENT '所属设备ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `file_type` varchar(10) DEFAULT NULL COMMENT '文件类型【images,pdf,doc】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '上传人',
  `add_time` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`tech_id`) USING BTREE,
  KEY `assid` (`assid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——设备技术资料表';

-- ----------------------------
-- Records of sb_assets_technical_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_transfer
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_transfer`;
CREATE TABLE `sb_assets_transfer` (
  `atid` int(11) NOT NULL AUTO_INCREMENT,
  `assid` int(11) DEFAULT NULL COMMENT '资产ID',
  `order_price` decimal(10,2) DEFAULT '0.00' COMMENT '转科单总值',
  `transfernum` varchar(60) DEFAULT NULL COMMENT '转移单号（生成规则，zk+年月日时分秒）',
  `applicant_user` varchar(30) DEFAULT NULL COMMENT '申请人',
  `applicant_time` timestamp NULL DEFAULT NULL COMMENT '申请时间',
  `tranout_departid` int(11) DEFAULT NULL COMMENT '转出科室ID',
  `tranout_departrespon` varchar(30) DEFAULT NULL COMMENT '转出科室负责人',
  `tranin_departid` int(11) DEFAULT NULL COMMENT '转入科室ID',
  `tranin_departrespon` varchar(30) DEFAULT NULL COMMENT '转入科室负责人',
  `transfer_date` date DEFAULT NULL COMMENT '转科日期',
  `address` varchar(100) DEFAULT NULL COMMENT '存放地点',
  `tran_docnum` varchar(100) DEFAULT NULL COMMENT '转科文号',
  `tran_reason` varchar(255) DEFAULT NULL COMMENT '转科原因',
  `approve_status` tinyint(3) DEFAULT '0' COMMENT '审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` timestamp NULL DEFAULT NULL COMMENT '最后审核时间',
  `retrial_status` tinyint(3) DEFAULT NULL COMMENT '审批不通过后是否申请重审【1等待操作2重审中3直接结束】',
  `check_user` varchar(30) DEFAULT NULL COMMENT '验收人',
  `check_time` timestamp NULL DEFAULT NULL COMMENT '验收时间',
  `is_check` tinyint(3) DEFAULT '0' COMMENT '是否通过验收=【0为未验收，1为通过，2为不通过】',
  `check` varchar(255) DEFAULT NULL COMMENT '验收意见',
  `update_user` varchar(30) DEFAULT NULL COMMENT '更新人',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `current_approver` varchar(150) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '全部审批人',
  PRIMARY KEY (`atid`) USING BTREE,
  KEY `assid_tranout_tranin` (`assid`,`tranout_departid`,`tranin_departid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资产管理——转移科室申请表';

-- ----------------------------
-- Records of sb_assets_transfer
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_transfer_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_transfer_detail`;
CREATE TABLE `sb_assets_transfer_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `atid` int(10) DEFAULT NULL COMMENT '转科单id',
  `subsidiary_assid` int(10) DEFAULT NULL COMMENT '附属设备id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=gbk ROW_FORMAT=FIXED COMMENT='资产管理——转科附属设备信息';

-- ----------------------------
-- Records of sb_assets_transfer_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_assets_transfer_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_assets_transfer_report`;
CREATE TABLE `sb_assets_transfer_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `atid` int(11) DEFAULT NULL COMMENT '转科ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——设备转科报告';

-- ----------------------------
-- Records of sb_assets_transfer_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_base_areas
-- ----------------------------
DROP TABLE IF EXISTS `sb_base_areas`;
CREATE TABLE `sb_base_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `areaid` varchar(20) NOT NULL,
  `area` varchar(50) NOT NULL,
  `cityid` varchar(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3145 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='行政区域县区信息表';

-- ----------------------------
-- Records of sb_base_areas
-- ----------------------------
BEGIN;
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1, '110101', '东城区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2, '110102', '西城区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3, '110103', '崇文区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (4, '110104', '宣武区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (5, '110105', '朝阳区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (6, '110106', '丰台区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (7, '110107', '石景山区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (8, '110108', '海淀区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (9, '110109', '门头沟区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (10, '110111', '房山区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (11, '110112', '通州区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (12, '110113', '顺义区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (13, '110114', '昌平区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (14, '110115', '大兴区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (15, '110116', '怀柔区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (16, '110117', '平谷区', '110100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (17, '110228', '密云县', '110200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (18, '110229', '延庆县', '110200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (19, '120101', '和平区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (20, '120102', '河东区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (21, '120103', '河西区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (22, '120104', '南开区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (23, '120105', '河北区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (24, '120106', '红桥区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (25, '120107', '塘沽区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (26, '120108', '汉沽区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (27, '120109', '大港区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (28, '120110', '东丽区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (29, '120111', '西青区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (30, '120112', '津南区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (31, '120113', '北辰区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (32, '120114', '武清区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (33, '120115', '宝坻区', '120100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (34, '120221', '宁河县', '120200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (35, '120223', '静海县', '120200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (36, '120225', '蓟　县', '120200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (37, '130101', '市辖区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (38, '130102', '长安区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (39, '130103', '桥东区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (40, '130104', '桥西区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (41, '130105', '新华区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (42, '130107', '井陉矿区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (43, '130108', '裕华区', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (44, '130121', '井陉县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (45, '130123', '正定县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (46, '130124', '栾城县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (47, '130125', '行唐县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (48, '130126', '灵寿县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (49, '130127', '高邑县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (50, '130128', '深泽县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (51, '130129', '赞皇县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (52, '130130', '无极县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (53, '130131', '平山县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (54, '130132', '元氏县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (55, '130133', '赵　县', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (56, '130181', '辛集市', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (57, '130182', '藁城市', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (58, '130183', '晋州市', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (59, '130184', '新乐市', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (60, '130185', '鹿泉市', '130100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (61, '130201', '市辖区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (62, '130202', '路南区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (63, '130203', '路北区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (64, '130204', '古冶区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (65, '130205', '开平区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (66, '130207', '丰南区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (67, '130208', '丰润区', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (68, '130223', '滦　县', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (69, '130224', '滦南县', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (70, '130225', '乐亭县', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (71, '130227', '迁西县', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (72, '130229', '玉田县', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (73, '130230', '唐海县', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (74, '130281', '遵化市', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (75, '130283', '迁安市', '130200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (76, '130301', '市辖区', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (77, '130302', '海港区', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (78, '130303', '山海关区', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (79, '130304', '北戴河区', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (80, '130321', '青龙满族自治县', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (81, '130322', '昌黎县', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (82, '130323', '抚宁县', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (83, '130324', '卢龙县', '130300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (84, '130401', '市辖区', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (85, '130402', '邯山区', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (86, '130403', '丛台区', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (87, '130404', '复兴区', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (88, '130406', '峰峰矿区', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (89, '130421', '邯郸县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (90, '130423', '临漳县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (91, '130424', '成安县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (92, '130425', '大名县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (93, '130426', '涉　县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (94, '130427', '磁　县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (95, '130428', '肥乡县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (96, '130429', '永年县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (97, '130430', '邱　县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (98, '130431', '鸡泽县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (99, '130432', '广平县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (100, '130433', '馆陶县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (101, '130434', '魏　县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (102, '130435', '曲周县', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (103, '130481', '武安市', '130400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (104, '130501', '市辖区', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (105, '130502', '桥东区', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (106, '130503', '桥西区', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (107, '130521', '邢台县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (108, '130522', '临城县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (109, '130523', '内丘县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (110, '130524', '柏乡县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (111, '130525', '隆尧县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (112, '130526', '任　县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (113, '130527', '南和县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (114, '130528', '宁晋县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (115, '130529', '巨鹿县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (116, '130530', '新河县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (117, '130531', '广宗县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (118, '130532', '平乡县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (119, '130533', '威　县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (120, '130534', '清河县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (121, '130535', '临西县', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (122, '130581', '南宫市', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (123, '130582', '沙河市', '130500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (124, '130601', '市辖区', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (125, '130602', '新市区', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (126, '130603', '北市区', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (127, '130604', '南市区', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (128, '130621', '满城县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (129, '130622', '清苑县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (130, '130623', '涞水县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (131, '130624', '阜平县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (132, '130625', '徐水县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (133, '130626', '定兴县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (134, '130627', '唐　县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (135, '130628', '高阳县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (136, '130629', '容城县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (137, '130630', '涞源县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (138, '130631', '望都县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (139, '130632', '安新县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (140, '130633', '易　县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (141, '130634', '曲阳县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (142, '130635', '蠡　县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (143, '130636', '顺平县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (144, '130637', '博野县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (145, '130638', '雄　县', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (146, '130681', '涿州市', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (147, '130682', '定州市', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (148, '130683', '安国市', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (149, '130684', '高碑店市', '130600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (150, '130701', '市辖区', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (151, '130702', '桥东区', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (152, '130703', '桥西区', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (153, '130705', '宣化区', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (154, '130706', '下花园区', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (155, '130721', '宣化县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (156, '130722', '张北县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (157, '130723', '康保县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (158, '130724', '沽源县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (159, '130725', '尚义县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (160, '130726', '蔚　县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (161, '130727', '阳原县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (162, '130728', '怀安县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (163, '130729', '万全县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (164, '130730', '怀来县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (165, '130731', '涿鹿县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (166, '130732', '赤城县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (167, '130733', '崇礼县', '130700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (168, '130801', '市辖区', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (169, '130802', '双桥区', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (170, '130803', '双滦区', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (171, '130804', '鹰手营子矿区', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (172, '130821', '承德县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (173, '130822', '兴隆县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (174, '130823', '平泉县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (175, '130824', '滦平县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (176, '130825', '隆化县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (177, '130826', '丰宁满族自治县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (178, '130827', '宽城满族自治县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (179, '130828', '围场满族蒙古族自治县', '130800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (180, '130901', '市辖区', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (181, '130902', '新华区', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (182, '130903', '运河区', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (183, '130921', '沧　县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (184, '130922', '青　县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (185, '130923', '东光县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (186, '130924', '海兴县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (187, '130925', '盐山县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (188, '130926', '肃宁县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (189, '130927', '南皮县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (190, '130928', '吴桥县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (191, '130929', '献　县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (192, '130930', '孟村回族自治县', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (193, '130981', '泊头市', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (194, '130982', '任丘市', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (195, '130983', '黄骅市', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (196, '130984', '河间市', '130900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (197, '131001', '市辖区', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (198, '131002', '安次区', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (199, '131003', '广阳区', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (200, '131022', '固安县', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (201, '131023', '永清县', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (202, '131024', '香河县', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (203, '131025', '大城县', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (204, '131026', '文安县', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (205, '131028', '大厂回族自治县', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (206, '131081', '霸州市', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (207, '131082', '三河市', '131000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (208, '131101', '市辖区', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (209, '131102', '桃城区', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (210, '131121', '枣强县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (211, '131122', '武邑县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (212, '131123', '武强县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (213, '131124', '饶阳县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (214, '131125', '安平县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (215, '131126', '故城县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (216, '131127', '景　县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (217, '131128', '阜城县', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (218, '131181', '冀州市', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (219, '131182', '深州市', '131100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (220, '140101', '市辖区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (221, '140105', '小店区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (222, '140106', '迎泽区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (223, '140107', '杏花岭区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (224, '140108', '尖草坪区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (225, '140109', '万柏林区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (226, '140110', '晋源区', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (227, '140121', '清徐县', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (228, '140122', '阳曲县', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (229, '140123', '娄烦县', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (230, '140181', '古交市', '140100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (231, '140201', '市辖区', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (232, '140202', '城　区', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (233, '140203', '矿　区', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (234, '140211', '南郊区', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (235, '140212', '新荣区', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (236, '140221', '阳高县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (237, '140222', '天镇县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (238, '140223', '广灵县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (239, '140224', '灵丘县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (240, '140225', '浑源县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (241, '140226', '左云县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (242, '140227', '大同县', '140200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (243, '140301', '市辖区', '140300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (244, '140302', '城　区', '140300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (245, '140303', '矿　区', '140300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (246, '140311', '郊　区', '140300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (247, '140321', '平定县', '140300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (248, '140322', '盂　县', '140300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (249, '140401', '市辖区', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (250, '140402', '城　区', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (251, '140411', '郊　区', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (252, '140421', '长治县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (253, '140423', '襄垣县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (254, '140424', '屯留县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (255, '140425', '平顺县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (256, '140426', '黎城县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (257, '140427', '壶关县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (258, '140428', '长子县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (259, '140429', '武乡县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (260, '140430', '沁　县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (261, '140431', '沁源县', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (262, '140481', '潞城市', '140400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (263, '140501', '市辖区', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (264, '140502', '城　区', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (265, '140521', '沁水县', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (266, '140522', '阳城县', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (267, '140524', '陵川县', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (268, '140525', '泽州县', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (269, '140581', '高平市', '140500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (270, '140601', '市辖区', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (271, '140602', '朔城区', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (272, '140603', '平鲁区', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (273, '140621', '山阴县', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (274, '140622', '应　县', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (275, '140623', '右玉县', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (276, '140624', '怀仁县', '140600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (277, '140701', '市辖区', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (278, '140702', '榆次区', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (279, '140721', '榆社县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (280, '140722', '左权县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (281, '140723', '和顺县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (282, '140724', '昔阳县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (283, '140725', '寿阳县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (284, '140726', '太谷县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (285, '140727', '祁　县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (286, '140728', '平遥县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (287, '140729', '灵石县', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (288, '140781', '介休市', '140700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (289, '140801', '市辖区', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (290, '140802', '盐湖区', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (291, '140821', '临猗县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (292, '140822', '万荣县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (293, '140823', '闻喜县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (294, '140824', '稷山县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (295, '140825', '新绛县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (296, '140826', '绛　县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (297, '140827', '垣曲县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (298, '140828', '夏　县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (299, '140829', '平陆县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (300, '140830', '芮城县', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (301, '140881', '永济市', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (302, '140882', '河津市', '140800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (303, '140901', '市辖区', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (304, '140902', '忻府区', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (305, '140921', '定襄县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (306, '140922', '五台县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (307, '140923', '代　县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (308, '140924', '繁峙县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (309, '140925', '宁武县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (310, '140926', '静乐县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (311, '140927', '神池县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (312, '140928', '五寨县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (313, '140929', '岢岚县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (314, '140930', '河曲县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (315, '140931', '保德县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (316, '140932', '偏关县', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (317, '140981', '原平市', '140900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (318, '141001', '市辖区', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (319, '141002', '尧都区', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (320, '141021', '曲沃县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (321, '141022', '翼城县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (322, '141023', '襄汾县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (323, '141024', '洪洞县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (324, '141025', '古　县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (325, '141026', '安泽县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (326, '141027', '浮山县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (327, '141028', '吉　县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (328, '141029', '乡宁县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (329, '141030', '大宁县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (330, '141031', '隰　县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (331, '141032', '永和县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (332, '141033', '蒲　县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (333, '141034', '汾西县', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (334, '141081', '侯马市', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (335, '141082', '霍州市', '141000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (336, '141101', '市辖区', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (337, '141102', '离石区', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (338, '141121', '文水县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (339, '141122', '交城县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (340, '141123', '兴　县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (341, '141124', '临　县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (342, '141125', '柳林县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (343, '141126', '石楼县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (344, '141127', '岚　县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (345, '141128', '方山县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (346, '141129', '中阳县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (347, '141130', '交口县', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (348, '141181', '孝义市', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (349, '141182', '汾阳市', '141100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (350, '150101', '市辖区', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (351, '150102', '新城区', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (352, '150103', '回民区', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (353, '150104', '玉泉区', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (354, '150105', '赛罕区', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (355, '150121', '土默特左旗', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (356, '150122', '托克托县', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (357, '150123', '和林格尔县', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (358, '150124', '清水河县', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (359, '150125', '武川县', '150100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (360, '150201', '市辖区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (361, '150202', '东河区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (362, '150203', '昆都仑区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (363, '150204', '青山区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (364, '150205', '石拐区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (365, '150206', '白云矿区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (366, '150207', '九原区', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (367, '150221', '土默特右旗', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (368, '150222', '固阳县', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (369, '150223', '达尔罕茂明安联合旗', '150200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (370, '150301', '市辖区', '150300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (371, '150302', '海勃湾区', '150300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (372, '150303', '海南区', '150300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (373, '150304', '乌达区', '150300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (374, '150401', '市辖区', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (375, '150402', '红山区', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (376, '150403', '元宝山区', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (377, '150404', '松山区', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (378, '150421', '阿鲁科尔沁旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (379, '150422', '巴林左旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (380, '150423', '巴林右旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (381, '150424', '林西县', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (382, '150425', '克什克腾旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (383, '150426', '翁牛特旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (384, '150428', '喀喇沁旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (385, '150429', '宁城县', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (386, '150430', '敖汉旗', '150400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (387, '150501', '市辖区', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (388, '150502', '科尔沁区', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (389, '150521', '科尔沁左翼中旗', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (390, '150522', '科尔沁左翼后旗', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (391, '150523', '开鲁县', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (392, '150524', '库伦旗', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (393, '150525', '奈曼旗', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (394, '150526', '扎鲁特旗', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (395, '150581', '霍林郭勒市', '150500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (396, '150602', '东胜区', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (397, '150621', '达拉特旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (398, '150622', '准格尔旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (399, '150623', '鄂托克前旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (400, '150624', '鄂托克旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (401, '150625', '杭锦旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (402, '150626', '乌审旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (403, '150627', '伊金霍洛旗', '150600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (404, '150701', '市辖区', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (405, '150702', '海拉尔区', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (406, '150721', '阿荣旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (407, '150722', '莫力达瓦达斡尔族自治旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (408, '150723', '鄂伦春自治旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (409, '150724', '鄂温克族自治旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (410, '150725', '陈巴尔虎旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (411, '150726', '新巴尔虎左旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (412, '150727', '新巴尔虎右旗', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (413, '150781', '满洲里市', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (414, '150782', '牙克石市', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (415, '150783', '扎兰屯市', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (416, '150784', '额尔古纳市', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (417, '150785', '根河市', '150700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (418, '150801', '市辖区', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (419, '150802', '临河区', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (420, '150821', '五原县', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (421, '150822', '磴口县', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (422, '150823', '乌拉特前旗', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (423, '150824', '乌拉特中旗', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (424, '150825', '乌拉特后旗', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (425, '150826', '杭锦后旗', '150800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (426, '150901', '市辖区', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (427, '150902', '集宁区', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (428, '150921', '卓资县', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (429, '150922', '化德县', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (430, '150923', '商都县', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (431, '150924', '兴和县', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (432, '150925', '凉城县', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (433, '150926', '察哈尔右翼前旗', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (434, '150927', '察哈尔右翼中旗', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (435, '150928', '察哈尔右翼后旗', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (436, '150929', '四子王旗', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (437, '150981', '丰镇市', '150900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (438, '152201', '乌兰浩特市', '152200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (439, '152202', '阿尔山市', '152200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (440, '152221', '科尔沁右翼前旗', '152200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (441, '152222', '科尔沁右翼中旗', '152200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (442, '152223', '扎赉特旗', '152200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (443, '152224', '突泉县', '152200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (444, '152501', '二连浩特市', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (445, '152502', '锡林浩特市', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (446, '152522', '阿巴嘎旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (447, '152523', '苏尼特左旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (448, '152524', '苏尼特右旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (449, '152525', '东乌珠穆沁旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (450, '152526', '西乌珠穆沁旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (451, '152527', '太仆寺旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (452, '152528', '镶黄旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (453, '152529', '正镶白旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (454, '152530', '正蓝旗', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (455, '152531', '多伦县', '152500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (456, '152921', '阿拉善左旗', '152900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (457, '152922', '阿拉善右旗', '152900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (458, '152923', '额济纳旗', '152900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (459, '210101', '市辖区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (460, '210102', '和平区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (461, '210103', '沈河区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (462, '210104', '大东区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (463, '210105', '皇姑区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (464, '210106', '铁西区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (465, '210111', '苏家屯区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (466, '210112', '东陵区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (467, '210113', '新城子区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (468, '210114', '于洪区', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (469, '210122', '辽中县', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (470, '210123', '康平县', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (471, '210124', '法库县', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (472, '210181', '新民市', '210100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (473, '210201', '市辖区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (474, '210202', '中山区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (475, '210203', '西岗区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (476, '210204', '沙河口区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (477, '210211', '甘井子区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (478, '210212', '旅顺口区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (479, '210213', '金州区', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (480, '210224', '长海县', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (481, '210281', '瓦房店市', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (482, '210282', '普兰店市', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (483, '210283', '庄河市', '210200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (484, '210301', '市辖区', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (485, '210302', '铁东区', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (486, '210303', '铁西区', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (487, '210304', '立山区', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (488, '210311', '千山区', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (489, '210321', '台安县', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (490, '210323', '岫岩满族自治县', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (491, '210381', '海城市', '210300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (492, '210401', '市辖区', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (493, '210402', '新抚区', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (494, '210403', '东洲区', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (495, '210404', '望花区', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (496, '210411', '顺城区', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (497, '210421', '抚顺县', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (498, '210422', '新宾满族自治县', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (499, '210423', '清原满族自治县', '210400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (500, '210501', '市辖区', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (501, '210502', '平山区', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (502, '210503', '溪湖区', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (503, '210504', '明山区', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (504, '210505', '南芬区', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (505, '210521', '本溪满族自治县', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (506, '210522', '桓仁满族自治县', '210500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (507, '210601', '市辖区', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (508, '210602', '元宝区', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (509, '210603', '振兴区', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (510, '210604', '振安区', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (511, '210624', '宽甸满族自治县', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (512, '210681', '东港市', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (513, '210682', '凤城市', '210600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (514, '210701', '市辖区', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (515, '210702', '古塔区', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (516, '210703', '凌河区', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (517, '210711', '太和区', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (518, '210726', '黑山县', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (519, '210727', '义　县', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (520, '210781', '凌海市', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (521, '210782', '北宁市', '210700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (522, '210801', '市辖区', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (523, '210802', '站前区', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (524, '210803', '西市区', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (525, '210804', '鲅鱼圈区', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (526, '210811', '老边区', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (527, '210881', '盖州市', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (528, '210882', '大石桥市', '210800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (529, '210901', '市辖区', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (530, '210902', '海州区', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (531, '210903', '新邱区', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (532, '210904', '太平区', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (533, '210905', '清河门区', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (534, '210911', '细河区', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (535, '210921', '阜新蒙古族自治县', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (536, '210922', '彰武县', '210900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (537, '211001', '市辖区', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (538, '211002', '白塔区', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (539, '211003', '文圣区', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (540, '211004', '宏伟区', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (541, '211005', '弓长岭区', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (542, '211011', '太子河区', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (543, '211021', '辽阳县', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (544, '211081', '灯塔市', '211000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (545, '211101', '市辖区', '211100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (546, '211102', '双台子区', '211100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (547, '211103', '兴隆台区', '211100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (548, '211121', '大洼县', '211100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (549, '211122', '盘山县', '211100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (550, '211201', '市辖区', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (551, '211202', '银州区', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (552, '211204', '清河区', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (553, '211221', '铁岭县', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (554, '211223', '西丰县', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (555, '211224', '昌图县', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (556, '211281', '调兵山市', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (557, '211282', '开原市', '211200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (558, '211301', '市辖区', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (559, '211302', '双塔区', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (560, '211303', '龙城区', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (561, '211321', '朝阳县', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (562, '211322', '建平县', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (563, '211324', '喀喇沁左翼蒙古族自治县', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (564, '211381', '北票市', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (565, '211382', '凌源市', '211300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (566, '211401', '市辖区', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (567, '211402', '连山区', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (568, '211403', '龙港区', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (569, '211404', '南票区', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (570, '211421', '绥中县', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (571, '211422', '建昌县', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (572, '211481', '兴城市', '211400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (573, '220101', '市辖区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (574, '220102', '南关区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (575, '220103', '宽城区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (576, '220104', '朝阳区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (577, '220105', '二道区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (578, '220106', '绿园区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (579, '220112', '双阳区', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (580, '220122', '农安县', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (581, '220181', '九台市', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (582, '220182', '榆树市', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (583, '220183', '德惠市', '220100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (584, '220201', '市辖区', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (585, '220202', '昌邑区', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (586, '220203', '龙潭区', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (587, '220204', '船营区', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (588, '220211', '丰满区', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (589, '220221', '永吉县', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (590, '220281', '蛟河市', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (591, '220282', '桦甸市', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (592, '220283', '舒兰市', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (593, '220284', '磐石市', '220200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (594, '220301', '市辖区', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (595, '220302', '铁西区', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (596, '220303', '铁东区', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (597, '220322', '梨树县', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (598, '220323', '伊通满族自治县', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (599, '220381', '公主岭市', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (600, '220382', '双辽市', '220300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (601, '220401', '市辖区', '220400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (602, '220402', '龙山区', '220400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (603, '220403', '西安区', '220400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (604, '220421', '东丰县', '220400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (605, '220422', '东辽县', '220400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (606, '220501', '市辖区', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (607, '220502', '东昌区', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (608, '220503', '二道江区', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (609, '220521', '通化县', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (610, '220523', '辉南县', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (611, '220524', '柳河县', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (612, '220581', '梅河口市', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (613, '220582', '集安市', '220500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (614, '220601', '市辖区', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (615, '220602', '八道江区', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (616, '220621', '抚松县', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (617, '220622', '靖宇县', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (618, '220623', '长白朝鲜族自治县', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (619, '220625', '江源县', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (620, '220681', '临江市', '220600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (621, '220701', '市辖区', '220700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (622, '220702', '宁江区', '220700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (623, '220721', '前郭尔罗斯蒙古族自治县', '220700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (624, '220722', '长岭县', '220700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (625, '220723', '乾安县', '220700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (626, '220724', '扶余县', '220700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (627, '220801', '市辖区', '220800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (628, '220802', '洮北区', '220800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (629, '220821', '镇赉县', '220800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (630, '220822', '通榆县', '220800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (631, '220881', '洮南市', '220800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (632, '220882', '大安市', '220800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (633, '222401', '延吉市', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (634, '222402', '图们市', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (635, '222403', '敦化市', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (636, '222404', '珲春市', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (637, '222405', '龙井市', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (638, '222406', '和龙市', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (639, '222424', '汪清县', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (640, '222426', '安图县', '222400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (641, '230101', '市辖区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (642, '230102', '道里区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (643, '230103', '南岗区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (644, '230104', '道外区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (645, '230106', '香坊区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (646, '230107', '动力区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (647, '230108', '平房区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (648, '230109', '松北区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (649, '230111', '呼兰区', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (650, '230123', '依兰县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (651, '230124', '方正县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (652, '230125', '宾　县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (653, '230126', '巴彦县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (654, '230127', '木兰县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (655, '230128', '通河县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (656, '230129', '延寿县', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (657, '230181', '阿城市', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (658, '230182', '双城市', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (659, '230183', '尚志市', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (660, '230184', '五常市', '230100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (661, '230201', '市辖区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (662, '230202', '龙沙区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (663, '230203', '建华区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (664, '230204', '铁锋区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (665, '230205', '昂昂溪区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (666, '230206', '富拉尔基区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (667, '230207', '碾子山区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (668, '230208', '梅里斯达斡尔族区', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (669, '230221', '龙江县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (670, '230223', '依安县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (671, '230224', '泰来县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (672, '230225', '甘南县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (673, '230227', '富裕县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (674, '230229', '克山县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (675, '230230', '克东县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (676, '230231', '拜泉县', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (677, '230281', '讷河市', '230200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (678, '230301', '市辖区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (679, '230302', '鸡冠区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (680, '230303', '恒山区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (681, '230304', '滴道区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (682, '230305', '梨树区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (683, '230306', '城子河区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (684, '230307', '麻山区', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (685, '230321', '鸡东县', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (686, '230381', '虎林市', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (687, '230382', '密山市', '230300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (688, '230401', '市辖区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (689, '230402', '向阳区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (690, '230403', '工农区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (691, '230404', '南山区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (692, '230405', '兴安区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (693, '230406', '东山区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (694, '230407', '兴山区', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (695, '230421', '萝北县', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (696, '230422', '绥滨县', '230400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (697, '230501', '市辖区', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (698, '230502', '尖山区', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (699, '230503', '岭东区', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (700, '230505', '四方台区', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (701, '230506', '宝山区', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (702, '230521', '集贤县', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (703, '230522', '友谊县', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (704, '230523', '宝清县', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (705, '230524', '饶河县', '230500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (706, '230601', '市辖区', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (707, '230602', '萨尔图区', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (708, '230603', '龙凤区', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (709, '230604', '让胡路区', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (710, '230605', '红岗区', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (711, '230606', '大同区', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (712, '230621', '肇州县', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (713, '230622', '肇源县', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (714, '230623', '林甸县', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (715, '230624', '杜尔伯特蒙古族自治县', '230600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (716, '230701', '市辖区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (717, '230702', '伊春区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (718, '230703', '南岔区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (719, '230704', '友好区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (720, '230705', '西林区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (721, '230706', '翠峦区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (722, '230707', '新青区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (723, '230708', '美溪区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (724, '230709', '金山屯区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (725, '230710', '五营区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (726, '230711', '乌马河区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (727, '230712', '汤旺河区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (728, '230713', '带岭区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (729, '230714', '乌伊岭区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (730, '230715', '红星区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (731, '230716', '上甘岭区', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (732, '230722', '嘉荫县', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (733, '230781', '铁力市', '230700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (734, '230801', '市辖区', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (735, '230802', '永红区', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (736, '230803', '向阳区', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (737, '230804', '前进区', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (738, '230805', '东风区', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (739, '230811', '郊　区', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (740, '230822', '桦南县', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (741, '230826', '桦川县', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (742, '230828', '汤原县', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (743, '230833', '抚远县', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (744, '230881', '同江市', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (745, '230882', '富锦市', '230800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (746, '230901', '市辖区', '230900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (747, '230902', '新兴区', '230900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (748, '230903', '桃山区', '230900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (749, '230904', '茄子河区', '230900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (750, '230921', '勃利县', '230900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (751, '231001', '市辖区', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (752, '231002', '东安区', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (753, '231003', '阳明区', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (754, '231004', '爱民区', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (755, '231005', '西安区', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (756, '231024', '东宁县', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (757, '231025', '林口县', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (758, '231081', '绥芬河市', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (759, '231083', '海林市', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (760, '231084', '宁安市', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (761, '231085', '穆棱市', '231000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (762, '231101', '市辖区', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (763, '231102', '爱辉区', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (764, '231121', '嫩江县', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (765, '231123', '逊克县', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (766, '231124', '孙吴县', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (767, '231181', '北安市', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (768, '231182', '五大连池市', '231100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (769, '231201', '市辖区', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (770, '231202', '北林区', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (771, '231221', '望奎县', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (772, '231222', '兰西县', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (773, '231223', '青冈县', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (774, '231224', '庆安县', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (775, '231225', '明水县', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (776, '231226', '绥棱县', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (777, '231281', '安达市', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (778, '231282', '肇东市', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (779, '231283', '海伦市', '231200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (780, '232721', '呼玛县', '232700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (781, '232722', '塔河县', '232700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (782, '232723', '漠河县', '232700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (783, '310101', '黄浦区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (784, '310103', '卢湾区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (785, '310104', '徐汇区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (786, '310105', '长宁区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (787, '310106', '静安区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (788, '310107', '普陀区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (789, '310108', '闸北区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (790, '310109', '虹口区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (791, '310110', '杨浦区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (792, '310112', '闵行区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (793, '310113', '宝山区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (794, '310114', '嘉定区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (795, '310115', '浦东新区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (796, '310116', '金山区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (797, '310117', '松江区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (798, '310118', '青浦区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (799, '310119', '南汇区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (800, '310120', '奉贤区', '310100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (801, '310230', '崇明县', '310200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (802, '320101', '市辖区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (803, '320102', '玄武区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (804, '320103', '白下区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (805, '320104', '秦淮区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (806, '320105', '建邺区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (807, '320106', '鼓楼区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (808, '320107', '下关区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (809, '320111', '浦口区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (810, '320113', '栖霞区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (811, '320114', '雨花台区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (812, '320115', '江宁区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (813, '320116', '六合区', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (814, '320124', '溧水县', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (815, '320125', '高淳县', '320100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (816, '320201', '市辖区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (817, '320202', '崇安区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (818, '320203', '南长区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (819, '320204', '北塘区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (820, '320205', '锡山区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (821, '320206', '惠山区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (822, '320211', '滨湖区', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (823, '320281', '江阴市', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (824, '320282', '宜兴市', '320200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (825, '320301', '市辖区', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (826, '320302', '鼓楼区', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (827, '320303', '云龙区', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (828, '320304', '九里区', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (829, '320305', '贾汪区', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (830, '320311', '泉山区', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (831, '320321', '丰　县', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (832, '320322', '沛　县', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (833, '320323', '铜山县', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (834, '320324', '睢宁县', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (835, '320381', '新沂市', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (836, '320382', '邳州市', '320300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (837, '320401', '市辖区', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (838, '320402', '天宁区', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (839, '320404', '钟楼区', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (840, '320405', '戚墅堰区', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (841, '320411', '新北区', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (842, '320412', '武进区', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (843, '320481', '溧阳市', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (844, '320482', '金坛市', '320400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (845, '320501', '市辖区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (846, '320502', '沧浪区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (847, '320503', '平江区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (848, '320504', '金阊区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (849, '320505', '虎丘区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (850, '320506', '吴中区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (851, '320507', '相城区', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (852, '320581', '常熟市', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (853, '320582', '张家港市', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (854, '320583', '昆山市', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (855, '320584', '吴江市', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (856, '320585', '太仓市', '320500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (857, '320601', '市辖区', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (858, '320602', '崇川区', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (859, '320611', '港闸区', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (860, '320621', '海安县', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (861, '320623', '如东县', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (862, '320681', '启东市', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (863, '320682', '如皋市', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (864, '320683', '通州市', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (865, '320684', '海门市', '320600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (866, '320701', '市辖区', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (867, '320703', '连云区', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (868, '320705', '新浦区', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (869, '320706', '海州区', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (870, '320721', '赣榆县', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (871, '320722', '东海县', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (872, '320723', '灌云县', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (873, '320724', '灌南县', '320700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (874, '320801', '市辖区', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (875, '320802', '清河区', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (876, '320803', '楚州区', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (877, '320804', '淮阴区', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (878, '320811', '清浦区', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (879, '320826', '涟水县', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (880, '320829', '洪泽县', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (881, '320830', '盱眙县', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (882, '320831', '金湖县', '320800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (883, '320901', '市辖区', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (884, '320902', '亭湖区', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (885, '320903', '盐都区', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (886, '320921', '响水县', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (887, '320922', '滨海县', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (888, '320923', '阜宁县', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (889, '320924', '射阳县', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (890, '320925', '建湖县', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (891, '320981', '东台市', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (892, '320982', '大丰市', '320900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (893, '321001', '市辖区', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (894, '321002', '广陵区', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (895, '321003', '邗江区', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (896, '321011', '郊　区', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (897, '321023', '宝应县', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (898, '321081', '仪征市', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (899, '321084', '高邮市', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (900, '321088', '江都市', '321000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (901, '321101', '市辖区', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (902, '321102', '京口区', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (903, '321111', '润州区', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (904, '321112', '丹徒区', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (905, '321181', '丹阳市', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (906, '321182', '扬中市', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (907, '321183', '句容市', '321100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (908, '321201', '市辖区', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (909, '321202', '海陵区', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (910, '321203', '高港区', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (911, '321281', '兴化市', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (912, '321282', '靖江市', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (913, '321283', '泰兴市', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (914, '321284', '姜堰市', '321200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (915, '321301', '市辖区', '321300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (916, '321302', '宿城区', '321300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (917, '321311', '宿豫区', '321300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (918, '321322', '沭阳县', '321300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (919, '321323', '泗阳县', '321300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (920, '321324', '泗洪县', '321300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (921, '330101', '市辖区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (922, '330102', '上城区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (923, '330103', '下城区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (924, '330104', '江干区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (925, '330105', '拱墅区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (926, '330106', '西湖区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (927, '330108', '滨江区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (928, '330109', '萧山区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (929, '330110', '余杭区', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (930, '330122', '桐庐县', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (931, '330127', '淳安县', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (932, '330182', '建德市', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (933, '330183', '富阳市', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (934, '330185', '临安市', '330100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (935, '330201', '市辖区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (936, '330203', '海曙区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (937, '330204', '江东区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (938, '330205', '江北区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (939, '330206', '北仑区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (940, '330211', '镇海区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (941, '330212', '鄞州区', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (942, '330225', '象山县', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (943, '330226', '宁海县', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (944, '330281', '余姚市', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (945, '330282', '慈溪市', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (946, '330283', '奉化市', '330200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (947, '330301', '市辖区', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (948, '330302', '鹿城区', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (949, '330303', '龙湾区', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (950, '330304', '瓯海区', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (951, '330322', '洞头县', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (952, '330324', '永嘉县', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (953, '330326', '平阳县', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (954, '330327', '苍南县', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (955, '330328', '文成县', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (956, '330329', '泰顺县', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (957, '330381', '瑞安市', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (958, '330382', '乐清市', '330300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (959, '330401', '市辖区', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (960, '330402', '秀城区', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (961, '330411', '秀洲区', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (962, '330421', '嘉善县', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (963, '330424', '海盐县', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (964, '330481', '海宁市', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (965, '330482', '平湖市', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (966, '330483', '桐乡市', '330400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (967, '330501', '市辖区', '330500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (968, '330502', '吴兴区', '330500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (969, '330503', '南浔区', '330500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (970, '330521', '德清县', '330500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (971, '330522', '长兴县', '330500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (972, '330523', '安吉县', '330500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (973, '330601', '市辖区', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (974, '330602', '越城区', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (975, '330621', '绍兴县', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (976, '330624', '新昌县', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (977, '330681', '诸暨市', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (978, '330682', '上虞市', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (979, '330683', '嵊州市', '330600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (980, '330701', '市辖区', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (981, '330702', '婺城区', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (982, '330703', '金东区', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (983, '330723', '武义县', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (984, '330726', '浦江县', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (985, '330727', '磐安县', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (986, '330781', '兰溪市', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (987, '330782', '义乌市', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (988, '330783', '东阳市', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (989, '330784', '永康市', '330700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (990, '330801', '市辖区', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (991, '330802', '柯城区', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (992, '330803', '衢江区', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (993, '330822', '常山县', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (994, '330824', '开化县', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (995, '330825', '龙游县', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (996, '330881', '江山市', '330800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (997, '330901', '市辖区', '330900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (998, '330902', '定海区', '330900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (999, '330903', '普陀区', '330900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1000, '330921', '岱山县', '330900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1001, '330922', '嵊泗县', '330900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1002, '331001', '市辖区', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1003, '331002', '椒江区', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1004, '331003', '黄岩区', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1005, '331004', '路桥区', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1006, '331021', '玉环县', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1007, '331022', '三门县', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1008, '331023', '天台县', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1009, '331024', '仙居县', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1010, '331081', '温岭市', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1011, '331082', '临海市', '331000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1012, '331101', '市辖区', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1013, '331102', '莲都区', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1014, '331121', '青田县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1015, '331122', '缙云县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1016, '331123', '遂昌县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1017, '331124', '松阳县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1018, '331125', '云和县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1019, '331126', '庆元县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1020, '331127', '景宁畲族自治县', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1021, '331181', '龙泉市', '331100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1022, '340101', '市辖区', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1023, '340102', '瑶海区', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1024, '340103', '庐阳区', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1025, '340104', '蜀山区', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1026, '340111', '包河区', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1027, '340121', '长丰县', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1028, '340122', '肥东县', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1029, '340123', '肥西县', '340100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1030, '340201', '市辖区', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1031, '340202', '镜湖区', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1032, '340203', '马塘区', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1033, '340204', '新芜区', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1034, '340207', '鸠江区', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1035, '340221', '芜湖县', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1036, '340222', '繁昌县', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1037, '340223', '南陵县', '340200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1038, '340301', '市辖区', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1039, '340302', '龙子湖区', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1040, '340303', '蚌山区', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1041, '340304', '禹会区', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1042, '340311', '淮上区', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1043, '340321', '怀远县', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1044, '340322', '五河县', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1045, '340323', '固镇县', '340300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1046, '340401', '市辖区', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1047, '340402', '大通区', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1048, '340403', '田家庵区', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1049, '340404', '谢家集区', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1050, '340405', '八公山区', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1051, '340406', '潘集区', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1052, '340421', '凤台县', '340400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1053, '340501', '市辖区', '340500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1054, '340502', '金家庄区', '340500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1055, '340503', '花山区', '340500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1056, '340504', '雨山区', '340500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1057, '340521', '当涂县', '340500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1058, '340601', '市辖区', '340600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1059, '340602', '杜集区', '340600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1060, '340603', '相山区', '340600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1061, '340604', '烈山区', '340600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1062, '340621', '濉溪县', '340600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1063, '340701', '市辖区', '340700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1064, '340702', '铜官山区', '340700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1065, '340703', '狮子山区', '340700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1066, '340711', '郊　区', '340700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1067, '340721', '铜陵县', '340700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1068, '340801', '市辖区', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1069, '340802', '迎江区', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1070, '340803', '大观区', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1071, '340811', '郊　区', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1072, '340822', '怀宁县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1073, '340823', '枞阳县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1074, '340824', '潜山县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1075, '340825', '太湖县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1076, '340826', '宿松县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1077, '340827', '望江县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1078, '340828', '岳西县', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1079, '340881', '桐城市', '340800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1080, '341001', '市辖区', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1081, '341002', '屯溪区', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1082, '341003', '黄山区', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1083, '341004', '徽州区', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1084, '341021', '歙　县', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1085, '341022', '休宁县', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1086, '341023', '黟　县', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1087, '341024', '祁门县', '341000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1088, '341101', '市辖区', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1089, '341102', '琅琊区', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1090, '341103', '南谯区', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1091, '341122', '来安县', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1092, '341124', '全椒县', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1093, '341125', '定远县', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1094, '341126', '凤阳县', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1095, '341181', '天长市', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1096, '341182', '明光市', '341100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1097, '341201', '市辖区', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1098, '341202', '颍州区', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1099, '341203', '颍东区', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1100, '341204', '颍泉区', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1101, '341221', '临泉县', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1102, '341222', '太和县', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1103, '341225', '阜南县', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1104, '341226', '颍上县', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1105, '341282', '界首市', '341200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1106, '341301', '市辖区', '341300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1107, '341302', '墉桥区', '341300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1108, '341321', '砀山县', '341300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1109, '341322', '萧　县', '341300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1110, '341323', '灵璧县', '341300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1111, '341324', '泗　县', '341300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1112, '341401', '市辖区', '341400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1113, '341402', '居巢区', '341400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1114, '341421', '庐江县', '341400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1115, '341422', '无为县', '341400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1116, '341423', '含山县', '341400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1117, '341424', '和　县', '341400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1118, '341501', '市辖区', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1119, '341502', '金安区', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1120, '341503', '裕安区', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1121, '341521', '寿　县', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1122, '341522', '霍邱县', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1123, '341523', '舒城县', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1124, '341524', '金寨县', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1125, '341525', '霍山县', '341500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1126, '341601', '市辖区', '341600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1127, '341602', '谯城区', '341600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1128, '341621', '涡阳县', '341600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1129, '341622', '蒙城县', '341600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1130, '341623', '利辛县', '341600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1131, '341701', '市辖区', '341700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1132, '341702', '贵池区', '341700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1133, '341721', '东至县', '341700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1134, '341722', '石台县', '341700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1135, '341723', '青阳县', '341700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1136, '341801', '市辖区', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1137, '341802', '宣州区', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1138, '341821', '郎溪县', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1139, '341822', '广德县', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1140, '341823', '泾　县', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1141, '341824', '绩溪县', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1142, '341825', '旌德县', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1143, '341881', '宁国市', '341800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1144, '350101', '市辖区', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1145, '350102', '鼓楼区', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1146, '350103', '台江区', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1147, '350104', '仓山区', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1148, '350105', '马尾区', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1149, '350111', '晋安区', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1150, '350121', '闽侯县', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1151, '350122', '连江县', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1152, '350123', '罗源县', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1153, '350124', '闽清县', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1154, '350125', '永泰县', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1155, '350128', '平潭县', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1156, '350181', '福清市', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1157, '350182', '长乐市', '350100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1158, '350201', '市辖区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1159, '350203', '思明区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1160, '350205', '海沧区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1161, '350206', '湖里区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1162, '350211', '集美区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1163, '350212', '同安区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1164, '350213', '翔安区', '350200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1165, '350301', '市辖区', '350300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1166, '350302', '城厢区', '350300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1167, '350303', '涵江区', '350300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1168, '350304', '荔城区', '350300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1169, '350305', '秀屿区', '350300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1170, '350322', '仙游县', '350300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1171, '350401', '市辖区', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1172, '350402', '梅列区', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1173, '350403', '三元区', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1174, '350421', '明溪县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1175, '350423', '清流县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1176, '350424', '宁化县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1177, '350425', '大田县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1178, '350426', '尤溪县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1179, '350427', '沙　县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1180, '350428', '将乐县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1181, '350429', '泰宁县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1182, '350430', '建宁县', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1183, '350481', '永安市', '350400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1184, '350501', '市辖区', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1185, '350502', '鲤城区', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1186, '350503', '丰泽区', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1187, '350504', '洛江区', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1188, '350505', '泉港区', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1189, '350521', '惠安县', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1190, '350524', '安溪县', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1191, '350525', '永春县', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1192, '350526', '德化县', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1193, '350527', '金门县', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1194, '350581', '石狮市', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1195, '350582', '晋江市', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1196, '350583', '南安市', '350500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1197, '350601', '市辖区', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1198, '350602', '芗城区', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1199, '350603', '龙文区', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1200, '350622', '云霄县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1201, '350623', '漳浦县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1202, '350624', '诏安县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1203, '350625', '长泰县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1204, '350626', '东山县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1205, '350627', '南靖县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1206, '350628', '平和县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1207, '350629', '华安县', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1208, '350681', '龙海市', '350600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1209, '350701', '市辖区', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1210, '350702', '延平区', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1211, '350721', '顺昌县', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1212, '350722', '浦城县', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1213, '350723', '光泽县', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1214, '350724', '松溪县', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1215, '350725', '政和县', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1216, '350781', '邵武市', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1217, '350782', '武夷山市', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1218, '350783', '建瓯市', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1219, '350784', '建阳市', '350700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1220, '350801', '市辖区', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1221, '350802', '新罗区', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1222, '350821', '长汀县', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1223, '350822', '永定县', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1224, '350823', '上杭县', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1225, '350824', '武平县', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1226, '350825', '连城县', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1227, '350881', '漳平市', '350800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1228, '350901', '市辖区', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1229, '350902', '蕉城区', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1230, '350921', '霞浦县', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1231, '350922', '古田县', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1232, '350923', '屏南县', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1233, '350924', '寿宁县', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1234, '350925', '周宁县', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1235, '350926', '柘荣县', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1236, '350981', '福安市', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1237, '350982', '福鼎市', '350900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1238, '360101', '市辖区', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1239, '360102', '东湖区', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1240, '360103', '西湖区', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1241, '360104', '青云谱区', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1242, '360105', '湾里区', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1243, '360111', '青山湖区', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1244, '360121', '南昌县', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1245, '360122', '新建县', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1246, '360123', '安义县', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1247, '360124', '进贤县', '360100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1248, '360201', '市辖区', '360200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1249, '360202', '昌江区', '360200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1250, '360203', '珠山区', '360200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1251, '360222', '浮梁县', '360200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1252, '360281', '乐平市', '360200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1253, '360301', '市辖区', '360300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1254, '360302', '安源区', '360300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1255, '360313', '湘东区', '360300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1256, '360321', '莲花县', '360300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1257, '360322', '上栗县', '360300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1258, '360323', '芦溪县', '360300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1259, '360401', '市辖区', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1260, '360402', '庐山区', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1261, '360403', '浔阳区', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1262, '360421', '九江县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1263, '360423', '武宁县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1264, '360424', '修水县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1265, '360425', '永修县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1266, '360426', '德安县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1267, '360427', '星子县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1268, '360428', '都昌县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1269, '360429', '湖口县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1270, '360430', '彭泽县', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1271, '360481', '瑞昌市', '360400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1272, '360501', '市辖区', '360500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1273, '360502', '渝水区', '360500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1274, '360521', '分宜县', '360500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1275, '360601', '市辖区', '360600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1276, '360602', '月湖区', '360600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1277, '360622', '余江县', '360600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1278, '360681', '贵溪市', '360600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1279, '360701', '市辖区', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1280, '360702', '章贡区', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1281, '360721', '赣　县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1282, '360722', '信丰县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1283, '360723', '大余县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1284, '360724', '上犹县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1285, '360725', '崇义县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1286, '360726', '安远县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1287, '360727', '龙南县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1288, '360728', '定南县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1289, '360729', '全南县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1290, '360730', '宁都县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1291, '360731', '于都县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1292, '360732', '兴国县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1293, '360733', '会昌县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1294, '360734', '寻乌县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1295, '360735', '石城县', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1296, '360781', '瑞金市', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1297, '360782', '南康市', '360700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1298, '360801', '市辖区', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1299, '360802', '吉州区', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1300, '360803', '青原区', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1301, '360821', '吉安县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1302, '360822', '吉水县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1303, '360823', '峡江县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1304, '360824', '新干县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1305, '360825', '永丰县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1306, '360826', '泰和县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1307, '360827', '遂川县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1308, '360828', '万安县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1309, '360829', '安福县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1310, '360830', '永新县', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1311, '360881', '井冈山市', '360800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1312, '360901', '市辖区', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1313, '360902', '袁州区', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1314, '360921', '奉新县', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1315, '360922', '万载县', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1316, '360923', '上高县', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1317, '360924', '宜丰县', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1318, '360925', '靖安县', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1319, '360926', '铜鼓县', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1320, '360981', '丰城市', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1321, '360982', '樟树市', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1322, '360983', '高安市', '360900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1323, '361001', '市辖区', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1324, '361002', '临川区', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1325, '361021', '南城县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1326, '361022', '黎川县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1327, '361023', '南丰县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1328, '361024', '崇仁县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1329, '361025', '乐安县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1330, '361026', '宜黄县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1331, '361027', '金溪县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1332, '361028', '资溪县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1333, '361029', '东乡县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1334, '361030', '广昌县', '361000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1335, '361101', '市辖区', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1336, '361102', '信州区', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1337, '361121', '上饶县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1338, '361122', '广丰县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1339, '361123', '玉山县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1340, '361124', '铅山县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1341, '361125', '横峰县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1342, '361126', '弋阳县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1343, '361127', '余干县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1344, '361128', '鄱阳县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1345, '361129', '万年县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1346, '361130', '婺源县', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1347, '361181', '德兴市', '361100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1348, '370101', '市辖区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1349, '370102', '历下区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1350, '370103', '市中区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1351, '370104', '槐荫区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1352, '370105', '天桥区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1353, '370112', '历城区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1354, '370113', '长清区', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1355, '370124', '平阴县', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1356, '370125', '济阳县', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1357, '370126', '商河县', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1358, '370181', '章丘市', '370100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1359, '370201', '市辖区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1360, '370202', '市南区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1361, '370203', '市北区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1362, '370205', '四方区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1363, '370211', '黄岛区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1364, '370212', '崂山区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1365, '370213', '李沧区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1366, '370214', '城阳区', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1367, '370281', '胶州市', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1368, '370282', '即墨市', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1369, '370283', '平度市', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1370, '370284', '胶南市', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1371, '370285', '莱西市', '370200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1372, '370301', '市辖区', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1373, '370302', '淄川区', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1374, '370303', '张店区', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1375, '370304', '博山区', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1376, '370305', '临淄区', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1377, '370306', '周村区', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1378, '370321', '桓台县', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1379, '370322', '高青县', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1380, '370323', '沂源县', '370300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1381, '370401', '市辖区', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1382, '370402', '市中区', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1383, '370403', '薛城区', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1384, '370404', '峄城区', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1385, '370405', '台儿庄区', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1386, '370406', '山亭区', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1387, '370481', '滕州市', '370400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1388, '370501', '市辖区', '370500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1389, '370502', '东营区', '370500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1390, '370503', '河口区', '370500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1391, '370521', '垦利县', '370500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1392, '370522', '利津县', '370500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1393, '370523', '广饶县', '370500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1394, '370601', '市辖区', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1395, '370602', '芝罘区', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1396, '370611', '福山区', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1397, '370612', '牟平区', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1398, '370613', '莱山区', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1399, '370634', '长岛县', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1400, '370681', '龙口市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1401, '370682', '莱阳市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1402, '370683', '莱州市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1403, '370684', '蓬莱市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1404, '370685', '招远市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1405, '370686', '栖霞市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1406, '370687', '海阳市', '370600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1407, '370701', '市辖区', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1408, '370702', '潍城区', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1409, '370703', '寒亭区', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1410, '370704', '坊子区', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1411, '370705', '奎文区', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1412, '370724', '临朐县', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1413, '370725', '昌乐县', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1414, '370781', '青州市', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1415, '370782', '诸城市', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1416, '370783', '寿光市', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1417, '370784', '安丘市', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1418, '370785', '高密市', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1419, '370786', '昌邑市', '370700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1420, '370801', '市辖区', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1421, '370802', '市中区', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1422, '370811', '任城区', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1423, '370826', '微山县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1424, '370827', '鱼台县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1425, '370828', '金乡县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1426, '370829', '嘉祥县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1427, '370830', '汶上县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1428, '370831', '泗水县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1429, '370832', '梁山县', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1430, '370881', '曲阜市', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1431, '370882', '兖州市', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1432, '370883', '邹城市', '370800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1433, '370901', '市辖区', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1434, '370902', '泰山区', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1435, '370903', '岱岳区', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1436, '370921', '宁阳县', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1437, '370923', '东平县', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1438, '370982', '新泰市', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1439, '370983', '肥城市', '370900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1440, '371001', '市辖区', '371000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1441, '371002', '环翠区', '371000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1442, '371081', '文登市', '371000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1443, '371082', '荣成市', '371000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1444, '371083', '乳山市', '371000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1445, '371101', '市辖区', '371100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1446, '371102', '东港区', '371100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1447, '371103', '岚山区', '371100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1448, '371121', '五莲县', '371100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1449, '371122', '莒　县', '371100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1450, '371201', '市辖区', '371200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1451, '371202', '莱城区', '371200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1452, '371203', '钢城区', '371200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1453, '371301', '市辖区', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1454, '371302', '兰山区', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1455, '371311', '罗庄区', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1456, '371312', '河东区', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1457, '371321', '沂南县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1458, '371322', '郯城县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1459, '371323', '沂水县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1460, '371324', '苍山县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1461, '371325', '费　县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1462, '371326', '平邑县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1463, '371327', '莒南县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1464, '371328', '蒙阴县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1465, '371329', '临沭县', '371300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1466, '371401', '市辖区', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1467, '371402', '德城区', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1468, '371421', '陵　县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1469, '371422', '宁津县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1470, '371423', '庆云县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1471, '371424', '临邑县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1472, '371425', '齐河县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1473, '371426', '平原县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1474, '371427', '夏津县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1475, '371428', '武城县', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1476, '371481', '乐陵市', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1477, '371482', '禹城市', '371400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1478, '371501', '市辖区', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1479, '371502', '东昌府区', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1480, '371521', '阳谷县', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1481, '371522', '莘　县', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1482, '371523', '茌平县', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1483, '371524', '东阿县', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1484, '371525', '冠　县', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1485, '371526', '高唐县', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1486, '371581', '临清市', '371500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1487, '371601', '市辖区', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1488, '371602', '滨城区', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1489, '371621', '惠民县', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1490, '371622', '阳信县', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1491, '371623', '无棣县', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1492, '371624', '沾化县', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1493, '371625', '博兴县', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1494, '371626', '邹平县', '371600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1495, '371701', '市辖区', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1496, '371702', '牡丹区', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1497, '371721', '曹　县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1498, '371722', '单　县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1499, '371723', '成武县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1500, '371724', '巨野县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1501, '371725', '郓城县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1502, '371726', '鄄城县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1503, '371727', '定陶县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1504, '371728', '东明县', '371700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1505, '410101', '市辖区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1506, '410102', '中原区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1507, '410103', '二七区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1508, '410104', '管城回族区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1509, '410105', '金水区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1510, '410106', '上街区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1511, '410108', '邙山区', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1512, '410122', '中牟县', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1513, '410181', '巩义市', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1514, '410182', '荥阳市', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1515, '410183', '新密市', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1516, '410184', '新郑市', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1517, '410185', '登封市', '410100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1518, '410201', '市辖区', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1519, '410202', '龙亭区', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1520, '410203', '顺河回族区', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1521, '410204', '鼓楼区', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1522, '410205', '南关区', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1523, '410211', '郊　区', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1524, '410221', '杞　县', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1525, '410222', '通许县', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1526, '410223', '尉氏县', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1527, '410224', '开封县', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1528, '410225', '兰考县', '410200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1529, '410301', '市辖区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1530, '410302', '老城区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1531, '410303', '西工区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1532, '410304', '廛河回族区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1533, '410305', '涧西区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1534, '410306', '吉利区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1535, '410307', '洛龙区', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1536, '410322', '孟津县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1537, '410323', '新安县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1538, '410324', '栾川县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1539, '410325', '嵩　县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1540, '410326', '汝阳县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1541, '410327', '宜阳县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1542, '410328', '洛宁县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1543, '410329', '伊川县', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1544, '410381', '偃师市', '410300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1545, '410401', '市辖区', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1546, '410402', '新华区', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1547, '410403', '卫东区', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1548, '410404', '石龙区', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1549, '410411', '湛河区', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1550, '410421', '宝丰县', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1551, '410422', '叶　县', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1552, '410423', '鲁山县', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1553, '410425', '郏　县', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1554, '410481', '舞钢市', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1555, '410482', '汝州市', '410400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1556, '410501', '市辖区', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1557, '410502', '文峰区', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1558, '410503', '北关区', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1559, '410505', '殷都区', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1560, '410506', '龙安区', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1561, '410522', '安阳县', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1562, '410523', '汤阴县', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1563, '410526', '滑　县', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1564, '410527', '内黄县', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1565, '410581', '林州市', '410500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1566, '410601', '市辖区', '410600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1567, '410602', '鹤山区', '410600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1568, '410603', '山城区', '410600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1569, '410611', '淇滨区', '410600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1570, '410621', '浚　县', '410600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1571, '410622', '淇　县', '410600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1572, '410701', '市辖区', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1573, '410702', '红旗区', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1574, '410703', '卫滨区', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1575, '410704', '凤泉区', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1576, '410711', '牧野区', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1577, '410721', '新乡县', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1578, '410724', '获嘉县', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1579, '410725', '原阳县', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1580, '410726', '延津县', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1581, '410727', '封丘县', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1582, '410728', '长垣县', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1583, '410781', '卫辉市', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1584, '410782', '辉县市', '410700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1585, '410801', '市辖区', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1586, '410802', '解放区', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1587, '410803', '中站区', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1588, '410804', '马村区', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1589, '410811', '山阳区', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1590, '410821', '修武县', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1591, '410822', '博爱县', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1592, '410823', '武陟县', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1593, '410825', '温　县', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1594, '410881', '济源市', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1595, '410882', '沁阳市', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1596, '410883', '孟州市', '410800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1597, '410901', '市辖区', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1598, '410902', '华龙区', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1599, '410922', '清丰县', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1600, '410923', '南乐县', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1601, '410926', '范　县', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1602, '410927', '台前县', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1603, '410928', '濮阳县', '410900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1604, '411001', '市辖区', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1605, '411002', '魏都区', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1606, '411023', '许昌县', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1607, '411024', '鄢陵县', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1608, '411025', '襄城县', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1609, '411081', '禹州市', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1610, '411082', '长葛市', '411000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1611, '411101', '市辖区', '411100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1612, '411102', '源汇区', '411100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1613, '411103', '郾城区', '411100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1614, '411104', '召陵区', '411100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1615, '411121', '舞阳县', '411100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1616, '411122', '临颍县', '411100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1617, '411201', '市辖区', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1618, '411202', '湖滨区', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1619, '411221', '渑池县', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1620, '411222', '陕　县', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1621, '411224', '卢氏县', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1622, '411281', '义马市', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1623, '411282', '灵宝市', '411200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1624, '411301', '市辖区', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1625, '411302', '宛城区', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1626, '411303', '卧龙区', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1627, '411321', '南召县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1628, '411322', '方城县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1629, '411323', '西峡县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1630, '411324', '镇平县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1631, '411325', '内乡县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1632, '411326', '淅川县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1633, '411327', '社旗县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1634, '411328', '唐河县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1635, '411329', '新野县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1636, '411330', '桐柏县', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1637, '411381', '邓州市', '411300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1638, '411401', '市辖区', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1639, '411402', '梁园区', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1640, '411403', '睢阳区', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1641, '411421', '民权县', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1642, '411422', '睢　县', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1643, '411423', '宁陵县', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1644, '411424', '柘城县', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1645, '411425', '虞城县', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1646, '411426', '夏邑县', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1647, '411481', '永城市', '411400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1648, '411501', '市辖区', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1649, '411502', '师河区', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1650, '411503', '平桥区', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1651, '411521', '罗山县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1652, '411522', '光山县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1653, '411523', '新　县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1654, '411524', '商城县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1655, '411525', '固始县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1656, '411526', '潢川县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1657, '411527', '淮滨县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1658, '411528', '息　县', '411500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1659, '411601', '市辖区', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1660, '411602', '川汇区', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1661, '411621', '扶沟县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1662, '411622', '西华县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1663, '411623', '商水县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1664, '411624', '沈丘县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1665, '411625', '郸城县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1666, '411626', '淮阳县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1667, '411627', '太康县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1668, '411628', '鹿邑县', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1669, '411681', '项城市', '411600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1670, '411701', '市辖区', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1671, '411702', '驿城区', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1672, '411721', '西平县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1673, '411722', '上蔡县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1674, '411723', '平舆县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1675, '411724', '正阳县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1676, '411725', '确山县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1677, '411726', '泌阳县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1678, '411727', '汝南县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1679, '411728', '遂平县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1680, '411729', '新蔡县', '411700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1681, '420101', '市辖区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1682, '420102', '江岸区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1683, '420103', '江汉区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1684, '420104', '乔口区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1685, '420105', '汉阳区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1686, '420106', '武昌区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1687, '420107', '青山区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1688, '420111', '洪山区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1689, '420112', '东西湖区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1690, '420113', '汉南区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1691, '420114', '蔡甸区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1692, '420115', '江夏区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1693, '420116', '黄陂区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1694, '420117', '新洲区', '420100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1695, '420201', '市辖区', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1696, '420202', '黄石港区', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1697, '420203', '西塞山区', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1698, '420204', '下陆区', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1699, '420205', '铁山区', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1700, '420222', '阳新县', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1701, '420281', '大冶市', '420200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1702, '420301', '市辖区', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1703, '420302', '茅箭区', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1704, '420303', '张湾区', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1705, '420321', '郧　县', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1706, '420322', '郧西县', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1707, '420323', '竹山县', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1708, '420324', '竹溪县', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1709, '420325', '房　县', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1710, '420381', '丹江口市', '420300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1711, '420501', '市辖区', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1712, '420502', '西陵区', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1713, '420503', '伍家岗区', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1714, '420504', '点军区', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1715, '420505', '猇亭区', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1716, '420506', '夷陵区', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1717, '420525', '远安县', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1718, '420526', '兴山县', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1719, '420527', '秭归县', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1720, '420528', '长阳土家族自治县', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1721, '420529', '五峰土家族自治县', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1722, '420581', '宜都市', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1723, '420582', '当阳市', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1724, '420583', '枝江市', '420500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1725, '420601', '市辖区', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1726, '420602', '襄城区', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1727, '420606', '樊城区', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1728, '420607', '襄阳区', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1729, '420624', '南漳县', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1730, '420625', '谷城县', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1731, '420626', '保康县', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1732, '420682', '老河口市', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1733, '420683', '枣阳市', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1734, '420684', '宜城市', '420600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1735, '420701', '市辖区', '420700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1736, '420702', '梁子湖区', '420700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1737, '420703', '华容区', '420700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1738, '420704', '鄂城区', '420700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1739, '420801', '市辖区', '420800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1740, '420802', '东宝区', '420800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1741, '420804', '掇刀区', '420800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1742, '420821', '京山县', '420800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1743, '420822', '沙洋县', '420800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1744, '420881', '钟祥市', '420800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1745, '420901', '市辖区', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1746, '420902', '孝南区', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1747, '420921', '孝昌县', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1748, '420922', '大悟县', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1749, '420923', '云梦县', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1750, '420981', '应城市', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1751, '420982', '安陆市', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1752, '420984', '汉川市', '420900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1753, '421001', '市辖区', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1754, '421002', '沙市区', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1755, '421003', '荆州区', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1756, '421022', '公安县', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1757, '421023', '监利县', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1758, '421024', '江陵县', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1759, '421081', '石首市', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1760, '421083', '洪湖市', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1761, '421087', '松滋市', '421000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1762, '421101', '市辖区', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1763, '421102', '黄州区', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1764, '421121', '团风县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1765, '421122', '红安县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1766, '421123', '罗田县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1767, '421124', '英山县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1768, '421125', '浠水县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1769, '421126', '蕲春县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1770, '421127', '黄梅县', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1771, '421181', '麻城市', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1772, '421182', '武穴市', '421100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1773, '421201', '市辖区', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1774, '421202', '咸安区', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1775, '421221', '嘉鱼县', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1776, '421222', '通城县', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1777, '421223', '崇阳县', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1778, '421224', '通山县', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1779, '421281', '赤壁市', '421200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1780, '421301', '市辖区', '421300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1781, '421302', '曾都区', '421300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1782, '421381', '广水市', '421300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1783, '422801', '恩施市', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1784, '422802', '利川市', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1785, '422822', '建始县', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1786, '422823', '巴东县', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1787, '422825', '宣恩县', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1788, '422826', '咸丰县', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1789, '422827', '来凤县', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1790, '422828', '鹤峰县', '422800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1791, '429004', '仙桃市', '429000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1792, '429005', '潜江市', '429000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1793, '429006', '天门市', '429000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1794, '429021', '神农架林区', '429000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1795, '430101', '市辖区', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1796, '430102', '芙蓉区', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1797, '430103', '天心区', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1798, '430104', '岳麓区', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1799, '430105', '开福区', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1800, '430111', '雨花区', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1801, '430121', '长沙县', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1802, '430122', '望城县', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1803, '430124', '宁乡县', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1804, '430181', '浏阳市', '430100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1805, '430201', '市辖区', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1806, '430202', '荷塘区', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1807, '430203', '芦淞区', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1808, '430204', '石峰区', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1809, '430211', '天元区', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1810, '430221', '株洲县', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1811, '430223', '攸　县', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1812, '430224', '茶陵县', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1813, '430225', '炎陵县', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1814, '430281', '醴陵市', '430200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1815, '430301', '市辖区', '430300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1816, '430302', '雨湖区', '430300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1817, '430304', '岳塘区', '430300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1818, '430321', '湘潭县', '430300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1819, '430381', '湘乡市', '430300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1820, '430382', '韶山市', '430300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1821, '430401', '市辖区', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1822, '430405', '珠晖区', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1823, '430406', '雁峰区', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1824, '430407', '石鼓区', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1825, '430408', '蒸湘区', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1826, '430412', '南岳区', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1827, '430421', '衡阳县', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1828, '430422', '衡南县', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1829, '430423', '衡山县', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1830, '430424', '衡东县', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1831, '430426', '祁东县', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1832, '430481', '耒阳市', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1833, '430482', '常宁市', '430400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1834, '430501', '市辖区', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1835, '430502', '双清区', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1836, '430503', '大祥区', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1837, '430511', '北塔区', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1838, '430521', '邵东县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1839, '430522', '新邵县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1840, '430523', '邵阳县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1841, '430524', '隆回县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1842, '430525', '洞口县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1843, '430527', '绥宁县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1844, '430528', '新宁县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1845, '430529', '城步苗族自治县', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1846, '430581', '武冈市', '430500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1847, '430601', '市辖区', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1848, '430602', '岳阳楼区', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1849, '430603', '云溪区', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1850, '430611', '君山区', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1851, '430621', '岳阳县', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1852, '430623', '华容县', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1853, '430624', '湘阴县', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1854, '430626', '平江县', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1855, '430681', '汨罗市', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1856, '430682', '临湘市', '430600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1857, '430701', '市辖区', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1858, '430702', '武陵区', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1859, '430703', '鼎城区', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1860, '430721', '安乡县', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1861, '430722', '汉寿县', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1862, '430723', '澧　县', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1863, '430724', '临澧县', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1864, '430725', '桃源县', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1865, '430726', '石门县', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1866, '430781', '津市市', '430700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1867, '430801', '市辖区', '430800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1868, '430802', '永定区', '430800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1869, '430811', '武陵源区', '430800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1870, '430821', '慈利县', '430800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1871, '430822', '桑植县', '430800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1872, '430901', '市辖区', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1873, '430902', '资阳区', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1874, '430903', '赫山区', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1875, '430921', '南　县', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1876, '430922', '桃江县', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1877, '430923', '安化县', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1878, '430981', '沅江市', '430900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1879, '431001', '市辖区', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1880, '431002', '北湖区', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1881, '431003', '苏仙区', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1882, '431021', '桂阳县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1883, '431022', '宜章县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1884, '431023', '永兴县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1885, '431024', '嘉禾县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1886, '431025', '临武县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1887, '431026', '汝城县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1888, '431027', '桂东县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1889, '431028', '安仁县', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1890, '431081', '资兴市', '431000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1891, '431101', '市辖区', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1892, '431102', '芝山区', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1893, '431103', '冷水滩区', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1894, '431121', '祁阳县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1895, '431122', '东安县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1896, '431123', '双牌县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1897, '431124', '道　县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1898, '431125', '江永县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1899, '431126', '宁远县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1900, '431127', '蓝山县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1901, '431128', '新田县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1902, '431129', '江华瑶族自治县', '431100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1903, '431201', '市辖区', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1904, '431202', '鹤城区', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1905, '431221', '中方县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1906, '431222', '沅陵县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1907, '431223', '辰溪县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1908, '431224', '溆浦县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1909, '431225', '会同县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1910, '431226', '麻阳苗族自治县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1911, '431227', '新晃侗族自治县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1912, '431228', '芷江侗族自治县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1913, '431229', '靖州苗族侗族自治县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1914, '431230', '通道侗族自治县', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1915, '431281', '洪江市', '431200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1916, '431301', '市辖区', '431300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1917, '431302', '娄星区', '431300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1918, '431321', '双峰县', '431300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1919, '431322', '新化县', '431300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1920, '431381', '冷水江市', '431300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1921, '431382', '涟源市', '431300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1922, '433101', '吉首市', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1923, '433122', '泸溪县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1924, '433123', '凤凰县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1925, '433124', '花垣县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1926, '433125', '保靖县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1927, '433126', '古丈县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1928, '433127', '永顺县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1929, '433130', '龙山县', '433100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1930, '440101', '市辖区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1931, '440102', '东山区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1932, '440103', '荔湾区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1933, '440104', '越秀区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1934, '440105', '海珠区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1935, '440106', '天河区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1936, '440107', '芳村区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1937, '440111', '白云区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1938, '440112', '黄埔区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1939, '440113', '番禺区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1940, '440114', '花都区', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1941, '440183', '增城市', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1942, '440184', '从化市', '440100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1943, '440201', '市辖区', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1944, '440203', '武江区', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1945, '440204', '浈江区', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1946, '440205', '曲江区', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1947, '440222', '始兴县', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1948, '440224', '仁化县', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1949, '440229', '翁源县', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1950, '440232', '乳源瑶族自治县', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1951, '440233', '新丰县', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1952, '440281', '乐昌市', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1953, '440282', '南雄市', '440200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1954, '440301', '市辖区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1955, '440303', '罗湖区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1956, '440304', '福田区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1957, '440305', '南山区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1958, '440306', '宝安区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1959, '440307', '龙岗区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1960, '440308', '盐田区', '440300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1961, '440401', '市辖区', '440400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1962, '440402', '香洲区', '440400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1963, '440403', '斗门区', '440400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1964, '440404', '金湾区', '440400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1965, '440501', '市辖区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1966, '440507', '龙湖区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1967, '440511', '金平区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1968, '440512', '濠江区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1969, '440513', '潮阳区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1970, '440514', '潮南区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1971, '440515', '澄海区', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1972, '440523', '南澳县', '440500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1973, '440601', '市辖区', '440600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1974, '440604', '禅城区', '440600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1975, '440605', '南海区', '440600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1976, '440606', '顺德区', '440600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1977, '440607', '三水区', '440600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1978, '440608', '高明区', '440600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1979, '440701', '市辖区', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1980, '440703', '蓬江区', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1981, '440704', '江海区', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1982, '440705', '新会区', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1983, '440781', '台山市', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1984, '440783', '开平市', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1985, '440784', '鹤山市', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1986, '440785', '恩平市', '440700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1987, '440801', '市辖区', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1988, '440802', '赤坎区', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1989, '440803', '霞山区', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1990, '440804', '坡头区', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1991, '440811', '麻章区', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1992, '440823', '遂溪县', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1993, '440825', '徐闻县', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1994, '440881', '廉江市', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1995, '440882', '雷州市', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1996, '440883', '吴川市', '440800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1997, '440901', '市辖区', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1998, '440902', '茂南区', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (1999, '440903', '茂港区', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2000, '440923', '电白县', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2001, '440981', '高州市', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2002, '440982', '化州市', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2003, '440983', '信宜市', '440900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2004, '441201', '市辖区', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2005, '441202', '端州区', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2006, '441203', '鼎湖区', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2007, '441223', '广宁县', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2008, '441224', '怀集县', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2009, '441225', '封开县', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2010, '441226', '德庆县', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2011, '441283', '高要市', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2012, '441284', '四会市', '441200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2013, '441301', '市辖区', '441300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2014, '441302', '惠城区', '441300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2015, '441303', '惠阳区', '441300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2016, '441322', '博罗县', '441300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2017, '441323', '惠东县', '441300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2018, '441324', '龙门县', '441300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2019, '441401', '市辖区', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2020, '441402', '梅江区', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2021, '441421', '梅　县', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2022, '441422', '大埔县', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2023, '441423', '丰顺县', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2024, '441424', '五华县', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2025, '441426', '平远县', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2026, '441427', '蕉岭县', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2027, '441481', '兴宁市', '441400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2028, '441501', '市辖区', '441500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2029, '441502', '城　区', '441500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2030, '441521', '海丰县', '441500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2031, '441523', '陆河县', '441500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2032, '441581', '陆丰市', '441500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2033, '441601', '市辖区', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2034, '441602', '源城区', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2035, '441621', '紫金县', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2036, '441622', '龙川县', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2037, '441623', '连平县', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2038, '441624', '和平县', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2039, '441625', '东源县', '441600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2040, '441701', '市辖区', '441700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2041, '441702', '江城区', '441700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2042, '441721', '阳西县', '441700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2043, '441723', '阳东县', '441700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2044, '441781', '阳春市', '441700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2045, '441801', '市辖区', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2046, '441802', '清城区', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2047, '441821', '佛冈县', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2048, '441823', '阳山县', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2049, '441825', '连山壮族瑶族自治县', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2050, '441826', '连南瑶族自治县', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2051, '441827', '清新县', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2052, '441881', '英德市', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2053, '441882', '连州市', '441800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2054, '445101', '市辖区', '445100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2055, '445102', '湘桥区', '445100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2056, '445121', '潮安县', '445100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2057, '445122', '饶平县', '445100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2058, '445201', '市辖区', '445200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2059, '445202', '榕城区', '445200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2060, '445221', '揭东县', '445200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2061, '445222', '揭西县', '445200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2062, '445224', '惠来县', '445200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2063, '445281', '普宁市', '445200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2064, '445301', '市辖区', '445300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2065, '445302', '云城区', '445300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2066, '445321', '新兴县', '445300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2067, '445322', '郁南县', '445300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2068, '445323', '云安县', '445300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2069, '445381', '罗定市', '445300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2070, '450101', '市辖区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2071, '450102', '兴宁区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2072, '450103', '青秀区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2073, '450105', '江南区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2074, '450107', '西乡塘区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2075, '450108', '良庆区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2076, '450109', '邕宁区', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2077, '450122', '武鸣县', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2078, '450123', '隆安县', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2079, '450124', '马山县', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2080, '450125', '上林县', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2081, '450126', '宾阳县', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2082, '450127', '横　县', '450100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2083, '450201', '市辖区', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2084, '450202', '城中区', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2085, '450203', '鱼峰区', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2086, '450204', '柳南区', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2087, '450205', '柳北区', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2088, '450221', '柳江县', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2089, '450222', '柳城县', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2090, '450223', '鹿寨县', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2091, '450224', '融安县', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2092, '450225', '融水苗族自治县', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2093, '450226', '三江侗族自治县', '450200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2094, '450301', '市辖区', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2095, '450302', '秀峰区', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2096, '450303', '叠彩区', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2097, '450304', '象山区', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2098, '450305', '七星区', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2099, '450311', '雁山区', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2100, '450321', '阳朔县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2101, '450322', '临桂县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2102, '450323', '灵川县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2103, '450324', '全州县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2104, '450325', '兴安县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2105, '450326', '永福县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2106, '450327', '灌阳县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2107, '450328', '龙胜各族自治县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2108, '450329', '资源县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2109, '450330', '平乐县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2110, '450331', '荔蒲县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2111, '450332', '恭城瑶族自治县', '450300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2112, '450401', '市辖区', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2113, '450403', '万秀区', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2114, '450404', '蝶山区', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2115, '450405', '长洲区', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2116, '450421', '苍梧县', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2117, '450422', '藤　县', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2118, '450423', '蒙山县', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2119, '450481', '岑溪市', '450400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2120, '450501', '市辖区', '450500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2121, '450502', '海城区', '450500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2122, '450503', '银海区', '450500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2123, '450512', '铁山港区', '450500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2124, '450521', '合浦县', '450500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2125, '450601', '市辖区', '450600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2126, '450602', '港口区', '450600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2127, '450603', '防城区', '450600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2128, '450621', '上思县', '450600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2129, '450681', '东兴市', '450600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2130, '450701', '市辖区', '450700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2131, '450702', '钦南区', '450700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2132, '450703', '钦北区', '450700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2133, '450721', '灵山县', '450700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2134, '450722', '浦北县', '450700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2135, '450801', '市辖区', '450800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2136, '450802', '港北区', '450800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2137, '450803', '港南区', '450800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2138, '450804', '覃塘区', '450800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2139, '450821', '平南县', '450800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2140, '450881', '桂平市', '450800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2141, '450901', '市辖区', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2142, '450902', '玉州区', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2143, '450921', '容　县', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2144, '450922', '陆川县', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2145, '450923', '博白县', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2146, '450924', '兴业县', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2147, '450981', '北流市', '450900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2148, '451001', '市辖区', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2149, '451002', '右江区', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2150, '451021', '田阳县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2151, '451022', '田东县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2152, '451023', '平果县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2153, '451024', '德保县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2154, '451025', '靖西县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2155, '451026', '那坡县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2156, '451027', '凌云县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2157, '451028', '乐业县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2158, '451029', '田林县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2159, '451030', '西林县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2160, '451031', '隆林各族自治县', '451000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2161, '451101', '市辖区', '451100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2162, '451102', '八步区', '451100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2163, '451121', '昭平县', '451100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2164, '451122', '钟山县', '451100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2165, '451123', '富川瑶族自治县', '451100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2166, '451201', '市辖区', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2167, '451202', '金城江区', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2168, '451221', '南丹县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2169, '451222', '天峨县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2170, '451223', '凤山县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2171, '451224', '东兰县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2172, '451225', '罗城仫佬族自治县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2173, '451226', '环江毛南族自治县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2174, '451227', '巴马瑶族自治县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2175, '451228', '都安瑶族自治县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2176, '451229', '大化瑶族自治县', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2177, '451281', '宜州市', '451200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2178, '451301', '市辖区', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2179, '451302', '兴宾区', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2180, '451321', '忻城县', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2181, '451322', '象州县', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2182, '451323', '武宣县', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2183, '451324', '金秀瑶族自治县', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2184, '451381', '合山市', '451300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2185, '451401', '市辖区', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2186, '451402', '江洲区', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2187, '451421', '扶绥县', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2188, '451422', '宁明县', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2189, '451423', '龙州县', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2190, '451424', '大新县', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2191, '451425', '天等县', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2192, '451481', '凭祥市', '451400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2193, '460101', '市辖区', '460100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2194, '460105', '秀英区', '460100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2195, '460106', '龙华区', '460100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2196, '460107', '琼山区', '460100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2197, '460108', '美兰区', '460100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2198, '460201', '市辖区', '460200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2199, '469001', '五指山市', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2200, '469002', '琼海市', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2201, '469003', '儋州市', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2202, '469005', '文昌市', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2203, '469006', '万宁市', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2204, '469007', '东方市', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2205, '469025', '定安县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2206, '469026', '屯昌县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2207, '469027', '澄迈县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2208, '469028', '临高县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2209, '469030', '白沙黎族自治县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2210, '469031', '昌江黎族自治县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2211, '469033', '乐东黎族自治县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2212, '469034', '陵水黎族自治县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2213, '469035', '保亭黎族苗族自治县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2214, '469036', '琼中黎族苗族自治县', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2215, '469037', '西沙群岛', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2216, '469038', '南沙群岛', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2217, '469039', '中沙群岛的岛礁及其海域', '469000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2218, '500101', '万州区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2219, '500102', '涪陵区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2220, '500103', '渝中区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2221, '500104', '大渡口区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2222, '500105', '江北区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2223, '500106', '沙坪坝区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2224, '500107', '九龙坡区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2225, '500108', '南岸区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2226, '500109', '北碚区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2227, '500110', '万盛区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2228, '500111', '双桥区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2229, '500112', '渝北区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2230, '500113', '巴南区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2231, '500114', '黔江区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2232, '500115', '长寿区', '500100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2233, '500222', '綦江县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2234, '500223', '潼南县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2235, '500224', '铜梁县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2236, '500225', '大足县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2237, '500226', '荣昌县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2238, '500227', '璧山县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2239, '500228', '梁平县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2240, '500229', '城口县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2241, '500230', '丰都县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2242, '500231', '垫江县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2243, '500232', '武隆县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2244, '500233', '忠　县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2245, '500234', '开　县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2246, '500235', '云阳县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2247, '500236', '奉节县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2248, '500237', '巫山县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2249, '500238', '巫溪县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2250, '500240', '石柱土家族自治县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2251, '500241', '秀山土家族苗族自治县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2252, '500242', '酉阳土家族苗族自治县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2253, '500243', '彭水苗族土家族自治县', '500200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2254, '500381', '江津市', '500300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2255, '500382', '合川市', '500300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2256, '500383', '永川市', '500300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2257, '500384', '南川市', '500300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2258, '510101', '市辖区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2259, '510104', '锦江区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2260, '510105', '青羊区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2261, '510106', '金牛区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2262, '510107', '武侯区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2263, '510108', '成华区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2264, '510112', '龙泉驿区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2265, '510113', '青白江区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2266, '510114', '新都区', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2267, '510115', '温江县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2268, '510121', '金堂县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2269, '510122', '双流县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2270, '510124', '郫　县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2271, '510129', '大邑县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2272, '510131', '蒲江县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2273, '510132', '新津县', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2274, '510181', '都江堰市', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2275, '510182', '彭州市', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2276, '510183', '邛崃市', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2277, '510184', '崇州市', '510100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2278, '510301', '市辖区', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2279, '510302', '自流井区', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2280, '510303', '贡井区', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2281, '510304', '大安区', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2282, '510311', '沿滩区', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2283, '510321', '荣　县', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2284, '510322', '富顺县', '510300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2285, '510401', '市辖区', '510400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2286, '510402', '东　区', '510400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2287, '510403', '西　区', '510400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2288, '510411', '仁和区', '510400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2289, '510421', '米易县', '510400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2290, '510422', '盐边县', '510400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2291, '510501', '市辖区', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2292, '510502', '江阳区', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2293, '510503', '纳溪区', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2294, '510504', '龙马潭区', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2295, '510521', '泸　县', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2296, '510522', '合江县', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2297, '510524', '叙永县', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2298, '510525', '古蔺县', '510500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2299, '510601', '市辖区', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2300, '510603', '旌阳区', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2301, '510623', '中江县', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2302, '510626', '罗江县', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2303, '510681', '广汉市', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2304, '510682', '什邡市', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2305, '510683', '绵竹市', '510600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2306, '510701', '市辖区', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2307, '510703', '涪城区', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2308, '510704', '游仙区', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2309, '510722', '三台县', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2310, '510723', '盐亭县', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2311, '510724', '安　县', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2312, '510725', '梓潼县', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2313, '510726', '北川羌族自治县', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2314, '510727', '平武县', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2315, '510781', '江油市', '510700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2316, '510801', '市辖区', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2317, '510802', '市中区', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2318, '510811', '元坝区', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2319, '510812', '朝天区', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2320, '510821', '旺苍县', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2321, '510822', '青川县', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2322, '510823', '剑阁县', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2323, '510824', '苍溪县', '510800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2324, '510901', '市辖区', '510900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2325, '510903', '船山区', '510900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2326, '510904', '安居区', '510900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2327, '510921', '蓬溪县', '510900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2328, '510922', '射洪县', '510900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2329, '510923', '大英县', '510900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2330, '511001', '市辖区', '511000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2331, '511002', '市中区', '511000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2332, '511011', '东兴区', '511000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2333, '511024', '威远县', '511000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2334, '511025', '资中县', '511000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2335, '511028', '隆昌县', '511000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2336, '511101', '市辖区', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2337, '511102', '市中区', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2338, '511111', '沙湾区', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2339, '511112', '五通桥区', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2340, '511113', '金口河区', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2341, '511123', '犍为县', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2342, '511124', '井研县', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2343, '511126', '夹江县', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2344, '511129', '沐川县', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2345, '511132', '峨边彝族自治县', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2346, '511133', '马边彝族自治县', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2347, '511181', '峨眉山市', '511100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2348, '511301', '市辖区', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2349, '511302', '顺庆区', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2350, '511303', '高坪区', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2351, '511304', '嘉陵区', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2352, '511321', '南部县', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2353, '511322', '营山县', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2354, '511323', '蓬安县', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2355, '511324', '仪陇县', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2356, '511325', '西充县', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2357, '511381', '阆中市', '511300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2358, '511401', '市辖区', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2359, '511402', '东坡区', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2360, '511421', '仁寿县', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2361, '511422', '彭山县', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2362, '511423', '洪雅县', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2363, '511424', '丹棱县', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2364, '511425', '青神县', '511400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2365, '511501', '市辖区', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2366, '511502', '翠屏区', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2367, '511521', '宜宾县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2368, '511522', '南溪县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2369, '511523', '江安县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2370, '511524', '长宁县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2371, '511525', '高　县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2372, '511526', '珙　县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2373, '511527', '筠连县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2374, '511528', '兴文县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2375, '511529', '屏山县', '511500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2376, '511601', '市辖区', '511600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2377, '511602', '广安区', '511600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2378, '511621', '岳池县', '511600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2379, '511622', '武胜县', '511600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2380, '511623', '邻水县', '511600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2381, '511681', '华莹市', '511600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2382, '511701', '市辖区', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2383, '511702', '通川区', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2384, '511721', '达　县', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2385, '511722', '宣汉县', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2386, '511723', '开江县', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2387, '511724', '大竹县', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2388, '511725', '渠　县', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2389, '511781', '万源市', '511700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2390, '511801', '市辖区', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2391, '511802', '雨城区', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2392, '511821', '名山县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2393, '511822', '荥经县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2394, '511823', '汉源县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2395, '511824', '石棉县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2396, '511825', '天全县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2397, '511826', '芦山县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2398, '511827', '宝兴县', '511800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2399, '511901', '市辖区', '511900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2400, '511902', '巴州区', '511900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2401, '511921', '通江县', '511900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2402, '511922', '南江县', '511900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2403, '511923', '平昌县', '511900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2404, '512001', '市辖区', '512000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2405, '512002', '雁江区', '512000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2406, '512021', '安岳县', '512000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2407, '512022', '乐至县', '512000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2408, '512081', '简阳市', '512000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2409, '513221', '汶川县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2410, '513222', '理　县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2411, '513223', '茂　县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2412, '513224', '松潘县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2413, '513225', '九寨沟县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2414, '513226', '金川县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2415, '513227', '小金县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2416, '513228', '黑水县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2417, '513229', '马尔康县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2418, '513230', '壤塘县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2419, '513231', '阿坝县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2420, '513232', '若尔盖县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2421, '513233', '红原县', '513200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2422, '513321', '康定县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2423, '513322', '泸定县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2424, '513323', '丹巴县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2425, '513324', '九龙县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2426, '513325', '雅江县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2427, '513326', '道孚县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2428, '513327', '炉霍县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2429, '513328', '甘孜县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2430, '513329', '新龙县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2431, '513330', '德格县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2432, '513331', '白玉县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2433, '513332', '石渠县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2434, '513333', '色达县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2435, '513334', '理塘县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2436, '513335', '巴塘县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2437, '513336', '乡城县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2438, '513337', '稻城县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2439, '513338', '得荣县', '513300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2440, '513401', '西昌市', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2441, '513422', '木里藏族自治县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2442, '513423', '盐源县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2443, '513424', '德昌县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2444, '513425', '会理县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2445, '513426', '会东县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2446, '513427', '宁南县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2447, '513428', '普格县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2448, '513429', '布拖县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2449, '513430', '金阳县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2450, '513431', '昭觉县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2451, '513432', '喜德县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2452, '513433', '冕宁县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2453, '513434', '越西县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2454, '513435', '甘洛县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2455, '513436', '美姑县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2456, '513437', '雷波县', '513400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2457, '520101', '市辖区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2458, '520102', '南明区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2459, '520103', '云岩区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2460, '520111', '花溪区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2461, '520112', '乌当区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2462, '520113', '白云区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2463, '520114', '小河区', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2464, '520121', '开阳县', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2465, '520122', '息烽县', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2466, '520123', '修文县', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2467, '520181', '清镇市', '520100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2468, '520201', '钟山区', '520200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2469, '520203', '六枝特区', '520200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2470, '520221', '水城县', '520200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2471, '520222', '盘　县', '520200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2472, '520301', '市辖区', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2473, '520302', '红花岗区', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2474, '520303', '汇川区', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2475, '520321', '遵义县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2476, '520322', '桐梓县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2477, '520323', '绥阳县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2478, '520324', '正安县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2479, '520325', '道真仡佬族苗族自治县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2480, '520326', '务川仡佬族苗族自治县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2481, '520327', '凤冈县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2482, '520328', '湄潭县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2483, '520329', '余庆县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2484, '520330', '习水县', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2485, '520381', '赤水市', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2486, '520382', '仁怀市', '520300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2487, '520401', '市辖区', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2488, '520402', '西秀区', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2489, '520421', '平坝县', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2490, '520422', '普定县', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2491, '520423', '镇宁布依族苗族自治县', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2492, '520424', '关岭布依族苗族自治县', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2493, '520425', '紫云苗族布依族自治县', '520400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2494, '522201', '铜仁市', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2495, '522222', '江口县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2496, '522223', '玉屏侗族自治县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2497, '522224', '石阡县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2498, '522225', '思南县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2499, '522226', '印江土家族苗族自治县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2500, '522227', '德江县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2501, '522228', '沿河土家族自治县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2502, '522229', '松桃苗族自治县', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2503, '522230', '万山特区', '522200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2504, '522301', '兴义市', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2505, '522322', '兴仁县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2506, '522323', '普安县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2507, '522324', '晴隆县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2508, '522325', '贞丰县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2509, '522326', '望谟县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2510, '522327', '册亨县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2511, '522328', '安龙县', '522300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2512, '522401', '毕节市', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2513, '522422', '大方县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2514, '522423', '黔西县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2515, '522424', '金沙县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2516, '522425', '织金县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2517, '522426', '纳雍县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2518, '522427', '威宁彝族回族苗族自治县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2519, '522428', '赫章县', '522400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2520, '522601', '凯里市', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2521, '522622', '黄平县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2522, '522623', '施秉县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2523, '522624', '三穗县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2524, '522625', '镇远县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2525, '522626', '岑巩县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2526, '522627', '天柱县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2527, '522628', '锦屏县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2528, '522629', '剑河县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2529, '522630', '台江县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2530, '522631', '黎平县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2531, '522632', '榕江县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2532, '522633', '从江县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2533, '522634', '雷山县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2534, '522635', '麻江县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2535, '522636', '丹寨县', '522600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2536, '522701', '都匀市', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2537, '522702', '福泉市', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2538, '522722', '荔波县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2539, '522723', '贵定县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2540, '522725', '瓮安县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2541, '522726', '独山县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2542, '522727', '平塘县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2543, '522728', '罗甸县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2544, '522729', '长顺县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2545, '522730', '龙里县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2546, '522731', '惠水县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2547, '522732', '三都水族自治县', '522700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2548, '530101', '市辖区', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2549, '530102', '五华区', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2550, '530103', '盘龙区', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2551, '530111', '官渡区', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2552, '530112', '西山区', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2553, '530113', '东川区', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2554, '530121', '呈贡县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2555, '530122', '晋宁县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2556, '530124', '富民县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2557, '530125', '宜良县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2558, '530126', '石林彝族自治县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2559, '530127', '嵩明县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2560, '530128', '禄劝彝族苗族自治县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2561, '530129', '寻甸回族彝族自治县', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2562, '530181', '安宁市', '530100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2563, '530301', '市辖区', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2564, '530302', '麒麟区', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2565, '530321', '马龙县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2566, '530322', '陆良县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2567, '530323', '师宗县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2568, '530324', '罗平县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2569, '530325', '富源县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2570, '530326', '会泽县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2571, '530328', '沾益县', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2572, '530381', '宣威市', '530300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2573, '530401', '市辖区', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2574, '530402', '红塔区', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2575, '530421', '江川县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2576, '530422', '澄江县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2577, '530423', '通海县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2578, '530424', '华宁县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2579, '530425', '易门县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2580, '530426', '峨山彝族自治县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2581, '530427', '新平彝族傣族自治县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2582, '530428', '元江哈尼族彝族傣族自治县', '530400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2583, '530501', '市辖区', '530500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2584, '530502', '隆阳区', '530500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2585, '530521', '施甸县', '530500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2586, '530522', '腾冲县', '530500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2587, '530523', '龙陵县', '530500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2588, '530524', '昌宁县', '530500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2589, '530601', '市辖区', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2590, '530602', '昭阳区', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2591, '530621', '鲁甸县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2592, '530622', '巧家县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2593, '530623', '盐津县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2594, '530624', '大关县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2595, '530625', '永善县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2596, '530626', '绥江县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2597, '530627', '镇雄县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2598, '530628', '彝良县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2599, '530629', '威信县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2600, '530630', '水富县', '530600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2601, '530701', '市辖区', '530700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2602, '530702', '古城区', '530700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2603, '530721', '玉龙纳西族自治县', '530700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2604, '530722', '永胜县', '530700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2605, '530723', '华坪县', '530700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2606, '530724', '宁蒗彝族自治县', '530700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2607, '530801', '市辖区', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2608, '530802', '翠云区', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2609, '530821', '普洱哈尼族彝族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2610, '530822', '墨江哈尼族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2611, '530823', '景东彝族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2612, '530824', '景谷傣族彝族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2613, '530825', '镇沅彝族哈尼族拉祜族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2614, '530826', '江城哈尼族彝族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2615, '530827', '孟连傣族拉祜族佤族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2616, '530828', '澜沧拉祜族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2617, '530829', '西盟佤族自治县', '530800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2618, '530901', '市辖区', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2619, '530902', '临翔区', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2620, '530921', '凤庆县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2621, '530922', '云　县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2622, '530923', '永德县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2623, '530924', '镇康县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2624, '530925', '双江拉祜族佤族布朗族傣族自治县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2625, '530926', '耿马傣族佤族自治县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2626, '530927', '沧源佤族自治县', '530900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2627, '532301', '楚雄市', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2628, '532322', '双柏县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2629, '532323', '牟定县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2630, '532324', '南华县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2631, '532325', '姚安县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2632, '532326', '大姚县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2633, '532327', '永仁县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2634, '532328', '元谋县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2635, '532329', '武定县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2636, '532331', '禄丰县', '532300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2637, '532501', '个旧市', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2638, '532502', '开远市', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2639, '532522', '蒙自县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2640, '532523', '屏边苗族自治县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2641, '532524', '建水县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2642, '532525', '石屏县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2643, '532526', '弥勒县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2644, '532527', '泸西县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2645, '532528', '元阳县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2646, '532529', '红河县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2647, '532530', '金平苗族瑶族傣族自治县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2648, '532531', '绿春县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2649, '532532', '河口瑶族自治县', '532500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2650, '532621', '文山县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2651, '532622', '砚山县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2652, '532623', '西畴县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2653, '532624', '麻栗坡县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2654, '532625', '马关县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2655, '532626', '丘北县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2656, '532627', '广南县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2657, '532628', '富宁县', '532600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2658, '532801', '景洪市', '532800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2659, '532822', '勐海县', '532800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2660, '532823', '勐腊县', '532800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2661, '532901', '大理市', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2662, '532922', '漾濞彝族自治县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2663, '532923', '祥云县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2664, '532924', '宾川县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2665, '532925', '弥渡县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2666, '532926', '南涧彝族自治县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2667, '532927', '巍山彝族回族自治县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2668, '532928', '永平县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2669, '532929', '云龙县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2670, '532930', '洱源县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2671, '532931', '剑川县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2672, '532932', '鹤庆县', '532900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2673, '533102', '瑞丽市', '533100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2674, '533103', '潞西市', '533100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2675, '533122', '梁河县', '533100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2676, '533123', '盈江县', '533100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2677, '533124', '陇川县', '533100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2678, '533321', '泸水县', '533300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2679, '533323', '福贡县', '533300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2680, '533324', '贡山独龙族怒族自治县', '533300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2681, '533325', '兰坪白族普米族自治县', '533300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2682, '533421', '香格里拉县', '533400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2683, '533422', '德钦县', '533400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2684, '533423', '维西傈僳族自治县', '533400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2685, '540101', '市辖区', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2686, '540102', '城关区', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2687, '540121', '林周县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2688, '540122', '当雄县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2689, '540123', '尼木县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2690, '540124', '曲水县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2691, '540125', '堆龙德庆县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2692, '540126', '达孜县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2693, '540127', '墨竹工卡县', '540100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2694, '542121', '昌都县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2695, '542122', '江达县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2696, '542123', '贡觉县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2697, '542124', '类乌齐县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2698, '542125', '丁青县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2699, '542126', '察雅县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2700, '542127', '八宿县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2701, '542128', '左贡县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2702, '542129', '芒康县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2703, '542132', '洛隆县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2704, '542133', '边坝县', '542100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2705, '542221', '乃东县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2706, '542222', '扎囊县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2707, '542223', '贡嘎县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2708, '542224', '桑日县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2709, '542225', '琼结县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2710, '542226', '曲松县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2711, '542227', '措美县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2712, '542228', '洛扎县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2713, '542229', '加查县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2714, '542231', '隆子县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2715, '542232', '错那县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2716, '542233', '浪卡子县', '542200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2717, '542301', '日喀则市', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2718, '542322', '南木林县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2719, '542323', '江孜县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2720, '542324', '定日县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2721, '542325', '萨迦县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2722, '542326', '拉孜县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2723, '542327', '昂仁县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2724, '542328', '谢通门县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2725, '542329', '白朗县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2726, '542330', '仁布县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2727, '542331', '康马县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2728, '542332', '定结县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2729, '542333', '仲巴县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2730, '542334', '亚东县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2731, '542335', '吉隆县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2732, '542336', '聂拉木县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2733, '542337', '萨嘎县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2734, '542338', '岗巴县', '542300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2735, '542421', '那曲县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2736, '542422', '嘉黎县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2737, '542423', '比如县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2738, '542424', '聂荣县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2739, '542425', '安多县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2740, '542426', '申扎县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2741, '542427', '索　县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2742, '542428', '班戈县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2743, '542429', '巴青县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2744, '542430', '尼玛县', '542400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2745, '542521', '普兰县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2746, '542522', '札达县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2747, '542523', '噶尔县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2748, '542524', '日土县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2749, '542525', '革吉县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2750, '542526', '改则县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2751, '542527', '措勤县', '542500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2752, '542621', '林芝县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2753, '542622', '工布江达县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2754, '542623', '米林县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2755, '542624', '墨脱县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2756, '542625', '波密县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2757, '542626', '察隅县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2758, '542627', '朗　县', '542600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2759, '610101', '市辖区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2760, '610102', '新城区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2761, '610103', '碑林区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2762, '610104', '莲湖区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2763, '610111', '灞桥区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2764, '610112', '未央区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2765, '610113', '雁塔区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2766, '610114', '阎良区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2767, '610115', '临潼区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2768, '610116', '长安区', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2769, '610122', '蓝田县', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2770, '610124', '周至县', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2771, '610125', '户　县', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2772, '610126', '高陵县', '610100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2773, '610201', '市辖区', '610200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2774, '610202', '王益区', '610200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2775, '610203', '印台区', '610200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2776, '610204', '耀州区', '610200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2777, '610222', '宜君县', '610200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2778, '610301', '市辖区', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2779, '610302', '渭滨区', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2780, '610303', '金台区', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2781, '610304', '陈仓区', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2782, '610322', '凤翔县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2783, '610323', '岐山县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2784, '610324', '扶风县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2785, '610326', '眉　县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2786, '610327', '陇　县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2787, '610328', '千阳县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2788, '610329', '麟游县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2789, '610330', '凤　县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2790, '610331', '太白县', '610300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2791, '610401', '市辖区', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2792, '610402', '秦都区', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2793, '610403', '杨凌区', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2794, '610404', '渭城区', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2795, '610422', '三原县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2796, '610423', '泾阳县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2797, '610424', '乾　县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2798, '610425', '礼泉县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2799, '610426', '永寿县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2800, '610427', '彬　县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2801, '610428', '长武县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2802, '610429', '旬邑县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2803, '610430', '淳化县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2804, '610431', '武功县', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2805, '610481', '兴平市', '610400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2806, '610501', '市辖区', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2807, '610502', '临渭区', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2808, '610521', '华　县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2809, '610522', '潼关县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2810, '610523', '大荔县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2811, '610524', '合阳县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2812, '610525', '澄城县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2813, '610526', '蒲城县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2814, '610527', '白水县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2815, '610528', '富平县', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2816, '610581', '韩城市', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2817, '610582', '华阴市', '610500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2818, '610601', '市辖区', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2819, '610602', '宝塔区', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2820, '610621', '延长县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2821, '610622', '延川县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2822, '610623', '子长县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2823, '610624', '安塞县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2824, '610625', '志丹县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2825, '610626', '吴旗县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2826, '610627', '甘泉县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2827, '610628', '富　县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2828, '610629', '洛川县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2829, '610630', '宜川县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2830, '610631', '黄龙县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2831, '610632', '黄陵县', '610600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2832, '610701', '市辖区', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2833, '610702', '汉台区', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2834, '610721', '南郑县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2835, '610722', '城固县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2836, '610723', '洋　县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2837, '610724', '西乡县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2838, '610725', '勉　县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2839, '610726', '宁强县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2840, '610727', '略阳县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2841, '610728', '镇巴县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2842, '610729', '留坝县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2843, '610730', '佛坪县', '610700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2844, '610801', '市辖区', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2845, '610802', '榆阳区', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2846, '610821', '神木县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2847, '610822', '府谷县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2848, '610823', '横山县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2849, '610824', '靖边县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2850, '610825', '定边县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2851, '610826', '绥德县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2852, '610827', '米脂县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2853, '610828', '佳　县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2854, '610829', '吴堡县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2855, '610830', '清涧县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2856, '610831', '子洲县', '610800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2857, '610901', '市辖区', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2858, '610902', '汉滨区', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2859, '610921', '汉阴县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2860, '610922', '石泉县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2861, '610923', '宁陕县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2862, '610924', '紫阳县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2863, '610925', '岚皋县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2864, '610926', '平利县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2865, '610927', '镇坪县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2866, '610928', '旬阳县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2867, '610929', '白河县', '610900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2868, '611001', '市辖区', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2869, '611002', '商州区', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2870, '611021', '洛南县', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2871, '611022', '丹凤县', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2872, '611023', '商南县', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2873, '611024', '山阳县', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2874, '611025', '镇安县', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2875, '611026', '柞水县', '611000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2876, '620101', '市辖区', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2877, '620102', '城关区', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2878, '620103', '七里河区', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2879, '620104', '西固区', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2880, '620105', '安宁区', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2881, '620111', '红古区', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2882, '620121', '永登县', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2883, '620122', '皋兰县', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2884, '620123', '榆中县', '620100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2885, '620201', '市辖区', '620200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2886, '620301', '市辖区', '620300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2887, '620302', '金川区', '620300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2888, '620321', '永昌县', '620300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2889, '620401', '市辖区', '620400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2890, '620402', '白银区', '620400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2891, '620403', '平川区', '620400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2892, '620421', '靖远县', '620400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2893, '620422', '会宁县', '620400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2894, '620423', '景泰县', '620400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2895, '620501', '市辖区', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2896, '620502', '秦城区', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2897, '620503', '北道区', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2898, '620521', '清水县', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2899, '620522', '秦安县', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2900, '620523', '甘谷县', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2901, '620524', '武山县', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2902, '620525', '张家川回族自治县', '620500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2903, '620601', '市辖区', '620600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2904, '620602', '凉州区', '620600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2905, '620621', '民勤县', '620600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2906, '620622', '古浪县', '620600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2907, '620623', '天祝藏族自治县', '620600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2908, '620701', '市辖区', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2909, '620702', '甘州区', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2910, '620721', '肃南裕固族自治县', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2911, '620722', '民乐县', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2912, '620723', '临泽县', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2913, '620724', '高台县', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2914, '620725', '山丹县', '620700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2915, '620801', '市辖区', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2916, '620802', '崆峒区', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2917, '620821', '泾川县', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2918, '620822', '灵台县', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2919, '620823', '崇信县', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2920, '620824', '华亭县', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2921, '620825', '庄浪县', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2922, '620826', '静宁县', '620800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2923, '620901', '市辖区', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2924, '620902', '肃州区', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2925, '620921', '金塔县', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2926, '620922', '安西县', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2927, '620923', '肃北蒙古族自治县', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2928, '620924', '阿克塞哈萨克族自治县', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2929, '620981', '玉门市', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2930, '620982', '敦煌市', '620900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2931, '621001', '市辖区', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2932, '621002', '西峰区', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2933, '621021', '庆城县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2934, '621022', '环　县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2935, '621023', '华池县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2936, '621024', '合水县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2937, '621025', '正宁县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2938, '621026', '宁　县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2939, '621027', '镇原县', '621000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2940, '621101', '市辖区', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2941, '621102', '安定区', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2942, '621121', '通渭县', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2943, '621122', '陇西县', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2944, '621123', '渭源县', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2945, '621124', '临洮县', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2946, '621125', '漳　县', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2947, '621126', '岷　县', '621100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2948, '621201', '市辖区', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2949, '621202', '武都区', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2950, '621221', '成　县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2951, '621222', '文　县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2952, '621223', '宕昌县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2953, '621224', '康　县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2954, '621225', '西和县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2955, '621226', '礼　县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2956, '621227', '徽　县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2957, '621228', '两当县', '621200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2958, '622901', '临夏市', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2959, '622921', '临夏县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2960, '622922', '康乐县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2961, '622923', '永靖县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2962, '622924', '广河县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2963, '622925', '和政县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2964, '622926', '东乡族自治县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2965, '622927', '积石山保安族东乡族撒拉族自治县', '622900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2966, '623001', '合作市', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2967, '623021', '临潭县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2968, '623022', '卓尼县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2969, '623023', '舟曲县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2970, '623024', '迭部县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2971, '623025', '玛曲县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2972, '623026', '碌曲县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2973, '623027', '夏河县', '623000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2974, '630101', '市辖区', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2975, '630102', '城东区', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2976, '630103', '城中区', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2977, '630104', '城西区', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2978, '630105', '城北区', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2979, '630121', '大通回族土族自治县', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2980, '630122', '湟中县', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2981, '630123', '湟源县', '630100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2982, '632121', '平安县', '632100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2983, '632122', '民和回族土族自治县', '632100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2984, '632123', '乐都县', '632100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2985, '632126', '互助土族自治县', '632100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2986, '632127', '化隆回族自治县', '632100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2987, '632128', '循化撒拉族自治县', '632100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2988, '632221', '门源回族自治县', '632200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2989, '632222', '祁连县', '632200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2990, '632223', '海晏县', '632200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2991, '632224', '刚察县', '632200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2992, '632321', '同仁县', '632300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2993, '632322', '尖扎县', '632300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2994, '632323', '泽库县', '632300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2995, '632324', '河南蒙古族自治县', '632300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2996, '632521', '共和县', '632500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2997, '632522', '同德县', '632500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2998, '632523', '贵德县', '632500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (2999, '632524', '兴海县', '632500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3000, '632525', '贵南县', '632500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3001, '632621', '玛沁县', '632600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3002, '632622', '班玛县', '632600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3003, '632623', '甘德县', '632600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3004, '632624', '达日县', '632600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3005, '632625', '久治县', '632600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3006, '632626', '玛多县', '632600');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3007, '632721', '玉树县', '632700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3008, '632722', '杂多县', '632700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3009, '632723', '称多县', '632700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3010, '632724', '治多县', '632700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3011, '632725', '囊谦县', '632700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3012, '632726', '曲麻莱县', '632700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3013, '632801', '格尔木市', '632800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3014, '632802', '德令哈市', '632800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3015, '632821', '乌兰县', '632800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3016, '632822', '都兰县', '632800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3017, '632823', '天峻县', '632800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3018, '640101', '市辖区', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3019, '640104', '兴庆区', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3020, '640105', '西夏区', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3021, '640106', '金凤区', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3022, '640121', '永宁县', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3023, '640122', '贺兰县', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3024, '640181', '灵武市', '640100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3025, '640201', '市辖区', '640200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3026, '640202', '大武口区', '640200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3027, '640205', '惠农区', '640200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3028, '640221', '平罗县', '640200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3029, '640301', '市辖区', '640300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3030, '640302', '利通区', '640300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3031, '640323', '盐池县', '640300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3032, '640324', '同心县', '640300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3033, '640381', '青铜峡市', '640300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3034, '640401', '市辖区', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3035, '640402', '原州区', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3036, '640422', '西吉县', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3037, '640423', '隆德县', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3038, '640424', '泾源县', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3039, '640425', '彭阳县', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3040, '640501', '市辖区', '640500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3041, '640502', '沙坡头区', '640500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3042, '640521', '中宁县', '640500');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3043, '640522', '海原县', '640400');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3044, '650101', '市辖区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3045, '650102', '天山区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3046, '650103', '沙依巴克区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3047, '650104', '新市区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3048, '650105', '水磨沟区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3049, '650106', '头屯河区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3050, '650107', '达坂城区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3051, '650108', '东山区', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3052, '650121', '乌鲁木齐县', '650100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3053, '650201', '市辖区', '650200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3054, '650202', '独山子区', '650200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3055, '650203', '克拉玛依区', '650200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3056, '650204', '白碱滩区', '650200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3057, '650205', '乌尔禾区', '650200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3058, '652101', '吐鲁番市', '652100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3059, '652122', '鄯善县', '652100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3060, '652123', '托克逊县', '652100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3061, '652201', '哈密市', '652200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3062, '652222', '巴里坤哈萨克自治县', '652200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3063, '652223', '伊吾县', '652200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3064, '652301', '昌吉市', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3065, '652302', '阜康市', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3066, '652303', '米泉市', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3067, '652323', '呼图壁县', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3068, '652324', '玛纳斯县', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3069, '652325', '奇台县', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3070, '652327', '吉木萨尔县', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3071, '652328', '木垒哈萨克自治县', '652300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3072, '652701', '博乐市', '652700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3073, '652722', '精河县', '652700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3074, '652723', '温泉县', '652700');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3075, '652801', '库尔勒市', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3076, '652822', '轮台县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3077, '652823', '尉犁县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3078, '652824', '若羌县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3079, '652825', '且末县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3080, '652826', '焉耆回族自治县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3081, '652827', '和静县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3082, '652828', '和硕县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3083, '652829', '博湖县', '652800');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3084, '652901', '阿克苏市', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3085, '652922', '温宿县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3086, '652923', '库车县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3087, '652924', '沙雅县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3088, '652925', '新和县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3089, '652926', '拜城县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3090, '652927', '乌什县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3091, '652928', '阿瓦提县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3092, '652929', '柯坪县', '652900');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3093, '653001', '阿图什市', '653000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3094, '653022', '阿克陶县', '653000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3095, '653023', '阿合奇县', '653000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3096, '653024', '乌恰县', '653000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3097, '653101', '喀什市', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3098, '653121', '疏附县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3099, '653122', '疏勒县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3100, '653123', '英吉沙县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3101, '653124', '泽普县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3102, '653125', '莎车县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3103, '653126', '叶城县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3104, '653127', '麦盖提县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3105, '653128', '岳普湖县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3106, '653129', '伽师县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3107, '653130', '巴楚县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3108, '653131', '塔什库尔干塔吉克自治县', '653100');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3109, '653201', '和田市', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3110, '653221', '和田县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3111, '653222', '墨玉县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3112, '653223', '皮山县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3113, '653224', '洛浦县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3114, '653225', '策勒县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3115, '653226', '于田县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3116, '653227', '民丰县', '653200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3117, '654002', '伊宁市', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3118, '654003', '奎屯市', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3119, '654021', '伊宁县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3120, '654022', '察布查尔锡伯自治县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3121, '654023', '霍城县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3122, '654024', '巩留县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3123, '654025', '新源县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3124, '654026', '昭苏县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3125, '654027', '特克斯县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3126, '654028', '尼勒克县', '654000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3127, '654201', '塔城市', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3128, '654202', '乌苏市', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3129, '654221', '额敏县', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3130, '654223', '沙湾县', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3131, '654224', '托里县', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3132, '654225', '裕民县', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3133, '654226', '和布克赛尔蒙古自治县', '654200');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3134, '654301', '阿勒泰市', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3135, '654321', '布尔津县', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3136, '654322', '富蕴县', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3137, '654323', '福海县', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3138, '654324', '哈巴河县', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3139, '654325', '青河县', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3140, '654326', '吉木乃县', '654300');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3141, '659001', '石河子市', '659000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3142, '659002', '阿拉尔市', '659000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3143, '659003', '图木舒克市', '659000');
INSERT INTO `sb_base_areas` (`id`, `areaid`, `area`, `cityid`) VALUES (3144, '659004', '五家渠市', '659000');
COMMIT;

-- ----------------------------
-- Table structure for sb_base_city
-- ----------------------------
DROP TABLE IF EXISTS `sb_base_city`;
CREATE TABLE `sb_base_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cityid` varchar(20) NOT NULL,
  `city` varchar(50) NOT NULL,
  `provinceid` varchar(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=346 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='行政区域地州市信息表';

-- ----------------------------
-- Records of sb_base_city
-- ----------------------------
BEGIN;
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (1, '110100', '市辖区', '110000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (2, '110200', '县', '110000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (3, '120100', '市辖区', '120000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (4, '120200', '县', '120000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (5, '130100', '石家庄市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (6, '130200', '唐山市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (7, '130300', '秦皇岛市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (8, '130400', '邯郸市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (9, '130500', '邢台市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (10, '130600', '保定市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (11, '130700', '张家口市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (12, '130800', '承德市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (13, '130900', '沧州市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (14, '131000', '廊坊市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (15, '131100', '衡水市', '130000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (16, '140100', '太原市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (17, '140200', '大同市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (18, '140300', '阳泉市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (19, '140400', '长治市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (20, '140500', '晋城市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (21, '140600', '朔州市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (22, '140700', '晋中市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (23, '140800', '运城市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (24, '140900', '忻州市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (25, '141000', '临汾市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (26, '141100', '吕梁市', '140000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (27, '150100', '呼和浩特市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (28, '150200', '包头市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (29, '150300', '乌海市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (30, '150400', '赤峰市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (31, '150500', '通辽市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (32, '150600', '鄂尔多斯市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (33, '150700', '呼伦贝尔市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (34, '150800', '巴彦淖尔市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (35, '150900', '乌兰察布市', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (36, '152200', '兴安盟', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (37, '152500', '锡林郭勒盟', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (38, '152900', '阿拉善盟', '150000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (39, '210100', '沈阳市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (40, '210200', '大连市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (41, '210300', '鞍山市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (42, '210400', '抚顺市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (43, '210500', '本溪市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (44, '210600', '丹东市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (45, '210700', '锦州市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (46, '210800', '营口市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (47, '210900', '阜新市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (48, '211000', '辽阳市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (49, '211100', '盘锦市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (50, '211200', '铁岭市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (51, '211300', '朝阳市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (52, '211400', '葫芦岛市', '210000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (53, '220100', '长春市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (54, '220200', '吉林市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (55, '220300', '四平市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (56, '220400', '辽源市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (57, '220500', '通化市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (58, '220600', '白山市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (59, '220700', '松原市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (60, '220800', '白城市', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (61, '222400', '延边朝鲜族自治州', '220000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (62, '230100', '哈尔滨市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (63, '230200', '齐齐哈尔市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (64, '230300', '鸡西市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (65, '230400', '鹤岗市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (66, '230500', '双鸭山市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (67, '230600', '大庆市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (68, '230700', '伊春市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (69, '230800', '佳木斯市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (70, '230900', '七台河市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (71, '231000', '牡丹江市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (72, '231100', '黑河市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (73, '231200', '绥化市', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (74, '232700', '大兴安岭地区', '230000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (75, '310100', '市辖区', '310000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (76, '310200', '县', '310000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (77, '320100', '南京市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (78, '320200', '无锡市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (79, '320300', '徐州市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (80, '320400', '常州市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (81, '320500', '苏州市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (82, '320600', '南通市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (83, '320700', '连云港市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (84, '320800', '淮安市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (85, '320900', '盐城市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (86, '321000', '扬州市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (87, '321100', '镇江市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (88, '321200', '泰州市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (89, '321300', '宿迁市', '320000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (90, '330100', '杭州市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (91, '330200', '宁波市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (92, '330300', '温州市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (93, '330400', '嘉兴市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (94, '330500', '湖州市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (95, '330600', '绍兴市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (96, '330700', '金华市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (97, '330800', '衢州市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (98, '330900', '舟山市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (99, '331000', '台州市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (100, '331100', '丽水市', '330000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (101, '340100', '合肥市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (102, '340200', '芜湖市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (103, '340300', '蚌埠市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (104, '340400', '淮南市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (105, '340500', '马鞍山市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (106, '340600', '淮北市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (107, '340700', '铜陵市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (108, '340800', '安庆市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (109, '341000', '黄山市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (110, '341100', '滁州市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (111, '341200', '阜阳市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (112, '341300', '宿州市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (113, '341400', '巢湖市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (114, '341500', '六安市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (115, '341600', '亳州市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (116, '341700', '池州市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (117, '341800', '宣城市', '340000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (118, '350100', '福州市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (119, '350200', '厦门市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (120, '350300', '莆田市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (121, '350400', '三明市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (122, '350500', '泉州市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (123, '350600', '漳州市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (124, '350700', '南平市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (125, '350800', '龙岩市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (126, '350900', '宁德市', '350000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (127, '360100', '南昌市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (128, '360200', '景德镇市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (129, '360300', '萍乡市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (130, '360400', '九江市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (131, '360500', '新余市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (132, '360600', '鹰潭市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (133, '360700', '赣州市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (134, '360800', '吉安市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (135, '360900', '宜春市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (136, '361000', '抚州市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (137, '361100', '上饶市', '360000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (138, '370100', '济南市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (139, '370200', '青岛市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (140, '370300', '淄博市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (141, '370400', '枣庄市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (142, '370500', '东营市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (143, '370600', '烟台市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (144, '370700', '潍坊市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (145, '370800', '济宁市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (146, '370900', '泰安市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (147, '371000', '威海市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (148, '371100', '日照市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (149, '371200', '莱芜市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (150, '371300', '临沂市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (151, '371400', '德州市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (152, '371500', '聊城市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (153, '371600', '滨州市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (154, '371700', '荷泽市', '370000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (155, '410100', '郑州市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (156, '410200', '开封市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (157, '410300', '洛阳市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (158, '410400', '平顶山市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (159, '410500', '安阳市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (160, '410600', '鹤壁市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (161, '410700', '新乡市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (162, '410800', '焦作市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (163, '410900', '濮阳市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (164, '411000', '许昌市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (165, '411100', '漯河市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (166, '411200', '三门峡市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (167, '411300', '南阳市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (168, '411400', '商丘市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (169, '411500', '信阳市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (170, '411600', '周口市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (171, '411700', '驻马店市', '410000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (172, '420100', '武汉市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (173, '420200', '黄石市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (174, '420300', '十堰市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (175, '420500', '宜昌市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (176, '420600', '襄樊市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (177, '420700', '鄂州市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (178, '420800', '荆门市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (179, '420900', '孝感市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (180, '421000', '荆州市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (181, '421100', '黄冈市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (182, '421200', '咸宁市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (183, '421300', '随州市', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (184, '422800', '恩施土家族苗族自治州', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (185, '429000', '省直辖行政单位', '420000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (186, '430100', '长沙市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (187, '430200', '株洲市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (188, '430300', '湘潭市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (189, '430400', '衡阳市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (190, '430500', '邵阳市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (191, '430600', '岳阳市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (192, '430700', '常德市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (193, '430800', '张家界市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (194, '430900', '益阳市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (195, '431000', '郴州市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (196, '431100', '永州市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (197, '431200', '怀化市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (198, '431300', '娄底市', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (199, '433100', '湘西土家族苗族自治州', '430000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (200, '440100', '广州市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (201, '440200', '韶关市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (202, '440300', '深圳市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (203, '440400', '珠海市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (204, '440500', '汕头市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (205, '440600', '佛山市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (206, '440700', '江门市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (207, '440800', '湛江市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (208, '440900', '茂名市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (209, '441200', '肇庆市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (210, '441300', '惠州市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (211, '441400', '梅州市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (212, '441500', '汕尾市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (213, '441600', '河源市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (214, '441700', '阳江市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (215, '441800', '清远市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (216, '441900', '东莞市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (217, '442000', '中山市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (218, '445100', '潮州市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (219, '445200', '揭阳市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (220, '445300', '云浮市', '440000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (221, '450100', '南宁市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (222, '450200', '柳州市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (223, '450300', '桂林市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (224, '450400', '梧州市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (225, '450500', '北海市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (226, '450600', '防城港市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (227, '450700', '钦州市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (228, '450800', '贵港市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (229, '450900', '玉林市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (230, '451000', '百色市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (231, '451100', '贺州市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (232, '451200', '河池市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (233, '451300', '来宾市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (234, '451400', '崇左市', '450000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (235, '460100', '海口市', '460000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (236, '460200', '三亚市', '460000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (237, '469000', '省直辖县级行政单位', '460000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (238, '500100', '市辖区', '500000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (239, '500200', '县', '500000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (240, '500300', '市', '500000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (241, '510100', '成都市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (242, '510300', '自贡市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (243, '510400', '攀枝花市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (244, '510500', '泸州市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (245, '510600', '德阳市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (246, '510700', '绵阳市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (247, '510800', '广元市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (248, '510900', '遂宁市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (249, '511000', '内江市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (250, '511100', '乐山市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (251, '511300', '南充市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (252, '511400', '眉山市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (253, '511500', '宜宾市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (254, '511600', '广安市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (255, '511700', '达州市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (256, '511800', '雅安市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (257, '511900', '巴中市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (258, '512000', '资阳市', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (259, '513200', '阿坝藏族羌族自治州', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (260, '513300', '甘孜藏族自治州', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (261, '513400', '凉山彝族自治州', '510000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (262, '520100', '贵阳市', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (263, '520200', '六盘水市', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (264, '520300', '遵义市', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (265, '520400', '安顺市', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (266, '522200', '铜仁地区', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (267, '522300', '黔西南布依族苗族自治州', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (268, '522400', '毕节地区', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (269, '522600', '黔东南苗族侗族自治州', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (270, '522700', '黔南布依族苗族自治州', '520000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (271, '530100', '昆明市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (272, '530300', '曲靖市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (273, '530400', '玉溪市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (274, '530500', '保山市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (275, '530600', '昭通市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (276, '530700', '丽江市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (277, '530800', '思茅市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (278, '530900', '临沧市', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (279, '532300', '楚雄彝族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (280, '532500', '红河哈尼族彝族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (281, '532600', '文山壮族苗族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (282, '532800', '西双版纳傣族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (283, '532900', '大理白族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (284, '533100', '德宏傣族景颇族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (285, '533300', '怒江傈僳族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (286, '533400', '迪庆藏族自治州', '530000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (287, '540100', '拉萨市', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (288, '542100', '昌都地区', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (289, '542200', '山南地区', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (290, '542300', '日喀则地区', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (291, '542400', '那曲地区', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (292, '542500', '阿里地区', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (293, '542600', '林芝地区', '540000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (294, '610100', '西安市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (295, '610200', '铜川市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (296, '610300', '宝鸡市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (297, '610400', '咸阳市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (298, '610500', '渭南市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (299, '610600', '延安市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (300, '610700', '汉中市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (301, '610800', '榆林市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (302, '610900', '安康市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (303, '611000', '商洛市', '610000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (304, '620100', '兰州市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (305, '620200', '嘉峪关市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (306, '620300', '金昌市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (307, '620400', '白银市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (308, '620500', '天水市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (309, '620600', '武威市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (310, '620700', '张掖市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (311, '620800', '平凉市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (312, '620900', '酒泉市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (313, '621000', '庆阳市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (314, '621100', '定西市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (315, '621200', '陇南市', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (316, '622900', '临夏回族自治州', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (317, '623000', '甘南藏族自治州', '620000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (318, '630100', '西宁市', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (319, '632100', '海东地区', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (320, '632200', '海北藏族自治州', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (321, '632300', '黄南藏族自治州', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (322, '632500', '海南藏族自治州', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (323, '632600', '果洛藏族自治州', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (324, '632700', '玉树藏族自治州', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (325, '632800', '海西蒙古族藏族自治州', '630000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (326, '640100', '银川市', '640000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (327, '640200', '石嘴山市', '640000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (328, '640300', '吴忠市', '640000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (329, '640400', '固原市', '640000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (330, '640500', '中卫市', '640000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (331, '650100', '乌鲁木齐市', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (332, '650200', '克拉玛依市', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (333, '652100', '吐鲁番地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (334, '652200', '哈密地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (335, '652300', '昌吉回族自治州', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (336, '652700', '博尔塔拉蒙古自治州', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (337, '652800', '巴音郭楞蒙古自治州', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (338, '652900', '阿克苏地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (339, '653000', '克孜勒苏柯尔克孜自治州', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (340, '653100', '喀什地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (341, '653200', '和田地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (342, '654000', '伊犁哈萨克自治州', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (343, '654200', '塔城地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (344, '654300', '阿勒泰地区', '650000');
INSERT INTO `sb_base_city` (`id`, `cityid`, `city`, `provinceid`) VALUES (345, '659000', '省直辖行政单位', '650000');
COMMIT;

-- ----------------------------
-- Table structure for sb_base_provinces
-- ----------------------------
DROP TABLE IF EXISTS `sb_base_provinces`;
CREATE TABLE `sb_base_provinces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provinceid` varchar(20) NOT NULL,
  `province` varchar(50) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='省份信息表';

-- ----------------------------
-- Records of sb_base_provinces
-- ----------------------------
BEGIN;
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (1, '110000', '北京市');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (2, '120000', '天津市');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (3, '130000', '河北省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (4, '140000', '山西省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (5, '150000', '内蒙古自治区');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (6, '210000', '辽宁省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (7, '220000', '吉林省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (8, '230000', '黑龙江省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (9, '310000', '上海市');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (10, '320000', '江苏省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (11, '330000', '浙江省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (12, '340000', '安徽省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (13, '350000', '福建省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (14, '360000', '江西省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (15, '370000', '山东省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (16, '410000', '河南省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (17, '420000', '湖北省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (18, '430000', '湖南省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (19, '440000', '广东省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (20, '450000', '广西壮族自治区');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (21, '460000', '海南省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (22, '500000', '重庆市');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (23, '510000', '四川省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (24, '520000', '贵州省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (25, '530000', '云南省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (26, '540000', '西藏自治区');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (27, '610000', '陕西省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (28, '620000', '甘肃省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (29, '630000', '青海省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (30, '640000', '宁夏回族自治区');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (31, '650000', '新疆维吾尔自治区');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (32, '710000', '台湾省');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (33, '810000', '香港特别行政区');
INSERT INTO `sb_base_provinces` (`id`, `provinceid`, `province`) VALUES (34, '820000', '澳门特别行政区');
COMMIT;

-- ----------------------------
-- Table structure for sb_base_setting
-- ----------------------------
DROP TABLE IF EXISTS `sb_base_setting`;
CREATE TABLE `sb_base_setting` (
  `setid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `module` varchar(20) DEFAULT NULL COMMENT '模块',
  `set_item` varchar(50) DEFAULT NULL COMMENT '设置项名称',
  `value` text COMMENT '设置项值',
  PRIMARY KEY (`setid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——模块基础配置&设置';

-- ----------------------------
-- Records of sb_base_setting
-- ----------------------------
BEGIN;
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (1, 'assets', 'assets_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (2, 'assets', 'assets_helpcat', '[\"保内设备\",\"保外设备\",\"测试\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (3, 'assets', 'assets_capitalfrom', '[\"自筹\",\"财政\",\"科研\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (4, 'assets', 'assets_assfrom', '[\"购入\",\"租用\",\"捐赠\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (5, 'assets', 'assets_finance', '[\"医疗设备\",\"固定资产\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (6, 'assets', 'acin_category', '[\"\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (7, 'assets', 'assets_add_remind_day', '6');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (9, 'assets', 'assets_encoding_rules', '[\"categoryCode\",\"departmentCode\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (10, 'repair', 'repair_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (11, 'repair', 'repair_system', '{\"repair_person\":\"1\",\"repair_phone\":\"1\",\"service_date\":\"1\",\"service_working\":\"1\",\"repair_check\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (12, 'repair', 'repair_required', '{\"repair_date\":\"1\",\"repair_detail\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (13, 'repair', 'repair_encoding_rules', '{\"prefix\":\"WX\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (14, 'repair', 'repair_print_watermark', '{\"watermark\":\"若水医疗\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (15, 'repair', 'repair_tmp', '{\"style\":\"2\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (16, 'repair', 'repair_uptime', '60');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (17, 'patrol', 'patrol_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (18, 'patrol', 'patrol_reminding_day', '5');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (19, 'patrol', 'patrol_soon_expire_day', '10');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (27, 'patrol', 'priceRange', '[\"0|10000\",\"10001|100000\",\"100001|500000\",\"500001|1000000\",\"1000000\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (30, 'metering', 'metering_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (31, 'qualities', 'qualities_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (32, 'adverse', 'adverse_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (33, 'benefit', 'benefit_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (34, 'statistics', 'statistics_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (35, 'strategy', 'strategy_open', '{\"is_open\":\"0\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (36, 'monitor', 'monitor_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (37, 'archives', 'archives_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (38, 'train', 'train_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (39, 'offlineSuppliers', 'offlineSuppliers_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (43, 'repair', 'repair_category', '[\"保内自修\",\"保内寄修\",\"保内上门\",\"保外自修\",\"保外寄修\",\"保外外修\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (44, 'assets', 'apply_borrow_back_time', '[\"08:00:00\",\"22:00:00\"]');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (45, 'repair', 'parts_warning', '1');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (48, 'target_setting', 'target_chart_depart_repair', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (49, 'target_setting', 'target_chart_assets_add', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (50, 'target_setting', 'target_chart_assets_scrap', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (51, 'target_setting', 'target_chart_assets_purchases', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (52, 'target_setting', 'target_chart_assets_benefit', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (53, 'target_setting', 'target_chart_assets_adverse', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (54, 'target_setting', 'target_chart_assets_move', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (55, 'target_setting', 'target_chart_assets_patrol', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (57, 'qualities', 'qualities_soon_expire_day', '10');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (58, 'qualities', 'qualities_patrol', '');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (59, 'purchases', 'purchases_open', '{\"is_open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (60, 'repair', 'open_sweepCode_overhaul', '{\"open\":\"0\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (61, 'target_setting', 'target_setting_open', 'null');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (62, 'priceRange', 'priceRange_open', '\"0\"');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (63, 'wx_setting', 'wx_setting_open', '{\"open\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (64, 'wx_setting', 'open_wx_login_binding', '{\"open\":\"0\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (65, 'repair', 'repair_template', '{\"title\":\"天成医疗技术股份有限公司\",\"version\":\"1\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (66, 'assets', 'assets_scrap_overPrice', '1000');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (67, 'assets', 'assets_scrap_licenseDay', NULL);
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (68, 'all_module', 'all_report_logo', '\"\\/Public\\/uploads\\/allModuleLogo\\/20240415\\/661c8129d2d54.jpg\"');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (69, 'file', 'file_open', '\"\"');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (70, 'file_url', 'file_url_open', '\"\\/\"');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (71, 'assets', 'borrow_template', '{\"title\":\"天成医疗技术股份有限公司\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (72, 'assets', 'transfer_template', '{\"title\":\"天成医疗技术股份有限公司\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (73, 'assets', 'outside_template', '{\"title\":\"天成医疗技术股份有限公司\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (74, 'assets', 'scrap_template', '{\"title\":\"天成医疗技术股份有限公司\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (75, 'patrol', 'patrol_template', '{\"title\":\"医疗设备\"}');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (76, 'patrol', 'patrol_wx_set_situation', '0');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (77, 'repair', 'life_assets_remind', '5');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (78, 'repair', 'normal_assets_remind', '15');
INSERT INTO `sb_base_setting` (`setid`, `module`, `set_item`, `value`) VALUES (79, 'inventory', 'inventory_open', '{\"is_open\":\"1\"}');
COMMIT;

-- ----------------------------
-- Table structure for sb_business_control
-- ----------------------------
DROP TABLE IF EXISTS `sb_business_control`;
CREATE TABLE `sb_business_control` (
  `contid` int(11) NOT NULL AUTO_INCREMENT,
  `project_contid` int(11) DEFAULT '0' COMMENT '对应项目组业务ID',
  `process_num` varchar(100) DEFAULT '' COMMENT 'OA流程编号',
  `project` varchar(100) NOT NULL DEFAULT '' COMMENT '二级部门名称',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '项目内/外【1项目内2,项目外】',
  `create_time` int(11) DEFAULT NULL COMMENT '业务发生时间',
  `repair_num` varchar(100) DEFAULT NULL COMMENT '维修编号',
  `desc` text COMMENT '业务描述',
  `buy_user` varchar(60) DEFAULT NULL COMMENT '采购对接人',
  `supplier_contacts` varchar(60) DEFAULT NULL COMMENT '供应商联系人',
  `supplier_tel` varchar(20) DEFAULT NULL COMMENT '联系方式【供应商】',
  `supplier` varchar(100) DEFAULT NULL COMMENT '供应商名称',
  `supplier_account` varchar(100) DEFAULT NULL COMMENT '供应商账户名称',
  `supplier_bank_num` varchar(30) DEFAULT NULL COMMENT '供应商银行账号',
  `supplier_bank` varchar(255) DEFAULT NULL COMMENT '开户行',
  `customer_name` varchar(100) DEFAULT NULL COMMENT '客户名称',
  `customer_contacts` varchar(60) DEFAULT NULL COMMENT '客户联系人',
  `sale_price` decimal(10,2) DEFAULT NULL COMMENT '经销价',
  `total_sale` decimal(10,2) DEFAULT NULL COMMENT '销售总额',
  `allowance` decimal(10,2) DEFAULT NULL COMMENT '计提额度',
  `create_user` varchar(255) DEFAULT NULL COMMENT '采购申请人',
  `buy_accept_date` date DEFAULT NULL COMMENT '采购受理日期',
  `purchase_time` int(11) DEFAULT NULL COMMENT '采购对接时间',
  `income_date` date DEFAULT NULL COMMENT '进项票日期',
  `income_num` varchar(60) DEFAULT NULL COMMENT '进项票编号',
  `goods_arrive` varchar(255) DEFAULT NULL COMMENT '货物到货情况',
  `logistics_num` varchar(60) DEFAULT NULL COMMENT '货物物流单号',
  `remark` varchar(255) DEFAULT NULL COMMENT '其他备注',
  `pay_user` varchar(60) DEFAULT NULL COMMENT '付款方',
  `pay_money` decimal(10,2) DEFAULT NULL COMMENT '付款金额',
  `pay_date` date DEFAULT NULL COMMENT '付款申请日期',
  `pay_to_capital` date DEFAULT NULL COMMENT '付款提交资金部日期',
  `actual_pay_date` date DEFAULT NULL COMMENT '实际付款时间',
  `pay_remark` varchar(255) DEFAULT NULL COMMENT '付款原因备注',
  `pay_for_spare` tinyint(3) DEFAULT NULL COMMENT '是否备用金支付【1是0否】',
  `in_kingdee` tinyint(3) DEFAULT NULL COMMENT '金蝶是否已入库【1已入0未入】',
  `out_kingdee` tinyint(3) DEFAULT NULL COMMENT '金蝶是否已出库【1已出0未出】',
  `first_operation` tinyint(3) DEFAULT NULL COMMENT '是否首营【1是0否】',
  `output_type` varchar(20) DEFAULT NULL COMMENT '销项票发票类型',
  `output_rate` varchar(10) DEFAULT NULL COMMENT '销项票税率',
  `output_date` date DEFAULT NULL COMMENT '销项票日期',
  `output_num` varchar(60) DEFAULT NULL COMMENT '销项票编号',
  `output_content` varchar(255) DEFAULT NULL COMMENT '销项票内容',
  `output_info` varchar(255) DEFAULT NULL COMMENT '销项票物流信息',
  `tax_amount` decimal(10,2) DEFAULT NULL COMMENT '含税金额',
  `non_tax_amount` decimal(10,2) DEFAULT NULL COMMENT '不含税金额',
  `payment_date` date DEFAULT NULL COMMENT '回款日期',
  `cancellation_amount` decimal(10,2) DEFAULT NULL COMMENT '核销金额',
  `non_repayment_amount` decimal(10,2) DEFAULT NULL COMMENT '未回款金额',
  `estimate_profit` decimal(10,2) DEFAULT NULL COMMENT '预估毛利',
  `settlement_date` date DEFAULT NULL COMMENT '结算日期',
  `settlement_amount` varchar(255) DEFAULT NULL COMMENT '结算额度',
  `allover_date` date DEFAULT NULL COMMENT '结单日期',
  `customer_tel` varchar(20) DEFAULT NULL COMMENT '客户联系电话',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `buy_complete` tinyint(3) DEFAULT '0' COMMENT '采购是否完成【1是0否】',
  `synchro_add` tinyint(3) DEFAULT '0' COMMENT '同步新增【1成功0失败】',
  `synchro_edit` tinyint(3) DEFAULT '0' COMMENT '同步修改【1成功0失败】',
  `follow` tinyint(3) DEFAULT '0' COMMENT '同步跟进【1成功0跟进失败】',
  `follow_time` int(11) DEFAULT NULL COMMENT '跟进时间',
  `purchase` tinyint(3) DEFAULT '0' COMMENT '同步采购对接【1成功0失败】',
  `sync_finance` tinyint(3) DEFAULT '0' COMMENT '财务同步【1成功0失败】',
  `finance_time` int(11) DEFAULT NULL COMMENT '财务同步时间',
  `all_complete` tinyint(3) DEFAULT '0' COMMENT '完成业务单【1全部完成0未完成】',
  PRIMARY KEY (`contid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——业务管控';

-- ----------------------------
-- Records of sb_business_control
-- ----------------------------


-- ----------------------------
-- Table structure for sb_business_materiel
-- ----------------------------
DROP TABLE IF EXISTS `sb_business_materiel`;
CREATE TABLE `sb_business_materiel` (
  `matid` int(11) NOT NULL AUTO_INCREMENT,
  `contid` int(11) DEFAULT NULL COMMENT '业务ID',
  `project_matid` int(11) DEFAULT '0' COMMENT '项目组对应物料ID',
  `title` varchar(255) DEFAULT NULL COMMENT '采购物料名称',
  `factory` varchar(255) DEFAULT NULL COMMENT '生产厂家',
  `model` varchar(60) DEFAULT NULL COMMENT '型号',
  `num` int(11) DEFAULT NULL COMMENT '数量',
  `price` decimal(10,2) DEFAULT NULL COMMENT '采购单价',
  `unit` varchar(60) DEFAULT NULL COMMENT '单位',
  `guarantee_date` varchar(100) DEFAULT NULL COMMENT '保修期',
  `is_depot` tinyint(3) DEFAULT '0' COMMENT '物料是否仓库领用【1是0否】',
  `is_invoice` tinyint(3) DEFAULT '0' COMMENT '是否有发票【1有0没有】',
  `invoice_content` varchar(255) DEFAULT NULL COMMENT '进项票开票内容',
  `invoice_type` varchar(20) DEFAULT '0' COMMENT '进项票类型',
  `invoice_rate` varchar(10) DEFAULT NULL COMMENT '税点',
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '采购总额',
  PRIMARY KEY (`matid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——业务管控--物料表';

-- ----------------------------
-- Records of sb_business_materiel
-- ----------------------------


-- ----------------------------
-- Table structure for sb_category
-- ----------------------------
DROP TABLE IF EXISTS `sb_category`;
CREATE TABLE `sb_category` (
  `catid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `catenum` varchar(10) NOT NULL COMMENT '分类编号',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类ID',
  `arrchildid` varchar(255) NOT NULL DEFAULT '' COMMENT '子类ID组',
  `child` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '子类数',
  `assetssum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '设备数量',
  `assetsprice` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '设备总金额',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0关闭1开启 （分类开关）',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1为已删除0未删除】',
  `remark` text COMMENT '品名举例',
  PRIMARY KEY (`catid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——主设备分类（68分类）';

-- ----------------------------
-- Records of sb_category
-- ----------------------------


-- ----------------------------
-- Table structure for sb_category_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_category_upload_temp`;
CREATE TABLE `sb_category_upload_temp` (
  `tempid` int(11) NOT NULL AUTO_INCREMENT COMMENT '临时ID',
  `hospital_code` varchar(20) DEFAULT NULL COMMENT '医院代码',
  `catenum` varchar(20) DEFAULT NULL COMMENT '分类编号',
  `category` varchar(50) DEFAULT NULL COMMENT '分类名称',
  `parentid` int(11) DEFAULT NULL COMMENT '分类父ID',
  `adduser` varchar(50) DEFAULT NULL COMMENT '上传人',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `edituser` varchar(50) DEFAULT NULL COMMENT '编辑人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '编辑时间',
  `is_save` tinyint(3) DEFAULT '0' COMMENT '是否保存【1已保存，0未保存】',
  `remark` text COMMENT '品名举例',
  PRIMARY KEY (`tempid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——主设备分类上传临时表';

-- ----------------------------
-- Records of sb_category_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_confirm_add_repair
-- ----------------------------
DROP TABLE IF EXISTS `sb_confirm_add_repair`;
CREATE TABLE `sb_confirm_add_repair` (
  `confirmId` int(10) NOT NULL AUTO_INCREMENT,
  `applicant` varchar(60) NOT NULL DEFAULT '' COMMENT '报修人员（被指定）',
  `patroluser` varchar(60) NOT NULL DEFAULT '' COMMENT '巡查人员',
  `assnum` varchar(60) DEFAULT '' COMMENT '设备编码',
  `remark` text COMMENT '备注',
  `comfirmDate` int(10) DEFAULT '0' COMMENT '确认时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '确认状态 0->待确认 1->已确定',
  `cycid` int(10) NOT NULL COMMENT '周期id',
  `execid` int(10) NOT NULL COMMENT '实施id',
  `asset_status` varchar(64) DEFAULT '' COMMENT '设备现状',
  `abnormalText` text COMMENT '报修原因',
  PRIMARY KEY (`confirmId`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——设备转至报修确认表';

-- ----------------------------
-- Records of sb_confirm_add_repair
-- ----------------------------


-- ----------------------------
-- Table structure for sb_department
-- ----------------------------
DROP TABLE IF EXISTS `sb_department`;
CREATE TABLE `sb_department` (
  `departid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '科室ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '医院代码',
  `departnum` int(3) unsigned NOT NULL COMMENT '科室编号',
  `department` varchar(50) NOT NULL DEFAULT '' COMMENT '科室名称',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类ID',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '科室所在位置',
  `departrespon` varchar(20) NOT NULL DEFAULT '' COMMENT '科室负责人',
  `manager` varchar(60) DEFAULT NULL COMMENT '系统科室负责人',
  `assetsrespon` varchar(20) NOT NULL DEFAULT '' COMMENT '设备负责人',
  `departtel` varchar(50) NOT NULL DEFAULT '' COMMENT '科室电话',
  `assetssum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '设备数量',
  `assetsprice` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '设备总金额',
  `edittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1为已删除0未删除】',
  PRIMARY KEY (`departid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——科室（部门）表';

-- ----------------------------
-- Records of sb_department
-- ----------------------------


-- ----------------------------
-- Table structure for sb_department_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_department_upload_temp`;
CREATE TABLE `sb_department_upload_temp` (
  `tempid` varchar(32) NOT NULL DEFAULT '' COMMENT '临时ID',
  `hospital_code` varchar(20) DEFAULT NULL COMMENT '医院代码',
  `departnum` int(3) unsigned DEFAULT NULL COMMENT '科室编码',
  `department` varchar(50) DEFAULT NULL COMMENT '科室名称',
  `address` varchar(50) DEFAULT NULL COMMENT '科室位置',
  `departrespon` varchar(50) DEFAULT NULL COMMENT '科室负责人',
  `assetsrespon` varchar(50) DEFAULT NULL COMMENT '设备负责人',
  `departtel` varchar(50) DEFAULT NULL COMMENT '科室电话',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `adduser` varchar(50) DEFAULT NULL COMMENT '上传人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `edituser` varchar(50) DEFAULT NULL COMMENT '修改人',
  `is_save` tinyint(3) DEFAULT '0' COMMENT '是否已入库【1已入库0未入】',
  PRIMARY KEY (`tempid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——科室上传临时表';

-- ----------------------------
-- Records of sb_department_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_dic_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_dic_assets`;
CREATE TABLE `sb_dic_assets` (
  `dic_assid` int(11) NOT NULL AUTO_INCREMENT COMMENT '设备名称字典ID，自增',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `assets` varchar(255) NOT NULL DEFAULT '' COMMENT '设备名称',
  `catid` int(11) NOT NULL DEFAULT '0' COMMENT '默认设备分类ID',
  `dic_category` varchar(255) DEFAULT NULL COMMENT '字典分类',
  `assets_category` varchar(255) DEFAULT NULL COMMENT '设备类别',
  `unit` varchar(10) DEFAULT NULL COMMENT '设备单位',
  `remark` text COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '1启用0停用',
  `adduser` varchar(60) DEFAULT NULL COMMENT '添加人',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`dic_assid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='数据字典——设备名称字典';

-- ----------------------------
-- Records of sb_dic_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_dic_assets_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_dic_assets_upload_temp`;
CREATE TABLE `sb_dic_assets_upload_temp` (
  `tempid` varchar(32) NOT NULL DEFAULT '' COMMENT '设备名称字典ID，自增',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `assets` varchar(255) NOT NULL DEFAULT '' COMMENT '设备名称',
  `category` varchar(60) DEFAULT NULL COMMENT '设备分类',
  `dic_category` varchar(255) DEFAULT NULL COMMENT '字典分类',
  `assets_category` varchar(255) DEFAULT NULL COMMENT '设备类别',
  `unit` varchar(10) DEFAULT NULL COMMENT '设备单位',
  `adduser` varchar(60) DEFAULT NULL COMMENT '添加人',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `is_save` tinyint(3) DEFAULT '0' COMMENT '是否保存【0未保存1已保存】',
  PRIMARY KEY (`tempid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='数据字典——设备名称字典';

-- ----------------------------
-- Records of sb_dic_assets_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_dic_brand
-- ----------------------------
DROP TABLE IF EXISTS `sb_dic_brand`;
CREATE TABLE `sb_dic_brand` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(60) DEFAULT NULL COMMENT '品牌名称',
  `brand_desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`brand_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='数据字典——品牌字典';

-- ----------------------------
-- Records of sb_dic_brand
-- ----------------------------


-- ----------------------------
-- Table structure for sb_dic_parts
-- ----------------------------
DROP TABLE IF EXISTS `sb_dic_parts`;
CREATE TABLE `sb_dic_parts` (
  `dic_partsid` int(11) NOT NULL AUTO_INCREMENT COMMENT '配件字典ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `parts` varchar(255) NOT NULL DEFAULT '' COMMENT '配件名称',
  `parts_model` varchar(255) DEFAULT NULL COMMENT '配件型号',
  `unit` varchar(100) DEFAULT '' COMMENT '单位',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '配件单价',
  `brand` varchar(150) DEFAULT '' COMMENT '品牌',
  `supplier_name` varchar(255) DEFAULT '' COMMENT '生产厂商名称',
  `supplier_id` int(10) DEFAULT '0' COMMENT '生产厂商id',
  `dic_category` varchar(255) DEFAULT '' COMMENT '字典分类',
  `remark` text COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '1启用0停用-1删除',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '0未删除 1已删除',
  `adduser` varchar(60) DEFAULT NULL COMMENT '添加人',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`dic_partsid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='数据字典——配件字典';

-- ----------------------------
-- Records of sb_dic_parts
-- ----------------------------


-- ----------------------------
-- Table structure for sb_edit
-- ----------------------------
DROP TABLE IF EXISTS `sb_edit`;
CREATE TABLE `sb_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL COMMENT '所属医院ID',
  `operation_type` varchar(10) DEFAULT NULL COMMENT '操作类型【edit修改、delete删除】',
  `table` varchar(20) DEFAULT NULL COMMENT '要操作的表名',
  `old_data` text COMMENT '修改前的数据，json格式保存，恢复数据用',
  `update_data` text COMMENT '要修改的数据，json格式保存',
  `update_where` text COMMENT '要操作的条件，json格式保存',
  `applicant_user` varchar(30) DEFAULT NULL COMMENT '申请人',
  `applicant_time` datetime DEFAULT NULL COMMENT '申请时间',
  `desc` varchar(255) DEFAULT NULL COMMENT '申请备注',
  `is_approval` tinyint(3) DEFAULT '0' COMMENT '核准状态【0未核准，1已核准，2驳回申请】',
  `approval_user` varchar(30) DEFAULT NULL COMMENT '核准人',
  `approval_time` datetime DEFAULT NULL COMMENT '核准时间',
  `is_back` tinyint(3) DEFAULT '0' COMMENT '回退状态【0未回退，1已回退恢复数据】',
  `back_user` varchar(30) DEFAULT NULL COMMENT '回退人',
  `back_time` datetime DEFAULT NULL COMMENT '回退时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理---修改、删除记录表';

-- ----------------------------
-- Records of sb_edit
-- ----------------------------


-- ----------------------------
-- Table structure for sb_feishu_msg_log
-- ----------------------------
DROP TABLE IF EXISTS `sb_feishu_msg_log`;
CREATE TABLE `sb_feishu_msg_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(25) DEFAULT NULL COMMENT '用户的openid',
  `code` varchar(25) DEFAULT NULL COMMENT '返回的编码0代表正确，其他数字代表出错',
  `msg` varchar(255) DEFAULT NULL COMMENT '返回的提示信息',
  `send_time` datetime DEFAULT NULL COMMENT '发送时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='飞书消息发送记录表';

-- ----------------------------
-- Records of sb_feishu_msg_log
-- ----------------------------


-- ----------------------------
-- Table structure for sb_hospital
-- ----------------------------
DROP TABLE IF EXISTS `sb_hospital`;
CREATE TABLE `sb_hospital` (
  `hospital_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_name` varchar(100) DEFAULT NULL COMMENT '医院名称',
  `hospital_code` varchar(20) DEFAULT NULL COMMENT '医院代码【唯一】',
  `contacts` varchar(60) DEFAULT NULL COMMENT '联系人',
  `phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `amount_limit` decimal(10,2) DEFAULT NULL COMMENT '采购年限下限',
  `address` varchar(255) DEFAULT NULL COMMENT '医院地址',
  `is_general_hospital` tinyint(3) DEFAULT '0' COMMENT '是否总院【1是0否】',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1已删除0未删除】',
  PRIMARY KEY (`hospital_id`) USING BTREE,
  KEY `name_code` (`hospital_name`,`hospital_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基础设置——医院记录表';

-- ----------------------------
-- Records of sb_hospital
-- ----------------------------


-- ----------------------------
-- Table structure for sb_inventory_plan
-- ----------------------------
DROP TABLE IF EXISTS `sb_inventory_plan`;
CREATE TABLE `sb_inventory_plan` (
  `inventory_plan_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '盘点计划ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `inventory_plan_no` varchar(120) NOT NULL COMMENT '盘点计划单号',
  `inventory_plan_name` varchar(120) NOT NULL COMMENT '盘点计划名称',
  `inventory_plan_start_time` datetime DEFAULT NULL COMMENT '盘点计划开始日期',
  `inventory_plan_end_time` datetime DEFAULT NULL COMMENT '盘点计划结束日期',
  `inventory_plan_status` int(11) NOT NULL COMMENT '盘点状态【0-待发布，1-待盘点，2-正在(暂存)盘点，3-审核中，4-已结束】',
  `inventory_users` json DEFAULT NULL COMMENT '盘点员',
  `is_push` tinyint(1) DEFAULT '0' COMMENT '是否自动【0-手动（后台），1-自动（盘点机）】',
  `push_system_name` varchar(60) DEFAULT NULL COMMENT '推送的下游系统名称',
  `push_status` tinyint(1) DEFAULT NULL COMMENT '推送状态',
  `push_time` datetime DEFAULT NULL COMMENT '下推时间',
  `receive_status` tinyint(1) DEFAULT NULL COMMENT '接收状态',
  `receive_time` datetime DEFAULT NULL COMMENT '接收时间',
  `error_msg` json DEFAULT NULL COMMENT '错误信息【push_error-推送失败信息，receive_error-接受失败消息】',
  `approve_status` tinyint(3) DEFAULT NULL COMMENT '审批状态(-1不需审批 0未审 1通过 2不通过)',
  `approve_time` datetime DEFAULT NULL COMMENT '最后审批时间',
  `current_approver` varchar(150) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '全部审批人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '计划制定人',
  `add_time` datetime DEFAULT NULL COMMENT '计划制定时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '计划修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '计划修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否 1是】',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`inventory_plan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='盘点管理——盘点计划表';

-- ----------------------------
-- Records of sb_inventory_plan
-- ----------------------------


-- ----------------------------
-- Table structure for sb_inventory_plan_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_inventory_plan_assets`;
CREATE TABLE `sb_inventory_plan_assets` (
  `inventory_plan_assets_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '盘点计划设备自增ID',
  `inventory_plan_id` int(10) NOT NULL COMMENT '盘点计划id',
  `assetnum` varchar(25) DEFAULT NULL COMMENT '设备编号',
  `inventory_status` tinyint(1) DEFAULT '0' COMMENT '盘点状态：【0-未盘点；1-正常；2-异常】',
  `reason` text COMMENT '原因',
  `result` varchar(255) DEFAULT NULL COMMENT '结果',
  `assets` varchar(255) DEFAULT '' COMMENT '资产名称',
  `departid` int(10) DEFAULT NULL COMMENT '所属科室',
  `address` varchar(255) DEFAULT NULL COMMENT '存放地点',
  `add_time` datetime DEFAULT NULL,
  `is_plan` tinyint(1) DEFAULT NULL COMMENT '是否计划内【0-否；1-是】',
  `financeid` tinyint(1) DEFAULT NULL COMMENT '财务分类',
  `inventory_user` varchar(60) DEFAULT NULL COMMENT '盘点员',
  `pic_urls` json DEFAULT NULL COMMENT '盘点图片',
  PRIMARY KEY (`inventory_plan_assets_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='盘点管理——盘点计划设备表';

-- ----------------------------
-- Records of sb_inventory_plan_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_log
-- ----------------------------
DROP TABLE IF EXISTS `sb_log`;
CREATE TABLE `sb_log` (
  `logid` int(10) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `userid` int(10) NOT NULL COMMENT '用户ID',
  `module` varchar(50) NOT NULL COMMENT '模块名称：如：assets、repair、patrol、inventory(盘点)、role、user、modset',
  `action` varchar(20) NOT NULL COMMENT '事件：add、update、delete、approve、print、upload、download、scan、login、logout',
  `actionid` int(10) NOT NULL COMMENT '事件ID，如：记录被操作的事件ID，如：设备ID、维修单ID、盘点ID、巡查ID、角色组ID、用户ID、setid',
  `action_time` int(10) NOT NULL COMMENT '事件操作时间',
  PRIMARY KEY (`logid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——操作日志表';

-- ----------------------------
-- Records of sb_log
-- ----------------------------


-- ----------------------------
-- Table structure for sb_login_app_token
-- ----------------------------
DROP TABLE IF EXISTS `sb_login_app_token`;
CREATE TABLE `sb_login_app_token` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `session` longtext,
  `login_time` datetime NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `token` (`token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of sb_login_app_token
-- ----------------------------


-- ----------------------------
-- Table structure for sb_menu
-- ----------------------------
DROP TABLE IF EXISTS `sb_menu`;
CREATE TABLE `sb_menu` (
  `menuid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL COMMENT '模块名称',
  `title` varchar(30) DEFAULT NULL COMMENT '菜单名称',
  `BaseSettingTitle` varchar(30) DEFAULT '' COMMENT '权限配置名称',
  `parentid` int(11) DEFAULT '0' COMMENT '上级ID',
  `op_parentid` int(11) DEFAULT NULL COMMENT '附属关系id',
  `orderID` int(11) DEFAULT '1' COMMENT '排序号',
  `status` tinyint(3) DEFAULT '1' COMMENT '是否启用【1为启用0为停用】',
  `leftShow` tinyint(3) DEFAULT '0' COMMENT '是否左边菜单显示',
  `icon` varchar(255) DEFAULT NULL COMMENT '图表样式代码',
  `jump` varchar(255) DEFAULT NULL COMMENT '自定义菜单地址',
  `tips` text COMMENT '贴士',
  `menuNotShow` tinyint(1) DEFAULT '0' COMMENT '0:显示 1:不显示',
  PRIMARY KEY (`menuid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=569 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='菜单表';

-- ----------------------------
-- Records of sb_menu
-- ----------------------------
BEGIN;
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (1, 'Assets', '设备管理', '设备管理', 0, NULL, 3, 1, 1, 'layui-icon-component', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (2, 'Repair', '维修管理', '维修管理', 0, NULL, 4, 1, 1, 'layui-icon-gongju', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (3, 'Patrol', '巡查保养管理', '巡查保养管理', 0, NULL, 5, 1, 1, 'layui-icon-zzstreet-view', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (4, 'BaseSetting', '基础设置', '基础设置', 0, NULL, 16, 1, 1, 'layui-icon-set', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (5, 'Scrap', '报废管理', '报废管理', 1, NULL, 7, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (6, 'Lookup', '设备列表', '设备列表', 1, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (7, 'getAssetsSearchList', '设备综合查询', '设备综合查询列表', 6, NULL, 4, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (9, 'Transfer', '转科管理', '转科管理', 1, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (10, 'AssetsSetting', '模块配置', '模块配置', 1, NULL, 11, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (11, 'AssetsStatis', '报表管理', '报表管理', 1, NULL, 10, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (12, 'RepairSetting', '模块配置', '模块配置', 2, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (13, 'RepairSearch', '维修记录查询', '维修记录查询', 2, NULL, 2, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (14, 'Repair', '维修业务管理', '维修业务管理', 2, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (15, 'Patrol', '巡查保养业务管理', '巡查保养业务管理', 3, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (16, 'PatrolSetting', '保养模板设置', '保养模板设置', 3, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (17, 'PatrolStatis', '报表管理', '报表管理', 3, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (18, 'IntegratedSetting', '基础参数', '基础参数', 4, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (19, 'Privilege', '权限设置', '权限设置', 4, NULL, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (20, 'ModuleSetting', '模块管理&配置', '模块管理&配置', 4, NULL, 6, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (21, 'User', '用户管理', '用户管理', 4, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (23, 'ApproveSetting', '审批设置', '审批设置', 4, NULL, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (24, 'getScrapList', '报废查询', '报废查询列表', 5, NULL, 2, 1, 1, NULL, NULL, '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (25, 'getApplyList', '报废申请', '报废申请列表', 5, NULL, 1, 1, 1, NULL, NULL, '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (26, 'getExamineList', '报废审核', '报废审核列表', 5, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (27, 'getResultList', '报废结果', '报废结果列表', 5, NULL, 4, 1, 1, NULL, NULL, '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (28, 'getAssetsList', '设备查询', '设备查询列表', 6, NULL, 1, 1, 1, NULL, 'Assets/Lookup/getAssetsList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (31, 'getInsuranceList', '设备参保列表', '设备参保列表', 6, NULL, 3, 1, 1, NULL, 'Assets/Lookup/getInsuranceList', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (35, 'getList', '转科申请', '转科申请列表', 9, NULL, 1, 1, 1, NULL, NULL, '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (36, 'examine', '转科审批', '转科审批列表', 9, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (37, 'progress', '转科进程', '转科进程', 9, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (38, 'assetsModuleSetting', '模块配置', '模块配置', 10, NULL, 10, 1, 1, NULL, 'Assets/AssetsSetting/assetsModuleSetting', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (39, 'onUseAssetsSurvey', '在用设备统计', '在用设备统计', 11, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (40, 'assetsSummary', '设备汇总统计', '设备汇总统计', 11, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (41, 'assetsSurvey', '设备概况', '设备概况', 11, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (42, 'faultSummary', '设备故障统计', '设备故障统计', 177, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (43, 'patrolPlanSurvey', '巡查计划概况', '巡查计划概况', 17, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (44, 'getAssetsLists', '设备报修', '设备报修列表', 14, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (45, 'ordersLists', '接单响应', '接单响应列表', 14, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (46, 'dispatchingLists', '派工响应', '派工响应列表', 14, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (47, 'getRepairLists', '维修处理', '维修处理列表', 14, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (48, 'batchAddRepair', '集中录入', '集中录入', 14, NULL, 8, 1, 1, NULL, NULL, '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (49, 'unifiedOffer', '统一报价列表', '统一报价列表', 14, NULL, 9, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (50, 'typeSetting', '故障类型设置', '故障类型设置', 12, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (52, 'repairModuleSetting', '模块配置', '模块配置', 12, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (53, 'getRepairSearchList', '维修记录查询', '维修记录列表', 13, NULL, 1, 1, 1, NULL, 'Repair/RepairSearch/getRepairSearchList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (54, 'patrolList', '计划查询列表', '巡查保养计划列表', 15, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (55, 'examineList', '科室验收列表', '科室验收列表', 15, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (56, 'tasksList', '任务实施列表', '任务实施列表', 15, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (57, 'points', '保养项目类别&明细', '保养项目类别&明细', 16, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (58, 'template', '模板维护', '模板维护', 16, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (59, 'initialization', '设备初始化模板', '设备初始化模板', 16, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (63, 'getUserList', '用户管理', '用户列表', 21, NULL, 1, 1, 1, NULL, 'BaseSetting/User/getUserList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (64, 'addUser', '新增用户', '新增用户', 21, 63, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (65, 'department', '科室设置', '科室设置', 18, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (66, 'category', '主设备分类设置', '主设备分类设置', 18, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (67, 'system', '微信参数设置', '微信参数设置', 18, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (68, 'getRoleList', '角色管理', '角色管理列表', 19, NULL, 1, 1, 1, NULL, 'BaseSetting/Privilege/getRoleList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (69, 'add', '转科', '转科', 9, 35, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (71, 'batchAdd', '批量转移科室', '批量转移科室', 9, 35, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (74, 'editRoleUser', '成员维护', '成员维护', 19, 68, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (76, 'operationLog', '用户行为日志', '用户行为日志', 18, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (77, 'addRole', '添加角色', '添加角色', 19, 68, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (78, 'addRepair', '报修', '报修', 14, 44, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (79, 'assigned', '指派', '指派', 14, 46, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (80, 'addType', '添加故障类型', '添加故障类型', 12, 50, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (82, 'editProblem', '修改故障问题', '修改故障问题', 12, 50, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (83, 'deleteProblem', '删除故障问题', '删除故障问题', 12, 50, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (86, 'accept', '接单', '接单', 14, 45, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (87, 'editRolePrivi', '权限维护', '权限维护', 19, 68, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (88, 'editRole', '编辑角色', '编辑角色', 19, 68, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (89, 'deleteRole', '删除角色', '删除角色', 19, 68, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (90, 'checkRepair', '验收', '验收', 14, 137, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (91, 'editProcess', '修改流程', '修改流程', 23, 174, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (92, 'deleteProcess', '删除流程', '删除流程', 23, 174, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (96, 'addApprove', '审批', '维修审批', 14, 169, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (97, 'startRepair', '开始维修', '开始维修', 14, 47, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (102, 'editUser', '修改用户', '修改用户', 21, 63, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (103, 'deleteUser', '删除用户', '删除用户', 21, 63, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (104, 'clearOpenid', '微信解绑', '微信解绑', 21, 63, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (105, 'addCategory', '添加主设备分类', '添加设备分类', 18, 66, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (106, 'editCategory', '修改分类', '修改分类', 18, 66, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (107, 'deleteCategory', '删除分类', '删除分类', 18, 66, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (110, 'batchAddCategory', '批量添加分类', '批量添加分类', 18, 66, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (111, 'addDepartment', '添加科室', '添加科室', 18, 65, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (112, 'editDepartment', '修改科室', '修改科室', 18, 65, 6, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (113, 'deleteDepartment', '删除科室', '删除科室', 18, 65, 7, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (114, 'addProcess', '添加流程', '添加流程', 23, 174, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (115, 'batchDeleteAssets', '批量删除主设备', '批量删除主设备', 6, 28, 1, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (116, 'batchAddAssets', '批量添加主设备', '批量添加主设备', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (117, 'addAssets', '添加主设备', '添加主设备', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (118, 'addPatrol', '新增巡查保养', '新增巡查保养', 15, 54, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (119, 'addPoints', '新增类别', '新增类别', 16, 57, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (121, 'addTemplate', '新增模板', '新增模板', 16, 58, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (122, 'batchSettingTemplate', '设定模板', '设定模板', 16, 59, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (125, 'deleteTemplate', '删除模板', '删除模板', 16, 58, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (126, 'editTemplate', '编辑模板', '编辑模板', 16, 58, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (127, 'editDetail', '修改明细', '修改明细', 16, 57, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (128, 'deleteDetail', '删除明细', '删除明细', 16, 57, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (129, 'editAssets', '修改主设备', '修改主设备', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (130, 'deleteAssets', '删除主设备', '删除主设备', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (132, 'editPatrol', '修订计划', '修订计划', 15, 54, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (133, 'releasePatrol', '发布计划', '发布计划', 15, 54, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (134, 'doOffer', '报价', '报价', 14, 49, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (135, 'doTask', '执行任务', '执行任务', 15, 56, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (136, 'applyScrap', '申请报废', '申请报废', 5, 25, 1, 1, 0, NULL, NULL, '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (137, 'examine', '科室验收', '科室验收', 14, NULL, 7, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (138, 'examine', '报废审批', '报废审批', 5, 26, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (139, 'result', '报废处置', '报废处置', 5, 27, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (145, 'uploadRepair', '上传维修相关文件', '上传维修相关文件', 14, 47, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (147, 'batchEditAssets', '批量修改主设备', '批量修改主设备', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (149, 'doRenewal', '续保', '续保', 6, 31, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (150, 'exportAssetsSurvey', '导出设备概况', '导出设备概况', 11, 41, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (151, 'exportUsingAssetsSurvey', '导出在用设备概况', '导出在用设备概况', 11, 39, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (152, 'exportAssetsSummary', '导出设备汇总统计概况', '导出设备汇总统计概况', 11, 40, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (153, 'exportDepartmentSummary', '导出部门电子账单', '导出部门电子账单', 11, 40, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (161, 'exportFaultSummary', '导出当前表格', '导出故维修障统计表格', 177, 42, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (162, 'batchAddDepartment', '批量添加科室', '批量添加科室', 18, 65, 8, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (163, 'approval', '审批', '审批', 9, 36, 1, 1, 0, NULL, NULL, '建议分配给设备科科长', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (165, 'exportRepairRecord', '导出设备维修记录', '导出设备维修记录', 11, 40, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (166, 'exportPlanSummary', '导出巡查计划概况', '导出巡查计划概况', 17, 43, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (167, 'exportPlanLists', '导出设备计划记录', '导出设备计划记录', 17, 43, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (169, 'repairApproveLists', '维修审批', '维修审批列表', 14, NULL, 6, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (171, 'progress', '维修进程', '维修进程列表', 14, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (174, 'approveLists', '多级审批设置', '多级审批设置', 23, NULL, 5, 1, 1, NULL, 'BaseSetting/ApproveSetting/approveLists', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (177, 'RepairStatis', '报表管理', '报表管理', 2, NULL, 4, 1, 1, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (179, 'examine', '巡查验收', '巡查验收', 15, 55, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (180, 'Qualities', '质控管理', '质控管理', 0, NULL, 6, 1, 1, 'layui-icon-auz', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (181, 'MaintenanceSummary', '参保信息统计', '参保信息统计', 11, NULL, 2, 1, 1, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (182, 'Notice', '系统公告管理', '系统公告管理', 4, NULL, 8, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (183, 'addNotice', '发布公告', '发布公告', 182, 230, 1, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (184, 'batchDeleteUser', '批量删除用户', '批量删除用户', 21, 63, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (185, 'Quality', '质控管理', '质控管理', 180, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (186, 'Metering', '计量管理', '计量管理', 0, NULL, 7, 1, 1, 'layui-icon-zzbalance-scale', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (187, 'exportFaultDetailed', '导出当前表格', '导出设备故障明细表格', 177, 42, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (188, 'editNotice', '编辑公告', '编辑公告', 182, 230, 1, 1, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (189, 'deleteNotice', '删除公告', '删除公告', 182, 230, 1, 1, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (191, 'getQualityList', '质控计划制定', '质控计划列表', 185, NULL, 2, 1, 1, NULL, 'Qualities/Quality/getQualityList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (192, 'addQuality', '新增质控计划', '新增质控计划', 185, 191, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (193, 'startQualityPlan', '启用质控计划', '启用质控计划', 185, 191, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (194, 'showQualityPlan', '质控计划详情', '质控计划详情', 185, 191, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (195, 'editQualityPlan', '编辑质控计划', '编辑质控计划', 185, 191, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (196, 'setQualityDetail', '明细录入', '明细录入/执行计划', 185, 197, 6, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (197, 'qualityDetailList', '质控明细录入', '质控明细录入', 185, NULL, 7, 1, 1, NULL, 'Qualities/Quality/qualityDetailList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (198, 'QualitiesSetting', '模块配置', '模块配置', 180, NULL, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (199, 'Statistics', '报表管理', '报表管理', 185, NULL, 10, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (200, 'qualityResult', '质控结果查询', '质控结果查询', 185, NULL, 8, 1, 1, NULL, 'Qualities/Quality/qualityResult', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (201, 'presetQualityItem', '设备质控项目预设', '设备质控项目预设', 185, NULL, 1, 1, 1, NULL, 'Qualities/Quality/presetQualityItem', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (202, 'addPresetQI', '质控项目设置', '质控项目设置', 185, 201, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (203, 'patrolModuleSetting', '巡查模块配置', '巡查模块配置', 517, NULL, 4, 1, 1, '', 'Patrol/PatModSetting/patrolModuleSetting', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (205, 'exportAssets', '导出主设备', '导出主设备', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (206, 'Benefit', '效益分析', '数据分析', 0, NULL, 9, 1, 1, 'layui-icon-zzline-chart', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (207, 'overhaul', '检修', '检修', 14, 45, 0, 0, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (208, 'assetsBenefitList', '设备收支录入', '设备收支录入列表', 215, NULL, 1, 1, 1, NULL, 'Benefit/Benefit/assetsBenefitList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (209, 'singleBenefitList', '单机数据分析', '单机数据分析列表', 215, NULL, 1, 1, 1, NULL, 'Benefit/Benefit/singleBenefitList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (210, 'departmentBenefitList', '科室数据分析', '科室数据分析列表', 215, NULL, 1, 1, 1, NULL, 'Benefit/Benefit/departmentBenefitList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (214, 'batchAddBenefit', '批量录入收支明细', '批量录入收支明细', 215, 208, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (215, 'Benefit', '数据分析管理', '数据分析管理', 206, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (216, 'exportBenefit', '批量导出收支明细', '批量导出收支明细', 215, 208, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (217, 'Adverse', '不良事件管理', '不良事件管理', 0, NULL, 8, 1, 1, 'layui-icon-zzexclamation-triangle', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (218, 'getAdverseList', '器械不良事件报告', '器械不良事件报告列表', 224, NULL, 1, 1, 1, NULL, 'Adverse/Adverse/getAdverseList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (219, 'addAdverse', '新增不良事件报告', '新增不良事件报告', 224, 218, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (220, 'assetsLifeList', '设备生命历程', '生命历程列表', 6, NULL, 2, 1, 1, NULL, 'Assets/Lookup/assetsLifeList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (222, 'home', '首页', '首页', 0, NULL, 1, 1, 1, 'layui-icon-home', '/', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (224, 'Adverse', '不良事件管理', '不良事件管理', 217, NULL, 5, 1, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (225, 'editAdverse', '编辑不良事件报告', '编辑不良事件报告', 224, 218, 2, 1, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (226, 'Consumables', '低值耗材', '低值耗材', 0, NULL, NULL, 0, 0, 'layui-icon-zchemistry-l', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (227, 'HVConsumables', '高值耗材', '高值耗材', 0, NULL, NULL, 0, 0, 'layui-icon-zzh-square', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (228, 'Statistics', '统计分析', '统计分析', 0, NULL, 10, 1, 1, 'layui-icon-zzbar-chart', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (229, 'getDetectingList', '检测仪器管理', '检测仪器管理列表', 185, NULL, 9, 1, 1, NULL, 'Qualities/Quality/getDetectingList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (230, 'getNoticeList', '公告管理', '系统公告列表', 182, NULL, 7, 1, 1, NULL, 'BaseSetting/Notice/getNoticeList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (231, 'Menu', '菜单管理', '菜单管理', 4, NULL, 7, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (232, 'getMenuLists', '菜单管理', '菜单明细&修改', 231, NULL, 8, 1, 1, NULL, 'BaseSetting/Menu/getMenuLists', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (233, 'checkLists', '转科验收', '转科验收列表', 9, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (234, 'check', '验收', '验收', 9, 233, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (236, 'repairAssign', '维修自动派工', '维修自动派工', 12, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (237, 'Metering', '计量管理', '计量管理', 186, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (238, 'getMeteringList', '计量计划制定', '计量计划制定', 237, NULL, 1, 1, 1, NULL, 'Metering/Metering/getMeteringList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (241, 'Archives', '档案管理', '档案管理', 0, NULL, 13, 1, 1, 'layui-icon-zlayers', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (242, 'addMetering', '新增计量计划', '新增计量计划', 237, 238, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (243, 'batchAddMetering', '批量新增计量计划', '批量新增计量计划', 237, 238, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (244, 'batchSaveMetering', '批量修改计量计划', '批量修改计量计划', 237, 238, 1, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (246, 'Remind', '工作提醒', '工作提醒', 0, NULL, 17, 0, 0, 'layui-icon-note', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (247, 'historyRemindList', '历史提醒列表', '历史提醒列表', 246, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (248, 'historyRemindList', '历史提醒列表', '历史提醒列表', 247, NULL, 1, 1, 1, NULL, 'Remind/Remind/historyRemindList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (249, 'remindSetting', '模块配置', '模块配置', 246, NULL, 3, 1, 0, NULL, 'Remind/Remind/remindSetting', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (250, 'ledRemind', 'LED消息屏', 'LED消息屏', 246, NULL, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (252, 'saveMetering', '编辑', '编辑计量计划', 237, 238, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (253, 'delMetering', '删除', '删除计量计划', 237, 238, 1, 1, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (254, 'getMeteringResult', '计量检定结果', '计量检定结果', 237, NULL, 2, 1, 1, '', 'Metering/Metering/getMeteringResult', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (256, 'Suppliers', '服务商管理（外）', '服务商管理（服务商云平台后台）', 0, NULL, 15, 1, 0, 'layui-icon-zzhandshake-o', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (257, 'Strategy', '决策分析', '决策分析', 0, NULL, 11, 0, 1, 'layui-icon-zzlegal', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (258, 'Train', '培训管理', '培训管理', 0, NULL, 14, 1, 0, 'layui-icon-zzmortar-board', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (259, 'Supplier', '供应商库', '注册供应商库', 256, NULL, 2, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (260, 'Product', '产品&服务管理', '供应商产品&服务管理', 256, NULL, 3, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (261, 'approveInAssets', '待入库设备审核', '供应商中标设备/服务详情信息审核入库', 256, NULL, 1, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (262, 'Statistics', '统计分析', '供应商服务统计分析', 256, NULL, 4, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (264, 'getSuppliersList', '供应商列表', '注册供应商列表', 259, NULL, 1, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (265, 'getLicencesList', '证照列表', '注册供应商证照管理列表', 259, NULL, 3, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (266, 'performanceList', '履约管理', '注册供应商履约管理列表', 259, NULL, 2, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (267, NULL, '推送产品列表', '注册供应商推送待审核产品列表', 260, NULL, 1, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (268, NULL, '设备遴选库', '采购管理的采购计划遴选设备库', 260, NULL, 2, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (269, NULL, '推送维保服务列表', '注册供应商推送待审核维保服务列表', 260, NULL, 3, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (270, NULL, '维保服务遴选库', '维保管理的设备参保计划遴选维保服务库', 260, NULL, 4, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (271, 'supplierDetailInfo', '供应商详情', '供应商详情主页', 259, 264, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (272, 'setMeteringResult', '检测', '计量检测', 237, 254, 1, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (273, 'Purchases', '采购管理', '采购管理', 0, NULL, 2, 1, 1, 'layui-icon-cart', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (282, 'Monitor', '监控管理', '监控管理', 0, NULL, 12, 1, 1, 'layui-icon-zzthermometer-half', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (288, 'Borrow', '借调管理', '借调管理', 1, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (289, 'Outside', '外调管理', '外调管理', 1, NULL, 6, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (290, 'borrowAssetsList', '借调申请', '借调申请设备列表', 288, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (291, 'approveBorrowList', '借调审批', '借调审批列表', 288, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (292, 'borrowLife', '借调进程', '借调进程', 288, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (293, 'borrowInCheck', '借入验收', '借入验收', 288, 322, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (294, 'giveBackCheck', '归还验收', '归还验收', 288, 323, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (295, 'borrowRecordList', '借调记录查询', '借调记录查询', 288, NULL, 6, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (296, NULL, '归还提醒', '归还提醒', 288, NULL, 1, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (297, 'applyBorrow', '借调申请', '借调申请操作', 288, 290, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (298, 'departApproveBorrow', '借调部门审批', '部门审批操作', 288, 291, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (299, 'module', '模块配置', '模块总配置', 20, NULL, 6, 1, 1, NULL, 'BaseSetting/ModuleSetting/module', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (322, 'borrowInCheckList', '借入验收', '借入验收列表', 288, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (323, 'giveBackCheckList', '归还验收', '归还验收列表', 288, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (324, 'getDepartAssetsList', '外调申请', '外调申请设备列表', 289, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (325, 'applyAssetOutSide', '外调申请', '外调申请操作', 289, 324, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (326, 'outSideApproveList', '外调审批', '外调申请审批列表', 289, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (327, 'assetOutSideApprove', '外调审批', '外调审批操作', 289, 326, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (328, 'outSideResultList', '外调结果', '外调结果列表', 289, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (329, 'checkOutSiteAsset', '验收单录入', '外调验收单录入', 289, 328, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (330, 'showOutSideResult', '结果查询', '外调结果明细查看', 289, 328, 1, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (354, 'PurchasePlans', '采购计划', '采购计划', 273, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (355, 'purchasePlansList', '采购计划上报', '采购计划上报列表', 354, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (356, 'departReport', '计划上报', '采购计划上报操作', 354, 355, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (357, 'addPlans', '新增计划', '新增采购计划操作', 354, 355, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (358, 'purPlansAppLists', '采购计划审批', '采购计划审批列表', 354, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (359, 'purchasePlanApprove', '审批', '审批采购计划操作', 354, 358, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (360, 'PurchaseApply', '科室申请', '科室申请', 273, NULL, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (361, 'purchaseApplyList', '科室申请', '科室申请列表', 360, NULL, 1, 1, 1, NULL, 'Purchases/PurchaseApply/purchaseApplyList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (362, 'addPurchaseApply', '新增采购申请', '科室新增采购申请操作', 360, 361, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (363, 'approveApply', '审批', '科室采购审批操作', 360, 361, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (367, 'showPurchaseApply', '科室申请详情', '科室采购申请详情查看', 360, 361, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (369, 'Tendering', '招标论证', '招标管理', 273, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (370, 'expertReviewList', '专家评审', '专家评审列表', 369, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (371, 'expertReview', '专家评审', '专家评审', 369, 370, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (372, 'tenderingBookList', '制定标书', '制定标书列表', 369, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (373, 'addTenderingBook', '制定标书', '制定标书', 369, 372, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (374, 'tbApproveList', '标书评审', '标书评审列表', 369, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (375, 'tbApprove', '标书评审', '标书评审', 369, 374, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (376, 'tbSubmitList', '标书提交', '标书提交列表', 369, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (377, 'tbSubmit', '标书提交', '标书提交', 369, 376, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (378, 'Contract', '合同管理', '合同管理', 273, NULL, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (379, 'contractList', '合同管理', '采购合同列表', 378, NULL, 5, 1, 1, NULL, 'Purchases/Contract/contractList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (380, 'addContract', '生成合同', '生成合同', 378, 379, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (381, 'showContract', '合同详情', '查看合同详情', 378, 379, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (382, 'PurchasePlace', '场地管理', '场地管理', 273, NULL, 6, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (383, 'purchasePlaceList', '场地管理', '场地管理列表', 382, NULL, 6, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (384, 'PurchaseCheck', '安装调试验收管理', '安装调试验收管理', 273, NULL, 7, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (386, 'installDebugList', '安装调试', '设备安装调试报告列表', 384, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (387, 'debugReport', '安装调试', '设备安装调试报告', 384, 386, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (388, 'departTrainList', '临床培训', '临床培训报告列表', 384, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (389, 'trainPlans', '制定计划', '制定设备培训计划', 384, 388, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (390, 'trainExamineList', '培训考核', '培训考核报告列表', 384, NULL, 6, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (391, 'assessReport', '上传报告', '上传培训考核报告', 384, 390, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (392, 'testReportList', '测试运行', '测试运行报告列表', 384, NULL, 7, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (393, 'testReport', '上传报告', '上传测试运行报告', 384, 392, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (394, 'firstMeteringList', '首次计量', '首次计量报告列表', 384, NULL, 8, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (395, 'firstMetering', '上传计量报告', '上传计量报告', 384, 394, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (396, 'PurchaseInvoice', '发票管理', '发票管理', 273, NULL, 8, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (397, 'purchaseInvoiceList', '发票管理', '发票管理列表', 396, NULL, 7, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (398, 'PurchasePayment', '付款管理', '付款管理', 378, NULL, 9, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (399, 'purchasePaymentList', '付款管理', '付款管理列表', 378, NULL, 8, 1, 1, NULL, 'Purchases/Contract/purchasePaymentList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (400, 'tenderingResultList', '项目结果', '项目结果列表', 369, NULL, 6, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (402, 'PurchaseLife', '设备采购进程', '设备采购进程', 273, NULL, 10, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (403, 'inquiryPricesList', '询价记录', '询价列表', 369, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (404, 'inquiryPrices', '登记询价', '登记询价', 369, 403, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (405, 'assetsApproveBorrow', '设备科审批', '设备科审批操作', 288, 291, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (406, 'Dictionary', '字典管理', '字典管理', 4, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (407, 'assetsDic', '设备字典', '设备字典', 406, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (408, 'addAssetsDic', '新增设备字典', '新增设备字典', 406, 407, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (409, 'editAssetsDic', '修改设备字典', '修改字典', 406, 407, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (410, 'delAssetsDic', '删除设备字典', '删除字典', 406, 407, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (411, 'StatisAdverse', '不良事件报表管理', '不良事件报表管理', 228, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (412, 'adverseAnalysis', '不良事件报表分析', '不良事件报表分析', 411, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (413, 'StatisQuality', '质控计划报表管理', '质控计划报表管理', 228, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (414, 'qualityAnalysis', '质控计划报表分析', '质控计划报表分析', 413, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (415, 'OfflineSuppliers', '厂商管理（内）', '厂商管理', 453, NULL, 1, 1, 0, '', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (416, 'offlineSuppliersList', '厂商列表', '厂商列表', 415, NULL, 1, 1, 1, NULL, 'OfflineSuppliers/OfflineSuppliers/offlineSuppliersList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (417, 'addOfflineSupplier', '新增厂商', '新增厂商', 415, 416, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (418, 'editOfflineSupplier', '维护厂商信息', '维护厂商信息', 415, 416, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (419, 'olsContract', '合同管理', '合同管理', 415, NULL, 2, 1, 1, NULL, 'OfflineSuppliers/OfflineSuppliers/olsContract', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (420, 'payOLSContractList', '合同付款管理', '合同付款管理列表', 415, NULL, 3, 1, 1, NULL, 'OfflineSuppliers/OfflineSuppliers/payOLSContractList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (421, 'olsInvoice', '发票管理', '发票管理', 415, NULL, 4, 0, 1, NULL, 'OfflineSuppliers/OfflineSuppliers/olsInvoice', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (422, '\r\nsupplierStatistical', '统计分析', '统计分析', 415, NULL, 5, 0, 1, NULL, 'OfflineSuppliers/OfflineSuppliers/supplierStatistical', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (423, 'StatisRepair', '维修报表管理', '维修报表管理', 228, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (424, 'repairFeeStatis', '维修费用统计', '维修费用统计', 423, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (425, 'repairAnalysis', '维修费用分析', '维修费用分析', 423, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (426, 'engineerCompar', '工程师工作量对比', '工程师工作量对比', 423, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (427, 'engineerEva', '工程师评价对比', '工程师评价对比', 423, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (428, 'repairFeeTrend', '维修设备趋势分析', '维修设备趋势分析', 423, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (430, 'TenderRecord', '招标记录', '招标记录', 273, NULL, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (431, 'tenderRecordList', '招标记录', '招标记录列表', 430, NULL, 4, 1, 1, NULL, 'Purchases/TenderRecord/tenderRecordList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (432, 'handleTender', '处理', '处理招标', 430, 431, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (433, 'showTender', '查看', '查看招标', 430, 431, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (434, 'Warehouse', '仓库管理', '仓库管理', 0, NULL, 18, 0, 0, 'layui-icon-app', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (435, 'checkAssetsLists', '设备验收', '设备验收列表', 384, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (437, 'emergencyWH', '应急设备库管理', '应急设备库管理', 434, NULL, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (438, 'RepairWH', '维修备件库管理', '维修备件库管理', 434, NULL, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (439, 'showCheck', '验收详情', '验收详情', 384, 435, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (440, 'checkAssets', '验收设备', '验收设备操作', 384, 435, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (441, 'assetsWareLists', '设备入库', '设备入库列表', 384, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (442, 'assetsOutLists', '设备出库', '设备出库列表', 384, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (443, 'purchaseLifeList', '设备采购进程', '设备采购进程列表', 402, NULL, 9, 1, 1, NULL, 'Purchases/PurchaseLife/purchaseLifeList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (444, 'addWare', '新增入库单', '新增入库单操作', 384, 441, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (445, 'approveWare', '审核', '设备入库审核', 384, 441, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (446, 'addOut', '新增出库单', '新增出库单操作', 384, 442, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (447, 'approveOut', '审核', '设备出库审核', 384, 442, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (449, 'qualityReportLists', '质量验收', '设备质量验收列表', 384, NULL, 9, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (450, 'trainReport', '上传培训报告', '上传培训报告', 384, 388, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (452, 'qualityReport', '上传质量报告', '上传质量验收报告', 384, 449, 9, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (453, 'OfflineSuppliers', '厂商管理（内）', '厂商管理', 0, NULL, 15, 1, 1, 'layui-icon-zpeople', '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (455, 'addOLSContract', '新增合同', '新增合同', 415, 419, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (456, 'editOLSContract', '编辑合同', '编辑合同', 415, 419, 1, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (457, 'emergencyWH', '应急设备库列表', '应急设备库列表', 437, 437, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (458, 'emergencyBorrow', '设备借用登记', '应急设备借用登记', 437, 437, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (459, 'emergencyBack', '设备归还管理', '应急设备归还管理', 437, 437, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (460, 'emergencyBorroInfo', '借用详情与汇总', '借用详情与汇总', 437, 437, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (463, NULL, '临床培训', '临床培训列表', 258, NULL, 1, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (464, NULL, '再培训', '再培训列表', 258, NULL, 2, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (465, NULL, '培训考核', '培训考核列表', 258, NULL, 3, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (466, NULL, '人员证书管理', '人员证书管理', 258, NULL, 4, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (467, NULL, '法规库', '法规库', 258, NULL, 5, 0, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (472, 'partsDic', '配件字典', '配件字典', 406, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (473, 'addPartsDic', '新增配件字典', '新增配件字典', 406, 472, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (474, 'editPartsDic', '修改配件字典', '修改配件字典', 406, 472, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (475, 'delPartsDic', '删除配件字典', '删除配件字典', 406, 472, 3, 1, 0, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (476, 'confirmOLSContract', '确认合同', '确认合同', 415, 419, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (477, 'brandDic', '品牌字典', '品牌字典', 406, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (478, 'addBrandDic', '新增品牌', '新增品牌', 406, 477, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (479, 'editBrandDic', '修改品牌', '修改品牌', 406, 477, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (480, 'delBrandDic', '删除品牌', '删除品牌', 406, 477, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (481, 'RepairParts', '配件管理', '配件管理', 2, NULL, 3, 1, 1, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (482, 'partsInWareList', '配件入库', '配件入库列表', 481, NULL, 1, 1, 1, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (483, 'partsOutWareList', '配件出库', '配件出库列表', 481, NULL, 2, 1, 1, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (484, 'partStockList', '配件库存', '配件库存', 481, NULL, 3, 1, 1, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (486, 'partsInWare', '配件入库', '配件入库操作', 481, 482, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (487, 'partsOutWare', '配件出库', '配件出库操作', 481, 483, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (489, 'StatisPurchases', '采购报表管理', '采购报表管理', 228, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (490, 'purFeeStatis', '采购费用统计', '采购费用统计', 489, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (491, 'purAnalysis', '采购费用分析', '采购费用分析', 489, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (492, 'payOLSContract', '合同付款', '合同付款操作', 415, 420, 4, 1, 0, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (493, 'Subsidiary', '附属设备分配管理', '附属设备分配管理', 1, NULL, 9, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (494, 'subsidiaryAllotList', '分配申请', '分配申请列表', 493, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (495, 'subsidiaryAllot', '分配申请', '分配申请操作', 493, 494, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (496, 'subsidiaryApproveList', '分配审批', '分配审批列表', 493, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (497, 'subsidiaryApprove', '分配审批', '分配审批操作', 493, 496, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (498, 'subsidiaryCheckList', '分配验收', '分配列表', 493, NULL, 5, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (499, 'subsidiaryCheck', '分配验收', '分配验收操作', 493, 498, 6, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (500, 'batchAddAssetsDic', '批量添加设备字典', '批量添加设备字典', 406, 407, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (501, 'Print', '设备打印', '设备打印', 1, NULL, 8, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (502, 'design', '标签设计', '标签设计', 501, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (503, 'printAssets', '标签打印', '标签打印', 501, NULL, 2, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (504, 'SmsModule', '短信配置管理', '短信配置管理', 4, NULL, 6, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (505, 'smsSetting', '短信配置', '短信配置', 504, NULL, 6, 1, 1, NULL, 'BaseSetting/SmsModule/smsSetting', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (506, 'delSupplier', '删除厂商', '删除厂商', 415, 416, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (507, 'QualityStatis', '质控报表管理', '质控报表管理', 180, NULL, 10, 1, 1, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (508, 'qualityDepartStatistics', '科室质控报表', '科室质控报表', 507, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (509, 'resultAnalysis', '质控结果报表分析', '质控结果报表分析', 413, NULL, 1, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (510, 'exportDepartStatistics', '导出报表', '导出报表', 507, NULL, 1, 1, 0, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (511, 'feedbackQuality', '接收每日质控反馈', '接收每日质控反馈', 185, NULL, 1, 1, 0, '', '', '', 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (512, 'editQualityDetail', '修改明细', '修改明细', 185, 197, 8, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (513, 'ExamApp', '审查批准', '审查批准', 4, NULL, 9, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (514, 'getExamLists', '审查批准', '审查批准', 513, NULL, 1, 1, 1, NULL, 'BaseSetting/ExamApp/getExamLists', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (515, 'passno', '同意/驳回', '同意/驳回', 513, 514, 1, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (516, 'PatrolRecords', '设备保养记录查询', '设备保养记录查询', 3, NULL, 1, 1, 0, NULL, '', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (517, 'PatModSetting', '巡查模块配置', '巡查模块配置', 3, NULL, 5, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (518, 'patrolApprove', '巡查计划审核', '巡查计划审核', 15, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (520, 'approve', '审核', '审核', 15, 518, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (521, 'getPatrolRecords', '设备保养记录查询', '设备保养记录查询', 516, NULL, 1, 1, 1, NULL, 'Patrol/PatrolRecords/getPatrolRecords', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (522, 'printReports', '批量打印保养报告', '批量打印保养报告', 516, 521, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (523, 'exportReports', '导出报告', '批量导出', 516, 521, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (524, 'batchAddUser', '批量添加用户', '批量添加用户', 21, 63, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (526, 'showAssetsPrice', '显示设备原值', '显示设备原值', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (530, 'uploadautograph', '上传用户签名', '上传用户签名', 21, NULL, 6, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (531, 'Emergency', '应急预案\r\n应急预案管理', '应急预案案管理', 241, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (532, 'emergencyPlanList', '应急预案', '应急预案列表', 531, 531, 1, 1, 1, NULL, 'Archives/Emergency/emergencyPlanList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (533, 'addEmergency', '添加应急预案', '添加应急预案', 531, 531, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (534, 'editEmergency', '修改应急预案', '修改应急预案', 531, 531, 4, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (535, 'delEmergency', '删除应急预案', '删除应急预案', 531, 531, 3, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (536, 'Monitor', '监控', '监控', 282, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (537, 'Stramonitor', '全院监护仪概况', '全院监护仪概况', 257, NULL, 1, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (538, 'verify', '标签核实', '标签核实', 501, NULL, 3, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (539, 'monitorsur', '全院监护仪概况', '全院监护仪概况', 537, 537, 1, 1, 1, NULL, 'Strategy/Stramonitor/monitorsur', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (540, 'Box', '档案盒管理', '档案盒管理', 241, NULL, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (541, 'boxList', '档案盒管理', '档案盒管理', 540, 531, 1, 1, 1, NULL, 'Archives/Box/boxList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (542, 'printBox', '档案标签打印', '档案标签打印', 501, NULL, 4, 1, 1, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (543, 'addBox', '添加档案盒', '添加档案盒', 540, 541, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (544, 'editBox', '编辑档案盒', '编辑档案盒', 540, 541, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (545, 'showAssetsfile', '显示设备档案', '显示设备档案', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (546, 'showTechnicalInformation', '显示技术资料', '显示技术资料', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (547, 'showAssetsInsurance', '显示设备参保', '显示设备参保', 6, 28, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (548, 'showUserPhone', '显示用户手机号码', '显示用户手机号码', 21, 63, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (549, 'cancelRepair', '撤单', '撤单', 13, 53, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (550, 'getMeteringHistory', '计量记录查询', '计量记录查询', 237, NULL, 3, 1, 1, NULL, 'Metering/Metering/getMeteringHistory', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (551, 'batchSetMeteringResult', '批量添加计量记录', '批量添加计量记录', 237, 254, 2, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (552, 'labelCheck', '核实', '核实', 501, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (553, 'auditInventoryPlanApprove', '盘点计划审核', '盘点计划审核', 561, 554, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (554, 'inventoryPlanApproveList', '盘点计划审批列表', '盘点计划审批列表', 561, NULL, 1, 1, 1, NULL, 'Inventory/InventoryPlan/inventoryPlanApproveList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (555, 'saveOrEndInventoryPlan', '暂存|结束 盘点', '暂存|结束 盘点', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (556, 'batchReleaseInventoryPlan', '发布', '批量发布盘点计划', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (557, 'delInventoryPlan', '删除盘点计划', '删除盘点计划', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (558, 'batchDelInventoryPlan', '批量删除', '批量删除盘点计划', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (559, 'editInventoryPlan', '编辑盘点计划', '编辑盘点计划', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (560, 'showInventoryPlan', '盘点计划详情', '盘点计划详情', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (561, 'InventoryPlan', '盘点管理', '盘点管理', 564, NULL, 2, 1, 0, '', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (562, 'addInventoryPlan', '新增盘点计划', '新增盘点计划', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (563, 'inventoryPlanList', '盘点计划列表', '盘点计划列表', 561, NULL, 1, 1, 1, NULL, 'Inventory/InventoryPlan/inventoryPlanList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (564, 'Inventory', '盘点管理', '盘点管理', 0, NULL, 2, 1, 1, 'layui-icon-component', NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (565, 'Interface', '接口日志', '接口日志', 4, NULL, 1, 1, 0, NULL, NULL, NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (566, 'getInterfaceList', '接口日志', '接口日志列表', 21, NULL, 1, 1, 1, NULL, 'BaseSetting/Interface/getInterfaceList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (567, 'getMonitorList', '实时监控', '实时监控', 282, NULL, 1, 1, 1, NULL, 'Monitor/Monitor/getMonitorList', NULL, 0);
INSERT INTO `sb_menu` (`menuid`, `name`, `title`, `BaseSettingTitle`, `parentid`, `op_parentid`, `orderID`, `status`, `leftShow`, `icon`, `jump`, `tips`, `menuNotShow`) VALUES (568, 'dealInventoryAsset', '盘点手工处理', '盘点手工处理', 561, 563, 1, 1, 0, NULL, NULL, NULL, 0);
COMMIT;

-- ----------------------------
-- Table structure for sb_metering_categorys
-- ----------------------------
DROP TABLE IF EXISTS `sb_metering_categorys`;
CREATE TABLE `sb_metering_categorys` (
  `mcid` int(11) NOT NULL AUTO_INCREMENT,
  `mcategory` varchar(100) NOT NULL DEFAULT '' COMMENT '''计量分类名称''',
  PRIMARY KEY (`mcid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='计量管理——计量分类表';

-- ----------------------------
-- Records of sb_metering_categorys
-- ----------------------------


-- ----------------------------
-- Table structure for sb_metering_plan
-- ----------------------------
DROP TABLE IF EXISTS `sb_metering_plan`;
CREATE TABLE `sb_metering_plan` (
  `mpid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `plan_num` varchar(100) NOT NULL COMMENT '计划编号',
  `assid` int(11) DEFAULT NULL COMMENT '设备ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `assets` varchar(255) DEFAULT NULL COMMENT '设备名称',
  `model` varchar(255) DEFAULT NULL COMMENT '设备规格型号',
  `unit` varchar(50) DEFAULT NULL COMMENT '单位',
  `factory` varchar(255) DEFAULT NULL COMMENT '生产厂商',
  `productid` varchar(100) DEFAULT NULL COMMENT '产品序列号，多个时再多一条数据',
  `departid` int(11) NOT NULL COMMENT '所属科室ID',
  `asset_count` int(2) DEFAULT '1' COMMENT '设备数量',
  `mcid` int(11) NOT NULL COMMENT '计量分类id',
  `cycle` tinyint(2) DEFAULT NULL COMMENT '计量周期',
  `test_way` tinyint(1) NOT NULL COMMENT '检定方式',
  `next_date` date NOT NULL COMMENT '下次待检日期',
  `respo_user` varchar(100) DEFAULT NULL COMMENT '计量负责人',
  `remind_day` int(11) NOT NULL COMMENT '提前提醒天数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计划状态,1启用0暂停-1删除',
  `remark` text COMMENT '备注',
  `adduseid` int(11) NOT NULL COMMENT '计划制定者ID',
  `adddate` date NOT NULL COMMENT '计划制定日期',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `edit_uuid` int(11) DEFAULT NULL COMMENT '修改人',
  PRIMARY KEY (`mpid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='计量管理——计量计划表';

-- ----------------------------
-- Records of sb_metering_plan
-- ----------------------------


-- ----------------------------
-- Table structure for sb_metering_plan_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_metering_plan_upload_temp`;
CREATE TABLE `sb_metering_plan_upload_temp` (
  `tempid` varchar(32) NOT NULL DEFAULT '' COMMENT '自增ID',
  `assid` int(11) DEFAULT NULL COMMENT '设备ID',
  `assets` varchar(255) DEFAULT NULL COMMENT '设备名称',
  `model` varchar(255) DEFAULT NULL COMMENT '设备规格型号',
  `unit` varchar(50) DEFAULT NULL COMMENT '单位',
  `factory` varchar(255) DEFAULT NULL COMMENT '生产厂商',
  `productid` varchar(100) DEFAULT NULL COMMENT '产品序列号，多个时再多一条数据',
  `department` varchar(255) DEFAULT '' COMMENT '科室名称',
  `departid` int(11) NOT NULL COMMENT '所属科室ID',
  `asset_count` int(2) DEFAULT '1' COMMENT '设备数量',
  `mcid` int(11) NOT NULL COMMENT '计量分类id',
  `mcategory` varchar(100) DEFAULT NULL COMMENT '''计量分类名称''',
  `cycle` tinyint(2) DEFAULT NULL COMMENT '计量周期',
  `test_way` varchar(25) NOT NULL DEFAULT '院内' COMMENT '检定方式',
  `next_date` date DEFAULT NULL COMMENT '下次待检日期',
  `respo_user` varchar(100) DEFAULT NULL COMMENT '负责人姓名',
  `remind_day` int(11) NOT NULL COMMENT '提前提醒天数',
  `status` varchar(25) NOT NULL DEFAULT '启用' COMMENT '计划状态,1启用0暂停',
  `remark` text COMMENT '备注',
  `adduseid` int(11) NOT NULL COMMENT '计划制定者ID',
  `adddate` int(10) NOT NULL COMMENT '计划制定日期',
  `is_save` tinyint(1) DEFAULT '0' COMMENT '是否已入库【1已入库0未入】',
  `edituserid` int(11) DEFAULT NULL COMMENT '编辑用户id',
  `editdate` int(11) DEFAULT NULL COMMENT '编辑时间',
  PRIMARY KEY (`tempid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='计量管理——计量计划批量上传临时表';

-- ----------------------------
-- Records of sb_metering_plan_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_metering_result
-- ----------------------------
DROP TABLE IF EXISTS `sb_metering_result`;
CREATE TABLE `sb_metering_result` (
  `mrid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mpid` int(11) DEFAULT NULL COMMENT '计划id',
  `this_date` date NOT NULL COMMENT '本次检定日期',
  `report_num` varchar(100) NOT NULL COMMENT '证书编号',
  `result` tinyint(1) NOT NULL DEFAULT '1' COMMENT '检定结果0不合格1合格',
  `company` varchar(255) DEFAULT NULL COMMENT '检定机构',
  `money` varchar(255) DEFAULT NULL COMMENT '计量费用',
  `test_person` varchar(100) DEFAULT NULL COMMENT '检定人',
  `auditor` varchar(100) DEFAULT NULL COMMENT '审核人',
  `remark` text COMMENT '结果备注',
  `adduserid` int(11) NOT NULL COMMENT '计量结果录入人员ID',
  `adddate` date NOT NULL COMMENT '计量结果录入日期',
  `status` tinyint(1) DEFAULT '0' COMMENT '检查状态 1=执行 0未执行',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `edit_uuid` int(11) DEFAULT NULL COMMENT '修改人',
  PRIMARY KEY (`mrid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='计量管理——计量结果';

-- ----------------------------
-- Records of sb_metering_result
-- ----------------------------


-- ----------------------------
-- Table structure for sb_metering_result_reports
-- ----------------------------
DROP TABLE IF EXISTS `sb_metering_result_reports`;
CREATE TABLE `sb_metering_result_reports` (
  `mreid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mrid` int(11) NOT NULL COMMENT '计量结果自增ID',
  `name` varchar(255) NOT NULL COMMENT '报告名称',
  `url` varchar(255) NOT NULL COMMENT '报告地址',
  `adduserid` int(11) NOT NULL COMMENT '提交人ID',
  `adddate` date NOT NULL COMMENT '提交日期',
  PRIMARY KEY (`mreid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='计量管理——计量结果报告表';

-- ----------------------------
-- Records of sb_metering_result_reports
-- ----------------------------


-- ----------------------------
-- Table structure for sb_metering_result_upload_temp
-- ----------------------------
DROP TABLE IF EXISTS `sb_metering_result_upload_temp`;
CREATE TABLE `sb_metering_result_upload_temp` (
  `temp_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '临时ID',
  `assid` int(11) NOT NULL COMMENT '设备id',
  `hospital_id` int(11) NOT NULL COMMENT '医院id',
  `departid` int(11) NOT NULL COMMENT '科室id',
  `assets` varchar(100) DEFAULT NULL COMMENT '设备名称',
  `assnum` varchar(100) NOT NULL COMMENT '设备编码',
  `model` varchar(100) DEFAULT NULL COMMENT '设备型号',
  `unit` varchar(50) DEFAULT NULL COMMENT '单位',
  `factory` varchar(120) DEFAULT NULL COMMENT '生产厂商',
  `productid` varchar(100) DEFAULT NULL COMMENT '产品序列号',
  `cate` varchar(100) DEFAULT NULL COMMENT '计量分类',
  `this_date` date NOT NULL COMMENT '检定日期',
  `test_way` tinyint(3) DEFAULT '1' COMMENT '检定方式【0=院外，1=院内】',
  `report_num` varchar(100) NOT NULL COMMENT '证书编号',
  `result` tinyint(3) DEFAULT '1' COMMENT '检定结果【0=不合格，1=合格】',
  `company` varchar(120) DEFAULT NULL COMMENT '检定机构',
  `money` decimal(10,2) DEFAULT NULL COMMENT '计量费用',
  `test_person` varchar(60) DEFAULT NULL COMMENT '检定人',
  `auditor` varchar(60) DEFAULT NULL COMMENT '审核人',
  `remark` varchar(255) DEFAULT NULL COMMENT '结果备注',
  `file_name` varchar(255) DEFAULT NULL COMMENT '计量附件',
  `is_save` tinyint(3) DEFAULT '0' COMMENT '是否保存【0=否，1=是】',
  `add_userid` int(11) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_userid` int(11) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`temp_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='计量管理——批量添加计量记录';

-- ----------------------------
-- Records of sb_metering_result_upload_temp
-- ----------------------------


-- ----------------------------
-- Table structure for sb_module
-- ----------------------------
DROP TABLE IF EXISTS `sb_module`;
CREATE TABLE `sb_module` (
  `moduleid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '模块目录',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '模块名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '模块状态，0为显示，1为不显示',
  `orderid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '排序',
  `url` varchar(255) DEFAULT NULL COMMENT '访问地址（模块首页流程面板）',
  `icon_style` varchar(100) DEFAULT '' COMMENT '图标样式',
  PRIMARY KEY (`moduleid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——系统模块表';

-- ----------------------------
-- Records of sb_module
-- ----------------------------
BEGIN;
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (1, 'HomePage', '首页', 1, 1, '/index.php/HomePage.html', 'glyphicon glyphicon-home');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (2, 'Assets', '资产管理', 1, 2, NULL, 'glyphicon glyphicon-inbox');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (3, 'Repair', '维修管理', 1, 3, NULL, 'glyphicon glyphicon-wrench');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (4, 'Patrol', '巡查保养管理', 1, 4, NULL, 'glyphicon glyphicon-retweet');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (5, 'Quality', '质控管理', 1, 5, NULL, 'glyphicon glyphicon-alert');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (6, 'Metering', '计量管理', 1, 6, NULL, 'glyphicon glyphicon-eye-open');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (7, 'Consumables', '普通耗材', 1, 7, NULL, '');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (8, 'HVconsumables', '高值耗材', 1, 8, NULL, 'glyphicon glyphicon-briefcase');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (9, 'Purchase', '设备申购', 1, 9, NULL, 'glyphicon glyphicon-shopping-cart');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (10, 'Benefit', '效益分析', 1, 10, NULL, 'glyphicon glyphicon-stats');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (11, 'Report', '报表管理', 1, 11, NULL, 'glyphicon glyphicon-equalizer');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (12, 'Remind', '工作提醒', 1, 12, NULL, 'glyphicon glyphicon-time');
INSERT INTO `sb_module` (`moduleid`, `module`, `name`, `status`, `orderid`, `url`, `icon_style`) VALUES (13, 'BaseSetting', '基础设置', 1, 13, NULL, 'glyphicon glyphicon-cog');
COMMIT;

-- ----------------------------
-- Table structure for sb_monitor
-- ----------------------------
DROP TABLE IF EXISTS `sb_monitor`;
CREATE TABLE `sb_monitor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `assid` int(11) NOT NULL COMMENT '设备表sb_assets_info的外键',
  `powerOnTime` timestamp NULL DEFAULT NULL COMMENT '最近开机时间',
  `useTotalTime` int(11) DEFAULT NULL COMMENT '监控总时长(单位：秒)',
  `powerOnTotals` int(11) DEFAULT NULL COMMENT '开机次数',
  `runningDuration` int(11) DEFAULT NULL COMMENT '开机总时长(单位:豪秒)',
  `useRate` varchar(255) DEFAULT NULL COMMENT '开机率(单位:百分比)',
  `realRunningTotals` int(11) DEFAULT NULL COMMENT '运行总次数',
  `realRunningDuration` varchar(255) DEFAULT NULL COMMENT '运行总时长(单位:豪秒)',
  `runUseRate` varchar(255) DEFAULT NULL COMMENT '使用率(单位:百分比)',
  `powerOnUtilizeRate` varchar(255) DEFAULT NULL COMMENT '开机利用率(单位:百分比) ',
  `exposureNum` int(11) DEFAULT NULL COMMENT '激发次数',
  `exposureTime` int(11) DEFAULT NULL COMMENT '激发时长(单位:豪秒)',
  `energyWork` varchar(255) DEFAULT NULL COMMENT '能耗',
  `isOnline` tinyint(1) DEFAULT NULL COMMENT '在线状态【0在线，1不在线】',
  `isAvailable` tinyint(1) DEFAULT NULL COMMENT '是否可用【0可用，1故障】',
  `status` tinyint(1) DEFAULT NULL COMMENT '使用状态【0开机，1关机】',
  `evaluate` tinyint(1) DEFAULT NULL COMMENT '评价【0优，1良，2一般，3差】',
  `place` varchar(255) DEFAULT NULL COMMENT '位置',
  `did` varchar(255) DEFAULT NULL COMMENT '物联网系统设备的唯一id',
  `create_at` timestamp NULL DEFAULT NULL COMMENT '新增时间',
  `update_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='监控管理-实时监控表';

-- ----------------------------
-- Records of sb_monitor
-- ----------------------------


-- ----------------------------
-- Table structure for sb_notice
-- ----------------------------
DROP TABLE IF EXISTS `sb_notice`;
CREATE TABLE `sb_notice` (
  `notid` int(11) NOT NULL AUTO_INCREMENT COMMENT '公告自增ID',
  `title` varchar(255) NOT NULL COMMENT '公告标题',
  `content` text NOT NULL COMMENT '公告内容',
  `adduser` varchar(100) NOT NULL COMMENT '添加者',
  `adduserid` int(11) NOT NULL COMMENT '添加者ID',
  `adddate` timestamp NULL DEFAULT NULL COMMENT '添加日期',
  `top` tinyint(1) DEFAULT '0' COMMENT '是否置顶【1是，0否】',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `send_user_id` text NOT NULL COMMENT '发送的用户id(包括能否查看上传文件下载文件的权限【仅公告是这样】)',
  PRIMARY KEY (`notid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——公告栏目';

-- ----------------------------
-- Records of sb_notice
-- ----------------------------


-- ----------------------------
-- Table structure for sb_notice_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_notice_file`;
CREATE TABLE `sb_notice_file` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `notid` int(10) NOT NULL COMMENT '公告ID另外一个表',
  `file_name` varchar(255) NOT NULL COMMENT '文件名称',
  `save_name` varchar(255) NOT NULL COMMENT '文件保存名称',
  `file_type` varchar(10) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,0) DEFAULT '0' COMMENT '文件大小',
  `file_url` varchar(255) NOT NULL COMMENT '文件地址',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `add_user` varchar(30) NOT NULL DEFAULT '0' COMMENT '添加人',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1是0否】',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——系统公告文件';

-- ----------------------------
-- Records of sb_notice_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_offline_suppliers
-- ----------------------------
DROP TABLE IF EXISTS `sb_offline_suppliers`;
CREATE TABLE `sb_offline_suppliers` (
  `olsid` int(11) NOT NULL AUTO_INCREMENT COMMENT '厂商ID',
  `sup_name` varchar(150) DEFAULT '' COMMENT '厂商名称',
  `sup_num` varchar(50) DEFAULT NULL COMMENT '厂商编号',
  `ECC_code` varchar(150) DEFAULT '' COMMENT '统一社会信用代码',
  `sup_abbr` varchar(150) DEFAULT '' COMMENT '厂商简称',
  `is_supplier` tinyint(1) DEFAULT '0' COMMENT '供应商 【1是，0否】',
  `is_manufacturer` tinyint(1) DEFAULT '0' COMMENT '生产商 【1是，0否】',
  `is_repair` tinyint(1) DEFAULT '0' COMMENT '维修商 【1是，0否】',
  `is_insurance` tinyint(1) DEFAULT '0' COMMENT '维保商 【1是，0否】',
  `salesman_name` varchar(100) DEFAULT '' COMMENT '业务员',
  `salesman_phone` varchar(100) DEFAULT '' COMMENT '业务员联系电话',
  `artisan_name` varchar(100) DEFAULT NULL COMMENT '技术人员',
  `artisan_phone` varchar(100) DEFAULT '' COMMENT '技术人员联系电话',
  `fax_number` varchar(100) DEFAULT NULL COMMENT '传真号码',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `provinces` int(10) DEFAULT NULL COMMENT '省',
  `city` int(10) DEFAULT NULL COMMENT '市',
  `areas` int(10) DEFAULT NULL COMMENT '区/城镇',
  `address` text COMMENT '详细地址',
  `break` text COMMENT '备注',
  `adduser` varchar(100) DEFAULT NULL COMMENT '添加用户',
  `adddate` int(10) DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(100) DEFAULT NULL COMMENT '修改用户',
  `editdate` int(10) DEFAULT NULL COMMENT '修改时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1 启用 0 停用',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删【0否1是】',
  PRIMARY KEY (`olsid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='厂商管理（内）——厂商信息表';

-- ----------------------------
-- Records of sb_offline_suppliers
-- ----------------------------


-- ----------------------------
-- Table structure for sb_offline_suppliers_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_offline_suppliers_file`;
CREATE TABLE `sb_offline_suppliers_file` (
  `fileid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `olsid` int(11) NOT NULL COMMENT '厂商id',
  `name` varchar(255) NOT NULL COMMENT '报告名称',
  `ext` varchar(100) DEFAULT '' COMMENT '文件后缀',
  `url` varchar(255) NOT NULL COMMENT '报告地址',
  `adduser` varchar(100) NOT NULL COMMENT '提交人用户',
  `adddate` int(10) NOT NULL COMMENT '提交日期',
  `edituser` varchar(100) DEFAULT NULL COMMENT '修改用户',
  `editdate` int(10) DEFAULT NULL COMMENT '修改时间',
  `record_date` int(10) DEFAULT NULL COMMENT '发证(备案)日期',
  `term_date` int(10) DEFAULT NULL COMMENT '有效期限',
  `type` tinyint(5) DEFAULT NULL COMMENT '文件种类 【0：营业执照，1：医疗器械经营许可证，2：第二类医疗器械经营备案凭证，3：医疗器械生产许可证，4：第一类医疗器械生产备案凭证，5：授权信息	】',
  PRIMARY KEY (`fileid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='厂商管理——证件信息表';

-- ----------------------------
-- Records of sb_offline_suppliers_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `sb_operation_log`;
CREATE TABLE `sb_operation_log` (
  `logid` int(10) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `username` varchar(30) NOT NULL COMMENT '操作人',
  `module` varchar(50) NOT NULL COMMENT '模块名称：如：assets、repair、patrol、inventory(盘点)、role、user、modset',
  `controller` varchar(50) DEFAULT NULL COMMENT '控制器名称',
  `action` varchar(20) NOT NULL COMMENT '事件：add、update、delete、approve、print、upload、download、scan、login、logout',
  `actionid` int(10) DEFAULT NULL COMMENT '事件ID，如：记录被操作的事件ID，如：设备ID、维修单ID、盘点ID、巡查ID、角色组ID、用户ID、setid',
  `table` varchar(50) DEFAULT NULL COMMENT '操作表',
  `sql` text COMMENT 'sql语句',
  `ip` varchar(15) NOT NULL COMMENT 'ip地址',
  `remark` text COMMENT '提示语',
  `action_time` timestamp NULL DEFAULT NULL COMMENT '事件操作时间',
  PRIMARY KEY (`logid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——用户行为日志表';

-- ----------------------------
-- Records of sb_operation_log
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_contract
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_contract`;
CREATE TABLE `sb_parts_contract` (
  `contract_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '合同自增ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `inwareid` varchar(100) DEFAULT NULL COMMENT '入库记录ID',
  `contract_num` varchar(100) DEFAULT NULL COMMENT '合同编号',
  `contract_name` varchar(100) DEFAULT NULL COMMENT '合同名称',
  `supplier_id` tinyint(3) DEFAULT NULL COMMENT '供货商ID',
  `supplier_name` varchar(100) DEFAULT NULL COMMENT '供货商名称',
  `contract_type` tinyint(1) DEFAULT '5' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_contacts` varchar(60) DEFAULT NULL COMMENT '供货商联系人',
  `supplier_phone` varchar(30) DEFAULT NULL COMMENT '供货商联系电话',
  `sign_date` date DEFAULT NULL COMMENT '签订日期',
  `end_date` date DEFAULT NULL COMMENT '合同截止日期',
  `guarantee_date` date DEFAULT NULL COMMENT '合同设备保修截止日期',
  `contract_amount` decimal(10,2) DEFAULT NULL COMMENT '合同金额',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `archives_num` varchar(100) DEFAULT NULL COMMENT '档案编号',
  `archives_manager` varchar(60) DEFAULT NULL COMMENT '档案管理人员',
  `hospital_manager` varchar(60) DEFAULT NULL COMMENT '院方负责人',
  `contract_content` text COMMENT '合同内容',
  `add_user` varchar(150) DEFAULT NULL COMMENT '添加人员',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(150) DEFAULT NULL COMMENT '编辑人员',
  `edit_time` datetime DEFAULT NULL COMMENT '编辑时间',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `confirm_user` varchar(150) DEFAULT NULL COMMENT '确认人员',
  `confirmdate` datetime DEFAULT NULL COMMENT '确认时间',
  `is_confirm` tinyint(1) DEFAULT '0' COMMENT '已确认【0否1是】',
  PRIMARY KEY (`contract_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件合同';

-- ----------------------------
-- Records of sb_parts_contract
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_contract_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_contract_file`;
CREATE TABLE `sb_parts_contract_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件合同附件';

-- ----------------------------
-- Records of sb_parts_contract_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_contract_pay
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_contract_pay`;
CREATE TABLE `sb_parts_contract_pay` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `pay_period` int(11) DEFAULT NULL COMMENT '付款期数',
  `estimate_pay_date` date DEFAULT NULL COMMENT '预计付款日期',
  `real_pay_date` date DEFAULT NULL COMMENT '实际付款日期',
  `pay_amount` decimal(10,2) DEFAULT NULL COMMENT '付款金额',
  `pay_status` tinyint(3) DEFAULT '0' COMMENT '付款状态【0未付款，1已付款】',
  `pay_user` varchar(60) DEFAULT NULL COMMENT '付款人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `supplier_id` int(10) DEFAULT NULL COMMENT '乙方单位id',
  `contract_type` tinyint(1) DEFAULT '5' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '乙方单位',
  `contract_name` varchar(255) DEFAULT NULL COMMENT '合同名称',
  `contract_num` varchar(150) DEFAULT NULL COMMENT '合同编号',
  PRIMARY KEY (`pay_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件合同付款信息';

-- ----------------------------
-- Records of sb_parts_contract_pay
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_inware_record
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_inware_record`;
CREATE TABLE `sb_parts_inware_record` (
  `inwareid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '入库记录ID',
  `hospital_id` int(10) DEFAULT NULL COMMENT '医院id',
  `contract_id` int(10) DEFAULT NULL COMMENT '合同id',
  `inware_num` varchar(150) DEFAULT NULL COMMENT '入库单号',
  `repid` int(10) DEFAULT NULL COMMENT '维修单id',
  `assid` int(10) DEFAULT NULL COMMENT '维修设备id',
  `buydate` date DEFAULT NULL COMMENT '入库时间',
  `sum` int(10) DEFAULT '0' COMMENT '入库数量',
  `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '入库金额',
  `supplier_name` varchar(255) DEFAULT '' COMMENT '生产厂商名称',
  `supplier_id` int(10) DEFAULT '0' COMMENT '生产厂商id',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '0' COMMENT '采购状态0未采购1已采购',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '1删除 0未删除',
  PRIMARY KEY (`inwareid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件入库记录';

-- ----------------------------
-- Records of sb_parts_inware_record
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_inware_record_apply
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_inware_record_apply`;
CREATE TABLE `sb_parts_inware_record_apply` (
  `applyid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请单id',
  `inwareid` int(10) DEFAULT NULL COMMENT '入库单ID',
  `parts` varchar(255) DEFAULT NULL COMMENT '配件名称',
  `parts_model` varchar(255) DEFAULT NULL COMMENT '配件型号',
  `status` tinyint(3) DEFAULT '0' COMMENT '采购状态0未采购1已采购',
  PRIMARY KEY (`applyid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件入库申请单';

-- ----------------------------
-- Records of sb_parts_inware_record_apply
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_inware_record_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_inware_record_detail`;
CREATE TABLE `sb_parts_inware_record_detail` (
  `detailid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `repid` int(10) DEFAULT '0' COMMENT '绑定的维修单id',
  `leader` varchar(150) DEFAULT '' COMMENT '领用人',
  `inwareid` int(10) DEFAULT NULL COMMENT '入库记录id',
  `hospital_id` int(10) DEFAULT NULL COMMENT '医院id',
  `parts` varchar(255) NOT NULL COMMENT '配件名称',
  `parts_model` varchar(255) NOT NULL COMMENT '配件型号',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '配件单价',
  `from` varchar(50) NOT NULL COMMENT '配件来源',
  `unit` varchar(100) DEFAULT '' COMMENT '单位',
  `brand` varchar(50) NOT NULL COMMENT '配件品牌',
  `supplier_name` varchar(255) DEFAULT '' COMMENT '供应商',
  `supplier_id` int(10) DEFAULT NULL COMMENT '供应商id',
  `manufacturer_name` varchar(255) NOT NULL DEFAULT '' COMMENT '生产厂商',
  `manufacturer_id` int(11) DEFAULT NULL COMMENT '生产厂商id',
  `addtime` date DEFAULT NULL COMMENT '入库时间',
  `adduser` varchar(150) DEFAULT NULL COMMENT '添加人员',
  `edittime` date DEFAULT NULL COMMENT '编辑时间',
  `edituser` varchar(150) DEFAULT NULL COMMENT '编辑人员',
  `status` tinyint(1) DEFAULT '0' COMMENT '出库状态 1同意出库 0未出库 ',
  `is_use` tinyint(1) DEFAULT '0' COMMENT '使用状态 0未使用 1已使用',
  PRIMARY KEY (`detailid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件入库记录明细';

-- ----------------------------
-- Records of sb_parts_inware_record_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_outware_record
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_outware_record`;
CREATE TABLE `sb_parts_outware_record` (
  `outwareid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '出库记录ID',
  `hospital_id` int(10) DEFAULT NULL COMMENT '医院id',
  `repid` int(11) DEFAULT NULL COMMENT '维修单id',
  `assid` int(11) DEFAULT NULL COMMENT '维修设备id',
  `outware_num` varchar(150) DEFAULT NULL COMMENT '入库单号',
  `leader` varchar(150) DEFAULT NULL COMMENT '领用人',
  `outdate` date DEFAULT NULL COMMENT '出库日期',
  `sum` int(10) DEFAULT '0' COMMENT '出库数量',
  `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '出库总金额',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(3) DEFAULT '0' COMMENT '出库状态 0未同意出库 1已同意出库 2不同意出库',
  `addtime` datetime DEFAULT NULL COMMENT '记录时间',
  PRIMARY KEY (`outwareid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件入库记录';

-- ----------------------------
-- Records of sb_parts_outware_record
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_outware_record_apply
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_outware_record_apply`;
CREATE TABLE `sb_parts_outware_record_apply` (
  `applyid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请单id',
  `outwareid` int(10) DEFAULT NULL COMMENT '出库单ID',
  `parts` varchar(255) DEFAULT NULL COMMENT '配件名称',
  `parts_model` varchar(255) DEFAULT NULL COMMENT '配件型号',
  `sum` int(10) DEFAULT NULL COMMENT '入库数量',
  `status` tinyint(3) DEFAULT '0' COMMENT '出库状态 0未同意出库 1已同意出库',
  PRIMARY KEY (`applyid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件出库申请单';

-- ----------------------------
-- Records of sb_parts_outware_record_apply
-- ----------------------------


-- ----------------------------
-- Table structure for sb_parts_outware_record_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_parts_outware_record_detail`;
CREATE TABLE `sb_parts_outware_record_detail` (
  `detailid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `inware_partsid` int(10) DEFAULT NULL COMMENT '入库单配件id',
  `hospital_id` int(10) DEFAULT NULL COMMENT '医院id',
  `outwareid` int(10) DEFAULT NULL COMMENT '出库记录id',
  `parts` varchar(255) NOT NULL COMMENT '配件名称',
  `parts_model` varchar(255) NOT NULL COMMENT '配件型号',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '单价',
  `from` varchar(50) NOT NULL COMMENT '配件来源',
  `unit` varchar(100) DEFAULT NULL COMMENT '单位',
  `supplier_name` varchar(255) NOT NULL COMMENT '供应商',
  `supplier_id` int(11) DEFAULT NULL COMMENT '供应商id',
  `addtime` date DEFAULT NULL COMMENT '入库时间',
  `adduser` varchar(150) DEFAULT NULL COMMENT '添加人员',
  `edittime` date DEFAULT NULL COMMENT '编辑时间',
  `edituser` varchar(150) DEFAULT NULL COMMENT '编辑人员',
  `leader` varchar(150) DEFAULT NULL COMMENT '领用人',
  `status` tinyint(1) DEFAULT '0' COMMENT '0待审批 1审批通过 2审批不通过',
  PRIMARY KEY (`detailid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配件管理——配件入库记录明细';

-- ----------------------------
-- Records of sb_parts_outware_record_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_assets_template
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_assets_template`;
CREATE TABLE `sb_patrol_assets_template` (
  `patid` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `assid` int(10) NOT NULL COMMENT '设备（资产）ID',
  `assnum` varchar(25) DEFAULT NULL COMMENT '设备编号',
  `tpid` varchar(255) DEFAULT NULL COMMENT '模板ID',
  `default_tpid` int(16) DEFAULT NULL COMMENT '默认模板ID',
  PRIMARY KEY (`patid`) USING BTREE,
  UNIQUE KEY `assid` (`assid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——设备初始化模板表';

-- ----------------------------
-- Records of sb_patrol_assets_template
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_examine_all
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_examine_all`;
CREATE TABLE `sb_patrol_examine_all` (
  `exallid` int(10) NOT NULL AUTO_INCREMENT COMMENT '检查（验收）自增ID',
  `patrid` int(10) NOT NULL COMMENT '巡查计划ID',
  `patrolname` varchar(100) NOT NULL COMMENT '计划名称',
  `patrolnum` varchar(100) NOT NULL COMMENT '计划编号',
  `cycid` varchar(10) NOT NULL COMMENT '周期ID',
  `patrol_level` tinyint(3) NOT NULL COMMENT '保养级别',
  `executor` varchar(255) NOT NULL COMMENT '科室设备保养人名单，英文逗号隔开',
  `repair_num` int(10) NOT NULL COMMENT '已转至报修的设备数量',
  `scrap_num` int(10) NOT NULL COMMENT '已报废数量',
  `abnormal_num` int(10) NOT NULL COMMENT '异常设备台数',
  `abnormal_point_num` int(10) NOT NULL COMMENT '异常明细项总数',
  `exam_user` varchar(60) DEFAULT NULL COMMENT '检查（验收）人',
  `exam_time` datetime DEFAULT NULL COMMENT '检查（验收）时间',
  `examine_departid` int(10) NOT NULL COMMENT '检查（验收）科室ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '检查（验收）状态（0否1是）',
  `remark` text COMMENT '备注',
  `assnum_num` int(10) DEFAULT NULL COMMENT '设备数量',
  `completion_time` datetime DEFAULT NULL COMMENT '巡查完成时间',
  PRIMARY KEY (`exallid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——周期检查（验收）表（整科室）';

-- ----------------------------
-- Records of sb_patrol_examine_all
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_examine_one
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_examine_one`;
CREATE TABLE `sb_patrol_examine_one` (
  `exoneid` int(10) NOT NULL AUTO_INCREMENT COMMENT '检查（验收）-one自增ID',
  `exallid` int(10) NOT NULL COMMENT '检查（验收）-all自增ID',
  `cycid` varchar(10) NOT NULL COMMENT '周期ID',
  `assnum` varchar(25) NOT NULL COMMENT '主设备编号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '检查（验收）状态（0否1是）',
  `examdate` datetime DEFAULT NULL COMMENT '检查（验收）时间',
  `examine_username` varchar(30) DEFAULT NULL COMMENT '检查（验收）人',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`exoneid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——周期检查（验收）表（整科室内单台）';

-- ----------------------------
-- Records of sb_patrol_examine_one
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_execute
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_execute`;
CREATE TABLE `sb_patrol_execute` (
  `execid` int(10) NOT NULL AUTO_INCREMENT COMMENT '实施自增ID',
  `cycid` int(10) NOT NULL COMMENT '周期ID',
  `assetnum` varchar(25) NOT NULL COMMENT '设备编号',
  `asset_status_num` tinyint(1) DEFAULT NULL COMMENT '设备现状：1、工作正常；2、有小问题，但不影响使用；3、有故障，需要进一步维修；4、无法正常使用；5、该设备正在维修；6、该设备已报废',
  `asset_status` varchar(64) NOT NULL COMMENT '设备现状：1、工作正常；2、有小问题，但不影响使用；3、有故障，需要进一步维修；4、无法正常使用；5、该设备正在维修；6、该设备已报废',
  `finish_time` datetime DEFAULT NULL COMMENT '完成时间',
  `reason` text COMMENT '该设备不保养的原因',
  `remark` text COMMENT '备注',
  `is_torepair` tinyint(1) DEFAULT '0' COMMENT '是否转至报修：0否，1是',
  `torepair_time` varchar(100) DEFAULT NULL COMMENT '转至报修时间',
  `applicant` varchar(60) DEFAULT NULL COMMENT '以谁的名义报修（正常报修业务申报人）',
  `userid` int(10) DEFAULT NULL COMMENT '同applicant用户ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态[''0=>待巡查'',''1=>巡查中'',''2=>已巡查'']',
  `report_num` varchar(60) DEFAULT NULL COMMENT '报告编号',
  `execute_user` varchar(60) DEFAULT NULL COMMENT '执行人',
  PRIMARY KEY (`execid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——计划实施表';

-- ----------------------------
-- Records of sb_patrol_execute
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_execute_abnormal
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_execute_abnormal`;
CREATE TABLE `sb_patrol_execute_abnormal` (
  `abnid` int(10) NOT NULL AUTO_INCREMENT COMMENT '实施异常自增ID，一对多',
  `execid` int(10) NOT NULL COMMENT '实施ID',
  `ppid` int(10) NOT NULL COMMENT '保养项目类型&明细ID',
  `result` varchar(64) NOT NULL COMMENT '保养结果（合格、修复、可用、待修）',
  `abnormal_remark` text COMMENT '异常处理详情',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  PRIMARY KEY (`abnid`) USING BTREE,
  KEY `execid` (`execid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——计划实施之异常表（一对多）';

-- ----------------------------
-- Records of sb_patrol_execute_abnormal
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plan
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plan`;
CREATE TABLE `sb_patrol_plan` (
  `patrid` int(10) NOT NULL AUTO_INCREMENT COMMENT '巡查计划ID',
  `packid` int(11) DEFAULT NULL COMMENT '设备包ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `patrol_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '巡查级别ID，1日常、2巡查、3预防',
  `patrolnum` varchar(100) NOT NULL COMMENT '计划编号（系统生成）：日常RC开头，巡查XC开头，预测PM开头',
  `patrolname` varchar(255) NOT NULL COMMENT '计划名称（系统生成）：设备包名称+级别名称',
  `patrol_assnums` text COMMENT '计划设备编号',
  `add_user` varchar(60) DEFAULT NULL COMMENT '计划制定人',
  `add_time` datetime DEFAULT NULL COMMENT '计划制定时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '计划修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '计划修改时间',
  `executedate` date DEFAULT NULL COMMENT '计划执行日期',
  `remark` text COMMENT '备注',
  `expect_complete_date` date DEFAULT NULL COMMENT '预计完成日期',
  `approve_status` tinyint(3) DEFAULT NULL COMMENT '审批状态(-1不需审批 0未审 1通过 2不通过)',
  `approve_time` datetime DEFAULT NULL COMMENT '最后审批时间',
  `retrial_status` tinyint(3) DEFAULT NULL COMMENT '审批不通过后是否申请重审【0等待操作1重审中2直接结束】',
  `current_approver` varchar(150) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '全部审批人',
  `is_release` tinyint(3) DEFAULT '0' COMMENT '计划发布状态【0待发布 1已发布】',
  `release_user` varchar(60) DEFAULT NULL COMMENT '计划发布人',
  `release_time` datetime DEFAULT NULL COMMENT '计划发布时间',
  `release_remark` varchar(255) DEFAULT NULL COMMENT '计划发布备注',
  `is_check` tinyint(3) DEFAULT '0' COMMENT '验收状态【0待验收 1已验收】',
  `check_user` varchar(60) DEFAULT NULL COMMENT '验收人',
  `check_time` datetime DEFAULT NULL COMMENT '验收时间',
  `check_remark` varchar(255) DEFAULT NULL COMMENT '验收备注',
  `patrol_status` tinyint(3) DEFAULT NULL COMMENT '巡查计划状态【1待审核 2待修订 3待发布 4待实施 5待验收 6已结束 7已逾期】',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否 1是】',
  PRIMARY KEY (`patrid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——计划制定表';

-- ----------------------------
-- Records of sb_patrol_plan
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plan_assets_pack
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plan_assets_pack`;
CREATE TABLE `sb_patrol_plan_assets_pack` (
  `packid` int(10) NOT NULL AUTO_INCREMENT COMMENT '条件设备包自增ID',
  `packname` varchar(255) NOT NULL COMMENT '设备包名称',
  `desc` varchar(255) DEFAULT NULL COMMENT '设备包名称说明',
  `arr_assnum` text NOT NULL COMMENT '设备编码集json',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  PRIMARY KEY (`packid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——条件设备包表';

-- ----------------------------
-- Records of sb_patrol_plan_assets_pack
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plan_cycle
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plan_cycle`;
CREATE TABLE `sb_patrol_plan_cycle` (
  `cycid` int(10) NOT NULL AUTO_INCREMENT COMMENT '周期自增ID',
  `patrid` int(10) NOT NULL COMMENT '巡查计划ID',
  `patrol_level` tinyint(3) DEFAULT NULL COMMENT '保养级别【1=''日常保养'',2=''巡查保养'',3=''预防性维护''】',
  `executor` varchar(60) NOT NULL DEFAULT '' COMMENT '执行人',
  `assnum_tpid` text COMMENT '每个设备当前巡查级别绑定的巡查模板ID',
  `plan_assnum` text COMMENT '设备编码组',
  `repair_assnum` text COMMENT '巡查过程中已经在维修的设备',
  `scrap_assnum` text COMMENT '巡查过程中已经报废的设备',
  `not_operation_assnum` text COMMENT '不进行巡查的设备',
  `abnormal_assnum` text COMMENT '异常设备编号',
  `abnormal_sum` int(10) NOT NULL DEFAULT '0' COMMENT '异常数',
  `implement_sum` int(11) NOT NULL DEFAULT '0' COMMENT '已执行巡查设备数量',
  `edit_reason` text COMMENT '修订原因',
  `edit_time` datetime DEFAULT NULL COMMENT '修订时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修订人',
  `complete_time` datetime DEFAULT NULL COMMENT '完成时间',
  `status` tinyint(3) DEFAULT '0' COMMENT '执行状态【0=''待执行'',1=''执行中'',2=''已完成'',3=''已验收''】',
  `sign_info` text COMMENT '微信扫码签到情况',
  PRIMARY KEY (`cycid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——计划制定之周期表';

-- ----------------------------
-- Records of sb_patrol_plan_cycle
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plan_cycle_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plan_cycle_file`;
CREATE TABLE `sb_patrol_plan_cycle_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `cycid` int(11) DEFAULT NULL COMMENT '计划周期ID',
  `assnum` varchar(25) DEFAULT NULL COMMENT '设备编号',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养隔离——巡查保养照片记录';

-- ----------------------------
-- Records of sb_patrol_plan_cycle_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plans
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plans`;
CREATE TABLE `sb_patrol_plans` (
  `patrid` int(11) NOT NULL AUTO_INCREMENT COMMENT '巡查计划ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `assets_departid` varchar(255) DEFAULT NULL COMMENT '计划设备包含科室',
  `patrol_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '巡查级别ID，1日常、2巡查、3预防',
  `patrol_name` varchar(120) NOT NULL COMMENT '巡查计划名称',
  `patrol_start_date` date DEFAULT NULL COMMENT '计划开始日期',
  `patrol_end_date` date DEFAULT NULL COMMENT '计划结束日期',
  `is_cycle` tinyint(3) DEFAULT '0' COMMENT '是否周期计划【0否 1是】',
  `cycle_unit` varchar(20) DEFAULT NULL COMMENT '周期单位',
  `cycle_setting` int(11) DEFAULT NULL COMMENT '周期设置',
  `total_cycle` int(11) DEFAULT '1' COMMENT '总周期数',
  `current_cycle` int(11) DEFAULT '1' COMMENT '当前期数',
  `patrol_status` tinyint(3) DEFAULT NULL COMMENT '巡查计划总状态【1待审核 2待发布 3实施中 4已结束】',
  `remark` text COMMENT '备注',
  `add_user` varchar(60) DEFAULT NULL COMMENT '计划制定人',
  `add_time` datetime DEFAULT NULL COMMENT '计划制定时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '计划修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '计划修改时间',
  `approve_status` tinyint(3) DEFAULT NULL COMMENT '审批状态(-1不需审批 0未审 1通过 2不通过)',
  `approve_time` datetime DEFAULT NULL COMMENT '最后审批时间',
  `retrial_status` tinyint(3) DEFAULT NULL COMMENT '审批不通过后是否申请重审【0等待操作1重审中2直接结束】',
  `current_approver` varchar(150) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '全部审批人',
  `is_release` tinyint(3) DEFAULT '0' COMMENT '计划发布状态【0待发布 1已发布】',
  `release_user` varchar(60) DEFAULT NULL COMMENT '计划发布人',
  `release_time` datetime DEFAULT NULL COMMENT '计划发布时间',
  `release_remark` varchar(255) DEFAULT NULL COMMENT '计划发布备注',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否 1是】',
  PRIMARY KEY (`patrid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——计划制定表';

-- ----------------------------
-- Records of sb_patrol_plans
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plans_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plans_assets`;
CREATE TABLE `sb_patrol_plans_assets` (
  `plan_asid` int(11) NOT NULL AUTO_INCREMENT COMMENT '计划设备ID',
  `patrid` int(11) NOT NULL COMMENT '所属巡查计划ID',
  `assid` int(11) NOT NULL COMMENT '设备ID',
  `assnum` varchar(120) DEFAULT NULL COMMENT '设备编码',
  `assnum_tpid` int(11) DEFAULT NULL COMMENT '绑定的模板ID',
  `enable_status` tinyint(3) DEFAULT '1' COMMENT '计划设备启用状态【0=暂停,1=启用】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `stop_user` varchar(60) DEFAULT NULL COMMENT '暂停人',
  `stop_time` datetime DEFAULT NULL COMMENT '暂停时间',
  PRIMARY KEY (`plan_asid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——巡查保养计划设备明细';

-- ----------------------------
-- Records of sb_patrol_plans_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plans_cycle
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plans_cycle`;
CREATE TABLE `sb_patrol_plans_cycle` (
  `cycid` int(11) NOT NULL AUTO_INCREMENT COMMENT '周期ID',
  `patrid` int(11) NOT NULL COMMENT '所属巡查计划ID',
  `period` int(11) NOT NULL COMMENT '期次',
  `patrol_num` varchar(120) DEFAULT NULL COMMENT '计划编号（系统生成）：日常RC开头，巡查XC开头，预测PM开头',
  `assets_nums` int(11) DEFAULT '0' COMMENT '周期计划设备总数量',
  `assets_departid` varchar(255) DEFAULT NULL COMMENT '计划设备包含科室',
  `implement_sum` int(11) DEFAULT '0' COMMENT '已执行设备数量',
  `abnormal_sum` int(11) DEFAULT '0' COMMENT '异常设备数量',
  `abnormal_assnum` text COMMENT '异常设备编号',
  `plan_assnum` text COMMENT '周期计划设备编码组',
  `repair_assnum` text COMMENT '巡查过程中已经在维修的设备',
  `scrap_assnum` text COMMENT '巡查过程中已经报废的设备',
  `not_operation_assnum` text COMMENT '不进行巡查的设备',
  `cycle_start_date` date DEFAULT NULL COMMENT '周期开始日期',
  `cycle_end_date` date DEFAULT NULL COMMENT '周期结束日期',
  `cycle_status` tinyint(3) DEFAULT '0' COMMENT '周期计划完成状态【0=待执行,1=执行中,2=按期完成,3=逾期完成,4=逾期未完成】',
  `check_status` tinyint(3) DEFAULT '0' COMMENT '周期计划验收状态【0=待验收,1=已验收】',
  `create_time` datetime DEFAULT NULL COMMENT '周期计划创建时间',
  `complete_time` datetime DEFAULT NULL COMMENT '周期完成时间',
  `sign_info` text COMMENT '微信扫码签到情况',
  PRIMARY KEY (`cycid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——巡查保养计划周期明细';

-- ----------------------------
-- Records of sb_patrol_plans_cycle
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_plans_cycle_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_plans_cycle_file`;
CREATE TABLE `sb_patrol_plans_cycle_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `cycid` int(11) DEFAULT NULL COMMENT '计划周期ID',
  `assnum` varchar(25) DEFAULT NULL COMMENT '设备编号',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养隔离——巡查保养照片记录';

-- ----------------------------
-- Records of sb_patrol_plans_cycle_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_patrol_points
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_points`;
CREATE TABLE `sb_patrol_points` (
  `ppid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '类型/明细ID',
  `num` varchar(10) DEFAULT NULL COMMENT '类型或明细编号',
  `name` varchar(50) NOT NULL COMMENT '类型或明细名称',
  `parentid` int(10) NOT NULL COMMENT '父级ID【0=''类型''】',
  `remark` varchar(255) DEFAULT NULL COMMENT '类型备注',
  `result` varchar(64) DEFAULT NULL COMMENT '默认结果',
  `require` varchar(255) DEFAULT NULL COMMENT '保养内容及要求',
  PRIMARY KEY (`ppid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=388 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——保养项目类型&明细表';

-- ----------------------------
-- Records of sb_patrol_points
-- ----------------------------
BEGIN;
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (1, NULL, '外观及附件检查', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (2, '1001', '老化破损', 1, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (3, '1002', '移动性、安全性检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (4, '1003', '震动头检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (5, '1004', '气体管路检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (6, '1005', '止血带外观检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (7, '1006', '设备活动关节完好，无损坏', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (8, '1007', '设备的螺钉完好、紧固', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (9, '1008', '电源线完好无损坏', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (10, '1009', '设备摆放适当，合理', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (12, '1011', '导联、电极线检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (13, '1012', '设备按键、旋钮检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (14, '1013', '设备外观完好、整洁', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (15, '1014', '治疗台无破损，附件无缺失', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (16, '1015', '螺丝无松动', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (17, '1016', '外观整洁，无明显污物', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (18, '1017', '分机血氧饱和度探头检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (19, '1018', '分机心电导联线检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (20, '1019', '分机外壳', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (21, '1020', '主机检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (22, '1021', '紫外线灯、活性炭有效期', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (23, '1022', '漏水检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (24, '1023', '台车移动性、安全性检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (25, '1024', '信号传输线检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (26, '1025', '显微镜光源检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (27, '1026', '显微镜活动臂转动检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (28, '1027', '导联线外观检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (29, '1028', '气体管路接口检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (30, '1029', '患者呼气管路清洁、更换检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (31, '1030', '定时装置检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (32, '1031', '各关节紧固情况检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (33, '1032', '其他设备导线是否被床位压迫或挤压', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (34, '1033', '升降时，床体周围无遮挡物', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (35, '1034', '床旁挡板活动正常', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (36, '1035', '患者床位安全性、舒适性检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (37, '1036', '治疗光源检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (38, '1037', '侧面保护装置检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (39, '1038', '多普勒探头线路检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (40, '1039', '各按钮，开关无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (41, '1040', '外观整洁，无明显污迹和破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (42, '1041', '电源线无破损，通讯线无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (43, '1042', '对椅位主控部分电路箱检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (44, '1043', '对供水、气、电的地箱检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (45, '1044', '照明灯检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (46, '1045', '患者椅位安全性、舒适性检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (47, '1046', '负压瓶、瓶盖无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (48, '1047', '开关、按钮、调速器无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (49, '1048', '外观整洁，无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (50, '1049', '电源线无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (51, '1050', '吸引瓶外观及密封性', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (52, '1051', '按键、旋钮检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (53, '1052', '整车移动性、安全性检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (54, '1053', '控制手柄外观检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (55, '1054', '手术台外观检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (56, '1055', '检查台车的移动性及安全性', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (57, '1056', '检查控制面板及显示器', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (58, '1057', '检查主机的液位', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (59, '1058', '检查降温毯是否破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (60, '1059', '治疗附件导联线检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (61, '1060', '电源线、电缆配件、充电器', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (62, '1061', '有正确清晰的设备编码、标签和警示', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (63, '1062', '控制开关正常', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (64, '1063', '设备干净整洁', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (65, '1064', '空气过滤网清洁、除尘', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (66, '1065', '监视器显示正常', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (67, '1066', '用户操作界面检测、除尘', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (68, '1067', '探头外观检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (69, '1068', '探头功能检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (70, '1069', '轨迹球检测、除尘', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (71, '1070', '电极板外观检测', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (72, '1071', '风箱皮囊的软化及消毒处理', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (73, '1072', '清理钠石灰罐，检查排水系统是否正常', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (74, '1073', '清洁放水阀门组件', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (75, '1074', '清洁或更换过滤器组件', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (76, '1075', '风扇过滤网清洁', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (77, '1076', '表面清洁', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (78, '1077', '屏幕显示检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (79, '1078', '注射完毕报警是否正常', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (80, '1079', '其他附件无短缺', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (81, '1080', '电源插头及电源线无破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (82, '1081', '旋钮和按键无松动或破损', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (83, '1082', '外观整洁，无明显的缺陷', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (84, '1083', '血氧饱和度探头检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (85, '1084', '血压管路及袖带检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (86, '1085', '心电导联线检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (87, '1086', '机器外壳检查', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (88, '1087', '检查电源线', 1, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (89, NULL, '性能检查', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (90, '89001', '电池性能检查', 89, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (91, '89002', '工作状态检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (92, '89003', '气泵部件运行正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (93, '89004', '机械部件运行正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (94, '89005', '电池工作正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (95, '89006', '报警功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (96, '89007', '设备输出值正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (97, '89008', '屏幕显示正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (98, '89009', '开机自检正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (99, '89010', '管路、下水口无堵塞', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (100, '89011', '治疗台升降正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (101, '89012', '脚踏开关正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (102, '89013', '指示灯正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (103, '89014', '负压吸引正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (104, '89015', '治疗台手机正常运转', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (105, '89016', '报警功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (106, '89017', '分析传输信号正确性检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (107, '89018', '分机与主机信号连接检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (108, '89019', '打印/传输功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (109, '89020', '电机功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (110, '89021', '镜体关节运动性检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (111, '89022', '显示效果检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (112, '89023', '白平衡检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (113, '89024', '软、硬镜透光性检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (114, '89025', '照明强度检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (115, '89026', '光源调节检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (116, '89027', '开机运行无异响', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (117, '89028', '断电报警检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (118, '89029', '散热风扇工作情况检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (119, '89030', '灯管工作状态检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (120, '89031', '按压力度检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (121, '89032', '按压频率检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (122, '89033', '按压轴承检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (123, '89034', '润滑电动机、纸质传动装置', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (124, '89035', '清洁滚筒、导纸机构', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (125, '89036', '光源亮度调节检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (126, '89037', '通道切换检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (127, '89038', '内部管路有无漏液检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (128, '89039', '吸液口O型圈检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (129, '89040', '快速接头O型圈状况检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (130, '89041', '后备电源的工作状态检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (131, '89042', '模式调节检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (132, '89043', '能量辐射检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (133, '89044', '刹车、移动功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (134, '89045', '床位各关节机动性检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (135, '89046', '温度超限报警检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (136, '89047', '车体安全检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (137, '89048', '温度探头功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (138, '89049', '辐射功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (139, '89050', '温度调节检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (140, '89051', '紧急开关功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (141, '89052', '各输出通道功率显示正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (142, '89053', '各输出通道功率调节正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (143, '89054', '各项水路、气路供应压力正常，无阻塞', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (144, '89055', '椅位活动性检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (145, '89056', '控制板上的遥控功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (146, '89057', '照明灯形成的光照形状正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (147, '89058', '紧急停止装置功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (148, '89059', '调压功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (149, '89060', '负压与时间显示功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (150, '89061', '负压泵是否工作正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (151, '89062', '负压压力是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (152, '89063', '机械关节超限位报警测试', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (153, '89064', '机械关节活动性检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (154, '89065', '遥控功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (155, '89066', '断电提示', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (156, '89067', '水位报警检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (157, '89068', '传感器报警检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (158, '89069', '停止触发温度检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (159, '89070', '启动触发温度检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (160, '89071', '控制板功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (161, '89072', '工作状况测试', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (162, '89073', '验证报警功能', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (163, '89074', '验证记录准确性', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (164, '89075', '验证注射信息准确', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (165, '89076', '验证管路部件运转正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (166, '89077', '验证显示设备清晰、正确', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (167, '89078', '散热部分功能正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (168, '89079', '机械部分维护保养', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (169, '89080', '电源线路正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (170, '89081', '打印报告检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (171, '89082', '报警检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (172, '89083', '放电能量结果检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (173, '89084', '异步放电功能检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (174, '89085', '同步放电功能检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (175, '89086', '充电时间检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (176, '89087', '能量调节检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (177, '89088', '系统时间校对', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (178, '89089', '系统开机自检', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (179, '89090', '检测窒息报警功能是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (180, '89091', '检测气道压力报警功能是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (181, '89092', '流量传感器定标', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (182, '89093', '各气源工作压力检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (183, '89094', '氧电池定标', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (184, '89095', '后备电源的工作状态检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (185, '89096', '呼吸回路的检测', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (186, '89097', '总流量清零', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (187, '89098', '流速调节检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (188, '89099', '开机自检检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (189, '89100', '机器启动报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (190, '89101', '即将注射完毕报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (191, '89102', '流速偏差是否在允许误差内', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (192, '89103', '输液器为校准提示是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (193, '89104', '遗忘操作报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (194, '89105', '市电故障或电源线脱落报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (195, '89106', '门未关启动报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (196, '89107', '管路气泡报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (197, '89108', '针头或管路堵塞报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (198, '89109', '即将输液完毕报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (199, '89110', '输液完毕报警是否正常', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (200, '89111', '电池是否充放电良好', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (201, '89112', '血氧功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (202, '89113', '血压功能检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (203, '89114', '心电功能模拟检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (204, '89115', '静音检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (205, '89116', '报警限检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (206, '89117', '声光报警检查', 89, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (207, NULL, '电气安全测试', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (208, '207001', '检测患者漏电流≤0.020mA', 207, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (209, '207002', '检测外壳漏电流≤0.100mA', 207, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (210, '207003', '检测接地漏电流≤0.500mA', 207, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (211, '207004', '检测接地电阻＜0.200Ω', 207, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (212, NULL, '保养调校', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (213, '212001', '检查显示屏显示是否正常', 212, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (214, '212002', '检查机器外壳有无破损', 212, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (215, '212003', '检查供电是否正常，检查电源线，外部管路连接是否正确', 212, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (216, '212004', '检查传感器连接线（氧电池，流量传感器，压力传感器）是否正常', 212, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (217, '212005', '检查各功能按键旋钮是否完好', 212, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (218, NULL, '通用', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (219, '218001', '舱体外观有无缺损，焊缝，接头及管道有无泄露', 218, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (220, '218002', '检查氧舱舱门观察窗有机玻璃是否老化', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (221, '218003', '氧舱供氧供气系统是否完好', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (222, '218004', '氧舱电气设备接线情况是否良好', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (223, '218005', '氧舱舱体与接地装置的连接可靠性', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (224, '218006', '手动操作系统的手操作机构是否正常', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (225, '218007', '应急电源及应急照射系统的工作状态是否好', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (226, '218008', '应急排气阀动作的灵活性', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (227, '218009', '测氧仪的工作可靠性', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (228, '218010', '氧舱安全阀和压力表校验', 218, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (229, NULL, '水路部分', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (230, '229001', '单向阀的工作状态', 229, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (231, '229002', '进水电磁阀工作状态', 229, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (232, '229003', '进水过滤器', 229, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (233, '229004', '水压要求', 229, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (234, '229005', '按键检查', 229, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (235, NULL, '真空管路', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (236, '235001', '腔内压力开关', 235, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (237, '235002', '单向阀的工作状态（真空）', 235, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (238, '235003', '格真空管路的接口密封', 235, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (239, '235004', '大气动阀工作状态', 235, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (240, '235005', '小热交换器', 235, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (241, '235006', '大热交换器', 235, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (242, '235007', '测试真空泵工作状态', 235, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (243, NULL, '电路控制', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (244, '243001', '各电磁阀的工作状态', 243, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (245, '243002', '数字输入状态检查', 243, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (246, '243003', '数字输出状态测试', 243, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (247, '243004', '继电器板插口的连接', 243, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (248, '243005', 'CPU板各插口的连接', 243, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (249, NULL, '门', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (250, '249001', '门封压力开关', 249, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (251, '249002', '无菌侧门封', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (252, '249003', '清洁侧门封', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (253, '249004', '门锁状态', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (254, '249005', '门互锁', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (255, '249006', '门限位开关', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (256, '249007', '门的升降', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (257, '249008', '门的安全联锁', 249, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (258, NULL, '压缩空气', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (259, '258001', '无菌空气过滤器', 258, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (260, '258002', '气动阀工作状态', 258, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (261, '258003', '压缩空气控制', 258, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (262, '258004', '压缩空气压力', 258, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (263, NULL, '其他', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (264, '263001', '倾倒保护装置检查', 263, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (265, '263002', '检查机器通风孔的空气过滤网是否堵塞，保持通风良好', 263, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (266, '263003', '急停保护装置检查', 263, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (267, '263004', '循环冷却系统检查', 263, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (268, '263005', '固定安全性检查', 263, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (269, NULL, '机械部件的保养与维护', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (270, '269001', '对轴承轨道的润滑，减少磨损增加灵敏度', 269, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (271, '269002', '检查维护（各部件间固定连接有否松动；诊断床的限位开关是否正常工作；各吊挂部分的钢丝有否出现磨损断股）', 269, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (272, '269003', '除尘保洁', 269, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (273, NULL, '冷却系统的保养与维护', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (274, '273001', '检查UPS电池蓄电能力', 273, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (275, '273002', '检查散热风扇是否正常运作', 273, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (276, '273003', '检查干燥器是否有积水', 273, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (277, '273004', '检查磁体内压力和梯度冷却机的温度', 273, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (278, '273005', '检查磁体各级温度是否符合要求', 273, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (279, '273006', '检查冷却水、液氦水平', 273, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (280, NULL, '高压电缆的保养与维护', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (281, '280001', '检查充填物是否因受热而熔化流出', 280, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (282, '280002', '电缆两端的固定环与X线球管及高压发生器是否紧固', 280, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (283, '280003', '防止弯曲', 280, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (284, '280004', '防潮防腐蚀（干燥清洁，恒温，防止受热、冷冻及压碰）', 280, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (285, NULL, 'X线球管的保养与维护', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (286, '285001', '防气泡（观察球管内有无气泡存在，及时添油排气）', 285, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (287, '285002', '检查防震防热措施', 285, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (288, NULL, '高压发生器及组合机头的保养与维护', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (289, '288001', '检查高压发生器内各部件是否完好', 288, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (290, '288002', '检查绝缘油的绝缘性能是否良好', 288, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (291, '288003', '检查机头散热情况是否良好', 288, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (294, NULL, '类别', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (297, '218011', '全面检查', 218, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (298, '1088', '外观完好', 1, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (299, '89118', '是的', 89, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (300, NULL, '类别-W', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (387, '294001', '除尘', 294, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (305, NULL, '外观及附件', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (306, NULL, '性能', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (307, '212006', '滤网除尘、机内除尘、紧固件加固', 212, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (308, NULL, '仪器现状', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (309, '1089', '旋钮、按键和轨迹球无松动或破损', 1, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (310, '1090', '电气连接无断线或松动', 1, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (311, '1091', '外观整洁，附件无明显缺陷或异常', 1, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (312, '89119', '电气安全测试', 89, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (313, '89120', '各项检测功能正常', 89, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (314, '89121', '影像结果合符临床要求', 89, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (315, '263006', '工作间洁净，温度、湿度正常', 263, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (316, '263007', '网络连接正常，图文工作站正常', 263, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (321, '308001', '运行', 308, NULL, '合格', '按键内容描述');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (322, NULL, '测试', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (323, NULL, '安全检查', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (324, '323001', '设备关机状态下，机房紧急停机按钮功能', 323, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (325, '323002', '检查电源柜防漏电功能', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (326, '323003', '检查电源柜防过流功能', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (327, '323004', '设备开机状态下，检查Stop Button功能', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (328, '323005', '检查安全标签', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (329, '323006', '检查机房门开关连锁功能', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (330, '323007', '检查CT床安全开关', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (331, '323008', '检查射线切断功能（X-ray time out）', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (332, '323009', '检查机架、控制盒和机房的射线指示灯功能', 323, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (333, NULL, '功能检查', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (334, '333001', '执行图像质量“Daily”检查', 333, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (335, '333002', '执行系统“Check Up” 检查', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (336, '333003', '检查系统日志及故障代码', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (337, '333004', '使用硬盘/光盘进行参数表格备份', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (338, '333005', '检查硬盘分区的剩余空间，清除垃圾文件', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (339, '333006', '选用“Constancy”做图像质量检查', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (340, '333007', '检查CT床固定附件', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (341, '333008', '检查DC_Link风扇', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (342, '333009', '检查INV风扇', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (343, '333010', '检查MVT风扇', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (344, '333011', '检查XGS控制器风扇', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (345, '333012', '检查机架UMAS风扇', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (346, '333013', '检查质检模体', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (347, '333014', '检查电源柜中浪涌保护器的状态', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (348, '333015', '检查计算机的风扇', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (349, '333016', '检查CT床水平运动牵引阻力(要求小于160牛顿)', 333, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (350, NULL, '设备润滑', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (351, '350001', '润滑CT床垂直升降电机', 350, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (352, '350002', '润滑CT床升降机构底部水平导轨和滑块', 350, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (353, '350003', '润滑CT床升降机构顶部水平导轨和滑块', 350, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (354, '350004', '润滑CT床板导轨', 350, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (355, '350005', '润滑CT床板托板导轨', 350, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (356, '350006', '润滑机架轴承', 350, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (357, NULL, '清洁设备及检查/更换备件', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (358, '357001', '用吸尘器清除滑环的碳粉及Gantry内的灰尘', 357, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (359, '357002', '检查/清洁探测器窗口', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (360, '357003', '检查/更换滑环接触刷', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (361, '357004', '检查/更换EMC导电铜刷', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (362, '357005', '更换电源柜的空气滤网', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (363, '357006', '清洁ICS(IES)内部及外部尘土', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (364, '357007', '清洁IRS内部及外部尘土', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (365, '357008', '清洁/更换IRS空气过滤网', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (366, '357009', '水冷机加水', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (367, '357010', '清洁水冷机室外机散热器', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (368, '357011', '机架外观清洁', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (369, '357012', '扫描床外观清洁', 357, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (370, NULL, '外观检查（安全检查）', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (371, '370001', '检查盖板', 370, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (372, '370002', '检查电缆波纹软管', 370, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (373, '370003', '检查防辐射装置', 370, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (374, '370004', '检查警示标签', 370, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (375, NULL, '高压发生装置', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (376, '375001', '清洁高压控制台', 375, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (377, '375002', '评估错误日志', 375, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (378, '375003', '高压电缆绝缘层及屏蔽层检查', 375, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (379, '375004', '检查 X 线管旋转阳极', 375, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (380, '375005', '电离室的控制（保养前）', 375, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (381, '375006', '最大发电功率检查', 375, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (382, '375007', '射线指示灯检查', 375, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (383, NULL, '检查和维护', 0, '', NULL, NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (384, '383001', '清洁', 383, NULL, '合格', '');
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (385, '383002', '检查橡胶停止', 383, NULL, '合格', NULL);
INSERT INTO `sb_patrol_points` (`ppid`, `num`, `name`, `parentid`, `remark`, `result`, `require`) VALUES (386, '383003', '检查升降安全限位开关', 383, NULL, '合格', NULL);
COMMIT;

-- ----------------------------
-- Table structure for sb_patrol_template
-- ----------------------------
DROP TABLE IF EXISTS `sb_patrol_template`;
CREATE TABLE `sb_patrol_template` (
  `tpid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `name` varchar(128) NOT NULL COMMENT '模板名称',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `points_num` text COMMENT '模板巡查明细编号',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`tpid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='巡查保养——模板维护表';

-- ----------------------------
-- Records of sb_patrol_template
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchase
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchase`;
CREATE TABLE `sb_purchase` (
  `purchase_id` int(11) NOT NULL,
  PRIMARY KEY (`purchase_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='采购管理——采购信息表';

-- ----------------------------
-- Records of sb_purchase
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_install_debug
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_install_debug`;
CREATE TABLE `sb_purchases_assets_install_debug` (
  `debug_id` int(11) NOT NULL AUTO_INCREMENT,
  `out_id` int(11) DEFAULT NULL COMMENT '设备出库ID',
  `debug_user` varchar(60) DEFAULT NULL COMMENT '调试人',
  `debug_start_date` date DEFAULT NULL COMMENT '调试开始日期',
  `debug_end_date` date DEFAULT NULL COMMENT '调试结束日期',
  `debug_area` varchar(255) DEFAULT NULL COMMENT '调试地点',
  `attendants_user` text COMMENT '参加人员',
  `debug_desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`debug_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——安装调试';

-- ----------------------------
-- Records of sb_purchases_assets_install_debug
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_install_debug_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_install_debug_report`;
CREATE TABLE `sb_purchases_assets_install_debug_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `ware_assets_id` int(11) DEFAULT NULL COMMENT '对应出库设备记录ID',
  `out_id` int(11) DEFAULT NULL COMMENT '设备出库ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备安装调试';

-- ----------------------------
-- Records of sb_purchases_assets_install_debug_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_metering_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_metering_report`;
CREATE TABLE `sb_purchases_assets_metering_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `ware_assets_id` int(11) DEFAULT NULL COMMENT '出库设备记录ID',
  `out_id` int(11) DEFAULT NULL COMMENT '设备出库ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备首次计量报告';

-- ----------------------------
-- Records of sb_purchases_assets_metering_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_quality_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_quality_report`;
CREATE TABLE `sb_purchases_assets_quality_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `ware_assets_id` int(11) DEFAULT NULL COMMENT '所属出库单记录ID',
  `out_id` int(11) DEFAULT NULL COMMENT '设备出库ID',
  `type_code` varchar(60) DEFAULT NULL COMMENT '资料类型【Y验收报告、M铭牌、H合格证、C操作手册】',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否，1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备质量验收报告';

-- ----------------------------
-- Records of sb_purchases_assets_quality_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_test_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_test_report`;
CREATE TABLE `sb_purchases_assets_test_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `ware_assets_id` int(11) DEFAULT NULL COMMENT '出库设备记录ID',
  `out_id` int(11) DEFAULT NULL COMMENT '设备出库ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备测试运行报告';

-- ----------------------------
-- Records of sb_purchases_assets_test_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_train
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_train`;
CREATE TABLE `sb_purchases_assets_train` (
  `train_id` int(11) NOT NULL AUTO_INCREMENT,
  `ware_ids` text COMMENT '出库设备ID',
  `train_user` varchar(60) DEFAULT NULL COMMENT '培训人',
  `train_start_date` date DEFAULT NULL COMMENT '培训日期始',
  `train_end_date` date DEFAULT NULL COMMENT '培训日期终',
  `train_area` varchar(255) DEFAULT NULL COMMENT '培训地点',
  `attendants_user` text COMMENT '参加人员',
  `train_desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_delete` tinyint(3) DEFAULT NULL COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`train_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备培训';

-- ----------------------------
-- Records of sb_purchases_assets_train
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_train_assessment_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_train_assessment_report`;
CREATE TABLE `sb_purchases_assets_train_assessment_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `train_id` int(11) DEFAULT NULL COMMENT '培训计划ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否，1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备临床培训考核报告';

-- ----------------------------
-- Records of sb_purchases_assets_train_assessment_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_assets_train_report
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_assets_train_report`;
CREATE TABLE `sb_purchases_assets_train_report` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `train_id` int(11) DEFAULT NULL COMMENT '培训计划ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否，1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备临床培训报告';

-- ----------------------------
-- Records of sb_purchases_assets_train_report
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_contract
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_contract`;
CREATE TABLE `sb_purchases_contract` (
  `contract_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '合同自增ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `record_id` varchar(100) DEFAULT NULL COMMENT '招标记录ID',
  `contract_num` varchar(100) DEFAULT NULL COMMENT '合同编号',
  `contract_name` varchar(100) DEFAULT NULL COMMENT '合同名称',
  `contract_type` tinyint(3) DEFAULT '1' COMMENT '合同类型【1采购合同 2维修合同 3保修合同 4维保合同 5配件合同】',
  `supplier_id` tinyint(3) DEFAULT NULL COMMENT '供应商ID',
  `supplier_name` varchar(100) DEFAULT NULL COMMENT '供应商名称',
  `supplier_contacts` varchar(60) DEFAULT NULL COMMENT '供应商联系人',
  `supplier_phone` varchar(30) DEFAULT NULL COMMENT '供应商联系电话',
  `sign_date` date DEFAULT NULL COMMENT '签订日期',
  `end_date` date DEFAULT NULL COMMENT '合同截止日期',
  `guarantee_date` date DEFAULT NULL COMMENT '合同设备保修截止日期',
  `contract_amount` decimal(10,2) DEFAULT NULL COMMENT '合同金额',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `archives_num` varchar(100) DEFAULT NULL COMMENT '档案编号',
  `archives_manager` varchar(60) DEFAULT NULL COMMENT '档案管理人员',
  `hospital_manager` varchar(60) DEFAULT NULL COMMENT '院方负责人',
  `contract_content` text COMMENT '合同内容',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人员',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '编辑人员',
  `edit_time` datetime DEFAULT NULL COMMENT '编辑时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `is_confirm` tinyint(1) DEFAULT '1' COMMENT '已确认【0否1是】',
  PRIMARY KEY (`contract_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——生产合同';

-- ----------------------------
-- Records of sb_purchases_contract
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_contract_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_contract_file`;
CREATE TABLE `sb_purchases_contract_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——采购合同附件';

-- ----------------------------
-- Records of sb_purchases_contract_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_contract_pay
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_contract_pay`;
CREATE TABLE `sb_purchases_contract_pay` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `pay_period` int(11) DEFAULT NULL COMMENT '付款期数',
  `estimate_pay_date` date DEFAULT NULL COMMENT '预计付款日期',
  `real_pay_date` date DEFAULT NULL COMMENT '实际付款日期',
  `pay_amount` decimal(10,2) DEFAULT NULL COMMENT '付款金额',
  `pay_status` tinyint(3) DEFAULT '0' COMMENT '付款状态【0未付款，1已付款】',
  `pay_user` varchar(60) DEFAULT NULL COMMENT '付款人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `supplier_id` int(10) DEFAULT NULL COMMENT '乙方单位id',
  `contract_type` tinyint(1) DEFAULT '1' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '乙方单位',
  `contract_name` varchar(255) DEFAULT NULL COMMENT '合同名称',
  `contract_num` varchar(150) DEFAULT NULL COMMENT '合同编号',
  PRIMARY KEY (`pay_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——采购合同付款信息';

-- ----------------------------
-- Records of sb_purchases_contract_pay
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_depart_apply
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_depart_apply`;
CREATE TABLE `sb_purchases_depart_apply` (
  `apply_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `apply_num` varchar(30) DEFAULT NULL COMMENT '申请单号',
  `plans_id` int(11) DEFAULT NULL COMMENT '计划ID【计划内的必填，计划外的不需填写】',
  `project_name` varchar(60) DEFAULT NULL COMMENT '项目名称',
  `apply_type` tinyint(3) DEFAULT '1' COMMENT '申请方式【1计划内，2计划外】',
  `apply_departid` int(11) DEFAULT NULL COMMENT '申请科室',
  `apply_user` varchar(60) DEFAULT NULL COMMENT '申请人',
  `apply_time` datetime DEFAULT NULL COMMENT '申请时间',
  `apply_reason` varchar(255) DEFAULT NULL COMMENT '申请理由',
  `expert_review` tinyint(3) DEFAULT '0' COMMENT '是否需要走专家评审流程【0否1是】',
  `assets_nums` int(11) DEFAULT NULL COMMENT '设备总数',
  `assets_amount` decimal(10,2) DEFAULT NULL COMMENT '设备总金额',
  `approve_status` tinyint(3) DEFAULT '0' COMMENT '审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` datetime DEFAULT NULL COMMENT '最后审核时间',
  `current_approver` varchar(255) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已完成审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未完成审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '全部审批人',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否，1是】',
  PRIMARY KEY (`apply_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——科室采购计划设备明细';

-- ----------------------------
-- Records of sb_purchases_depart_apply
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_depart_apply_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_depart_apply_assets`;
CREATE TABLE `sb_purchases_depart_apply_assets` (
  `assets_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `contract_id` int(11) DEFAULT NULL COMMENT '合同id',
  `contract_time` datetime DEFAULT NULL COMMENT '合同签订时间',
  `apply_id` int(11) DEFAULT NULL COMMENT '计划明细ID',
  `apply_user` varchar(60) DEFAULT NULL COMMENT '申请人',
  `apply_date` date DEFAULT NULL COMMENT '申请日期',
  `project_name` varchar(100) DEFAULT NULL COMMENT '项目名称',
  `assets_name` varchar(60) DEFAULT NULL COMMENT '设备名称',
  `assets_level` tinyint(3) DEFAULT NULL COMMENT '医疗器械类别【Ⅰ类=1、Ⅱ类=2、Ⅲ类=3】',
  `unit` varchar(20) DEFAULT NULL COMMENT '单位',
  `nums` int(11) DEFAULT NULL COMMENT '数量',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '预计总价',
  `is_import` tinyint(3) DEFAULT '0' COMMENT '是否进口【0否1是】',
  `buy_type` tinyint(3) DEFAULT NULL COMMENT '购置类型【1报废更新，2添置，3新增】',
  `brand` text COMMENT '参考品牌',
  `actually_brand` varchar(60) DEFAULT NULL COMMENT '实际中标品牌',
  `assets_num` varchar(60) DEFAULT NULL COMMENT '设备编号',
  `alias_name` varchar(60) DEFAULT NULL COMMENT '设备别名',
  `catid` int(11) DEFAULT NULL COMMENT '分类ID',
  `assfromid` tinyint(3) DEFAULT NULL COMMENT '设备来源',
  `helpcatid` tinyint(3) DEFAULT NULL COMMENT '辅助分类',
  `buy_price` decimal(10,2) DEFAULT NULL COMMENT '设备原值',
  `paytime` date DEFAULT NULL COMMENT '付款时间',
  `pay_status` tinyint(3) DEFAULT '3' COMMENT '设备付款是否付清【1已付清0未付清】',
  `serialnum` text COMMENT '序列号',
  `expected_life` tinyint(3) DEFAULT NULL COMMENT '预计使用年限',
  `departid` int(11) DEFAULT NULL COMMENT '所属科室',
  `managedepart` varchar(60) DEFAULT NULL COMMENT '管理科室',
  `address` varchar(255) DEFAULT NULL COMMENT '存放地点',
  `model` varchar(50) DEFAULT NULL COMMENT '规格型号',
  `supplier_id` int(11) DEFAULT NULL COMMENT '供应商ID',
  `supplier` varchar(60) DEFAULT NULL COMMENT '供应商名称',
  `factory_id` int(11) DEFAULT NULL COMMENT '生产厂商ID',
  `factory` varchar(60) DEFAULT NULL COMMENT '生产厂商名称',
  `repair_id` int(11) DEFAULT NULL COMMENT '维修商ID',
  `repair` varchar(60) DEFAULT NULL COMMENT '维修商名称',
  `maintain_id` int(11) DEFAULT NULL COMMENT '维保服务商id',
  `maintain` varchar(60) DEFAULT NULL COMMENT '维保服务商',
  `guarantee_year` int(11) DEFAULT NULL COMMENT '保修期限(年)',
  `guarantee_date` date DEFAULT NULL COMMENT '保修截止日期',
  `factorydate` date DEFAULT NULL COMMENT '出厂日期',
  `opendate` date DEFAULT NULL COMMENT '启用日期',
  `assetsrespon` varchar(60) DEFAULT NULL COMMENT '设备负责人',
  `factorynum` text COMMENT '出厂编号',
  `invoicenum` text COMMENT '发票编号',
  `financeid` tinyint(3) unsigned DEFAULT '0' COMMENT '财务分类',
  `capitalfrom` tinyint(3) DEFAULT '0' COMMENT '资金来源',
  `depreciation_method` tinyint(3) DEFAULT '0' COMMENT '折旧方式',
  `depreciable_lives` int(11) DEFAULT NULL COMMENT '折旧年限',
  `is_firstaid` tinyint(3) DEFAULT '0' COMMENT '是否急救设备【0否1是】',
  `is_special` tinyint(3) DEFAULT '0' COMMENT '是否特种设备【0否1是】',
  `is_metering` tinyint(3) DEFAULT '0' COMMENT '是否计量资产【0否1是】',
  `is_qualityAssets` tinyint(3) DEFAULT '0' COMMENT '是否质控设备【0否1是】',
  `is_patrol` tinyint(3) DEFAULT '0' COMMENT '是否保养设备【1是0否】',
  `is_domestic` tinyint(3) DEFAULT '3' COMMENT '设备是否国产【1国产2进口】',
  `is_benefit` tinyint(3) DEFAULT '0' COMMENT '是否效益分析设备【0否1是】',
  `is_lifesupport` tinyint(3) DEFAULT '0' COMMENT '是否生命支持类设备【0否1是】',
  `check_user` varchar(60) DEFAULT NULL COMMENT '验收人',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `arrival_date` date DEFAULT NULL COMMENT '到货日期',
  `check_desc` text COMMENT '验收情况',
  `arrival_notice` varchar(255) DEFAULT NULL COMMENT '到货通知单',
  `confirm_file` varchar(255) DEFAULT NULL COMMENT '设备技术标准确认表',
  `configuration_list` varchar(255) DEFAULT NULL COMMENT '设备配置清单',
  `technical_parameter_list` varchar(255) DEFAULT NULL COMMENT '设备技术参数表',
  `service_clause` varchar(255) DEFAULT NULL COMMENT '设备技术服务条款',
  `registration_certificate` varchar(255) DEFAULT NULL COMMENT '中华人民共和国医疗器械注册证',
  `instructions` tinyint(3) DEFAULT '0' COMMENT '是否附带说明书【0否1是】',
  `certificate` tinyint(3) DEFAULT '0' COMMENT '是否附带合格证【0否1是】',
  `repair_card` tinyint(3) DEFAULT '0' COMMENT '是否附带报修卡【0否1是】',
  `inspection_report` tinyint(3) DEFAULT '0' COMMENT '是否附带检验报告单【0否1是】',
  `customs_declaration` tinyint(3) DEFAULT '0' COMMENT '是否附带报关单【0否1是】',
  `is_check` tinyint(1) DEFAULT '0' COMMENT '验收状态 0未验收 1已验收',
  `check_time` datetime DEFAULT NULL COMMENT '验收时间',
  `is_ware` tinyint(3) DEFAULT '0' COMMENT '是否已入库【0未申请1审核中2已通过3不通过】',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否已删除【0否1是】',
  PRIMARY KEY (`assets_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——采购计划设备明细';

-- ----------------------------
-- Records of sb_purchases_depart_apply_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_depart_apply_checkassets_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_depart_apply_checkassets_file`;
CREATE TABLE `sb_purchases_depart_apply_checkassets_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_id` int(11) DEFAULT NULL COMMENT '设备id',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `style` tinyint(1) DEFAULT NULL COMMENT '[0:普通附件][1:到货通知单][2:设备技术标准确认表][3:设备配置清单][4:设备技术参数表][5:设备技术服务条款][6:中华人民共和国医疗器械注册证]',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——验收设备附件表';

-- ----------------------------
-- Records of sb_purchases_depart_apply_checkassets_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_depart_apply_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_depart_apply_file`;
CREATE TABLE `sb_purchases_depart_apply_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `apply_id` int(11) DEFAULT NULL COMMENT '申请单ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——采购计划设备明细';

-- ----------------------------
-- Records of sb_purchases_depart_apply_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_expert_review
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_expert_review`;
CREATE TABLE `sb_purchases_expert_review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` tinyint(3) DEFAULT NULL COMMENT '医院ID',
  `apply_id` int(11) DEFAULT NULL COMMENT '申请单ID',
  `expert_name` varchar(60) DEFAULT NULL COMMENT '专家名字',
  `review_time` datetime DEFAULT NULL COMMENT '评审时间',
  `installation` tinyint(3) DEFAULT '0' COMMENT '安装条件【4完备3较完备2欠完备1不完备】',
  `business` tinyint(3) DEFAULT '0' COMMENT '病源/业务量评估【4充足3较多2一般1较少】',
  `rationality` tinyint(3) DEFAULT '0' COMMENT '合理性【4很好3较好2一般1差】',
  `technical` tinyint(3) DEFAULT '0' COMMENT '技术水平【4很好3较好2一般1差】',
  `benefit` tinyint(3) DEFAULT '0' COMMENT '经济效益/支撑医院发展的程度【4很高3较高2一般1差】',
  `necessity` tinyint(3) DEFAULT '0' COMMENT '必要性【4急需3非急需2需要1非必要】',
  `project_desc` varchar(255) DEFAULT NULL COMMENT '项目论证审批意见',
  `repair` tinyint(3) DEFAULT '0' COMMENT '可维修性【4很好3较好2一般1差】',
  `safety` tinyint(3) DEFAULT '0' COMMENT '安全性【4安全3较低风险2较高风险1危险】',
  `matching` tinyint(3) DEFAULT '0' COMMENT '技术与需求匹配度【4非常匹配3较匹配2一般1不匹配】',
  `reliability` tinyint(3) DEFAULT '0' COMMENT '设备可靠性【4很好3较好2一般1差】',
  `technical_desc` varchar(255) DEFAULT NULL COMMENT '技术评价审批意见',
  `score` decimal(10,2) DEFAULT '0.00' COMMENT '评审得分',
  `review_status` tinyint(3) DEFAULT '0' COMMENT '评审状态【0未评审1已评审】',
  PRIMARY KEY (`review_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——专家评审表';

-- ----------------------------
-- Records of sb_purchases_expert_review
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_in_warehouse
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_in_warehouse`;
CREATE TABLE `sb_purchases_in_warehouse` (
  `in_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '入库ID',
  `hospital_id` tinyint(3) DEFAULT NULL COMMENT '医院ID',
  `supplier_id` int(11) DEFAULT NULL COMMENT '供应商ID',
  `supplier` varchar(100) DEFAULT NULL COMMENT '供应商名称',
  `in_num` varchar(20) DEFAULT NULL COMMENT '入库单号',
  `in_user` varchar(60) DEFAULT NULL COMMENT '入库人',
  `in_date` date DEFAULT NULL COMMENT '入库日期',
  `nums` int(11) DEFAULT NULL COMMENT '入库数量',
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '入库总金额',
  `approve_user` varchar(60) DEFAULT NULL COMMENT '审核人',
  `approve_time` datetime DEFAULT NULL COMMENT '审核时间',
  `approve_status` tinyint(3) DEFAULT '0' COMMENT '审核状态【0未审1通过2不通过】',
  `approve_desc` varchar(255) DEFAULT NULL COMMENT '审核意见',
  `in_desc` varchar(255) DEFAULT NULL COMMENT '入库备注',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`in_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备库';

-- ----------------------------
-- Records of sb_purchases_in_warehouse
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_in_warehouse_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_in_warehouse_assets`;
CREATE TABLE `sb_purchases_in_warehouse_assets` (
  `ware_assets_id` int(11) NOT NULL AUTO_INCREMENT,
  `in_id` int(11) DEFAULT NULL COMMENT '所属入库单ID',
  `hospital_id` varchar(255) DEFAULT NULL COMMENT '医院ID',
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `apply_id` int(11) DEFAULT NULL COMMENT '申请单ID',
  `assets_id` int(11) DEFAULT NULL COMMENT '设备ID',
  `assets_num` varchar(60) DEFAULT NULL COMMENT '设备编号',
  `assets_name` varchar(60) DEFAULT NULL COMMENT '设备名称',
  `model` varchar(60) DEFAULT NULL COMMENT '设备型号',
  `unit` varchar(10) DEFAULT NULL COMMENT '单位',
  `is_import` tinyint(3) DEFAULT '0' COMMENT '是否进口【0否1是】',
  `buy_type` tinyint(3) DEFAULT NULL COMMENT '购置类型【1报废新增2添置3新增】',
  `supplier_id` int(11) DEFAULT NULL COMMENT '供应商ID',
  `supplier` varchar(100) DEFAULT NULL COMMENT '供应商名称',
  `factory_id` int(11) DEFAULT NULL COMMENT '生产厂家ID',
  `factory` varchar(100) DEFAULT NULL COMMENT '生产厂家名称',
  `factorynum` varchar(100) DEFAULT NULL COMMENT '出厂编号',
  `serialnum` varchar(100) DEFAULT NULL COMMENT '序列号',
  `invoicenum` varchar(100) DEFAULT NULL COMMENT '发票编号',
  `departid` int(11) DEFAULT NULL COMMENT '申请科室ID',
  `catid` int(11) DEFAULT NULL COMMENT '设备分类',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `buy_price` decimal(10,2) DEFAULT NULL COMMENT '设备原值',
  `can_use` tinyint(3) DEFAULT '0' COMMENT '是否可用【0暂不可用1审核通过可用】',
  `is_out` tinyint(3) DEFAULT '0' COMMENT '是否已出库【0未出库1审核中2已出库】',
  PRIMARY KEY (`ware_assets_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——入库单设备列表';

-- ----------------------------
-- Records of sb_purchases_in_warehouse_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_inquiry_record
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_inquiry_record`;
CREATE TABLE `sb_purchases_inquiry_record` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `hospital_id` tinyint(3) DEFAULT NULL COMMENT '医院ID',
  `apply_id` int(11) DEFAULT NULL COMMENT '申请单ID',
  `apply_num` varchar(30) DEFAULT NULL COMMENT '申请单号',
  `apply_type` tinyint(3) DEFAULT NULL COMMENT '申请方式【1计划内，2计划外】',
  `apply_departid` tinyint(3) DEFAULT NULL COMMENT '申请科室',
  `apply_user` varchar(60) DEFAULT NULL COMMENT '申请人',
  `apply_time` datetime DEFAULT NULL COMMENT '申请时间',
  `project_name` varchar(60) DEFAULT NULL COMMENT '项目名称',
  `assets_id` int(11) DEFAULT NULL COMMENT '招标设备ID',
  `assets_name` varchar(60) DEFAULT NULL COMMENT '设备名称',
  `nums` int(11) DEFAULT NULL COMMENT '申请设备数量',
  `unit` varchar(20) DEFAULT NULL COMMENT '设备单位',
  `brand` varchar(60) DEFAULT NULL COMMENT '设备品牌',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '设备市场单价',
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '预算总额',
  `is_import` tinyint(3) DEFAULT '0' COMMENT '是否进口【0否1是】',
  `buy_type` tinyint(3) DEFAULT '0' COMMENT '采购类型【1报废更新2添置3新增】',
  `have_inquiry_record` tinyint(3) DEFAULT '0' COMMENT '是否已有询价记录【0否1是】',
  `have_final_supplier` tinyint(3) DEFAULT '0' COMMENT '是否已确认初步供货商【0否1是】',
  `supplier` varchar(100) DEFAULT NULL COMMENT '初步确认供应商',
  `factory` varchar(100) DEFAULT NULL COMMENT '初步确认厂家',
  `add_user` varchar(30) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `handle_user` varchar(60) DEFAULT NULL COMMENT '处理人',
  `handle_time` datetime DEFAULT NULL COMMENT '处理时间',
  `tender_status` tinyint(3) DEFAULT '0' COMMENT '标书状态【0未提交1已提交2已通过3已退回】',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——招标论证--询价记录';

-- ----------------------------
-- Records of sb_purchases_inquiry_record
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_inquiry_record_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_inquiry_record_detail`;
CREATE TABLE `sb_purchases_inquiry_record_detail` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` tinyint(3) DEFAULT NULL COMMENT '询价记录ID',
  `supplier_id` varchar(100) DEFAULT NULL COMMENT '供应商ID',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '供应商名称',
  `factory_id` int(11) DEFAULT NULL COMMENT '生产厂商ID',
  `factory_name` varchar(100) DEFAULT NULL COMMENT '生产厂商名称',
  `assets_id` int(11) DEFAULT NULL COMMENT '招标设备ID',
  `assets_name` varchar(100) DEFAULT NULL COMMENT '设备名称',
  `model` varchar(60) DEFAULT NULL COMMENT '型号',
  `brand` varchar(60) DEFAULT NULL COMMENT '品牌',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `company_price` decimal(10,2) DEFAULT NULL COMMENT '供应商报价',
  `guarantee_year` tinyint(3) DEFAULT NULL COMMENT '保修年限',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `final_select` tinyint(3) DEFAULT '0' COMMENT '最终选择【0否1是】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`detail_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——招标记录明细';

-- ----------------------------
-- Records of sb_purchases_inquiry_record_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_inquiry_record_detail_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_inquiry_record_detail_file`;
CREATE TABLE `sb_purchases_inquiry_record_detail_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `detail_id` int(11) DEFAULT NULL COMMENT '供应商ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——招标供应商附件';

-- ----------------------------
-- Records of sb_purchases_inquiry_record_detail_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_out_warehouse
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_out_warehouse`;
CREATE TABLE `sb_purchases_out_warehouse` (
  `out_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '出库ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `out_num` varchar(20) DEFAULT NULL COMMENT '出库单号',
  `departid` int(11) DEFAULT NULL COMMENT '领用科室ID',
  `out_user` varchar(60) DEFAULT NULL COMMENT '出库人',
  `out_date` date DEFAULT NULL COMMENT '出库日期',
  `nums` int(11) DEFAULT NULL COMMENT '出库数量',
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '出库总金额',
  `approve_user` varchar(60) DEFAULT NULL COMMENT '审核人',
  `approve_time` datetime DEFAULT NULL COMMENT '审核时间',
  `approve_status` tinyint(3) DEFAULT '0' COMMENT '审核状态【0未审1通过2不通过】',
  `approve_desc` varchar(255) DEFAULT NULL COMMENT '审核备注',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`out_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——设备出库表';

-- ----------------------------
-- Records of sb_purchases_out_warehouse
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_out_warehouse_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_out_warehouse_assets`;
CREATE TABLE `sb_purchases_out_warehouse_assets` (
  `ware_assets_id` int(11) NOT NULL AUTO_INCREMENT,
  `out_id` int(11) DEFAULT NULL COMMENT '所属出库单ID',
  `hospital_id` varchar(255) DEFAULT NULL COMMENT '医院ID',
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `apply_id` int(11) DEFAULT NULL COMMENT '申请单ID',
  `assets_id` int(11) DEFAULT NULL COMMENT '设备ID',
  `assets_num` varchar(60) DEFAULT NULL COMMENT '设备编号',
  `assets_name` varchar(60) DEFAULT NULL COMMENT '设备名称',
  `model` varchar(60) DEFAULT NULL COMMENT '设备型号',
  `unit` varchar(10) DEFAULT NULL COMMENT '单位',
  `is_import` tinyint(3) DEFAULT '0' COMMENT '是否进口【0否1是】',
  `buy_type` tinyint(3) DEFAULT NULL COMMENT '购置类型【1报废新增2添置3新增】',
  `supplier_id` int(11) DEFAULT NULL COMMENT '供应商ID',
  `supplier` varchar(100) DEFAULT NULL COMMENT '供应商名称',
  `factory_id` int(11) DEFAULT NULL COMMENT '生产厂家ID',
  `factory` varchar(100) DEFAULT NULL COMMENT '生产厂家名称',
  `factorynum` varchar(60) DEFAULT NULL COMMENT '出厂编号',
  `serialnum` varchar(100) DEFAULT NULL COMMENT '序列号',
  `invoicenum` varchar(100) DEFAULT NULL COMMENT '发票编码',
  `departid` int(11) DEFAULT NULL COMMENT '申请科室ID',
  `catid` int(11) DEFAULT NULL COMMENT '设备分类',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `buy_price` decimal(10,2) DEFAULT NULL COMMENT '设备原值',
  `can_use` tinyint(3) DEFAULT '0' COMMENT '是否可用【0暂不可用1审核通过可用】',
  `debug_status` tinyint(3) DEFAULT '0' COMMENT '调试状态【0未调试1调试中2已完成】',
  `plans_status` tinyint(3) DEFAULT '0' COMMENT '培训计划制定状态【0未制定1已制定】',
  `train_id` int(11) DEFAULT '0' COMMENT '培训计划ID',
  `train_status` tinyint(3) DEFAULT '0' COMMENT '培训状态【0未培训1培训中2已完成】',
  `assessment_status` tinyint(3) DEFAULT '0' COMMENT '培训考核状态【0未上传1已上传】',
  `test_status` tinyint(3) DEFAULT '0' COMMENT '测试运行报告【0未上传1已上传】',
  `firstMetering_status` tinyint(3) DEFAULT '0' COMMENT '首次计量报告【0未上传1已上传】',
  `quality_status` tinyint(3) DEFAULT '0' COMMENT '质量验收报告状态【0未上传1已上传】',
  `in_assetsinfo` tinyint(3) DEFAULT '0' COMMENT '是否已入信息库【0否1是】',
  PRIMARY KEY (`ware_assets_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——出库单设备列表';

-- ----------------------------
-- Records of sb_purchases_out_warehouse_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_plans
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_plans`;
CREATE TABLE `sb_purchases_plans` (
  `plans_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '计划ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `plans_num` varchar(20) DEFAULT NULL COMMENT '计划编号',
  `project_name` varchar(60) NOT NULL DEFAULT '' COMMENT '项目名称',
  `plans_year` year(4) DEFAULT NULL COMMENT '计划年份',
  `plans_start` date DEFAULT NULL COMMENT '计划开始日期',
  `plans_end` date DEFAULT NULL COMMENT '计划结束日期',
  `departid` int(11) DEFAULT '0' COMMENT '计划科室',
  `plans_status` tinyint(3) DEFAULT '1' COMMENT '计划状态【1启用，0停用】',
  `plans_desc` text COMMENT '计划备注',
  `apply_user` varchar(60) DEFAULT NULL COMMENT '计划上报人',
  `apply_time` datetime DEFAULT NULL COMMENT '计划上报时间',
  `apply_status` tinyint(3) DEFAULT '0' COMMENT '计划上报状态【0未上报1已上报】',
  `apply_date` date DEFAULT NULL COMMENT '计划上报日期',
  `assets_nums` int(11) DEFAULT NULL COMMENT '计划上报设备数量',
  `can_apply_nums` int(11) DEFAULT NULL COMMENT '计划内还可采购设备数量',
  `assets_amount` decimal(10,2) DEFAULT NULL COMMENT '上报设备总金额',
  `apply_reason` text COMMENT '计划上报理由',
  `approve_status` tinyint(3) DEFAULT '0' COMMENT '审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` datetime DEFAULT NULL COMMENT '最后审核时间',
  `current_approver` varchar(255) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已完成审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未完成审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '所有审批人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '计划创建人',
  `add_time` datetime DEFAULT NULL COMMENT '计划创建时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '计划修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '计划修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否已删除【0否1是】',
  PRIMARY KEY (`plans_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理—采购计划';

-- ----------------------------
-- Records of sb_purchases_plans
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_plans_assets
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_plans_assets`;
CREATE TABLE `sb_purchases_plans_assets` (
  `assets_id` int(11) NOT NULL AUTO_INCREMENT,
  `plans_id` int(11) DEFAULT NULL COMMENT '计划ID',
  `assets_name` varchar(60) DEFAULT NULL COMMENT '设备名称',
  `unit` varchar(20) DEFAULT NULL COMMENT '单位',
  `nums` int(11) DEFAULT NULL COMMENT '数量',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '预计总价',
  `is_import` tinyint(3) DEFAULT '0' COMMENT '是否进口【0否1是】',
  `buy_type` tinyint(3) DEFAULT NULL COMMENT '购置类型【1报废更新，2添置，3新增】',
  `brand` text COMMENT '参考品牌',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否已删除【0否1是】',
  PRIMARY KEY (`assets_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——采购计划上报设备明细';

-- ----------------------------
-- Records of sb_purchases_plans_assets
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_plans_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_plans_file`;
CREATE TABLE `sb_purchases_plans_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `plans_id` int(11) DEFAULT NULL COMMENT '计划ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——采购计划上报设备附件';

-- ----------------------------
-- Records of sb_purchases_plans_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_tender_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_tender_detail`;
CREATE TABLE `sb_purchases_tender_detail` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` tinyint(3) DEFAULT NULL COMMENT '招标记录ID',
  `supplier_id` varchar(100) DEFAULT NULL COMMENT '供应商ID',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '供应商名称',
  `factory_id` int(11) DEFAULT NULL COMMENT '生产厂商ID',
  `factory_name` varchar(100) DEFAULT NULL COMMENT '生产厂商名称',
  `assets_id` int(11) DEFAULT NULL COMMENT '招标设备ID',
  `assets_name` varchar(100) DEFAULT NULL COMMENT '设备名称',
  `model` varchar(60) DEFAULT NULL COMMENT '型号',
  `brand` varchar(60) DEFAULT NULL COMMENT '品牌',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `company_price` decimal(10,2) DEFAULT NULL COMMENT '供应商报价',
  `guarantee_year` tinyint(3) DEFAULT NULL COMMENT '保修年限',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `final_select` tinyint(3) DEFAULT '0' COMMENT '最终选择【0否1是】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`detail_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——招标记录明细';

-- ----------------------------
-- Records of sb_purchases_tender_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_tender_detail_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_tender_detail_file`;
CREATE TABLE `sb_purchases_tender_detail_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `detail_id` int(11) DEFAULT NULL COMMENT '供应商ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——招标供应商附件';

-- ----------------------------
-- Records of sb_purchases_tender_detail_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_tender_record
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_tender_record`;
CREATE TABLE `sb_purchases_tender_record` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `apply_id` int(11) DEFAULT NULL COMMENT '申请单ID',
  `apply_num` varchar(30) DEFAULT NULL COMMENT '申请单号',
  `apply_type` tinyint(3) DEFAULT NULL COMMENT '申请方式【1计划内，2计划外】',
  `apply_departid` tinyint(3) DEFAULT NULL COMMENT '申请科室',
  `apply_user` varchar(60) DEFAULT NULL COMMENT '申请人',
  `apply_time` datetime DEFAULT NULL COMMENT '申请时间',
  `project_name` varchar(60) DEFAULT NULL COMMENT '项目名称',
  `assets_id` int(11) DEFAULT NULL COMMENT '招标设备ID',
  `assets_name` varchar(60) DEFAULT NULL COMMENT '设备名称',
  `nums` int(11) DEFAULT NULL COMMENT '申请设备数量',
  `unit` varchar(20) DEFAULT NULL COMMENT '设备单位',
  `brand` varchar(60) DEFAULT NULL COMMENT '设备品牌',
  `market_price` decimal(10,2) DEFAULT NULL COMMENT '设备市场单价',
  `total_budget` decimal(10,2) DEFAULT NULL COMMENT '预算总额',
  `is_import` tinyint(3) DEFAULT '0' COMMENT '是否进口【0否1是】',
  `buy_type` tinyint(3) DEFAULT NULL COMMENT '购置类型【1报废更新2添置3新增】',
  `tender_status` tinyint(3) DEFAULT '0' COMMENT '招标状态【0未招标1已招标2已确定】',
  `add_user` varchar(30) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `handle_user` varchar(30) DEFAULT NULL COMMENT '处理人',
  `handle_time` datetime DEFAULT NULL COMMENT '处理时间',
  `record_from` tinyint(3) DEFAULT '0' COMMENT '招标记录来源【0科室申请1招标论证】',
  `have_final_supplier` tinyint(3) DEFAULT '0' COMMENT '是否已确认供应商【0否1是】',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——招标记录';

-- ----------------------------
-- Records of sb_purchases_tender_record
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_tender_review
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_tender_review`;
CREATE TABLE `sb_purchases_tender_review` (
  `rev_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL COMMENT '记录ID',
  `review_user` varchar(60) DEFAULT NULL COMMENT '评审人',
  `review_time` datetime DEFAULT NULL COMMENT '评审时间',
  `review_status` tinyint(3) DEFAULT '0' COMMENT '评审状态【0未评审1已通过2已退回】',
  `submit_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `submit_time` datetime DEFAULT NULL COMMENT '提交时间',
  `submit_status` tinyint(3) DEFAULT '0' COMMENT '提交状态【0未提交1已提交】',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`rev_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——制定标书--标书评审';

-- ----------------------------
-- Records of sb_purchases_tender_review
-- ----------------------------


-- ----------------------------
-- Table structure for sb_purchases_tender_review_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_purchases_tender_review_file`;
CREATE TABLE `sb_purchases_tender_review_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL COMMENT '招标记录ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `is_pass` tinyint(3) DEFAULT '0' COMMENT '是否通过【0未处理1通过2退回】',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='采购管理——标书评审--标书记录';

-- ----------------------------
-- Records of sb_purchases_tender_review_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_details
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_details`;
CREATE TABLE `sb_quality_details` (
  `qdid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `qsid` int(11) NOT NULL COMMENT '启用ID',
  `exterior` tinyint(1) DEFAULT '1' COMMENT '外观功能,1为符合，2为不符合',
  `exterior_explain` varchar(255) DEFAULT NULL COMMENT '外观功能不符合情况说明',
  `preset_detection` text COMMENT '项目预设明细检测值，json格式{"heartRate":"[{"30(28~32)":"30","60(57~63)":"61","":""}]"}',
  `fixed_detection` text COMMENT '固定非值明细检测值，json格式',
  `pre_preset_detection` text COMMENT '前一次项目预设明细检测值',
  `pre_fixed_detection` text COMMENT '前一次固定非值明细检测值',
  `score` tinyint(1) DEFAULT NULL COMMENT '评分：1、2、3、4、5',
  `result` tinyint(1) DEFAULT NULL COMMENT '检测结果，1为合格、2为不合格',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `report` varchar(255) DEFAULT NULL COMMENT '检测报告地址',
  `date` date DEFAULT NULL COMMENT '录入日期',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `adduser` varchar(20) DEFAULT NULL COMMENT '添加人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `edituser` varchar(20) DEFAULT NULL COMMENT '修改人',
  PRIMARY KEY (`qdid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——计划实施表（明细录入）';

-- ----------------------------
-- Records of sb_quality_details
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_details_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_details_file`;
CREATE TABLE `sb_quality_details_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `qsid` int(11) DEFAULT NULL COMMENT '质控ID',
  `type` varchar(60) DEFAULT NULL COMMENT '照片类型【nameplate=铭牌照，instrument_view=检测仪器视图照】',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——质控明细照片记录';

-- ----------------------------
-- Records of sb_quality_details_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_details_patrol
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_details_patrol`;
CREATE TABLE `sb_quality_details_patrol` (
  `qsid` int(10) NOT NULL COMMENT '质控ID',
  `ppid` int(10) NOT NULL COMMENT '保养项目类型&明细ID',
  `result` varchar(64) NOT NULL COMMENT '保养结果（合格、修复、可用、待修）',
  `abnormal_remark` text COMMENT '异常处理详情',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `add_user` varchar(30) DEFAULT NULL COMMENT '添加人'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——质控计划明细录入--日常巡检记录';

-- ----------------------------
-- Records of sb_quality_details_patrol
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_detection_basis
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_detection_basis`;
CREATE TABLE `sb_quality_detection_basis` (
  `qdbid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `basis` varchar(255) NOT NULL COMMENT '检测依据名称',
  `content` text NOT NULL COMMENT '检测依据内容',
  `adduserid` int(11) NOT NULL COMMENT '添加者ID',
  `adddate` timestamp NULL DEFAULT NULL COMMENT '添加日期',
  PRIMARY KEY (`qdbid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——检测依据表';

-- ----------------------------
-- Records of sb_quality_detection_basis
-- ----------------------------
BEGIN;
INSERT INTO `sb_quality_detection_basis` (`qdbid`, `basis`, `content`, `adduserid`, `adddate`) VALUES (1, '监护仪质量检测技术规范', '', 0, NULL);
INSERT INTO `sb_quality_detection_basis` (`qdbid`, `basis`, `content`, `adduserid`, `adddate`) VALUES (2, '输液装置质量检测技术规范', '', 0, NULL);
INSERT INTO `sb_quality_detection_basis` (`qdbid`, `basis`, `content`, `adduserid`, `adddate`) VALUES (3, '除颤仪质量检测技术规范', '', 0, NULL);
INSERT INTO `sb_quality_detection_basis` (`qdbid`, `basis`, `content`, `adduserid`, `adddate`) VALUES (4, '呼吸机质量检测技术规范', '', 0, NULL);
INSERT INTO `sb_quality_detection_basis` (`qdbid`, `basis`, `content`, `adduserid`, `adddate`) VALUES (5, '特吃', '啊手动阀', 1, '2021-08-09 02:50:26');
INSERT INTO `sb_quality_detection_basis` (`qdbid`, `basis`, `content`, `adduserid`, `adddate`) VALUES (6, '检测依据标题-W', '&lt;p&gt;内容1&lt;/p&gt;&lt;p&gt;内容2&lt;/p&gt;', 179, '2023-03-03 07:09:57');
COMMIT;

-- ----------------------------
-- Table structure for sb_quality_instruments
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_instruments`;
CREATE TABLE `sb_quality_instruments` (
  `qiid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `instrument` varchar(255) NOT NULL COMMENT '仪器名称',
  `model` varchar(255) NOT NULL COMMENT '仪器规格/型号',
  `productid` varchar(100) NOT NULL DEFAULT '' COMMENT '产品序列号--注册号',
  `metering_date` date DEFAULT NULL COMMENT '计量检定日期',
  `metering_place` varchar(255) DEFAULT NULL COMMENT '计量单位',
  `metering_num` varchar(100) NOT NULL DEFAULT '' COMMENT '计量编号（证书编号）',
  `metering_report` varchar(255) DEFAULT NULL COMMENT '计量报告',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `adduser` varchar(20) DEFAULT NULL COMMENT '添加人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `edituser` varchar(20) DEFAULT NULL COMMENT '修改人',
  PRIMARY KEY (`qiid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——检测仪器表';

-- ----------------------------
-- Records of sb_quality_instruments
-- ----------------------------
BEGIN;
INSERT INTO `sb_quality_instruments` (`qiid`, `instrument`, `model`, `productid`, `metering_date`, `metering_place`, `metering_num`, `metering_report`, `addtime`, `adduser`, `edittime`, `edituser`) VALUES (1, '开测试', '123', '123', '2021-01-18', 'adf', 'asdfdf', '/Public/uploads/qualities/report/instruments/20210118/6004eda94bdd7.jpg', '2021-01-18 02:08:42', '牛年', NULL, NULL);
INSERT INTO `sb_quality_instruments` (`qiid`, `instrument`, `model`, `productid`, `metering_date`, `metering_place`, `metering_num`, `metering_report`, `addtime`, `adduser`, `edittime`, `edituser`) VALUES (5, '心电监护仪', '1234', '1234', '2024-04-15', '天成医疗技术股份有限公司', '2024001', '/Public/uploads/qualities/report/instruments/20240415/661c9a0111180.jpg', '2024-04-15 11:09:18', '牛年', NULL, NULL);
INSERT INTO `sb_quality_instruments` (`qiid`, `instrument`, `model`, `productid`, `metering_date`, `metering_place`, `metering_num`, `metering_report`, `addtime`, `adduser`, `edittime`, `edituser`) VALUES (6, '呼吸机', 'MEC-1000', '6070061045507', '2024-07-26', '1111', 'asdfdf', '/Public/uploads/qualities/report/instruments/20240724/66a0526c5f457.png', '2024-07-24 09:01:39', '小王', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for sb_quality_preset
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_preset`;
CREATE TABLE `sb_quality_preset` (
  `qprid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `detection_name` varchar(255) DEFAULT NULL COMMENT '检测名称',
  `detection_Ename` varchar(255) NOT NULL COMMENT '英文名（即表单输入框名）',
  `unit` varchar(50) DEFAULT NULL COMMENT '单位',
  `value` text NOT NULL COMMENT '预设值，格式：{"","",""}',
  `tolerance` varchar(100) DEFAULT NULL COMMENT '最大允差',
  `is_display` tinyint(3) DEFAULT '0' COMMENT '是否隐藏【0="不隐藏",1="隐藏"】',
  PRIMARY KEY (`qprid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——质控项目预设';

-- ----------------------------
-- Records of sb_quality_preset
-- ----------------------------
BEGIN;
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (1, '心率', 'heartRate', '次/min', '[\"30(28~32)\",\"60(57~63)\",\"100(95~105)\",\"120(114~126)\",\"180(171~189)\"]', '显示值的±5%+1个值', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (2, '呼吸率', 'breathRate', '次/min', '[\"15\",\"20\",\"40\",\"60\",\"80\"]', '±5%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (3, '无创血压', 'pressure', 'mmHg', '[\"75/45(55)\",\"100/65(77)\",\"120/80(93)\",\"150/100(117)\",\"180/120(140)\",\"270/220\\uff08240\\uff09\"]', '±1.3kPa(±10mmHg)', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (4, '流量检测', 'flow', 'ml/h', '[\"30\",\"40\",\"50\",\"60\"]', '±10%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (5, '阻塞报警检测', 'block', NULL, '[\"4\",\"5\",\"6\",\"7\",\"8\"]', '±2', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (6, '释放能量', 'energesis', 'J', '[\"60\",\"70\",\"80\",\"90\"]', '±5', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (7, '充电时间', 'charge', 's', '[\"300\",\"400\",\"500\",\"600\"]', '±10', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (8, '潮气量', 'humidity', 'ml', '[\"100\",\"500\",\"800\",\"1000\",\"9999\"]', '±10%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (9, '强制通气频率', 'aeration', 'BPM', '[\"10\",\"20\",\"30\",\"40\"]', '±5%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (10, '吸入氧浓度', 'IOI', '%', '[\"30\",\"60\",\"90\",\"120\"]', '±5%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (11, '吸气压力水平', 'IPAP', 'cmH₂O', '[\"10\",\"20\",\"30\",\"40\"]', '±3', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (12, '呼气末正压', 'PEEP', 'cmH₂O', '[\"5\",\"10\",\"15\",\"20\"]', '±2', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (13, '血氧饱和度', 'BOS', '%', '[\"85\",\"90\",\"95\",\"98\",\"100\"]', '±3%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (14, '保护接地阻抗', 'protection', 'mΩ', '[\"200\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (15, '绝缘阻抗(电源—外壳)', 'insulation', 'MΩ', '[\"10\"]', '+', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (16, '对地漏电流(正常状态)', 'earthleakagecurrent', 'μA', '[\"500\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (17, '外壳漏电流(正常状态)', 'Case_normal', 'μA', '[\"100\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (18, '外壳漏电流(地线断开)', 'Case_abnormal', 'μA', '[\"500\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (19, '患者漏电流(正常状态)', 'patient_normal', 'μA', '[\"100\",\"10\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (20, '患者漏电流(地线断开)', 'patient_abnormal', 'μA', '[\"500\",\"50\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (21, '患者辅助漏电流(正常状态)', 'aid_normal', 'μA', '[\"100\",\"10\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (22, '患者辅助漏电流(地线断开)', 'aid_abnormal', 'μA', '[\"500\",\"50\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (23, '单极电切', 'Unipolar_cutting', 'W', '[\"75(62.5~93.7)\",\"150(125~187.5)\",\"220\",\"300(250~375)\"]', '±20%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (24, '单极电凝', 'Unipolar_coagulation', 'W', '[\"30(25~37.5)\",\"60(50~75)\",\"90(75~112.5)\",\"120(100~150)\"]', '±20%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (25, '双极电切', 'Bipolar_resection', 'W', '[\"12.5(10.5~15.6)\",\"25(20.9~31.2)\",\"37.5(31.3~46.8)\",\"50(41.7~62.5)\"]', '±20%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (26, '双极电凝', 'Bipolar_coagulation', 'W', '[\"12.5(10.5~15.6)\",\"25(20.9~31.2)\",\"37.5(31.3~46.8)\",\"50(41.7~62.5)\"]', '±20%', 0);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (27, '单极模式', 'Unipolar_mode', 'mA', '[\"150\",\"150\",\"150\",\"150\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (28, '双极模式', 'Bipolar_mode', 'mA', '[\"60\",\"60\",\"60\",\"60\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (29, 'Tx显示温度', 'Tx', '℃', '[\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (30, '中心温度测试点T1', 'T1', '℃', '[\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (31, '中心温度测试点T2', 'T2', '℃', '[\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (32, '中心温度测试点T3', 'T3', '℃', '[\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (33, '中心温度测试点T4', 'T4', '℃', '[\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (34, '中心温度测试点T5', 'T5', '℃', '[\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\",\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (35, '显示温度平均值Txa', 'Txa', '℃', '[\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (36, '测量平均值T1a', 'T1a', '℃', '[\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (37, '测量平均值T2a', 'T2a', '℃', '[\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (38, '测量平均值T3a', 'T3a', '℃', '[\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (39, '测量平均值T4a', 'T4a', '℃', '[\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (40, '测量平均值T5a', 'T5a', '℃', '[\"0\"]', '±10000', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (41, '温度偏差值', 'Temperature_deviation', NULL, '[\"0.8\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (42, '温度均匀性', 'Temperature_uniformity', NULL, '[\"0.8\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (43, '波动度', 'Volatility', NULL, '[\"0.5\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (44, '温控偏差', 'Temperature_control_deviation', NULL, '[\"1.5\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (45, '箱内正常噪音检测', 'Normal_noise_detection_in_the_box', 'dB', '[\"60\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (46, '箱内报警噪音测试', 'Alarm_noise_test_in_the_box', 'dB', '[\"80\"]', '-', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (47, '报警声级测试', 'Alarm_sound_level_test', NULL, '[\"65\"]', '+', 1);
INSERT INTO `sb_quality_preset` (`qprid`, `detection_name`, `detection_Ename`, `unit`, `value`, `tolerance`, `is_display`) VALUES (48, '湿度测试', 'Humidity_detection', NULL, '[\"0\",\"0\"]', '±10%', 1);
COMMIT;

-- ----------------------------
-- Table structure for sb_quality_result
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_result`;
CREATE TABLE `sb_quality_result` (
  `resultid` int(10) NOT NULL AUTO_INCREMENT,
  `qsid` int(10) DEFAULT NULL COMMENT '计划id',
  `hospital_id` int(10) DEFAULT NULL COMMENT '医院id',
  `qtemid` int(10) DEFAULT NULL COMMENT '模板id',
  `assid` int(10) DEFAULT NULL COMMENT '设备id',
  `detection_Ename` varchar(255) DEFAULT NULL COMMENT '英文名',
  `detection_name` varchar(255) DEFAULT NULL COMMENT '检测名称',
  `unit` varchar(100) DEFAULT NULL COMMENT '单位',
  `add_date` date DEFAULT NULL COMMENT '记录日期',
  `is_conformity` tinyint(1) DEFAULT '1' COMMENT '是否符合 1符合 2不符合 3不适用',
  `edit_date` date DEFAULT NULL COMMENT '修改日期',
  PRIMARY KEY (`resultid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——质控项目执行记录';

-- ----------------------------
-- Records of sb_quality_result
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_result_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_result_detail`;
CREATE TABLE `sb_quality_result_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `resultid` int(10) DEFAULT NULL COMMENT '对应结果记录id',
  `detail_name` varchar(255) DEFAULT NULL COMMENT '明细项名称',
  `detail_Ename` varchar(255) DEFAULT NULL COMMENT '明细项英文名',
  `scope_value` varchar(100) DEFAULT NULL COMMENT '范围值',
  `measured_value` varchar(255) DEFAULT NULL COMMENT '实测值',
  `tolerance` varchar(100) DEFAULT NULL COMMENT '最大允差',
  `add_date` date DEFAULT NULL COMMENT '记录日期',
  `is_conformity` tinyint(1) DEFAULT '1' COMMENT '是否符合 1符合 2不符合 3不适用',
  `edit_date` date DEFAULT NULL COMMENT '修改日期',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——质控项目执行记录明细';

-- ----------------------------
-- Records of sb_quality_result_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_starts
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_starts`;
CREATE TABLE `sb_quality_starts` (
  `qsid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `plan_identifier` varchar(20) DEFAULT NULL COMMENT '计划标识符',
  `plans` int(11) DEFAULT NULL COMMENT '计划数',
  `hospital_id` int(11) DEFAULT NULL COMMENT '所属医院id',
  `assid` int(11) NOT NULL COMMENT '设备ID',
  `userid` int(11) NOT NULL COMMENT '计划分配检测人ID',
  `username` varchar(50) NOT NULL COMMENT '计划分配检测人名称',
  `qtemid` int(11) DEFAULT NULL COMMENT '模板ID',
  `qdbid` int(11) DEFAULT NULL COMMENT '检测依据ID',
  `qiid` int(11) DEFAULT NULL COMMENT '检测仪器ID',
  `qi_model` varchar(255) DEFAULT NULL COMMENT '检测仪器规格型号',
  `qi_productid` varchar(255) DEFAULT NULL COMMENT '检测仪器产品序列号',
  `qi_metering_num` varchar(255) DEFAULT NULL COMMENT '检测仪器计量编号',
  `plan_name` varchar(255) NOT NULL COMMENT '质控计划名称',
  `plan_num` varchar(255) NOT NULL COMMENT '质控计划编号，QC开头',
  `plan_remark` varchar(255) DEFAULT NULL COMMENT '质控计划备注',
  `do_date` date DEFAULT NULL COMMENT '预计执行日期',
  `end_date` date DEFAULT NULL COMMENT '本期预计结束日期',
  `is_cycle` tinyint(1) DEFAULT NULL COMMENT '是否周期执行,1是0否',
  `cycle` int(10) DEFAULT NULL COMMENT '周期',
  `period` int(11) DEFAULT NULL COMMENT '期次',
  `start_preset` text COMMENT '启用的项目明细，格式{"heartRate":"{"30(28~32)","60(57~63)","100(95~105)","120(114~126)","180(171~189)"}","breathRate":"{"15","20","40","60","80"}"}',
  `tolerance` text COMMENT '各项目明细的最大允差值',
  `is_start` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用，0为未启用1为启用2为暂停3为完成4为结束',
  `start_date` date DEFAULT NULL COMMENT '启用日期',
  `start_userid` int(11) DEFAULT NULL COMMENT '谁启用的',
  `start_username` varchar(20) DEFAULT NULL COMMENT '启用人',
  `stop_date` date DEFAULT NULL COMMENT '暂停日期',
  `stop_userid` int(11) DEFAULT NULL COMMENT '谁暂停的',
  `stop_username` varchar(20) DEFAULT NULL COMMENT '停用人',
  `addtime` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `adduser` varchar(20) DEFAULT NULL COMMENT '添加人',
  `edittime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `edituser` varchar(20) DEFAULT NULL COMMENT '修改人',
  `keepdata` text COMMENT '暂存质控数据',
  PRIMARY KEY (`qsid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——计划启用表';

-- ----------------------------
-- Records of sb_quality_starts
-- ----------------------------


-- ----------------------------
-- Table structure for sb_quality_template_fixed_details
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_template_fixed_details`;
CREATE TABLE `sb_quality_template_fixed_details` (
  `qtdid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `qtemid` int(11) NOT NULL COMMENT '模板表ID',
  `asset_name` varchar(255) DEFAULT NULL COMMENT '固定非值项适合设备',
  `fixed_detection_name` varchar(255) NOT NULL COMMENT '固定明细中文名',
  `fixed_detection_Ename` varchar(255) NOT NULL COMMENT '固定明细英文名，即表单输入框名',
  PRIMARY KEY (`qtdid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——模板固定非值明细表';

-- ----------------------------
-- Records of sb_quality_template_fixed_details
-- ----------------------------
BEGIN;
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (1, 1, '监护仪', '声光报警', 'audible_and_visual_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (2, 1, '监护仪', '报警限检查', 'alarm_limit');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (3, 1, '监护仪', '静音检查', 'mute');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (4, 2, '输液装置', '堵塞', 'blocking');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (5, 2, '输液装置', '即将空瓶', 'forthcoming_empty_bottle');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (6, 2, '输液装置', '电池电量不足', 'battery_low');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (7, 2, '输液装置', '流速错误', 'flow_error');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (8, 2, '输液装置', '输液管路安装不妥', 'improper_installation');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (9, 2, '输液装置', '气泡报警', 'bubble_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (10, 2, '输液装置', '电源线脱开', 'power_line_disconnect');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (11, 2, '输液装置', '开门报警', 'open_door_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (12, 3, '除颤仪', '内部放电', 'internal_discharge');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (13, 4, '呼吸机', '容量预制模式', 'capacity_precut_mode');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (14, 4, '呼吸机', '流量触发功能', 'traffic_trigger');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (15, 4, '呼吸机', '压力预制模式', 'pressure_precut_mode');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (16, 4, '呼吸机', '压力触发功能', 'pressure_tigger');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (17, 4, '呼吸机', '电源报警', 'power_supply_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (18, 4, '呼吸机', '氧浓度上/下限报警', 'oxygen_concentration_bound');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (19, 4, '呼吸机', '气源报警', 'gas_supply_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (20, 4, '呼吸机', '窒息报警', 'apnea_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (21, 4, '呼吸机', '气道压力上/下限报警', 'AWP_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (22, 4, '呼吸机', '病人回路过压保护功能', 'loop_overvoltage_pretection');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (23, 4, '呼吸机', '分钟通气量上/下限报警', 'minute_ventilation_bound');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (24, 4, '呼吸机', '按键功能检查（含键盘锁）', 'press_key');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (25, 5, '通用电气', '应用类型', 'App_types');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (26, 6, '高频电刀', '接触电阻监测', 'Contact_resistance_monitoring');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (27, 6, '高频电刀', '声光报警', 'Sound_and_light_alarm_function');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (28, 7, '婴儿培养箱', '断电报警', 'Power_failure_alarm');
INSERT INTO `sb_quality_template_fixed_details` (`qtdid`, `qtemid`, `asset_name`, `fixed_detection_name`, `fixed_detection_Ename`) VALUES (29, 7, '婴儿培养箱', '超温报警', 'Over_temperature_alarm');
COMMIT;

-- ----------------------------
-- Table structure for sb_quality_templates
-- ----------------------------
DROP TABLE IF EXISTS `sb_quality_templates`;
CREATE TABLE `sb_quality_templates` (
  `qtemid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '模板中文名',
  `template_name` varchar(255) NOT NULL COMMENT '模板文件名',
  PRIMARY KEY (`qtemid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质控管理——质控模板表';

-- ----------------------------
-- Records of sb_quality_templates
-- ----------------------------
BEGIN;
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (1, '监护仪', 'Tem_JianHuYi');
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (2, '输液装置', 'Tem_ShuYeZhuangZhi');
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (3, '除颤仪', 'Tem_ChuChanYi');
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (4, '呼吸机', 'Tem_HuXiJi');
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (5, '通用电气', 'Tem_TongYongDianQi');
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (6, '高频电刀', 'Tem_GaoPingDianDao');
INSERT INTO `sb_quality_templates` (`qtemid`, `name`, `template_name`) VALUES (7, '婴儿培养箱', 'Tem_YingErPeiYangXiang');
COMMIT;

-- ----------------------------
-- Table structure for sb_qualiyt_preset_template
-- ----------------------------
DROP TABLE IF EXISTS `sb_qualiyt_preset_template`;
CREATE TABLE `sb_qualiyt_preset_template` (
  `qprid` int(11) NOT NULL COMMENT '质控项目明细ID',
  `qtemid` int(11) NOT NULL COMMENT '模板ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='质控管理——项目明细与模板关系表';

-- ----------------------------
-- Records of sb_qualiyt_preset_template
-- ----------------------------
BEGIN;
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (1, 1);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (13, 1);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (3, 1);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (6, 3);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (4, 2);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (5, 2);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (2, 1);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (7, 3);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (1, 3);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (11, 4);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (10, 4);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (9, 4);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (8, 4);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (12, 4);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (14, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (15, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (16, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (17, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (18, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (19, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (20, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (21, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (22, 5);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (23, 6);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (24, 6);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (25, 6);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (26, 6);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (27, 6);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (28, 6);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (29, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (30, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (31, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (32, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (33, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (34, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (35, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (36, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (37, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (38, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (39, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (40, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (41, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (42, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (43, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (44, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (45, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (46, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (47, 7);
INSERT INTO `sb_qualiyt_preset_template` (`qprid`, `qtemid`) VALUES (48, 7);
COMMIT;

-- ----------------------------
-- Table structure for sb_repair
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair`;
CREATE TABLE `sb_repair` (
  `repid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '维修单ID',
  `contract_id` int(11) DEFAULT NULL COMMENT '合同id',
  `repnum` varchar(50) NOT NULL DEFAULT '' COMMENT '维修单编号',
  `archives_num` varchar(50) DEFAULT NULL COMMENT '线下档案编号',
  `assid` int(10) unsigned NOT NULL COMMENT '资产ID',
  `code` varchar(10) DEFAULT NULL COMMENT '医院代码',
  `part_num` int(11) DEFAULT '0' COMMENT '配件数量',
  `company_total_price` decimal(10,2) DEFAULT '0.00' COMMENT '第三方总费用',
  `part_total_price` decimal(10,2) DEFAULT '0.00' COMMENT '配件总费用',
  `assnum` varchar(25) NOT NULL COMMENT '资产编号',
  `assets` varchar(255) NOT NULL COMMENT '资产名称',
  `status` tinyint(3) DEFAULT '1' COMMENT '报修状态[-1:已撤单] [1:已报修] [2:已接单] [3:已检修] [4:报价中] [5:审核中] [6:维修中] [7:维修完成] [8已:验收]',
  `guarantee_id` int(11) DEFAULT NULL COMMENT '保修厂家id',
  `guarantee_name` varchar(150) DEFAULT NULL COMMENT '保修厂家',
  `salesman_name` varchar(100) DEFAULT NULL COMMENT '保修厂联系人',
  `salesman_phone` varchar(20) DEFAULT NULL COMMENT '保修厂联系方式',
  `repair_type` tinyint(1) DEFAULT NULL COMMENT '维修性质【0自修1厂家2第三方】',
  `model` varchar(255) DEFAULT NULL COMMENT '规格/型号',
  `factorynum` varchar(255) DEFAULT NULL COMMENT '资产出厂编号',
  `factory` varchar(255) DEFAULT NULL COMMENT '生产厂商',
  `opendate` int(10) DEFAULT '0' COMMENT '开机/启用日期',
  `departid` int(11) NOT NULL COMMENT '科室id',
  `department` varchar(255) DEFAULT NULL COMMENT '报修科室',
  `is_guarantee` tinyint(1) DEFAULT '0' COMMENT '维修单来源 pc端 1 微信端0',
  `assprice` float(10,2) DEFAULT '0.00' COMMENT '资产价格',
  `applicant` varchar(60) NOT NULL DEFAULT '' COMMENT '申报人',
  `applicant_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申报时间',
  `applicant_tel` varchar(20) DEFAULT NULL COMMENT '申报人电话',
  `breakdown` text NOT NULL COMMENT '故障描述',
  `pic_url` text COMMENT '维修故障图片地址',
  `applicant_remark` varchar(255) DEFAULT NULL COMMENT '报修备注',
  `assign` varchar(60) DEFAULT 'system' COMMENT '派工人',
  `assign_time` int(11) DEFAULT NULL COMMENT '派工时间',
  `assign_tel` varchar(20) DEFAULT NULL COMMENT '派工人电话',
  `assign_remark` varchar(255) DEFAULT NULL COMMENT '派单备注',
  `is_assign` tinyint(3) DEFAULT '0' COMMENT '是否人工派单',
  `assign_engineer` varchar(60) DEFAULT NULL COMMENT '指派接单工程师',
  `response` varchar(60) DEFAULT NULL COMMENT '接单人',
  `response_date` int(10) DEFAULT '0' COMMENT '接单时间',
  `response_tel` varchar(20) DEFAULT NULL COMMENT '接单人电话',
  `reponse_remark` varchar(255) DEFAULT NULL COMMENT '接单备注',
  `sign_in_time` datetime DEFAULT NULL COMMENT '签到时间',
  `latitude` varchar(50) DEFAULT NULL COMMENT '维度',
  `longitude` varchar(50) DEFAULT NULL COMMENT '经度',
  `fault_type` varchar(255) DEFAULT NULL COMMENT '故障类型',
  `fault_problem` text COMMENT '故障问题',
  `expect_arrive` int(11) DEFAULT NULL COMMENT '预计到场时间',
  `expect_time` int(11) DEFAULT NULL COMMENT '预计修复日期',
  `expect_price` decimal(10,2) DEFAULT '0.00' COMMENT '预计维修费用',
  `actual_price` decimal(10,2) DEFAULT '0.00' COMMENT '实际维修费用',
  `other_price` decimal(10,2) DEFAULT '0.00' COMMENT '其他费用',
  `repair_remark` varchar(255) DEFAULT NULL COMMENT '检修意见',
  `engineer` varchar(60) DEFAULT NULL COMMENT '维修工程师',
  `engineer_time` int(11) DEFAULT NULL COMMENT '维修开始时间',
  `engineer_tel` varchar(20) DEFAULT NULL COMMENT '维修工程师电话',
  `assist_engineer` varchar(60) DEFAULT NULL COMMENT '协助工程师',
  `assist_engineer_tel` varchar(20) DEFAULT NULL COMMENT '协助工程师电话',
  `working_hours` float(10,2) DEFAULT '0.00' COMMENT '维修总工时',
  `is_spare` tinyint(1) DEFAULT '0' COMMENT '是否提供备用设备',
  `stopdates` int(1) unsigned DEFAULT NULL COMMENT '停机时间',
  `breakdown_cause` tinyint(3) DEFAULT NULL COMMENT '故障原因',
  `dispose_detail` text COMMENT '处理详情',
  `approve_status` tinyint(3) DEFAULT NULL COMMENT '维修审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` timestamp NULL DEFAULT NULL COMMENT '最后审核时间',
  `overdate` int(10) DEFAULT '0' COMMENT '维修完成时间',
  `checkperson` varchar(60) DEFAULT NULL COMMENT '验收人',
  `checkdate` int(10) unsigned DEFAULT NULL COMMENT '验收时间',
  `over_status` tinyint(3) DEFAULT '0' COMMENT '维修后设备状态',
  `service_attitude` tinyint(3) DEFAULT NULL COMMENT '服务态度【0好 1一般 2差】',
  `technical_level` tinyint(3) DEFAULT NULL COMMENT '技术水平【0好 1一般 2差】',
  `response_efficiency` tinyint(3) DEFAULT NULL COMMENT '响应时效【0好 1一般 2差】',
  `check_remark` text COMMENT '验收意见及建议',
  `remark` text COMMENT '备注',
  `adddate` int(10) DEFAULT '0' COMMENT '添加时间',
  `editdate` int(10) DEFAULT '0' COMMENT '修改时间',
  `from` tinyint(1) DEFAULT '1' COMMENT '维修单来源 pc端 1 微信端0',
  `wxTapeAmr` text COMMENT '微信录音 amr',
  `wxTapeServerId` varchar(255) DEFAULT NULL COMMENT '微信语音ID',
  `is_scene` tinyint(1) DEFAULT '0' COMMENT '是否现场解决',
  `is_offer` tinyint(1) DEFAULT '0' COMMENT '是否报价',
  `overhauldate` int(10) DEFAULT '0' COMMENT '检修时间',
  `factory_approach_time` int(10) DEFAULT '0' COMMENT '厂家到场时间',
  `notice_time` int(10) DEFAULT NULL COMMENT '发送通知时间',
  `offer_time` int(10) DEFAULT NULL COMMENT '结束报价时间',
  `offer_user` varchar(100) DEFAULT '' COMMENT '结束报价员',
  `examine_user` varchar(60) DEFAULT '' COMMENT '审核人',
  `examine_time` int(10) DEFAULT NULL COMMENT '最后一次审核时间',
  `is_newParts` tinyint(1) DEFAULT '0' COMMENT '维修过程 0=>没产生配件  1 =>有产生配件 2=>有添加配件并且已报价',
  `repair_category` tinyint(3) DEFAULT NULL COMMENT '自定义维修类型',
  `current_approver` varchar(150) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '全部审批人',
  PRIMARY KEY (`repid`) USING BTREE,
  KEY `departid_assets_assid_status` (`departid`,`assets`,`assid`,`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修业务记录表';

-- ----------------------------
-- Records of sb_repair
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_assign
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_assign`;
CREATE TABLE `sb_repair_assign` (
  `assignid` int(10) NOT NULL AUTO_INCREMENT COMMENT '指派ID',
  `style` tinyint(1) DEFAULT NULL COMMENT '指派类型 1：分类 2:科室  3:辅助分类 4：设备',
  `userid` int(10) NOT NULL COMMENT '维修工程师ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `valuedata` text COMMENT '值',
  `adddate` int(10) DEFAULT NULL COMMENT '添加时间',
  `adduser` varchar(25) DEFAULT '' COMMENT '添加用户',
  `editdate` int(10) DEFAULT NULL COMMENT '修改时间',
  `edituser` varchar(25) DEFAULT '' COMMENT '修改用户',
  PRIMARY KEY (`assignid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——自动派工记录表';

-- ----------------------------
-- Records of sb_repair_assign
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_cancle
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_cancle`;
CREATE TABLE `sb_repair_cancle` (
  `cancle_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '撤单ID',
  `repid` int(11) NOT NULL COMMENT '所属维修单ID',
  `fainal_status` tinyint(3) DEFAULT '1' COMMENT '撤单前维修单状态',
  `cancle_remark` varchar(255) DEFAULT NULL COMMENT '撤单备注',
  `cancle_user` varchar(60) DEFAULT NULL COMMENT '撤单人',
  `cancle_time` datetime DEFAULT NULL COMMENT '撤单时间',
  PRIMARY KEY (`cancle_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修撤单表';

-- ----------------------------
-- Records of sb_repair_cancle
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_contract
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_contract`;
CREATE TABLE `sb_repair_contract` (
  `contract_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '合同自增ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '所属医院ID',
  `repid` varchar(100) DEFAULT NULL COMMENT '维修记录ID',
  `contract_num` varchar(100) DEFAULT NULL COMMENT '合同编号',
  `contract_name` varchar(100) DEFAULT NULL COMMENT '合同名称',
  `supplier_id` tinyint(3) DEFAULT NULL COMMENT '维修商ID',
  `supplier_name` varchar(100) DEFAULT NULL COMMENT '维修商名称',
  `contract_type` tinyint(1) DEFAULT '2' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_contacts` varchar(60) DEFAULT NULL COMMENT '维修商联系人',
  `supplier_phone` varchar(30) DEFAULT NULL COMMENT '维修商联系电话',
  `sign_date` date DEFAULT NULL COMMENT '签订日期',
  `end_date` date DEFAULT NULL COMMENT '合同截止日期',
  `guarantee_date` date DEFAULT NULL COMMENT '合同设备保修截止日期',
  `contract_amount` decimal(10,2) DEFAULT NULL COMMENT '合同金额',
  `check_date` date DEFAULT NULL COMMENT '验收日期',
  `archives_num` varchar(100) DEFAULT NULL COMMENT '档案编号',
  `archives_manager` varchar(60) DEFAULT NULL COMMENT '档案管理人员',
  `hospital_manager` varchar(60) DEFAULT NULL COMMENT '院方负责人',
  `contract_content` text COMMENT '合同内容',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人员',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '编辑人员',
  `edit_time` datetime DEFAULT NULL COMMENT '编辑时间',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `is_confirm` tinyint(1) DEFAULT '0' COMMENT '已确认【0否1是】',
  PRIMARY KEY (`contract_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修第三方合同';

-- ----------------------------
-- Records of sb_repair_contract
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_contract_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_contract_file`;
CREATE TABLE `sb_repair_contract_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修第三方合同附件';

-- ----------------------------
-- Records of sb_repair_contract_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_contract_pay
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_contract_pay`;
CREATE TABLE `sb_repair_contract_pay` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL COMMENT '合同ID',
  `hospital_id` int(11) DEFAULT NULL COMMENT '医院ID',
  `pay_period` int(11) DEFAULT NULL COMMENT '付款期数',
  `estimate_pay_date` date DEFAULT NULL COMMENT '预计付款日期',
  `real_pay_date` date DEFAULT NULL COMMENT '实际付款日期',
  `pay_amount` decimal(10,2) DEFAULT NULL COMMENT '付款金额',
  `pay_status` tinyint(3) DEFAULT '0' COMMENT '付款状态【0未付款，1已付款】',
  `pay_user` varchar(60) DEFAULT NULL COMMENT '付款人',
  `add_user` varchar(60) DEFAULT NULL COMMENT '添加人',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_user` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【0否1是】',
  `supplier_id` int(10) DEFAULT NULL COMMENT '乙方单位id',
  `contract_type` tinyint(1) DEFAULT '2' COMMENT '合同类型【1采购合同 2维修合同 3维保合同 4补录合同 5配件合同】',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT '乙方单位',
  `contract_name` varchar(255) DEFAULT NULL COMMENT '合同名称',
  `contract_num` varchar(150) DEFAULT NULL COMMENT '合同编号',
  PRIMARY KEY (`pay_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修第三方合同付款信息';

-- ----------------------------
-- Records of sb_repair_contract_pay
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_fault
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_fault`;
CREATE TABLE `sb_repair_fault` (
  `repid` int(11) NOT NULL COMMENT '维修ID',
  `fault_type_id` int(11) NOT NULL COMMENT '故障类型',
  `fault_problem_id` int(11) NOT NULL COMMENT '故障问题'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修故障类型/问题记录表';

-- ----------------------------
-- Records of sb_repair_fault
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_file`;
CREATE TABLE `sb_repair_file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `repid` int(11) DEFAULT NULL COMMENT '维修单ID',
  `file_name` varchar(60) DEFAULT NULL COMMENT '文件名称',
  `save_name` varchar(255) DEFAULT NULL COMMENT '保存名称',
  `file_type` varchar(20) DEFAULT NULL COMMENT '文件类型',
  `file_size` float(10,2) DEFAULT '0.00' COMMENT '文件大小',
  `type` varchar(60) DEFAULT 'report' COMMENT '上传节点：action名称',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `add_user` varchar(60) DEFAULT NULL COMMENT '提交人',
  `add_time` datetime DEFAULT NULL COMMENT '提交时间',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否删除【0否1是】',
  PRIMARY KEY (`file_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修报告单';

-- ----------------------------
-- Records of sb_repair_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_follow
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_follow`;
CREATE TABLE `sb_repair_follow` (
  `repid` int(10) NOT NULL COMMENT '维修单ID',
  `followdate` int(10) NOT NULL COMMENT '跟进时间',
  `nextdate` int(10) DEFAULT NULL COMMENT '预计下一步跟进时间',
  `detail` text NOT NULL COMMENT '处理详情'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——维修业务详情跟进表';

-- ----------------------------
-- Records of sb_repair_follow
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_offer
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_offer`;
CREATE TABLE `sb_repair_offer` (
  `offid` int(10) NOT NULL AUTO_INCREMENT COMMENT '报价ID',
  `repid` int(10) NOT NULL COMMENT '维修ID',
  `offer_company` varchar(50) NOT NULL COMMENT '报价公司',
  `offer_price` int(10) NOT NULL COMMENT '报价金额',
  `is_adopt` tinyint(1) NOT NULL COMMENT '是否采纳，0为不采纳，1为采纳',
  `pic_url` varchar(100) NOT NULL COMMENT '资料地址',
  `adduser` varchar(30) DEFAULT NULL COMMENT '添加人',
  `addtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(100) NOT NULL DEFAULT '' COMMENT '修改用户',
  `edittime` int(10) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`offid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——厂家报价记录表（旧，会删）';

-- ----------------------------
-- Records of sb_repair_offer
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_offer_company
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_offer_company`;
CREATE TABLE `sb_repair_offer_company` (
  `offid` int(10) NOT NULL AUTO_INCREMENT COMMENT '报价公司自增ID',
  `repid` int(10) NOT NULL COMMENT '维修单ID',
  `offer_company` varchar(255) NOT NULL COMMENT '报价公司名称',
  `offer_company_id` int(10) DEFAULT NULL COMMENT '报价公司id',
  `offer_contacts` varchar(100) NOT NULL COMMENT '报价公司联系人',
  `telphone` varchar(100) NOT NULL COMMENT '联系电话',
  `invoice` varchar(255) NOT NULL COMMENT '发票',
  `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '总价',
  `cycle` varchar(255) NOT NULL COMMENT '到货/服务周期',
  `proposal` tinyint(1) DEFAULT NULL COMMENT '是否建议公司 1是 0否',
  `proposal_info` varchar(255) DEFAULT NULL COMMENT '建议说明',
  `last_decisioin` tinyint(1) DEFAULT '0' COMMENT '是否最后决定公司 1是 0否',
  `decision_reasion` varchar(255) DEFAULT NULL COMMENT '最后决定选择哪家原因说明',
  `decision_user` varchar(50) DEFAULT NULL COMMENT '最后决定者',
  `decision_adddate` int(11) DEFAULT NULL COMMENT '最后决定时间',
  `remark` text COMMENT '备注',
  `adduser` varchar(50) DEFAULT NULL COMMENT '添加者ID',
  `adddate` int(10) DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(50) DEFAULT NULL COMMENT '修改用户',
  `editdate` int(10) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`offid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——厂家报价记录表（1）';

-- ----------------------------
-- Records of sb_repair_offer_company
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_offer_parts_detail
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_offer_parts_detail`;
CREATE TABLE `sb_repair_offer_parts_detail` (
  `repopid` int(10) NOT NULL AUTO_INCREMENT COMMENT '报价公司配件详情自增ID',
  `offid` int(10) NOT NULL COMMENT '报价公司ID',
  `parts_name` varchar(255) NOT NULL COMMENT '配件物料/服务名称',
  `parts_model` varchar(100) DEFAULT NULL COMMENT '配件型号',
  `num` int(10) NOT NULL COMMENT '配件数量',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '配件单价',
  PRIMARY KEY (`repopid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——厂家报价记录表（2）配件明细';

-- ----------------------------
-- Records of sb_repair_offer_parts_detail
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_parts
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_parts`;
CREATE TABLE `sb_repair_parts` (
  `partid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配件单ID',
  `repid` int(10) unsigned NOT NULL COMMENT '维修单ID',
  `order` int(10) DEFAULT '0' COMMENT '序号',
  `parts` varchar(255) NOT NULL DEFAULT '' COMMENT '配件/服务名称',
  `part_model` varchar(255) NOT NULL COMMENT '配件型号',
  `part_num` int(10) unsigned NOT NULL COMMENT '个数',
  `part_price` decimal(10,2) DEFAULT '0.00' COMMENT '单价',
  `price_sum` decimal(10,2) DEFAULT '0.00' COMMENT '合计',
  `status` tinyint(1) DEFAULT '0' COMMENT '配件采购状态=【0=''未出库'',1=''已出库''】',
  `adduser` varchar(255) DEFAULT NULL COMMENT '添加人',
  `adddate` int(10) NOT NULL COMMENT '添加时间',
  `edituser` varchar(255) DEFAULT NULL COMMENT '修改人',
  `editdate` int(10) DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`partid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——设备维修配件使用清单表';

-- ----------------------------
-- Records of sb_repair_parts
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_record
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_record`;
CREATE TABLE `sb_repair_record` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_url` varchar(255) DEFAULT NULL COMMENT '语音存放地址',
  `seconds` float(10,2) DEFAULT NULL COMMENT '语音时长(s)',
  `add_user` varchar(60) DEFAULT NULL COMMENT '上传人',
  `add_time` datetime DEFAULT NULL COMMENT '保存时间',
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——语音报修录音';

-- ----------------------------
-- Records of sb_repair_record
-- ----------------------------


-- ----------------------------
-- Table structure for sb_repair_setting
-- ----------------------------
DROP TABLE IF EXISTS `sb_repair_setting`;
CREATE TABLE `sb_repair_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '类型或问题标题',
  `solve` varchar(255) DEFAULT NULL COMMENT '问题的解决办法',
  `parentid` int(11) DEFAULT NULL COMMENT '父级ID【0=''类型''】',
  `status` tinyint(3) DEFAULT '1' COMMENT '是否启用【1=''启用''，0=''不启用''】',
  `adduser` varchar(255) DEFAULT NULL COMMENT '添加人',
  `addtime` int(11) DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(255) DEFAULT NULL COMMENT '修改人',
  `edittime` int(11) DEFAULT NULL COMMENT '修改时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='维修管理——故障类型&问题表';

-- ----------------------------
-- Records of sb_repair_setting
-- ----------------------------
BEGIN;
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (4, '机械故障', NULL, 0, 1, '牛年', 1510799009, 'admin', 1521181228, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (5, '电气故障', NULL, 0, 1, '牛年', 1510799025, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (6, '老化损耗', NULL, 0, 1, '牛年', 1510799041, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (7, '软件故障', NULL, 0, 1, '牛年', 1510799049, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (8, '附件故障', NULL, 0, 1, '牛年', 1510799059, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (9, '其他故障', NULL, 0, 1, '牛年', 1510799066, 'admin', 1520907201, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (10, '机械部件松动、磨损', '暂无', 4, 1, '牛年', 1510799103, 'admin', 1520924279, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (11, '机械部件断裂、损坏', '暂无', 4, 1, '牛年', 1510799119, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (12, '机械部件缺失', '暂无', 4, 1, '牛年', 1510799131, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (13, '机械部件卡住、阻塞', '暂无', 4, 1, '牛年', 1510799142, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (14, '主板/控制板故障', '暂无', 5, 1, '牛年', 1510799156, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (15, '电源部分故障', '暂无', 5, 1, '牛年', 1510799169, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (16, '电气部件（电机、涡轮、传感器等）损坏', '暂无', 5, 1, '牛年', 1510799183, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (17, '板块接触不良', '暂无', 5, 1, '牛年', 1510799197, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (18, '板件损坏', '暂无', 5, 1, '牛年', 1510799209, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (19, '橡胶老化', '更换', 6, 1, '牛年', 1510799271, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (20, '管路老化', '更换', 6, 1, '牛年', 1510799291, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (21, '外壳生锈腐蚀', '暂无', 6, 1, '牛年', 1510799301, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (22, '线路破损', '更换', 6, 1, '牛年', 1510799311, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (23, '耗材（氧电池、蓄电池、灯泡、电极等）损耗', '更换', 6, 1, '牛年', 1510799325, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (24, '开机显示软件报错', '暂无', 7, 1, '牛年', 1510799346, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (25, '运行过程中显示软件报错', '暂无', 7, 1, '牛年', 1510799355, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (26, '开机蓝屏、黑屏', '暂无', 7, 1, '牛年', 1510799365, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (27, '软件系统崩溃', '暂无', 7, 1, '牛年', 1510799375, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (28, '附件（探头、导联线等）损坏', '更换', 8, 1, '牛年', 1510799389, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (29, '附件（探头、导联线等）缺失', '暂无', 8, 1, '牛年', 1510799403, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (30, '附件（探头、导联线等）型号不符', '暂无', 8, 1, '牛年', 1510799413, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (31, '操作失误', '暂无', 9, 1, '牛年', 1510799431, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (32, '人为故障', '暂无', 9, 1, '牛年', 1510799442, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (33, '球管损坏', '暂无', 9, 1, '牛年', 1510799457, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (34, '探测器损坏', '暂无', 9, 1, '牛年', 1510799465, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (35, '平板损坏', '暂无', 9, 1, '牛年', 1510799476, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (68, '电线外露', '更换电线', 6, 1, '王五', 1541038946, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (69, '线路断开', '重新焊接线路', 9, 1, '赖炜鲲', 1563507775, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (70, '测试系统异常', NULL, 0, 1, '赖炜鲲', 1563507856, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (71, '空白测试异常', '重新校准测试', 70, 1, '赖炜鲲', 1565085878, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (72, '质控测试异常', '校准后，重新测试', 70, 1, '赖炜鲲', 1565085919, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (74, '过滤材料损耗', '更换过滤材料', 6, 1, '赖炜鲲', 1567064075, NULL, NULL, '');
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (75, '设备保养', NULL, 0, 1, '赖炜鲲', 1576052666, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (76, '需更换过滤器', '更换过滤器', 75, 1, '赖炜鲲', 1576052717, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (77, '需更换过滤布', '更换过滤布', 75, 1, '赖炜鲲', 1576052733, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (78, '需清洁空气过滤网', '清洁空气过滤网', 75, 1, '赖炜鲲', 1576052749, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (79, '测试结果异常', '校准测试参数', 70, 1, '赖炜鲲', 1576655998, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (80, '配件需定期更换', '更换相对应配件。', 75, 1, '赖炜鲲', 1581038467, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (81, '机械部件不到设定位置', '重新调整/校准设定位置', 4, 1, '赖炜鲲', 1581039049, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (82, '软件无法打开', '重新安装软件程序', 7, 1, '赖炜鲲', 1581921802, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (83, '环境因素', '使用观察', 9, 1, '赖炜鲲', 1584934698, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (84, '蓄电池损坏', '更换蓄电池', 9, 1, '赖喜庆', 1641887443, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (85, '故障', NULL, 0, 1, '王喜娟', 1679535712, NULL, NULL, NULL);
INSERT INTO `sb_repair_setting` (`id`, `title`, `solve`, `parentid`, `status`, `adduser`, `addtime`, `edituser`, `edittime`, `remark`) VALUES (86, '1.自动关机', '1.维修主板断路部分', 85, 1, '王喜娟', 1679536106, '王喜娟', 1679540393, '1.维修主板断路部分2.');
COMMIT;

-- ----------------------------
-- Table structure for sb_role
-- ----------------------------
DROP TABLE IF EXISTS `sb_role`;
CREATE TABLE `sb_role` (
  `roleid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `hospital_id` int(11) DEFAULT '1' COMMENT '医院ID',
  `role` varchar(255) NOT NULL DEFAULT '' COMMENT '角色名称',
  `remark` text COMMENT '备注',
  `status` tinyint(3) DEFAULT '1' COMMENT '启用状态【1=''启用'',0=''未启用''】',
  `adduser` varchar(60) DEFAULT NULL COMMENT '添加人',
  `addtime` int(11) unsigned DEFAULT NULL COMMENT '添加时间',
  `edituser` varchar(60) DEFAULT NULL COMMENT '修改人',
  `edittime` int(11) unsigned DEFAULT NULL COMMENT '修改时间',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '系统默认角色 1：是 0:否',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1为已删除0未删除】',
  PRIMARY KEY (`roleid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——角色管理';

-- ----------------------------
-- Records of sb_role
-- ----------------------------


-- ----------------------------
-- Table structure for sb_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `sb_role_menu`;
CREATE TABLE `sb_role_menu` (
  `roleid` int(11) DEFAULT NULL COMMENT '角色ID',
  `menuid` int(11) DEFAULT NULL COMMENT '菜单ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统基础——角色权限表';

-- ----------------------------
-- Records of sb_role_menu
-- ----------------------------


-- ----------------------------
-- Table structure for sb_role_target_setting
-- ----------------------------
DROP TABLE IF EXISTS `sb_role_target_setting`;
CREATE TABLE `sb_role_target_setting` (
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  `set_type` varchar(60) DEFAULT NULL COMMENT '设置类型【survey=全院设备概况，detail=分项详细】',
  `chart_id` varchar(100) DEFAULT NULL COMMENT '图表ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基础设置--角色首页统计图表显示设置';

-- ----------------------------
-- Records of sb_role_target_setting
-- ----------------------------


-- ----------------------------
-- Table structure for sb_scrap_file
-- ----------------------------
DROP TABLE IF EXISTS `sb_scrap_file`;
CREATE TABLE `sb_scrap_file` (
  `scrid` int(11) NOT NULL COMMENT '报废ID',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='报废管理——报废上传文件管理表';

-- ----------------------------
-- Records of sb_scrap_file
-- ----------------------------


-- ----------------------------
-- Table structure for sb_sms
-- ----------------------------
DROP TABLE IF EXISTS `sb_sms`;
CREATE TABLE `sb_sms` (
  `smsid` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL DEFAULT '' COMMENT '模块名称：如：assets、repair、patrol、inventory(盘点)、role、user、modset',
  `actionid` varchar(120) DEFAULT '0' COMMENT '事件ID，如：记录被操作的事件ID，如：设备ID、维修单ID、盘点ID、巡查ID、角色组ID、用户ID、setid',
  `phone` varchar(255) NOT NULL DEFAULT '' COMMENT '用户号码',
  `send_time` datetime DEFAULT NULL COMMENT '发送短信时间',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '短信内容',
  `send_status` tinyint(3) DEFAULT '1' COMMENT '短信发送状态【1成功0失败】',
  PRIMARY KEY (`smsid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——短信日志表';

-- ----------------------------
-- Records of sb_sms
-- ----------------------------


-- ----------------------------
-- Table structure for sb_sms_basesetting
-- ----------------------------
DROP TABLE IF EXISTS `sb_sms_basesetting`;
CREATE TABLE `sb_sms_basesetting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(10) DEFAULT '0' COMMENT '医院ID',
  `action` varchar(150) DEFAULT NULL COMMENT '功能',
  `parentid` int(10) DEFAULT '0' COMMENT '父级id',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `status` tinyint(1) DEFAULT '1' COMMENT '开关状态 1开启 0关闭',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=272 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='模块配置——短信配置';

-- ----------------------------
-- Records of sb_sms_basesetting
-- ----------------------------
BEGIN;
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (211, 1, 'setting_open', 0, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (212, 1, 'Repair', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (213, 1, 'Patrol', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (214, 1, 'Borrow', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (215, 1, 'Outside', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (216, 1, 'Transfer', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (217, 1, 'Scrap', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (218, 1, 'Metering', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (219, 1, 'Qualities', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (220, 1, 'Purchases', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (221, 1, 'Subsidiary', 211, NULL, 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (222, 1, 'applyRepair', 212, '{department}科室,编号为{assnum}的设备申请报修，请处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (223, 1, 'assigned', 212, '{department}科室,编号为{assnum}的设备申请报修，请及时分配工程师接单处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (224, 1, 'acceptOrder', 212, '您报修设备编号为{assnum}的设备已有工程师接单处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (225, 1, 'repairPartsOutApply', 212, '维修单{repnum}有配件出库申请，请及时处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (226, 1, 'repairPartsOut', 212, '仓库已同意维修单{repnum}的配件出库申请，请及时领取', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (227, 1, 'doApprove', 212, '{department}科室,维修单{repnum}需审批，请处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (228, 1, 'repairApproveOver', 212, '维修单{repnum}已审批，审批结果：{approve_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (229, 1, 'repairApproveOverFAIL', 212, '维修单{repnum}审批结果：{approve_status},请重新申请', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (230, 1, 'repairOffer', 212, '{department}科室,维修单{repnum}需报价，请处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (231, 1, 'repairOfferOver', 212, '维修单{repnum}已确认最终厂商，请继续进行维修处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (232, 1, 'checkRepair', 212, '报修设备编号为{assnum}的设备工程师已维修处理结束，请及时验收', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (233, 1, 'checkRepairStatus', 212, '您维修编号为{assnum}的设备已被验收，验收结果：{over_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (234, 1, 'doApprove', 213, '用户{applicant}申请巡查计划{patrolname}，请处理审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (235, 1, 'borrowrApproveOver', 213, '巡查计划{patrolname}，审批结果：{examine_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (236, 1, 'doPatrolTask', 213, '计划{patrolname}已发布，开始时间{startdate},请及时处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (237, 1, 'checkPatrolTask', 213, '巡查任务{cyclenum}已实施完成，请及时验收', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (238, 1, 'confirmRepair', 213, '{department}科室设备{assets}巡查结果：需要报修，请登录确认转至维修', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (239, 1, 'doApprove', 214, '{apply_department}科室向{department}科室申请借调{assets}，请处理审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (240, 1, 'borrowrApproveOver', 214, '借调单{borrow_num}，审批结果：{examine_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (241, 1, 'borrowNotApply', 214, '科室{apply_department}借调设备{assets}的申请已取消', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (242, 1, 'borrowInCheck', 214, '科室{apply_department}已确认设备{assets}完好无损并且借入，预计在{estimate_back}归还', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (243, 1, 'borrowGiveBack', 214, '科室{department}已确认设备{assets}完好无损并结束流程', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (244, 1, 'doApprove', 215, '{department}科室申请外调设备{assets}，请处理审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (245, 1, 'outsideApproveOver', 215, '科室{department}外调设备{assets}，审批结果：{examine_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (246, 1, 'approveTransfer', 216, '科室{tranout_department}，编号{assnum}的设备申请转入科室{tranin_department}，请您进行审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (247, 1, 'approveTransferStatus', 216, '转科单号{transfer_num}的审批结果为{approve_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (248, 1, 'checkTransfer', 216, '科室{tranout_department}，编号为assnum}的设备申请转入科室{tranin_department}，请您进行验收', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (249, 1, 'checkTransferStatus', 216, '转科单号{transfer_num}的验收结果为{check_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (250, 1, 'approveScrap', 217, '编号为{assnum}的设备{assets}申请报废，请您进行审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (251, 1, 'approveScrapStatus', 217, '报废编号为{scrap_num}设备的审批结果为{approve_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (252, 1, 'setMeteringResult', 218, '科室{department}设备{assets}制定了计量计划，编号{plan_num}，下次待检日期{next_date}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (253, 1, 'startQualityPlan', 219, '{plan_name}预计{do_date}执行{assets}的质控计划,请按时处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (254, 1, 'stopQualityPlan', 219, '{plan_name}已被{stop_username}暂停', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (255, 1, 'noticeDoQualityPlan', 219, '计划{plan_name}将要逾期,截止日期{end_date}，请及时录入', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (256, 1, 'feedbackQuality', 219, '{hospital}今日完成录入的计划数量{completeNum},待录入数量{toBeDoneNum}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (257, 1, 'purchasePlanApprove', 220, '科室{department}上报了采购计划{project_name}，请审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (258, 1, 'purchasePlanApproveOver', 220, '上报计划{project_name}，审批结果：{approve_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (259, 1, 'approveApply', 220, '科室{department}申请采购计划{project_name}，请审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (260, 1, 'approveApplyOver', 220, '采购计划{project_name}，审批结果：{approve_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (261, 1, 'expertReview', 220, '采购申请{project_name}需评审，请及时处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (262, 1, 'tbApprove', 220, '采购申请{project_name}购买设备{assets}的标书需评审，请及时处理', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (263, 1, 'tbApproveOver', 220, '采购申请{project_name}购买设备{assets}的标书需评审结果：{review_status}，请重新制定标书', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (264, 1, 'tbSubmit', 220, '采购申请{project_name}购买设备{assets}的标书需评审结果：{review_status},请提交标书', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (265, 1, 'notOutApproveOver', 220, '出库单{out_num}出库设备申请已被拒绝,请及时跟进', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (266, 1, 'debugReport', 220, '请于{installStartDate}至{installEendDate}到达{debug_area}参与调试', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (267, 1, 'doTrain', 220, '请于{trainStartDate}至{trainEendDate}到达{train_area}进行{train_assets}设备的培训', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (268, 1, 'joinTrain', 220, '请于{trainStartDate}至{trainEendDate}到达{train_area}参加{train_assets}设备的培训', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (269, 1, 'doApprove', 221, '{department}科室申请分配附属设备{assets}，请处理审批', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (270, 1, 'subsidiaryApproveOver', 221, '科室{department}分配附属设备{assets}，审批结果：{approve_status}', 1);
INSERT INTO `sb_sms_basesetting` (`id`, `hospital_id`, `action`, `parentid`, `content`, `status`) VALUES (271, 1, 'subsidiaryCheck', 221, '分配附属设备{assets}审批已通过，请验收', 1);
COMMIT;

-- ----------------------------
-- Table structure for sb_subsidiary_allot
-- ----------------------------
DROP TABLE IF EXISTS `sb_subsidiary_allot`;
CREATE TABLE `sb_subsidiary_allot` (
  `allotid` int(10) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(10) DEFAULT NULL COMMENT '医院id',
  `assid` int(10) DEFAULT NULL COMMENT '设备id',
  `assets` varchar(150) DEFAULT NULL COMMENT '附属设备名',
  `main_assid` int(10) DEFAULT NULL COMMENT '主设备id',
  `main_assets` varchar(150) DEFAULT NULL COMMENT '主设备名称',
  `main_departid` int(10) DEFAULT NULL COMMENT '主设备科室',
  `main_managedepart` varchar(255) DEFAULT NULL COMMENT '主设备管理科室',
  `main_address` varchar(255) DEFAULT NULL COMMENT '主设备使用位置',
  `main_assetsrespon` varchar(150) DEFAULT NULL COMMENT '资产负责人',
  `apply_user` varchar(100) DEFAULT NULL COMMENT '申请人',
  `apply_date` date DEFAULT NULL COMMENT '申请日期',
  `remark` text COMMENT '申请备注',
  `status` tinyint(1) DEFAULT '0' COMMENT '分配状态-1:审批不通过 0:申请中 1：审批通过 2:验收结束 ',
  `approve_status` tinyint(1) DEFAULT '0' COMMENT '审核状态【-1不需审核，0未审，1通过，2不通过】',
  `approve_time` timestamp NULL DEFAULT NULL COMMENT '最后审核时间',
  `current_approver` varchar(100) DEFAULT NULL COMMENT '当前审批人',
  `complete_approver` varchar(255) DEFAULT NULL COMMENT '已审批人',
  `not_complete_approver` varchar(255) DEFAULT NULL COMMENT '未审批人',
  `all_approver` varchar(255) DEFAULT NULL COMMENT '所有审批人',
  `check_user` varchar(100) DEFAULT NULL COMMENT '验收用户',
  `check_time` date DEFAULT NULL COMMENT '验收日期',
  `check_status` tinyint(1) DEFAULT '0' COMMENT '验收情况 0待验收 1验收通过 2验收未通过',
  `check_remrk` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`allotid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='设备管理——附属设备分配表';

-- ----------------------------
-- Records of sb_subsidiary_allot
-- ----------------------------


-- ----------------------------
-- Table structure for sb_target_statistic_setting
-- ----------------------------
DROP TABLE IF EXISTS `sb_target_statistic_setting`;
CREATE TABLE `sb_target_statistic_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `set_hospital_id` int(11) DEFAULT NULL COMMENT '所在医院ID',
  `set_type` varchar(60) DEFAULT NULL COMMENT '设置类型【survey=全院设备概况，detail=分项详细】',
  `chart_id` varchar(100) DEFAULT NULL COMMENT '显示的图表ID',
  `is_show` tinyint(3) DEFAULT NULL COMMENT '是否显示【1显示0不显示】',
  `chart_type` varchar(20) DEFAULT NULL COMMENT '图表类型',
  `add_time` datetime DEFAULT NULL COMMENT '首次设置时间',
  `add_user` varchar(60) DEFAULT NULL COMMENT '首次设置人',
  `update_time` datetime DEFAULT NULL COMMENT '最近一次修改时间',
  `update_user` varchar(60) DEFAULT NULL COMMENT '最近一次修改人',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='首页统计图表显示设置';

-- ----------------------------
-- Records of sb_target_statistic_setting
-- ----------------------------


-- ----------------------------
-- Table structure for sb_user
-- ----------------------------
DROP TABLE IF EXISTS `sb_user`;
CREATE TABLE `sb_user` (
  `userid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `job_hospitalid` int(11) DEFAULT '1' COMMENT '用户工作医院ID',
  `manager_hospitalid` varchar(60) DEFAULT '1' COMMENT '用户能管理的医院ID',
  `code` varchar(255) DEFAULT NULL COMMENT '医院代码',
  `openid` varchar(255) NOT NULL,
  `qy_user_id` varchar(64) DEFAULT NULL,
  `nickname` varchar(30) DEFAULT NULL COMMENT '微信用户昵称',
  `job_departid` int(11) DEFAULT '0' COMMENT '工作科室',
  `usernum` int(10) unsigned NOT NULL COMMENT '工号',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别',
  `telephone` varchar(50) NOT NULL DEFAULT '' COMMENT '电话',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '人员状态',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登录时间',
  `logintimes` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '登录次数',
  `addid` int(10) NOT NULL,
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `is_super` tinyint(3) DEFAULT '0' COMMENT '是否超级管理员',
  `is_supplier` tinyint(1) DEFAULT '0' COMMENT '是否厂商用户【0:否 1:是】',
  `olsid` int(10) DEFAULT '0' COMMENT '所属厂商id',
  `identifier` varchar(32) DEFAULT NULL COMMENT '第二身份标识',
  `token` varchar(32) DEFAULT NULL COMMENT '登录标识',
  `timeout` int(11) unsigned DEFAULT NULL COMMENT '登录cookie过期时间',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `expire_time` datetime DEFAULT NULL COMMENT '账户过期时间',
  `email` varchar(60) DEFAULT NULL COMMENT '电子邮箱',
  `pic` varchar(255) DEFAULT NULL COMMENT '头像地址',
  `is_delete` tinyint(3) DEFAULT '0' COMMENT '是否删除【1为已删除0未删除】',
  `authorization` tinyint(3) DEFAULT NULL COMMENT '是否授权登录【1待确定2同意3拒绝】',
  `state` varchar(100) DEFAULT NULL COMMENT '登录扫码随机码',
  `autograph` text COMMENT '用户签名的图片以base64格式存储',
  `set_password_time` datetime DEFAULT NULL COMMENT '设置密码时间',
  `wx_public_account` tinyint(3) DEFAULT '0' COMMENT '是否微信公共账号【0=否，1=是】',
  PRIMARY KEY (`userid`) USING BTREE,
  UNIQUE KEY `username` (`username`) USING BTREE COMMENT 'username'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——用户表';

-- ----------------------------
-- Records of sb_user
-- ----------------------------
BEGIN;
INSERT INTO `sb_user` (`userid`, `job_hospitalid`, `manager_hospitalid`, `code`, `openid`, `qy_user_id`, `nickname`, `job_departid`, `usernum`, `username`, `password`, `gender`, `telephone`, `status`, `logintime`, `logintimes`, `addid`, `remark`, `is_super`, `is_supplier`, `olsid`, `identifier`, `token`, `timeout`, `add_time`, `edit_time`, `expire_time`, `email`, `pic`, `is_delete`, `authorization`, `state`, `autograph`, `set_password_time`, `wx_public_account`) VALUES (1, 1, '1', NULL, '', NULL, NULL, 0, 1, '牛年', 'rJ18MB6rpsSFScoB6bnstA==', 1, '13800138000', 1, 0, 1, 0, '', 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2025-01-10 16:20:52', 0);
COMMIT;

-- ----------------------------
-- Table structure for sb_user_app_token
-- ----------------------------
DROP TABLE IF EXISTS `sb_user_app_token`;
CREATE TABLE `sb_user_app_token` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `token` varchar(255) NOT NULL COMMENT 'token',
  `session` longtext COMMENT 'session值',
  `status` tinyint(2) NOT NULL COMMENT '0,禁用;1,启用;',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of sb_user_app_token
-- ----------------------------


-- ----------------------------
-- Table structure for sb_user_department
-- ----------------------------
DROP TABLE IF EXISTS `sb_user_department`;
CREATE TABLE `sb_user_department` (
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `departid` int(11) DEFAULT NULL COMMENT '科室ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统基础——用户部门表';

-- ----------------------------
-- Records of sb_user_department
-- ----------------------------


-- ----------------------------
-- Table structure for sb_user_key
-- ----------------------------
DROP TABLE IF EXISTS `sb_user_key`;
CREATE TABLE `sb_user_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户登录密钥';

-- ----------------------------
-- Records of sb_user_key
-- ----------------------------


-- ----------------------------
-- Table structure for sb_user_role
-- ----------------------------
DROP TABLE IF EXISTS `sb_user_role`;
CREATE TABLE `sb_user_role` (
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `roleid` int(11) DEFAULT NULL COMMENT '角色ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='系统基础——角色权限表';

-- ----------------------------
-- Records of sb_user_role
-- ----------------------------


-- ----------------------------
-- Table structure for sb_weixin_log
-- ----------------------------
DROP TABLE IF EXISTS `sb_weixin_log`;
CREATE TABLE `sb_weixin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` int(11) DEFAULT NULL COMMENT '用户的openid',
  `errcode` varchar(25) DEFAULT NULL COMMENT '错误编码',
  `errmsg` varchar(60) DEFAULT NULL COMMENT '错误描述',
  `add_time` varchar(255) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='微信模板消息发送失败记录表';

-- ----------------------------
-- Records of sb_weixin_log
-- ----------------------------


-- ----------------------------
-- Table structure for sb_wx
-- ----------------------------
DROP TABLE IF EXISTS `sb_wx`;
CREATE TABLE `sb_wx` (
  `wx_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `userid` int(10) NOT NULL COMMENT '用户表ID',
  `openid` varchar(255) NOT NULL COMMENT '微信openid',
  PRIMARY KEY (`wx_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——微信用户openid';

-- ----------------------------
-- Records of sb_wx
-- ----------------------------


-- ----------------------------
-- Table structure for sb_wx_access_token
-- ----------------------------
DROP TABLE IF EXISTS `sb_wx_access_token`;
CREATE TABLE `sb_wx_access_token` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `item` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `expire_time` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统基础——微信用户token';

-- ----------------------------
-- Records of sb_wx_access_token
-- ----------------------------


-- ----------------------------
-- Table structure for sb_wx_public_login
-- ----------------------------
DROP TABLE IF EXISTS `sb_wx_public_login`;
CREATE TABLE `sb_wx_public_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `userid` int(11) NOT NULL COMMENT '公共账户ID',
  `nickname` varchar(60) NOT NULL COMMENT '登录用户昵称',
  `openid` varchar(60) NOT NULL COMMENT '登录用户openid',
  `login_time` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户管理——公共账户登录历史记录';

-- ----------------------------
-- Records of sb_wx_public_login
-- ----------------------------


-- ----------------------------
-- Table structure for zzzztime
-- ----------------------------
DROP TABLE IF EXISTS `zzzztime`;
CREATE TABLE `zzzztime` (
  `id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='无用表，记录更新日期20190425';

-- ----------------------------
-- Records of zzzztime
-- ----------------------------


SET FOREIGN_KEY_CHECKS = 1;
