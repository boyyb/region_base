/*
Navicat MySQL Data Transfer

Source Server         : 192.168.8.11
Source Server Version : 50620
Source Host           : 192.168.8.11:3306
Source Database       : region_base

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2016-11-10 15:47:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `key` varchar(50) DEFAULT NULL COMMENT '键名',
  `val` text COMMENT '值',
  `group` varchar(50) DEFAULT NULL COMMENT '分组',
  `input_type` varchar(50) DEFAULT NULL COMMENT 'input类型',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `key` (`key`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='系统设置';

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config` VALUES ('1', 'app_name', '四川省区域中心综合管理平台', null, null, null);
INSERT INTO `config` VALUES ('2', 'region_no', 'R61007200', null, null, null);
INSERT INTO `config` VALUES ('3', 'region_name', '四川省博物院', null, null, null);

-- ----------------------------
-- Table structure for data_base
-- ----------------------------
DROP TABLE IF EXISTS `data_base`;
CREATE TABLE `data_base` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `mid` int(10) NOT NULL COMMENT '博物馆ID',
  `count_relic` int(5) DEFAULT NULL COMMENT '馆藏文物数量',
  `count_precious_relic` int(5) DEFAULT NULL COMMENT '珍贵文物数量',
  `count_fixed_exhibition` int(5) DEFAULT NULL COMMENT '固定展览数量',
  `count_temporary_exhibition` int(5) DEFAULT NULL COMMENT '临时展览数量',
  `count_showcase` int(5) DEFAULT NULL COMMENT '展柜数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 博物馆基础数据';

-- ----------------------------
-- Records of data_base
-- ----------------------------
INSERT INTO `data_base` VALUES ('1', '1', '2', '0', '9999', '9999', '93');
INSERT INTO `data_base` VALUES ('2', '2', '87', '0', '9999', '9999', '93');
INSERT INTO `data_base` VALUES ('3', '3', '21', '21', '9999', '9999', '30');
INSERT INTO `data_base` VALUES ('4', '4', '53', '53', '9999', '9999', '46');

-- ----------------------------
-- Table structure for data_complex
-- ----------------------------
DROP TABLE IF EXISTS `data_complex`;
CREATE TABLE `data_complex` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` int(8) NOT NULL COMMENT '日期',
  `mid` int(10) NOT NULL COMMENT '博物馆ID',
  `env_type` varchar(20) NOT NULL COMMENT '环境类型',
  `scatter_temp` float(2,2) DEFAULT NULL COMMENT '温度离散系数',
  `scatter_humidity` float(2,2) DEFAULT NULL COMMENT '湿度离散系数',
  `is_wave_abnormal` tinyint(1) DEFAULT NULL COMMENT '是否有日波动超标',
  `is_value_abnormal` tinyint(1) DEFAULT NULL COMMENT '是否有异常值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 博物馆综合统计';

-- ----------------------------
-- Records of data_complex
-- ----------------------------
INSERT INTO `data_complex` VALUES ('110', '20161024', '3', '展厅', '0.04', '0.05', '0', '0');
INSERT INTO `data_complex` VALUES ('111', '20161024', '3', '展柜', '0.05', '0.07', '0', '0');
INSERT INTO `data_complex` VALUES ('112', '20161024', '3', '库房', '0.01', '0.02', '0', '0');
INSERT INTO `data_complex` VALUES ('113', '20161024', '1', '展厅', '0.55', '0.58', '1', '0');
INSERT INTO `data_complex` VALUES ('114', '20161024', '4', '展柜', '0.58', '0.58', '1', '0');
INSERT INTO `data_complex` VALUES ('115', '20161024', '4', '库房', '0.63', '0.59', '1', '0');
INSERT INTO `data_complex` VALUES ('131', '20161024', '2', '展柜', '0.45', '0.21', '0', '0');
INSERT INTO `data_complex` VALUES ('132', '20161024', '1', '展柜', '0.05', '0.06', '0', '0');
INSERT INTO `data_complex` VALUES ('133', '20161024', '3', '展柜', '0.15', '0.23', '0', '0');
INSERT INTO `data_complex` VALUES ('134', '20161024', '4', '展柜', '0.55', '0.58', '0', '0');
INSERT INTO `data_complex` VALUES ('135', '20161024', '2', '展厅', '0.54', '0.56', '0', '0');
INSERT INTO `data_complex` VALUES ('136', '20161025', '4', '展柜', '0.61', '0.55', '0', '0');
INSERT INTO `data_complex` VALUES ('137', '20161025', '4', '库房', '0.52', '0.57', '0', '0');
INSERT INTO `data_complex` VALUES ('138', '20161024', '4', '展厅', '0.24', '0.61', '0', '0');
INSERT INTO `data_complex` VALUES ('139', '20161026', '4', '展柜', '0.11', '0.14', '0', '0');
INSERT INTO `data_complex` VALUES ('140', '20161026', '4', '库房', '0.08', '0.09', '0', '0');
INSERT INTO `data_complex` VALUES ('141', '20161026', '4', '展厅', '0.06', '0.09', '0', '0');
INSERT INTO `data_complex` VALUES ('142', '20161025', '4', '展厅', '0.11', '0.21', '0', '0');

-- ----------------------------
-- Table structure for data_env
-- ----------------------------
DROP TABLE IF EXISTS `data_env`;
CREATE TABLE `data_env` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `mid` int(10) NOT NULL COMMENT '博物馆ID',
  `sourceid` varchar(50) DEFAULT NULL COMMENT '原始环境ID',
  `pid` varchar(50) DEFAULT NULL COMMENT '上级环境ID（原始ID）',
  `name` varchar(100) DEFAULT NULL COMMENT '环境名称',
  `env_type` varchar(50) DEFAULT NULL COMMENT '环境类型',
  `material_humidity` varchar(50) DEFAULT NULL COMMENT '湿度材质分类',
  `material_light` varchar(50) DEFAULT NULL COMMENT '光照材质分类',
  `temperature_upper` float(5,2) DEFAULT NULL COMMENT '温度标准上限',
  `temperature_lower` float(5,2) DEFAULT NULL COMMENT '温度标准下限',
  `humidity_upper` float(5,2) DEFAULT NULL COMMENT '湿度标准上限',
  `humidity_lower` float(5,2) DEFAULT NULL COMMENT '湿度标准下限',
  `light_upper` float(5,2) DEFAULT NULL COMMENT '光照标准上限',
  `light_lower` float(5,2) DEFAULT NULL COMMENT '光照标准下限',
  `uv_upper` float(5,2) DEFAULT NULL COMMENT '紫外标准上限',
  `uv_lower` float(5,2) DEFAULT NULL COMMENT '紫外标准下限',
  `voc_upper` float(5,2) DEFAULT NULL COMMENT 'VOC标准上限',
  `voc_lower` float(5,2) DEFAULT NULL COMMENT 'VOC标准下限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=320 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 环境表';

-- ----------------------------
-- Records of data_env
-- ----------------------------
INSERT INTO `data_env` VALUES ('1', '3', '62500000010101', null, '边茶藏马展厅', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('2', '3', '62500000010501', null, '辟疆拓土展厅', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('3', '3', '62500000010502', null, '汉风流被展厅', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('4', '3', '62500000010103', null, '汉嘉神韵展厅', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('5', '3', '62500000010503', null, '雅风流韵展厅', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('6', '3', '6250000001010107', '62500000010101', '边茶饮用', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('7', '3', '6250000001010108', '62500000010101', '边茶交易', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('8', '3', '6250000001010109', '62500000010101', '边茶生产', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('9', '3', '6250000001010110', '62500000010101', '边茶制作', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('10', '3', '62500000320401', null, '库房', '库房', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('11', '3', '6250000001050307', '62500000010503', '“雅州印”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('12', '3', '6250000001010111', '62500000010101', '边茶运输', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('13', '3', '6250000001010302', '62500000010103', '碑刻', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('14', '3', '6250000001010303', '62500000010103', '石兽', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('15', '3', '6250000001010304', '62500000010103', '石碑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('16', '3', '6250000001010371R', '62500000010103', '石棺', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('17', '3', '6250000001010372R', '62500000010103', '汉石阙', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('18', '3', '6250000001010373R', '62500000010103', '遗址坑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('19', '3', '6250000001010374R', '62500000010103', '墓门', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('20', '3', '6250000001050308', '62500000010503', '“珐华蒜头瓶”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('21', '3', '6250000001050309', '62500000010503', '“影青釉狮钮盖三足炉”中心柜', '展柜', '1', '4', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('22', '3', '6250000001050310', '62500000010503', '“至正七年置-青花盖罐”中心柜', '展柜', '1', '4', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('23', '3', '6250000001050311', '62500000010503', '“双头人面首蛇身卧姿陶俑”中心柜', '展柜', '1', '4', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('24', '3', '6250000001050312', '62500000010503', '“凤穿花葵形铜镜”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('25', '3', '6250000001050313', '62500000010503', '“八瓣菱花形航海铜镜”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('26', '3', '6250000001050314', '62500000010503', '“藏地密码”边柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('27', '3', '6250000001050206', '62500000010502', '“摇钱树座”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('28', '3', '6250000001050207', '62500000010502', '“跽坐青铜人像”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('29', '3', '6250000001050208', '62500000010502', '“石马”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('30', '3', '6250000001050108', '62500000010501', '“成都”铭文青铜矛中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('31', '3', '6250000001050109', '62500000010501', '“边地雄风”青铜兵器边柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('32', '3', '6250000001050110', '62500000010501', '“蜀兵披靡”青铜兵器边柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('33', '3', '6250000001050111', '62500000010501', '“错金银铜带钩”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('34', '3', '6250000001050112', '62500000010501', '“带盖青铜罍”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('35', '3', '6250000001050113', '62500000010501', '“巴蜀印章”边柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('36', '3', '6250000001050114', '62500000010501', '“石矛”中心柜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('37', '4', '62500000010101', '625000000101', '边茶藏马展厅', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('38', '4', '62500000010501', '625000000105', '辟疆拓土展厅', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('39', '4', '62500000010502', '625000000105', '汉风流被展厅汉风流被展厅汉风流被展厅汉风流被展厅', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('40', '4', '62500000010103', '625000000101', '汉嘉神韵展厅汉嘉神韵展厅汉嘉神韵展厅', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('41', '4', '62500000010503', '625000000105', '雅风流韵展厅雅风流韵展厅雅风流韵展厅', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('42', '4', '6250000001010107', '62500000010101', '边茶饮用', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('43', '4', '6250000001010108', '62500000010101', '边茶交易边茶交易边茶交易边茶交易边茶交易边茶交易', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('44', '4', '6250000001010109', '62500000010101', '边茶生产', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('45', '4', '6250000001010110', '62500000010101', '边茶制作', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('46', '4', '6250000001050307', '62500000010503', '“雅州印”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('47', '4', '62500000320401', '625000003204', '库房1', '库房', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('48', '4', '6250000001010111', '62500000010101', '边茶运输', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('49', '4', '6250000001010302', '62500000010103', '碑刻', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('50', '4', '6250000001010303', '62500000010103', '石兽', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('51', '4', '6250000001010304', '62500000010103', '石碑', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('52', '4', '6250000001010371', '62500000010103', '石棺', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('53', '4', '6250000001010372', '62500000010103', '汉石阙', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('54', '4', '6250000001010373', '62500000010103', '遗址坑', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('55', '4', '6250000001010374', '62500000010103', '墓门', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('56', '4', '6250000001050308', '62500000010503', '“珐华蒜头瓶”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('57', '4', '6250000001050309', '62500000010503', '“影青釉狮钮盖三足炉”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('58', '4', '6250000001050310', '62500000010503', '“至正七年置-青花盖罐”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('59', '4', '6250000001050311', '62500000010503', '双头人面首蛇身卧姿陶俑双头人面首蛇身卧姿陶', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('60', '4', '6250000001050312', '62500000010503', '“凤穿花葵形铜镜”中心柜“凤穿花葵形铜镜”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('61', '4', '6250000001050313', '62500000010503', '“八瓣菱花形航海铜镜”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('62', '4', '6250000001050314', '62500000010503', '“藏地密码”边柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('63', '4', '6250000001050206', '62500000010502', '“摇钱树座”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('64', '4', '6250000001050207', '62500000010502', '“跽坐青铜人像”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('65', '4', '6250000001050208', '62500000010502', '“石马”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('66', '4', '6250000001050108', '62500000010501', '“成都”铭文青铜矛中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('67', '4', '6250000001050109', '62500000010501', '“边地雄风”青铜兵器边柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('68', '4', '6250000001050110', '62500000010501', '“蜀兵披靡”青铜兵器边柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('69', '4', '6250000001050111', '62500000010501', '“错金银铜带钩”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('70', '4', '6250000001050112', '62500000010501', '“带盖青铜罍”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('71', '4', '6250000001050113', '62500000010501', '“巴蜀印章”边柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('72', '4', '6250000001050114', '62500000010501', '“石矛”中心柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('73', '4', '62500000010601', '625000000106', '展览馆三层展厅1', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('74', '4', '6250000001050115', '62500000010501', '石矛中心柜 副柜', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('75', '4', '6250000001110202', '62500000011102', '测试展厅B展柜2', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('76', '4', '62500000011101', '625000000111', '测试展厅A测试展厅A测试展厅A', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('77', '4', '62500000011102', '625000000111', '测试展厅B测试展厅B测试展厅B测试展厅B测试展厅B', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('78', '4', '6250000001110201', '62500000011102', '测试展厅B展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('79', '4', '62500000010102', '625000000101', '四川汉代陶石艺术馆一区', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('80', '4', '62500000010104', '625000000101', '展览馆一层测试展厅4', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('81', '4', '62500000010105', '625000000101', '展览馆一层测试展厅5', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('82', '4', '62500000010106', '625000000101', '展览馆一层测试展厅6', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('83', '4', '62500000010107', '625000000101', '展览馆一层测试展厅7', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('84', '4', '62500000010108', '625000000101', '展览馆一层测试展厅8', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('85', '4', '62500000010109', '625000000101', '展览馆一层测试展厅9', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('86', '4', '62500000010110', '625000000101', '展览馆一层测试展厅10', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('87', '4', '62500000010111', '625000000101', '展览馆一层展厅11', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('88', '4', '6250000001110101', '62500000011101', '测试展厅A展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('89', '4', '62500000320402', '625000003204', '库房2', '库房', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('90', '4', '6250000001060102', '62500000010601', '展览馆三层展厅1展柜2', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('91', '4', '6250000001060101', '62500000010601', '展览馆三层展厅1展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('92', '4', '62500000010602', '625000000106', '展览馆三层展厅2', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('93', '4', '6250000001060201', '62500000010602', '展览馆三层展厅2展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('94', '4', '6250000001060202', '62500000010602', '展览馆三层展厅2展柜2', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('95', '4', '6250000001010401', '62500000010104', '展览馆一层展厅4展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('96', '4', '6250000001010501', '62500000010105', '展览馆一层展厅5展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('97', '4', '62500000010701', '625000000107', '展览馆四层展厅1', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('98', '4', '6250000001010601', '62500000010106', '展览馆一层测试展厅6展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('99', '4', '6250000001010701', '62500000010107', '展览馆一层测试展厅7展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('100', '4', '6250000001010801', '62500000010108', '展览馆一层测试展厅8展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('101', '4', '6250000001010901', '62500000010109', '展览馆一层测试展厅9展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('102', '4', '6250000001011001', '62500000010110', '展览馆一层测试展厅10展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('103', '4', '6250000001011101', '62500000010111', '展览馆一层展厅11展柜1', '展柜', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('104', '4', '62500000010112', '625000000101', '展览馆一层展厅12', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('105', '4', '62500000010113', '625000000101', '展览馆一层展厅13', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('106', '4', '62500000010114', '625000000101', '展览馆一层展厅14', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('107', '4', '62500000010115', '625000000101', '展览馆一层展厅15', '展厅', '3', '6', '24.00', '21.00', '65.00', '39.00', '66.00', null, '56.00', null, '66.00', null);
INSERT INTO `data_env` VALUES ('108', '1', '47100000010101', null, '第一展厅“史前、夏商周”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('109', '1', '47100000010102', null, '第二展厅“汉魏展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('110', '1', '47100000010103', null, '第三展厅“隋唐、五代”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('111', '1', '47100000010201', null, '第六展厅“书画展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('112', '1', '47100000010202', null, '第七展厅“汉唐陶俑馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('113', '1', '47100000010203', null, '第九展厅“洛阳珍宝展”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('114', '1', '47100000010204', null, '第八展厅“唐三彩馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('115', '1', '47100000010205', null, '第十展厅“宫廷文物馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('116', '1', '4710000001010101', null, '象牙化石', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('117', '1', '4710000001010103', null, '鹳鱼石斧图', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('118', '1', '4710000001010104', null, '妯娌、王湾遗址', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('119', '1', '4710000001010105', null, '龙山', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('120', '1', '4710000001010106', null, '原始生活场景模拟', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('121', '1', '4710000001010107', null, '中国第一龙', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('122', '1', '4710000001010108', null, '母鼓方罍', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('123', '1', '4710000001010109', null, '北窑西周贵族墓地', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('124', '1', '4710000001010110', null, '“内白”提梁铜卣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('125', '1', '4710000001010111', null, '西周青铜器（林校车马坑）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('126', '1', '4710000001010102', null, '裴李岗文化', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('127', '1', '4710000001010112', null, '鳞环纹铜鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('128', '1', '4710000001010113', null, '玉牛形调色器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('129', '1', '4710000001010114', null, '齐候铜盂', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('130', '1', '4710000001010115', null, '龙纹铜方壶', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('131', '1', '4710000001010118', null, '金财陵村', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('132', '1', '4710000001010119', null, '孔子、老子', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('133', '1', '4710000001010120', null, '东周玉石加工', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('134', '1', '4710000001020101', null, '行书五言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('135', '1', '4710000001020102', null, '行书对联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('136', '1', '4710000001020103', null, '行书七言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('137', '1', '4710000001020104', null, '桃花、松柏、山水画', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('138', '1', '4710000001020105', null, '行书轴', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('139', '1', '4710000001020106', null, '四骏、蔬菜、楷书八言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('140', '1', '4710000001020107', null, '行书四言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('141', '1', '4710000001020108', null, '行书七言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('142', '1', '4710000001020109', null, '石鼓文、枇杷、墨菊', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('143', '1', '4710000001020110', null, '行书四言、隶书七言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('144', '1', '4710000001020111', null, '花鸟、芭蕉图', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('145', '1', '4710000001020112', null, '墨竹、三水画', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('146', '1', '4710000001020113', null, '郑文翰作品', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('147', '1', '4710000001020114', null, '洛神、牡丹图', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('148', '1', '47100000010104', null, '第四展厅“古代石刻”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('149', '1', '47100000010105', null, '第五展厅“临展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('150', '1', '47100000010206', null, '第十一展厅“临展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('151', '1', '47100000010207', null, '第十二展厅“王绣牡丹艺术馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('152', '1', '47100000010208', null, '第十三展厅“临展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('153', '1', '4710000001020301', null, '银鎏金宗喀巴像', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('154', '1', '4710000001020302', null, '鎏金铜观音', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('155', '1', '4710000001020303', null, '  叔牝方鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('156', '1', '4710000001020304', null, '王作鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('157', '1', '4710000001020305', null, '兽面纹方鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('158', '1', '4710000001020306', null, '黑釉马', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('159', '1', '4710000001020307', null, '错金银铜鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('160', '1', '4710000001020308', null, '白玉杯', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('161', '1', '4710000001020309', null, '方格纹铜鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('162', '1', '4710000001020310', null, '鼓母方鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('163', '1', '4710000001020311', null, '母鼓铜方罍', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('164', '1', '4710000001020312', null, '乳丁纹铜爵', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('165', '1', '4710000001020501', null, '漆金弥勒佛像', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('166', '1', '4710000001020502', null, '镶玉石博古插屏', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('167', '1', '4710000001020503', null, '九莲座铜鎏金无量寿佛', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('168', '1', '4710000001020504', null, '鎏金银坛城（调控8）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('169', '1', '4710000001020505', null, '铜鎏金曼达（调控7）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('170', '1', '4710000001020507', null, '黄杨木雕', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('171', '1', '4710000001020508', null, '慈宁宫', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('172', '1', '4710000001020201', null, '汉代陶俑与丧葬制度', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('173', '1', '4710000001020202', null, '汉代陶俑与汉代生活', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('174', '1', '4710000001020203', null, '彩绘陶乐舞俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('175', '1', '4710000001020204', null, '彩绘陶舞士佣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('176', '1', '4710000001020205', null, '彩绘陶持盾武士', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('177', '1', '4710000001020206', null, '北朝陶俑与南北朝军备', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('178', '1', '4710000001020207', null, '北朝与南北朝丧葬制度', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('179', '1', '4710000001020208', null, '彩绘陶驯马与舞马', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('180', '1', '4710000001020209', null, '武士陶俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('181', '1', '4710000001020210', null, '彩绘陶俑文官（CO2）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('182', '1', '4710000001010201', null, '青釉镇墓兽（调控4）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('183', '1', '4710000001010202', null, '彩绘仪仗陶俑群', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('184', '1', '4710000001010203', null, '铜鎏金兽（调控3）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('185', '1', '4710000001010204', null, '玻璃瓶（调控2）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('186', '1', '4710000001010205', null, '东汉墓室壁画', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('187', '1', '4710000001010206', null, '彩绘陶制百花灯', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('188', '1', '4710000001010207', null, '汉代铜器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('189', '1', '4710000001010208', null, '汉代彩绘陶器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('190', '1', '4710000001010209', null, '彩绘人物紋陶奁', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('191', '1', '4710000001010210', null, '汉代玉器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('192', '1', '4710000001010301', null, '唐恭陵', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('193', '1', '4710000001010302', null, '丝绸之路', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('194', '1', '4710000001010303', null, '绿釉载丝骆驼', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('195', '1', '4710000001010304', null, '三彩大王佣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('196', '1', '4710000001010305', null, '唐彩绘乐舞俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('197', '1', '4710000001010306', null, '铜币、铜镜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('198', '1', '4710000001010307', null, '彩绘贴金马', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('199', '1', '4710000001010308', null, '贾敦赜墓', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('200', '1', '4710000001010309', null, '瓷枕', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('201', '1', '4710000001010310', null, '白釉剔花牡丹纹瓷罐', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('202', '1', '4710000001010311', null, '唐尺', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('203', '1', '4710000001010312', null, '彩绘文官佣（调控6）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('204', '1', '4710000001010313', null, '三彩马（调控5）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('205', '1', '4710000001010121', null, '青铜（调控1）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('206', '1', '4710000001020313', null, '面具', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('207', '1', '4710000001020211', null, '南北朝服饰佣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('208', '1', '4710000001020509', null, '紫檀木包边镶玉石插屏', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('209', '1', '4710000001020510', null, '藏教文化', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('210', '1', '4710000001020212', null, '汉唐服饰俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('211', '1', '4710000001020213', null, '唐代陶俑与唐代乐舞', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('212', '1', '4710000001020214', null, '隋唐陶俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('213', '1', '4710000001020215', null, '唐代乐舞', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('214', '2', '47100000010101', null, '第一展厅“史前、夏商周”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('215', '2', '47100000010102', null, '第二展厅“汉魏展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('216', '2', '47100000010103', null, '第三展厅“隋唐、五代”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('217', '2', '47100000010201', null, '第六展厅“书画展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('218', '2', '47100000010202', null, '第七展厅“汉唐陶俑馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('219', '2', '47100000010203', null, '第九展厅“洛阳珍宝展”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('220', '2', '47100000010204', null, '第八展厅“唐三彩馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('221', '2', '47100000010205', null, '第十展厅“宫廷文物馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('222', '2', '4710000001010101', null, '象牙化石', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('223', '2', '4710000001010103', null, '鹳鱼石斧图', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('224', '2', '4710000001010104', null, '妯娌、王湾遗址', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('225', '2', '4710000001010105', null, '龙山', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('226', '2', '4710000001010106', null, '原始生活场景模拟', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('227', '2', '4710000001010107', null, '中国第一龙', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('228', '2', '4710000001010108', null, '母鼓方罍', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('229', '2', '4710000001010109', null, '北窑西周贵族墓地', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('230', '2', '4710000001010110', null, '“内白”提梁铜卣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('231', '2', '4710000001010111', null, '西周青铜器（林校车马坑）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('232', '2', '4710000001010102', null, '裴李岗文化', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('233', '2', '4710000001010112', null, '鳞环纹铜鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('234', '2', '4710000001010113', null, '玉牛形调色器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('235', '2', '4710000001010114', null, '齐候铜盂', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('236', '2', '4710000001010115', null, '龙纹铜方壶', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('237', '2', '4710000001010118', null, '金财陵村', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('238', '2', '4710000001010119', null, '孔子、老子', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('239', '2', '4710000001010120', null, '东周玉石加工', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('240', '2', '4710000001020101', null, '行书五言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('241', '2', '4710000001020102', null, '行书对联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('242', '2', '4710000001020103', null, '行书七言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('243', '2', '4710000001020104', null, '桃花、松柏、山水画', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('244', '2', '4710000001020105', null, '行书轴', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('245', '2', '4710000001020106', null, '四骏、蔬菜、楷书八言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('246', '2', '4710000001020107', null, '行书四言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('247', '2', '4710000001020108', null, '行书七言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('248', '2', '4710000001020109', null, '石鼓文、枇杷、墨菊', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('249', '2', '4710000001020110', null, '行书四言、隶书七言联', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('250', '2', '4710000001020111', null, '花鸟、芭蕉图', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('251', '2', '4710000001020112', null, '墨竹、三水画', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('252', '2', '4710000001020113', null, '郑文翰作品', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('253', '2', '4710000001020114', null, '洛神、牡丹图', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('254', '2', '47100000010104', null, '第四展厅“古代石刻”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('255', '2', '47100000010105', null, '第五展厅“临展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('256', '2', '47100000010206', null, '第十一展厅“临展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('257', '2', '47100000010207', null, '第十二展厅“王绣牡丹艺术馆”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('258', '2', '47100000010208', null, '第十三展厅“临展厅”', '展厅', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('259', '2', '4710000001020301', null, '银鎏金宗喀巴像', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('260', '2', '4710000001020302', null, '鎏金铜观音', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('261', '2', '4710000001020303', null, '  叔牝方鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('262', '2', '4710000001020304', null, '王作鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('263', '2', '4710000001020305', null, '兽面纹方鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('264', '2', '4710000001020306', null, '黑釉马', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('265', '2', '4710000001020307', null, '错金银铜鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('266', '2', '4710000001020308', null, '白玉杯', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('267', '2', '4710000001020309', null, '方格纹铜鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('268', '2', '4710000001020310', null, '鼓母方鼎', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('269', '2', '4710000001020311', null, '母鼓铜方罍', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('270', '2', '4710000001020312', null, '乳丁纹铜爵', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('271', '2', '4710000001020501', null, '漆金弥勒佛像', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('272', '2', '4710000001020502', null, '镶玉石博古插屏', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('273', '2', '4710000001020503', null, '九莲座铜鎏金无量寿佛', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('274', '2', '4710000001020504', null, '鎏金银坛城（调控8）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('275', '2', '4710000001020505', null, '铜鎏金曼达（调控7）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('276', '2', '4710000001020507', null, '黄杨木雕', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('277', '2', '4710000001020508', null, '慈宁宫', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('278', '2', '4710000001020201', null, '汉代陶俑与丧葬制度', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('279', '2', '4710000001020202', null, '汉代陶俑与汉代生活', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('280', '2', '4710000001020203', null, '彩绘陶乐舞俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('281', '2', '4710000001020204', null, '彩绘陶舞士佣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('282', '2', '4710000001020205', null, '彩绘陶持盾武士', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('283', '2', '4710000001020206', null, '北朝陶俑与南北朝军备', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('284', '2', '4710000001020207', null, '北朝与南北朝丧葬制度', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('285', '2', '4710000001020208', null, '彩绘陶驯马与舞马', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('286', '2', '4710000001020209', null, '武士陶俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('287', '2', '4710000001020210', null, '彩绘陶俑文官（CO2）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('288', '2', '4710000001010201', null, '青釉镇墓兽（调控4）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('289', '2', '4710000001010202', null, '彩绘仪仗陶俑群', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('290', '2', '4710000001010203', null, '铜鎏金兽（调控3）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('291', '2', '4710000001010204', null, '玻璃瓶（调控2）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('292', '2', '4710000001010205', null, '东汉墓室壁画', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('293', '2', '4710000001010206', null, '彩绘陶制百花灯', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('294', '2', '4710000001010207', null, '汉代铜器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('295', '2', '4710000001010208', null, '汉代彩绘陶器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('296', '2', '4710000001010209', null, '彩绘人物紋陶奁', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('297', '2', '4710000001010210', null, '汉代玉器', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('298', '2', '4710000001010301', null, '唐恭陵', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('299', '2', '4710000001010302', null, '丝绸之路', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('300', '2', '4710000001010303', null, '绿釉载丝骆驼', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('301', '2', '4710000001010304', null, '三彩大王佣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('302', '2', '4710000001010305', null, '唐彩绘乐舞俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('303', '2', '4710000001010306', null, '铜币、铜镜', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('304', '2', '4710000001010307', null, '彩绘贴金马', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('305', '2', '4710000001010308', null, '贾敦赜墓', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('306', '2', '4710000001010309', null, '瓷枕', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('307', '2', '4710000001010310', null, '白釉剔花牡丹纹瓷罐', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('308', '2', '4710000001010311', null, '唐尺', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('309', '2', '4710000001010312', null, '彩绘文官佣（调控6）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('310', '2', '4710000001010313', null, '三彩马（调控5）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('311', '2', '4710000001010121', null, '青铜（调控1）', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('312', '2', '4710000001020313', null, '面具', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('313', '2', '4710000001020211', null, '南北朝服饰佣', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('314', '2', '4710000001020509', null, '紫檀木包边镶玉石插屏', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('315', '2', '4710000001020510', null, '藏教文化', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('316', '2', '4710000001020212', null, '汉唐服饰俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('317', '2', '4710000001020213', null, '唐代陶俑与唐代乐舞', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('318', '2', '4710000001020214', null, '隋唐陶俑', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);
INSERT INTO `data_env` VALUES ('319', '2', '4710000001020215', null, '唐代乐舞', '展柜', '3', '6', null, null, null, null, null, null, null, null, null, null);

-- ----------------------------
-- Table structure for data_envtype_param
-- ----------------------------
DROP TABLE IF EXISTS `data_envtype_param`;
CREATE TABLE `data_envtype_param` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` int(8) NOT NULL COMMENT '日期',
  `mid` int(10) NOT NULL COMMENT '博物馆ID',
  `env_type` varchar(20) DEFAULT NULL COMMENT '环境类型',
  `param` varchar(20) DEFAULT NULL COMMENT '参数名称',
  `max` float(5,2) DEFAULT NULL COMMENT '最大值',
  `min` float(5,2) DEFAULT NULL COMMENT '最小值',
  `max2` float(5,2) DEFAULT NULL COMMENT '最大值（剔除异常值）',
  `min2` float(5,2) DEFAULT NULL COMMENT '最小值（剔除异常值）',
  `middle` float(5,2) DEFAULT NULL COMMENT '中位值',
  `average` float(5,2) DEFAULT NULL COMMENT '平均值（剔除异常值）',
  `count_abnormal` int(5) DEFAULT NULL COMMENT '异常值个数',
  `standard` float(5,2) DEFAULT NULL COMMENT '标准差',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=802 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 环境类型参数综合统计';

-- ----------------------------
-- Records of data_envtype_param
-- ----------------------------
INSERT INTO `data_envtype_param` VALUES ('678', '20161024', '3', '展厅', '7', '16.44', '14.36', '16.44', '14.36', '15.78', '15.65', '0', '0.62');
INSERT INTO `data_envtype_param` VALUES ('679', '20161024', '3', '展柜', '7', '16.38', '13.12', '16.38', '13.12', '15.14', '15.05', '0', '0.73');
INSERT INTO `data_envtype_param` VALUES ('680', '20161024', '3', '库房', '7', '13.87', '13.09', '13.87', '13.09', '13.36', '13.39', '0', '0.19');
INSERT INTO `data_envtype_param` VALUES ('681', '20161024', '3', '展柜', '1', '64.20', '60.77', '64.20', '60.77', '62.62', '62.51', '0', '0.84');
INSERT INTO `data_envtype_param` VALUES ('682', '20161024', '3', '展柜', '2', '66.59', '49.74', '66.59', '49.74', '61.13', '59.91', '0', '5.11');
INSERT INTO `data_envtype_param` VALUES ('683', '20161024', '3', '展柜', '3', '62.79', '60.77', '62.79', '60.94', '62.21', '62.27', '4', '0.46');
INSERT INTO `data_envtype_param` VALUES ('684', '20161024', '3', '展柜', '4', '393.00', '0.00', '393.00', '0.00', '1.00', '55.70', '0', '126.96');
INSERT INTO `data_envtype_param` VALUES ('685', '20161024', '3', '展柜', '6', '323.00', '0.00', '323.00', '0.00', '1.00', '51.39', '0', '112.89');
INSERT INTO `data_envtype_param` VALUES ('686', '20161024', '3', '展柜', '8', '0.08', '0.02', '0.02', '0.02', '0.02', '0.02', '16', '0.01');
INSERT INTO `data_envtype_param` VALUES ('687', '20161024', '3', '展柜', '9', '100.00', '0.00', '100.00', '0.00', '0.00', '32.63', '0', '46.89');
INSERT INTO `data_envtype_param` VALUES ('688', '20161024', '4', '展厅', '7', '99.88', '0.24', '99.88', '0.24', '50.70', '50.74', '0', '28.08');
INSERT INTO `data_envtype_param` VALUES ('689', '20161024', '4', '展柜', '7', '99.93', '0.01', '99.93', '0.01', '48.92', '49.76', '0', '28.93');
INSERT INTO `data_envtype_param` VALUES ('690', '20161024', '4', '库房', '7', '99.42', '0.55', '99.42', '0.55', '48.73', '48.26', '0', '30.24');
INSERT INTO `data_envtype_param` VALUES ('691', '20161024', '4', '展厅', '1', '99.71', '0.23', '99.71', '0.23', '48.55', '47.59', '0', '28.37');
INSERT INTO `data_envtype_param` VALUES ('692', '20161024', '4', '展厅', '2', '99.53', '0.23', '99.53', '0.23', '50.51', '48.09', '0', '28.79');
INSERT INTO `data_envtype_param` VALUES ('693', '20161024', '4', '展厅', '3', '99.92', '0.35', '99.92', '0.35', '56.81', '53.85', '0', '28.04');
INSERT INTO `data_envtype_param` VALUES ('694', '20161024', '4', '展柜', '1', '99.70', '0.06', '99.70', '0.06', '49.46', '49.22', '0', '28.68');
INSERT INTO `data_envtype_param` VALUES ('695', '20161024', '4', '展柜', '2', '99.99', '0.06', '99.99', '0.06', '50.15', '50.15', '0', '29.28');
INSERT INTO `data_envtype_param` VALUES ('696', '20161024', '4', '展柜', '3', '99.99', '0.06', '99.99', '0.06', '50.30', '49.84', '0', '29.02');
INSERT INTO `data_envtype_param` VALUES ('697', '20161024', '4', '库房', '1', '99.13', '0.50', '99.13', '0.50', '51.91', '49.37', '0', '29.22');
INSERT INTO `data_envtype_param` VALUES ('698', '20161024', '4', '库房', '2', '99.13', '0.50', '99.13', '0.50', '51.91', '49.37', '0', '29.22');
INSERT INTO `data_envtype_param` VALUES ('699', '20161024', '4', '库房', '3', '99.13', '0.50', '99.13', '0.50', '51.91', '49.37', '0', '29.22');
INSERT INTO `data_envtype_param` VALUES ('700', '20161024', '4', '展厅', '4', '100.00', '0.00', '100.00', '0.00', '55.50', '52.43', '0', '29.42');
INSERT INTO `data_envtype_param` VALUES ('701', '20161024', '4', '展柜', '4', '100.00', '0.00', '100.00', '0.00', '46.50', '45.83', '0', '29.20');
INSERT INTO `data_envtype_param` VALUES ('702', '20161024', '4', '展厅', '8', '99.85', '0.05', '99.85', '0.05', '53.37', '52.14', '0', '28.75');
INSERT INTO `data_envtype_param` VALUES ('703', '20161024', '4', '展柜', '8', '99.84', '0.03', '99.84', '0.03', '50.87', '51.15', '0', '27.89');
INSERT INTO `data_envtype_param` VALUES ('704', '20161024', '4', '展厅', '9', '100.00', '0.00', '100.00', '0.00', '49.50', '49.18', '0', '29.42');
INSERT INTO `data_envtype_param` VALUES ('705', '20161024', '4', '展柜', '9', '100.00', '0.00', '100.00', '0.00', '49.00', '48.84', '0', '29.33');
INSERT INTO `data_envtype_param` VALUES ('706', '20161024', '4', '库房', '9', '99.00', '0.00', '99.00', '0.00', '44.50', '47.06', '0', '28.03');
INSERT INTO `data_envtype_param` VALUES ('745', '20161024', '1', '展柜', '1', '99.88', '0.24', '99.88', '0.24', '50.70', '50.74', '0', '28.08');
INSERT INTO `data_envtype_param` VALUES ('746', '20161024', '1', '展柜', '2', '99.93', '0.01', '99.93', '0.01', '48.91', '49.76', '0', '28.93');
INSERT INTO `data_envtype_param` VALUES ('747', '20161024', '1', '展柜', '3', '99.42', '0.55', '99.42', '0.55', '48.73', '48.26', '0', '30.24');
INSERT INTO `data_envtype_param` VALUES ('748', '20161024', '1', '展柜', '4', '99.71', '0.23', '99.71', '0.23', '48.55', '47.59', '0', '28.37');
INSERT INTO `data_envtype_param` VALUES ('749', '20161024', '1', '展柜', '5', '99.53', '0.23', '99.53', '0.23', '50.51', '48.09', '0', '28.79');
INSERT INTO `data_envtype_param` VALUES ('750', '20161024', '1', '展柜', '6', '99.92', '0.35', '99.92', '0.35', '56.80', '53.85', '0', '28.04');
INSERT INTO `data_envtype_param` VALUES ('751', '20161024', '1', '展柜', '7', '99.70', '0.43', '99.70', '0.43', '49.49', '50.03', '0', '28.93');
INSERT INTO `data_envtype_param` VALUES ('752', '20161024', '1', '展柜', '8', '99.99', '0.06', '99.99', '0.06', '50.15', '50.15', '0', '29.28');
INSERT INTO `data_envtype_param` VALUES ('753', '20161024', '1', '展柜', '9', '99.99', '0.06', '99.99', '0.06', '50.30', '49.84', '0', '29.02');
INSERT INTO `data_envtype_param` VALUES ('754', '20161024', '2', '展柜', '3', '99.13', '0.50', '99.13', '0.50', '51.91', '49.37', '0', '29.22');
INSERT INTO `data_envtype_param` VALUES ('755', '20161024', '2', '展柜', '4', '99.13', '0.50', '99.13', '0.50', '51.91', '49.37', '0', '29.22');
INSERT INTO `data_envtype_param` VALUES ('756', '20161024', '2', '展柜', '2', '99.13', '0.50', '99.13', '0.50', '51.91', '49.37', '0', '29.22');
INSERT INTO `data_envtype_param` VALUES ('757', '20161024', '2', '展柜', '1', '100.00', '0.00', '100.00', '0.00', '55.50', '52.43', '0', '29.42');
INSERT INTO `data_envtype_param` VALUES ('758', '20161024', '2', '展柜', '6', '100.00', '0.00', '100.00', '0.00', '46.50', '45.83', '0', '29.20');
INSERT INTO `data_envtype_param` VALUES ('759', '20161024', '2', '展柜', '5', '99.85', '0.05', '99.85', '0.05', '53.37', '52.14', '0', '28.75');
INSERT INTO `data_envtype_param` VALUES ('760', '20161024', '2', '展柜', '8', '99.84', '0.03', '99.84', '0.03', '50.87', '51.15', '0', '27.89');
INSERT INTO `data_envtype_param` VALUES ('761', '20161024', '2', '展柜', '7', '100.00', '0.00', '100.00', '0.00', '49.50', '49.18', '0', '29.42');
INSERT INTO `data_envtype_param` VALUES ('762', '20161024', '2', '展柜', '9', '100.00', '0.00', '100.00', '0.00', '49.00', '48.84', '0', '29.33');

-- ----------------------------
-- Table structure for data_env_complex
-- ----------------------------
DROP TABLE IF EXISTS `data_env_complex`;
CREATE TABLE `data_env_complex` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` int(8) NOT NULL COMMENT '日期',
  `eid` int(10) NOT NULL COMMENT '环境ID',
  `temperature_scatter` float(2,2) DEFAULT NULL COMMENT '温度离散系数',
  `humidity_scatter` float(2,2) DEFAULT NULL COMMENT '湿度离散系数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 环境综合统计';

-- ----------------------------
-- Records of data_env_complex
-- ----------------------------
INSERT INTO `data_env_complex` VALUES ('192', '20161024', '37', '0.59', '0.53');
INSERT INTO `data_env_complex` VALUES ('193', '20161024', '41', '0.53', '0.55');
INSERT INTO `data_env_complex` VALUES ('195', '20160104', '26', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('196', '20160104', '33', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('197', '20160104', '4', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('198', '20160104', '28', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('199', '20160104', '11', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('200', '20160104', '21', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('201', '20160104', '2', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('202', '20160104', '25', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('203', '20160104', '31', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('204', '20160104', '3', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('205', '20160104', '36', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('206', '20160104', '27', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('207', '20160104', '30', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('208', '20160104', '32', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('209', '20160104', '1', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('210', '20160104', '34', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('211', '20160104', '29', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('212', '20160104', '20', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('213', '20160104', '35', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('214', '20160104', '23', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('215', '20160104', '22', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('216', '20160104', '24', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('217', '20160104', '10', '0.00', '0.01');
INSERT INTO `data_env_complex` VALUES ('218', '20160104', '5', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('219', '20160104', '26', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('220', '20160104', '33', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('221', '20160104', '4', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('222', '20160104', '28', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('223', '20160104', '11', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('224', '20160104', '21', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('225', '20160104', '2', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('226', '20160104', '25', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('227', '20160104', '31', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('228', '20160104', '3', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('229', '20160104', '36', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('230', '20160104', '27', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('231', '20160104', '30', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('232', '20160104', '32', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('233', '20160104', '1', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('234', '20160104', '34', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('235', '20160104', '29', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('236', '20160104', '20', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('237', '20160104', '35', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('238', '20160104', '23', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('239', '20160104', '22', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('240', '20160104', '24', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('241', '20160104', '10', '0.00', '0.01');
INSERT INTO `data_env_complex` VALUES ('242', '20160104', '5', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('243', '20160103', '28', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('244', '20160103', '10', '0.00', '0.01');
INSERT INTO `data_env_complex` VALUES ('245', '20160103', '22', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('246', '20160103', '24', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('247', '20160103', '5', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('248', '20160103', '21', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('249', '20160103', '25', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('250', '20160103', '29', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('251', '20160103', '2', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('252', '20160103', '27', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('253', '20160103', '11', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('254', '20160103', '32', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('255', '20160103', '26', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('256', '20160103', '34', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('257', '20160103', '31', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('258', '20160103', '3', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('259', '20160103', '20', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('260', '20160103', '35', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('261', '20160103', '23', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('262', '20160103', '30', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('263', '20160103', '33', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('264', '20160103', '1', '0.00', '0.03');
INSERT INTO `data_env_complex` VALUES ('265', '20160103', '4', '0.00', '0.03');
INSERT INTO `data_env_complex` VALUES ('266', '20160103', '36', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('267', '20160102', '33', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('268', '20160102', '1', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('269', '20160102', '24', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('270', '20160102', '28', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('271', '20160102', '22', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('272', '20160102', '21', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('273', '20160102', '25', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('274', '20160102', '29', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('275', '20160102', '26', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('276', '20160102', '10', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('277', '20160102', '4', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('278', '20160102', '36', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('279', '20160102', '27', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('280', '20160102', '32', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('281', '20160102', '34', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('282', '20160102', '5', '0.00', '0.02');
INSERT INTO `data_env_complex` VALUES ('283', '20160102', '11', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('284', '20160102', '20', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('285', '20160102', '35', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('286', '20160102', '23', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('287', '20160102', '30', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('288', '20160102', '2', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('289', '20160102', '31', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('290', '20160102', '3', '0.00', '0.00');
INSERT INTO `data_env_complex` VALUES ('291', '20160101', '22', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('292', '20160101', '29', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('293', '20160101', '21', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('294', '20160101', '25', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('295', '20160101', '10', '0.01', '0.02');
INSERT INTO `data_env_complex` VALUES ('296', '20160101', '4', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('297', '20160101', '26', '0.03', '0.01');
INSERT INTO `data_env_complex` VALUES ('298', '20160101', '30', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('299', '20160101', '36', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('300', '20160101', '27', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('301', '20160101', '32', '0.02', '0.01');
INSERT INTO `data_env_complex` VALUES ('302', '20160101', '5', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('303', '20160101', '34', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('304', '20160101', '11', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('305', '20160101', '20', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('306', '20160101', '2', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('307', '20160101', '35', '0.02', '0.01');
INSERT INTO `data_env_complex` VALUES ('308', '20160101', '23', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('309', '20160101', '33', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('310', '20160101', '24', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('311', '20160101', '28', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('312', '20160101', '31', '0.02', '0.01');
INSERT INTO `data_env_complex` VALUES ('313', '20160101', '3', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('314', '20160101', '1', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('328', '20160101', '22', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('329', '20160101', '29', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('330', '20160101', '21', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('331', '20160101', '25', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('332', '20160101', '10', '0.01', '0.02');
INSERT INTO `data_env_complex` VALUES ('333', '20160101', '4', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('334', '20160101', '26', '0.03', '0.01');
INSERT INTO `data_env_complex` VALUES ('335', '20160101', '30', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('336', '20160101', '36', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('337', '20160101', '27', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('338', '20160101', '32', '0.02', '0.01');
INSERT INTO `data_env_complex` VALUES ('339', '20160101', '5', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('340', '20160101', '34', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('341', '20160101', '11', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('342', '20160101', '20', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('343', '20160101', '2', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('344', '20160101', '35', '0.02', '0.01');
INSERT INTO `data_env_complex` VALUES ('345', '20160101', '23', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('346', '20160101', '33', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('347', '20160101', '24', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('348', '20160101', '28', '0.01', '0.00');
INSERT INTO `data_env_complex` VALUES ('349', '20160101', '31', '0.02', '0.01');
INSERT INTO `data_env_complex` VALUES ('350', '20160101', '3', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('351', '20160101', '1', '0.01', '0.01');
INSERT INTO `data_env_complex` VALUES ('352', '20161024', '42', '0.05', '0.02');
INSERT INTO `data_env_complex` VALUES ('353', '20161024', '43', '0.03', '0.01');

-- ----------------------------
-- Table structure for data_env_compliance
-- ----------------------------
DROP TABLE IF EXISTS `data_env_compliance`;
CREATE TABLE `data_env_compliance` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` int(8) NOT NULL COMMENT '日期',
  `eid` int(10) NOT NULL COMMENT '环境ID',
  `temperature_total` int(5) DEFAULT NULL COMMENT '温度数据总数',
  `temperature_abnormal` int(5) DEFAULT NULL COMMENT '温度未达标数',
  `humidity_total` int(5) DEFAULT NULL COMMENT '湿度数据总数',
  `humidity_abnormal` int(5) DEFAULT NULL COMMENT '湿度未达标数',
  `light_total` int(5) DEFAULT NULL COMMENT '光照数据总数',
  `light_abnormal` int(5) DEFAULT NULL COMMENT '光照未达标数',
  `uv_total` int(5) DEFAULT NULL COMMENT '紫外数据总数',
  `uv_abnormal` int(5) DEFAULT NULL COMMENT '紫外未达标数',
  `voc_total` int(5) DEFAULT NULL COMMENT 'VOC数据总数',
  `voc_abnormal` int(5) DEFAULT NULL COMMENT 'VOC未达标数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=339 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 环境参数达标统计';

-- ----------------------------
-- Records of data_env_compliance
-- ----------------------------
INSERT INTO `data_env_compliance` VALUES ('158', '20161025', '37', '144', '139', '144', '106', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('159', '20161024', '42', '144', '137', '144', '104', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('160', '20161024', '43', '288', '276', '288', '202', '143', '0', '144', '116', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('161', '20161024', '45', '144', '141', '144', '95', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('162', '20161024', '40', '288', '272', '288', '211', '0', '0', '0', '0', '143', '0');
INSERT INTO `data_env_compliance` VALUES ('163', '20161024', '49', '288', '272', '288', '201', '0', '0', '0', '0', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('164', '20161024', '68', '144', '138', '144', '94', '142', '0', '144', '104', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('165', '20161024', '39', '144', '138', '144', '102', '144', '0', '144', '119', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('166', '20161024', '41', '289', '279', '289', '189', '145', '0', '145', '111', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('167', '20161024', '59', '144', '140', '144', '99', '0', '0', '0', '0', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('168', '20161024', '60', '288', '276', '288', '202', '0', '0', '144', '121', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('169', '20161024', '61', '146', '142', '146', '101', '144', '0', '146', '115', '143', '0');
INSERT INTO `data_env_compliance` VALUES ('170', '20161024', '37', '144', '141', '144', '97', '0', '0', '0', '0', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('171', '20160104', '26', '192', '0', '192', '0', '92', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('172', '20160104', '33', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('173', '20160104', '4', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('174', '20160104', '28', '140', '0', '140', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('175', '20160104', '11', '96', '0', '96', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('176', '20160104', '21', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('177', '20160104', '2', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('178', '20160104', '25', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('179', '20160104', '31', '96', '0', '96', '0', '15', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('180', '20160104', '3', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('181', '20160104', '36', '133', '0', '133', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('182', '20160104', '27', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('183', '20160104', '30', '93', '0', '93', '0', '0', '0', '0', '0', '93', '0');
INSERT INTO `data_env_compliance` VALUES ('184', '20160104', '32', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('185', '20160104', '1', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('186', '20160104', '34', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('187', '20160104', '29', '141', '0', '141', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('188', '20160104', '20', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('189', '20160104', '35', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('190', '20160104', '23', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('191', '20160104', '22', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('192', '20160104', '24', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('193', '20160104', '10', '382', '0', '382', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('194', '20160104', '5', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('195', '20160104', '26', '192', '0', '192', '0', '92', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('196', '20160104', '33', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('197', '20160104', '4', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('198', '20160104', '28', '140', '0', '140', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('199', '20160104', '11', '96', '0', '96', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('200', '20160104', '21', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('201', '20160104', '2', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('202', '20160104', '25', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('203', '20160104', '31', '96', '0', '96', '0', '15', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('204', '20160104', '3', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('205', '20160104', '36', '133', '0', '133', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('206', '20160104', '27', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('207', '20160104', '30', '93', '0', '93', '0', '0', '0', '0', '0', '93', '0');
INSERT INTO `data_env_compliance` VALUES ('208', '20160104', '32', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('209', '20160104', '1', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('210', '20160104', '34', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('211', '20160104', '29', '141', '0', '141', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('212', '20160104', '20', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('213', '20160104', '35', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('214', '20160104', '23', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('215', '20160104', '22', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('216', '20160104', '24', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('217', '20160104', '10', '382', '0', '382', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('218', '20160104', '5', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('219', '20160103', '28', '140', '0', '140', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('220', '20160103', '10', '382', '0', '382', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('221', '20160103', '22', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('222', '20160103', '24', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('223', '20160103', '5', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('224', '20160103', '21', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('225', '20160103', '25', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('226', '20160103', '29', '142', '0', '142', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('227', '20160103', '2', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('228', '20160103', '27', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('229', '20160103', '11', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('230', '20160103', '32', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('231', '20160103', '26', '192', '0', '192', '0', '91', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('232', '20160103', '34', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('233', '20160103', '31', '96', '0', '96', '0', '31', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('234', '20160103', '3', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('235', '20160103', '20', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('236', '20160103', '35', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('237', '20160103', '23', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('238', '20160103', '30', '95', '0', '95', '0', '0', '0', '0', '0', '95', '0');
INSERT INTO `data_env_compliance` VALUES ('239', '20160103', '33', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('240', '20160103', '1', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('241', '20160103', '4', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('242', '20160103', '36', '135', '0', '135', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('243', '20160102', '33', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('244', '20160102', '1', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('245', '20160102', '24', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('246', '20160102', '28', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('247', '20160102', '22', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('248', '20160102', '21', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('249', '20160102', '25', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('250', '20160102', '29', '141', '0', '141', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('251', '20160102', '26', '191', '0', '191', '0', '93', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('252', '20160102', '10', '382', '0', '382', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('253', '20160102', '4', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('254', '20160102', '36', '137', '0', '137', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('255', '20160102', '27', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('256', '20160102', '32', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('257', '20160102', '34', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('258', '20160102', '5', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('259', '20160102', '11', '96', '0', '96', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('260', '20160102', '20', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('261', '20160102', '35', '142', '0', '142', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('262', '20160102', '23', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('263', '20160102', '30', '91', '0', '91', '0', '0', '0', '0', '0', '91', '0');
INSERT INTO `data_env_compliance` VALUES ('264', '20160102', '2', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('265', '20160102', '31', '95', '0', '95', '0', '30', '0', '95', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('266', '20160102', '3', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('267', '20160101', '22', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('268', '20160101', '29', '141', '0', '141', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('269', '20160101', '21', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('270', '20160101', '25', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('271', '20160101', '10', '381', '0', '381', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('272', '20160101', '4', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('273', '20160101', '26', '191', '0', '191', '0', '95', '0', '95', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('274', '20160101', '30', '90', '0', '90', '0', '0', '0', '0', '0', '90', '0');
INSERT INTO `data_env_compliance` VALUES ('275', '20160101', '36', '130', '0', '130', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('276', '20160101', '27', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('277', '20160101', '32', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('278', '20160101', '5', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('279', '20160101', '34', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('280', '20160101', '11', '96', '0', '96', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('281', '20160101', '20', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('282', '20160101', '2', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('283', '20160101', '35', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('284', '20160101', '23', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('285', '20160101', '33', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('286', '20160101', '24', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('287', '20160101', '28', '142', '0', '142', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('288', '20160101', '31', '96', '0', '96', '0', '32', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('289', '20160101', '3', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('290', '20160101', '1', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('291', '20161026', '37', '144', '139', '144', '106', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('292', '20161024', '42', '144', '137', '144', '104', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('293', '20161024', '43', '288', '276', '288', '202', '143', '0', '144', '116', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('294', '20161024', '45', '144', '141', '144', '95', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('295', '20161024', '40', '288', '272', '288', '211', '0', '0', '0', '0', '143', '0');
INSERT INTO `data_env_compliance` VALUES ('296', '20161024', '49', '288', '272', '288', '201', '0', '0', '0', '0', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('297', '20161024', '68', '144', '138', '144', '94', '142', '0', '144', '104', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('298', '20161024', '39', '144', '138', '144', '102', '144', '0', '144', '119', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('299', '20161024', '41', '289', '279', '289', '189', '145', '0', '145', '111', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('300', '20161024', '59', '144', '140', '144', '99', '0', '0', '0', '0', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('301', '20161024', '60', '288', '276', '288', '202', '0', '0', '144', '121', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('302', '20161024', '61', '146', '142', '146', '101', '144', '0', '146', '115', '143', '0');
INSERT INTO `data_env_compliance` VALUES ('303', '20161024', '47', '144', '141', '144', '97', '0', '0', '0', '0', '144', '0');
INSERT INTO `data_env_compliance` VALUES ('304', '20160101', '22', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('305', '20160101', '29', '141', '0', '141', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('306', '20160101', '21', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('307', '20160101', '25', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('308', '20160101', '10', '381', '0', '381', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('309', '20160101', '4', '95', '0', '105', '0', '98', '0', '88', '0', '93', '0');
INSERT INTO `data_env_compliance` VALUES ('310', '20160101', '26', '191', '0', '191', '0', '95', '0', '98', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('311', '20160101', '30', '90', '0', '90', '0', '0', '0', '0', '0', '90', '0');
INSERT INTO `data_env_compliance` VALUES ('312', '20160101', '36', '130', '0', '130', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('313', '20160101', '27', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('314', '20160101', '32', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('315', '20160101', '5', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('316', '20160101', '34', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('317', '20160101', '11', '96', '0', '96', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('318', '20160101', '20', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('319', '20160101', '2', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('320', '20160101', '35', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('321', '20160101', '23', '144', '0', '144', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('322', '20160101', '33', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('323', '20160101', '24', '143', '0', '143', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('324', '20160101', '28', '142', '0', '142', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('325', '20160101', '31', '96', '0', '96', '0', '32', '0', '96', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('326', '20160101', '3', '95', '0', '95', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('327', '20160101', '1', '94', '0', '94', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `data_env_compliance` VALUES ('328', '20161024', '6', '45', '5', '80', '8', '50', '8', '25', '3', '78', '5');
INSERT INTO `data_env_compliance` VALUES ('329', '20161024', '7', '48', '2', '34', '4', '34', '5', '44', '4', '48', '6');
INSERT INTO `data_env_compliance` VALUES ('330', '20161024', '211', '78', '4', '34', '2', '54', '3', '54', '4', '65', '6');
INSERT INTO `data_env_compliance` VALUES ('331', '20161024', '212', '43', '5', '43', '3', '34', '5', '65', '5', '76', '5');
INSERT INTO `data_env_compliance` VALUES ('332', '20161024', '213', '35', '6', '54', '4', '67', '6', '76', '6', '87', '8');
INSERT INTO `data_env_compliance` VALUES ('333', '20161024', '222', '45', '7', '54', '34', '43', '23', '76', '8', '76', '13');
INSERT INTO `data_env_compliance` VALUES ('334', '20161024', '223', '34', '9', '76', '23', '54', '43', '87', '23', '78', '24');
INSERT INTO `data_env_compliance` VALUES ('335', '20161024', '224', '46', '10', '78', '13', '76', '25', '65', '23', '34', '12');
INSERT INTO `data_env_compliance` VALUES ('336', '20161025', '41', '122', '2', '55', '0', '85', '4', '96', '0', '74', '0');
INSERT INTO `data_env_compliance` VALUES ('337', '20161024', '214', '55', '4', '78', '4', '78', '1', '57', '2', '85', '0');
INSERT INTO `data_env_compliance` VALUES ('338', '20161024', '215', '66', '4', '48', '3', '68', '2', '68', '0', '49', '0');

-- ----------------------------
-- Table structure for data_env_param
-- ----------------------------
DROP TABLE IF EXISTS `data_env_param`;
CREATE TABLE `data_env_param` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` int(8) NOT NULL COMMENT '日期',
  `eid` int(10) NOT NULL COMMENT '环境ID',
  `param` varchar(20) DEFAULT NULL COMMENT '参数名称',
  `max` float(5,2) DEFAULT NULL COMMENT '最大值',
  `min` float(5,2) DEFAULT NULL COMMENT '最小值',
  `max2` float(5,2) DEFAULT NULL COMMENT '最大值（剔除异常值）',
  `min2` float(5,2) DEFAULT NULL COMMENT '最小值（剔除异常值）',
  `middle` float(5,2) DEFAULT NULL COMMENT '中位值',
  `average` float(5,2) DEFAULT NULL COMMENT '平均值（剔除异常值）',
  `count_abnormal` int(5) DEFAULT NULL COMMENT '异常值个数',
  `standard` float(5,2) DEFAULT NULL COMMENT '标准差',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=742 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 环境参数综合统计';

-- ----------------------------
-- Records of data_env_param
-- ----------------------------
INSERT INTO `data_env_param` VALUES ('689', '20160101', '22', 'temperature', '16.57', '16.15', '16.57', '16.15', '16.22', '16.33', '0', '0.12');
INSERT INTO `data_env_param` VALUES ('690', '20160101', '22', 'humidity', '61.19', '60.31', '61.19', '60.31', '61.05', '60.86', '0', '0.27');
INSERT INTO `data_env_param` VALUES ('691', '20160101', '29', 'temperature', '14.92', '14.48', '14.92', '14.48', '14.54', '14.70', '0', '0.12');
INSERT INTO `data_env_param` VALUES ('692', '20160101', '29', 'humidity', '61.03', '60.52', '61.03', '60.52', '60.92', '60.79', '0', '0.14');
INSERT INTO `data_env_param` VALUES ('693', '20160101', '21', 'temperature', '16.26', '15.89', '16.26', '15.89', '15.93', '16.06', '0', '0.10');
INSERT INTO `data_env_param` VALUES ('694', '20160101', '21', 'humidity', '61.53', '60.97', '61.53', '60.97', '61.49', '61.30', '0', '0.17');
INSERT INTO `data_env_param` VALUES ('695', '20160101', '25', 'temperature', '16.18', '15.54', '16.18', '15.54', '15.62', '15.80', '0', '0.19');
INSERT INTO `data_env_param` VALUES ('696', '20160101', '25', 'humidity', '60.77', '59.64', '60.77', '59.64', '60.58', '60.37', '0', '0.36');
INSERT INTO `data_env_param` VALUES ('697', '20160101', '10', 'temperature', '13.55', '13.17', '13.55', '13.17', '13.24', '13.37', '0', '0.11');
INSERT INTO `data_env_param` VALUES ('698', '20160101', '10', 'humidity', '68.71', '63.99', '68.71', '63.99', '64.56', '65.51', '0', '1.37');
INSERT INTO `data_env_param` VALUES ('699', '20160101', '4', 'temperature', '16.42', '15.97', '16.42', '15.97', '16.03', '16.23', '0', '0.13');
INSERT INTO `data_env_param` VALUES ('700', '20160101', '4', 'humidity', '59.20', '56.99', '59.20', '56.99', '58.15', '58.01', '0', '0.58');
INSERT INTO `data_env_param` VALUES ('701', '20160101', '26', 'temperature', '16.20', '14.76', '16.20', '14.76', '15.43', '15.42', '0', '0.42');
INSERT INTO `data_env_param` VALUES ('702', '20160101', '26', 'humidity', '60.25', '57.25', '60.25', '57.25', '58.18', '58.58', '0', '0.70');
INSERT INTO `data_env_param` VALUES ('703', '20160101', '26', 'light', '320.00', '1.00', '320.00', '1.00', '296.00', '102.98', '0', '143.27');
INSERT INTO `data_env_param` VALUES ('704', '20160101', '26', 'uv', '0.08', '0.02', '0.08', '0.02', '0.07', '0.04', '0', '0.02');
INSERT INTO `data_env_param` VALUES ('705', '20160101', '30', 'temperature', '14.33', '13.84', '14.33', '13.84', '13.89', '14.02', '0', '0.14');
INSERT INTO `data_env_param` VALUES ('706', '20160101', '30', 'humidity', '51.39', '50.96', '51.39', '50.96', '51.27', '51.14', '0', '0.12');
INSERT INTO `data_env_param` VALUES ('707', '20160101', '30', 'voc', '100.00', '100.00', '100.00', '100.00', '100.00', '100.00', '0', '0.00');
INSERT INTO `data_env_param` VALUES ('708', '20160101', '36', 'temperature', '15.05', '14.60', '15.05', '14.60', '14.73', '14.79', '0', '0.13');
INSERT INTO `data_env_param` VALUES ('709', '20160101', '36', 'humidity', '62.97', '62.25', '62.97', '62.25', '62.63', '62.55', '0', '0.18');
INSERT INTO `data_env_param` VALUES ('710', '20160101', '27', 'temperature', '15.59', '15.14', '15.59', '15.14', '15.14', '15.35', '0', '0.12');
INSERT INTO `data_env_param` VALUES ('711', '20160101', '27', 'humidity', '62.52', '61.93', '62.52', '61.93', '62.49', '62.30', '0', '0.16');
INSERT INTO `data_env_param` VALUES ('712', '20160101', '32', 'temperature', '15.29', '14.49', '15.29', '14.49', '14.79', '14.77', '0', '0.23');
INSERT INTO `data_env_param` VALUES ('713', '20160101', '32', 'humidity', '63.31', '61.55', '63.31', '61.55', '62.35', '62.24', '0', '0.59');
INSERT INTO `data_env_param` VALUES ('714', '20160101', '5', 'temperature', '16.04', '15.56', '16.04', '15.56', '15.67', '15.76', '0', '0.12');
INSERT INTO `data_env_param` VALUES ('715', '20160101', '5', 'humidity', '63.49', '60.67', '63.49', '60.67', '60.95', '61.87', '0', '0.83');
INSERT INTO `data_env_param` VALUES ('716', '20160101', '34', 'temperature', '15.36', '14.84', '15.36', '14.84', '14.96', '15.04', '0', '0.15');
INSERT INTO `data_env_param` VALUES ('717', '20160101', '34', 'humidity', '56.73', '56.32', '56.73', '56.32', '56.46', '56.47', '0', '0.11');
INSERT INTO `data_env_param` VALUES ('718', '20160101', '11', 'temperature', '15.41', '15.06', '15.41', '15.06', '15.07', '15.22', '0', '0.10');
INSERT INTO `data_env_param` VALUES ('719', '20160101', '11', 'humidity', '50.45', '49.62', '50.45', '49.62', '49.74', '49.85', '0', '0.15');
INSERT INTO `data_env_param` VALUES ('720', '20160101', '20', 'temperature', '16.39', '16.06', '16.39', '16.06', '16.08', '16.21', '0', '0.10');
INSERT INTO `data_env_param` VALUES ('721', '20160101', '20', 'humidity', '62.50', '61.94', '62.50', '61.94', '62.46', '62.24', '0', '0.16');
INSERT INTO `data_env_param` VALUES ('722', '20160101', '2', 'temperature', '15.19', '14.65', '15.19', '14.65', '14.79', '14.82', '0', '0.14');
INSERT INTO `data_env_param` VALUES ('723', '20160101', '2', 'humidity', '65.39', '62.80', '65.39', '62.80', '64.36', '64.22', '0', '0.89');
INSERT INTO `data_env_param` VALUES ('724', '20160101', '35', 'temperature', '15.55', '14.72', '15.55', '14.72', '15.05', '15.03', '0', '0.24');
INSERT INTO `data_env_param` VALUES ('725', '20160101', '35', 'humidity', '63.31', '61.84', '63.31', '61.84', '62.47', '62.58', '0', '0.37');
INSERT INTO `data_env_param` VALUES ('726', '20160101', '23', 'temperature', '15.74', '15.36', '15.74', '15.36', '15.40', '15.53', '0', '0.11');
INSERT INTO `data_env_param` VALUES ('727', '20160101', '23', 'humidity', '62.47', '61.93', '62.47', '61.93', '62.40', '62.23', '0', '0.16');
INSERT INTO `data_env_param` VALUES ('728', '20160101', '33', 'temperature', '15.16', '14.67', '15.16', '14.67', '14.76', '14.84', '0', '0.14');
INSERT INTO `data_env_param` VALUES ('729', '20160101', '33', 'humidity', '57.56', '56.52', '57.56', '56.52', '57.18', '57.01', '0', '0.26');
INSERT INTO `data_env_param` VALUES ('730', '20160101', '24', 'temperature', '15.93', '15.38', '15.93', '15.38', '15.44', '15.61', '0', '0.15');
INSERT INTO `data_env_param` VALUES ('731', '20160101', '24', 'humidity', '53.69', '53.41', '53.69', '53.41', '53.68', '53.57', '0', '0.07');
INSERT INTO `data_env_param` VALUES ('732', '20160101', '28', 'temperature', '15.74', '15.30', '15.74', '15.30', '15.33', '15.50', '0', '0.13');
INSERT INTO `data_env_param` VALUES ('733', '20160101', '28', 'humidity', '63.37', '62.63', '63.37', '62.63', '63.14', '62.94', '0', '0.19');
INSERT INTO `data_env_param` VALUES ('734', '20160101', '31', 'temperature', '14.23', '13.37', '14.23', '13.37', '13.72', '13.71', '0', '0.26');
INSERT INTO `data_env_param` VALUES ('735', '20160101', '31', 'humidity', '64.76', '63.28', '64.76', '63.28', '63.86', '63.95', '0', '0.39');
INSERT INTO `data_env_param` VALUES ('736', '20160101', '31', 'light', '398.00', '378.00', '398.00', '378.00', '379.50', '383.06', '0', '4.21');
INSERT INTO `data_env_param` VALUES ('737', '20160101', '31', 'uv', '0.02', '0.02', '0.02', '0.02', '0.02', '0.02', '0', '0.00');
INSERT INTO `data_env_param` VALUES ('738', '20160101', '3', 'temperature', '16.54', '15.93', '16.54', '15.93', '16.08', '16.13', '0', '0.17');
INSERT INTO `data_env_param` VALUES ('739', '20160101', '3', 'humidity', '61.05', '58.84', '61.05', '58.84', '60.39', '60.15', '0', '0.74');
INSERT INTO `data_env_param` VALUES ('740', '20160101', '1', 'temperature', '16.04', '15.47', '16.04', '15.47', '15.65', '15.68', '0', '0.16');
INSERT INTO `data_env_param` VALUES ('741', '20160101', '1', 'humidity', '59.64', '56.87', '59.64', '56.87', '58.16', '58.22', '0', '0.69');

-- ----------------------------
-- Table structure for data_param
-- ----------------------------
DROP TABLE IF EXISTS `data_param`;
CREATE TABLE `data_param` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` int(8) NOT NULL COMMENT '日期',
  `mid` int(10) NOT NULL COMMENT '博物馆ID',
  `param` varchar(20) DEFAULT NULL COMMENT '参数名称',
  `max` float(5,2) DEFAULT NULL COMMENT '最大值',
  `min` float(5,2) DEFAULT NULL COMMENT '最小值',
  `max2` float(5,2) DEFAULT NULL COMMENT '最大值（剔除异常值）',
  `min2` float(5,2) DEFAULT NULL COMMENT '最小值（剔除异常值）',
  `middle` float(5,2) DEFAULT NULL COMMENT '中位值',
  `average` float(5,2) DEFAULT NULL COMMENT '平均值（剔除异常值）',
  `count_abnormal` int(5) DEFAULT NULL COMMENT '异常值个数',
  `standard` float(5,2) DEFAULT NULL COMMENT '标准差',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=531 DEFAULT CHARSET=utf8 COMMENT='数据统计 - 博物馆参数综合统计';

-- ----------------------------
-- Records of data_param
-- ----------------------------
INSERT INTO `data_param` VALUES ('481', '20161031', '3', '7', '16.44', '13.09', '16.44', '13.09', '15.10', '14.90', '0', '0.89');
INSERT INTO `data_param` VALUES ('482', '20161031', '3', '1', '64.20', '60.77', '64.20', '60.77', '62.62', '62.51', '0', '0.84');
INSERT INTO `data_param` VALUES ('483', '20161031', '3', '2', '66.59', '49.74', '66.59', '49.74', '61.13', '59.91', '0', '5.11');
INSERT INTO `data_param` VALUES ('484', '20161031', '3', '3', '62.79', '60.77', '62.79', '60.94', '62.21', '62.27', '4', '0.46');
INSERT INTO `data_param` VALUES ('485', '20161031', '3', '4', '393.00', '0.00', '393.00', '0.00', '1.00', '55.70', '0', '126.96');
INSERT INTO `data_param` VALUES ('486', '20161031', '3', '6', '323.00', '0.00', '323.00', '0.00', '1.00', '51.39', '0', '112.89');
INSERT INTO `data_param` VALUES ('487', '20161031', '3', '8', '0.08', '0.02', '0.02', '0.02', '0.02', '0.02', '16', '0.01');
INSERT INTO `data_param` VALUES ('488', '20161031', '3', '9', '100.00', '0.00', '100.00', '0.00', '0.00', '32.63', '0', '46.89');
INSERT INTO `data_param` VALUES ('489', '20161031', '4', '7', '100.00', '0.00', '100.00', '0.00', '49.90', '49.94', '0', '28.88');
INSERT INTO `data_param` VALUES ('490', '20161031', '4', '1', '99.71', '0.06', '99.71', '0.06', '49.10', '48.58', '0', '28.62');
INSERT INTO `data_param` VALUES ('491', '20161031', '4', '2', '100.00', '0.06', '100.00', '0.06', '50.35', '49.91', '0', '29.12');
INSERT INTO `data_param` VALUES ('492', '20161031', '4', '3', '99.99', '0.06', '99.99', '0.06', '51.46', '50.53', '0', '28.90');
INSERT INTO `data_param` VALUES ('493', '20161031', '4', '4', '100.00', '0.00', '100.00', '0.00', '49.50', '50.23', '0', '29.51');
INSERT INTO `data_param` VALUES ('494', '20161031', '4', '8', '99.85', '0.03', '99.85', '0.03', '50.86', '50.85', '0', '28.11');
INSERT INTO `data_param` VALUES ('495', '20161031', '4', '9', '100.00', '0.00', '100.00', '0.00', '49.00', '49.03', '0', '29.11');
INSERT INTO `data_param` VALUES ('510', '20161031', '1', '7', '100.00', '0.00', '100.00', '0.00', '49.90', '49.94', '0', '28.88');
INSERT INTO `data_param` VALUES ('511', '20161031', '1', '1', '99.71', '0.23', '99.71', '0.23', '49.05', '48.73', '0', '28.71');
INSERT INTO `data_param` VALUES ('512', '20161031', '1', '2', '100.00', '0.06', '100.00', '0.06', '50.35', '49.91', '0', '29.12');
INSERT INTO `data_param` VALUES ('513', '20161031', '1', '3', '99.99', '0.06', '99.99', '0.06', '51.45', '50.53', '0', '28.90');
INSERT INTO `data_param` VALUES ('514', '20161031', '1', '4', '100.00', '0.00', '100.00', '0.00', '49.50', '50.23', '0', '29.51');
INSERT INTO `data_param` VALUES ('515', '20161031', '1', '8', '99.85', '0.03', '99.85', '0.03', '50.85', '50.85', '0', '28.11');
INSERT INTO `data_param` VALUES ('516', '20161031', '1', '9', '100.00', '0.00', '100.00', '0.00', '49.00', '49.03', '0', '29.11');
INSERT INTO `data_param` VALUES ('517', '20161031', '1', '5', '100.00', '0.00', '100.00', '0.00', '49.90', '49.94', '0', '28.88');
INSERT INTO `data_param` VALUES ('518', '20161031', '1', '6', '99.71', '0.23', '99.71', '0.23', '49.05', '48.73', '0', '28.71');
INSERT INTO `data_param` VALUES ('519', '20161031', '2', '2', '100.00', '0.06', '100.00', '0.06', '50.35', '49.91', '0', '29.12');
INSERT INTO `data_param` VALUES ('520', '20161031', '2', '3', '99.99', '0.06', '99.99', '0.06', '51.45', '50.53', '0', '28.90');
INSERT INTO `data_param` VALUES ('521', '20161031', '2', '4', '100.00', '0.00', '100.00', '0.00', '49.50', '50.23', '0', '29.51');
INSERT INTO `data_param` VALUES ('522', '20161031', '2', '8', '99.85', '0.03', '99.85', '0.03', '50.85', '50.85', '0', '28.11');
INSERT INTO `data_param` VALUES ('523', '20161031', '2', '9', '100.00', '0.00', '100.00', '0.00', '49.00', '49.03', '0', '29.11');
INSERT INTO `data_param` VALUES ('524', '20161031', '2', '7', '100.00', '0.00', '100.00', '0.00', '49.90', '49.94', '0', '28.88');
INSERT INTO `data_param` VALUES ('525', '20161031', '2', '5', '99.71', '0.23', '99.71', '0.23', '49.05', '48.73', '0', '28.71');
INSERT INTO `data_param` VALUES ('526', '20161031', '2', '1', '100.00', '0.06', '100.00', '0.06', '50.35', '49.91', '0', '29.12');

-- ----------------------------
-- Table structure for log
-- ----------------------------
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `time` int(11) DEFAULT NULL COMMENT '记录时间',
  `content` text COMMENT '记录内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='日志表';

-- ----------------------------
-- Records of log
-- ----------------------------

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text,
  `token` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `exec_time` varchar(10) DEFAULT NULL,
  `user` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='api访问日志';

-- ----------------------------
-- Records of logs
-- ----------------------------

-- ----------------------------
-- Table structure for museum
-- ----------------------------
DROP TABLE IF EXISTS `museum`;
CREATE TABLE `museum` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) NOT NULL COMMENT '博物馆名称',
  `db_type` varchar(20) DEFAULT NULL COMMENT '数据库类型：mysql或者mongo',
  `db_host` varchar(20) DEFAULT NULL COMMENT '数据库主机（包含端口）',
  `db_user` varchar(20) DEFAULT NULL COMMENT '数据库用户名',
  `db_pass` varchar(20) DEFAULT NULL COMMENT '数据库密码',
  `db_name` varchar(20) DEFAULT NULL COMMENT 'mysql：子系统数据库名称前缀；mongo：数据库名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='博物馆列表';

-- ----------------------------
-- Records of museum
-- ----------------------------
INSERT INTO `museum` VALUES ('1', '金沙博物馆', 'mysql', '192.168.8.11', 'root', 'mysql', 'jinsha');
INSERT INTO `museum` VALUES ('2', '洛阳博物馆', 'mysql', '192.168.8.11', 'root', 'mysql', 'luoyang');
INSERT INTO `museum` VALUES ('3', '雅安博物馆', 'mongo', '192.168.8.11', null, null, 'museum_ya');
INSERT INTO `museum` VALUES ('4', '智联博物馆', 'mysql', '192.168.8.11', 'root', 'mysql', 'museum');

-- ----------------------------
-- Table structure for permission
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(100) DEFAULT NULL COMMENT '权限名',
  `val` varchar(100) DEFAULT NULL COMMENT '权限值',
  `group` varchar(50) DEFAULT NULL COMMENT '权限分组',
  `app` varchar(50) DEFAULT NULL COMMENT '所属子系统',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of permission
-- ----------------------------
INSERT INTO `permission` VALUES ('1', '环境监测', '环境监测', '页面', 'base', '1');
INSERT INTO `permission` VALUES ('2', '系统管理', '系统管理', '页面', 'base', '2');
INSERT INTO `permission` VALUES ('3', '查询用户列表', '查询用户列表', '用户', 'base', '99');
INSERT INTO `permission` VALUES ('4', '获取单个用户基本信息', '获取单个用户基本信息', '用户', 'base', '90');
INSERT INTO `permission` VALUES ('5', '添加用户', '添加用户', '用户', 'base', null);
INSERT INTO `permission` VALUES ('6', '修改用户', '修改用户', '用户', 'base', null);
INSERT INTO `permission` VALUES ('7', '删除用户', '删除用户', '用户', 'base', null);
INSERT INTO `permission` VALUES ('8', '获取角色列表', '获取角色列表', '角色', 'base', null);

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `parent_id` int(11) DEFAULT NULL COMMENT '上级角色id',
  `name` varchar(50) DEFAULT NULL COMMENT '角色名',
  `permissions` text COMMENT '权限值列表,以","分割',
  `data_scope` text COMMENT '数据范围(环境编号列表)',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='角色表';

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', '0', '管理员', '环境监测,系统管理', null, '999');
INSERT INTO `role` VALUES ('2', '0', '领导', '环境监测,系统管理,查询用户列表', null, '888');
INSERT INTO `role` VALUES ('3', '0', 'test', '环境监测,系统管理', '1222', '777');
INSERT INTO `role` VALUES ('4', '3', 'test', '环境监测,系统管理', '1222', '777');

-- ----------------------------
-- Table structure for tokens
-- ----------------------------
DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `token` varchar(100) DEFAULT NULL COMMENT 'token字符串',
  `level` tinyint(2) DEFAULT NULL COMMENT '级别',
  `ip` varchar(20) DEFAULT NULL COMMENT 'ip',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `last_activity` int(11) DEFAULT NULL COMMENT '最后存活时间',
  `user` text COMMENT '绑定用户json,包含用户及权限信息',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COMMENT='token身份认证';

-- ----------------------------
-- Records of tokens
-- ----------------------------
INSERT INTO `tokens` VALUES ('21', 'base_RFNWbURmY24zZVJ1c1dQeEQ3UHF4L2RMK0dSSlNmUGFtQ3M0UGJNMnorWi8wcEJlcy9EOTBRPT0=', '1', '192.168.8.108', '1478743052', '1478743052', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('23', 'base_d05LVGlvMkR2NGFMNXZROTd5cUxxTElYV3BGTUNtQ1lpTlNSekMzWlVhTDlnQ3RwMHlPVTFnPT0=', '1', '192.168.8.108', '1478743161', '1478743161', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('29', 'base_UEpVekZhQzdSVjVYSXhNendwbG9jT2NXSlhWU0ljQkpYMEw3V3g1NldwNDR3cmNXdmx3Z0x3PT0=', '1', '192.168.8.108', '1478743379', '1478743679', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('30', 'base_em9CK3h2eVh5QmtOQ1lJS0dIeXhLZG1mZWs0Wi95M1B3SVFaN3pnRGxiWHl4R0pHcmVtMXJRPT0=', '1', '192.168.8.108', '1478743380', '1478743380', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('31', 'base_TmRuTzVhTHlINlVYaEdQQWZza0tVMkpuRzNiYmRFditBMkVsOUkxWk9EcFVVYnBVNS9xdDdBPT0=', '1', '192.168.8.108', '1478743590', '1478744252', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('32', 'base_YkVPRHN4cjg0VGh0TDVsT1lVNjVKUTZubDFjL1pKZUVJMEVLbm1RM1d0cjlnQ3RwMHlPVTFnPT0=', '1', '127.0.0.1', '1478743694', '1478756182', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"超级管理员\",\"real_name\":\"超级管理员\",\"data_scope\":\"\",\"ip\":\"127.0.0.1\"}');
INSERT INTO `tokens` VALUES ('33', 'base_eEhoU3RIY3NPNEpwNGhxNTZmRnJKSFRZeXdHMTREWHFWa0RFZkM0UXJCMDV3Z1pYS3hiQmJ3PT0=', '1', '192.168.8.108', '1478743711', '1478743714', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('35', 'base_bXBYbFV4M1ZuZFd3eUcxY21qUjI4RG1vZmFqQjB5ZVdoNkxjck91MFUxWG9wcmVEWi9XNDlRPT0=', '1', '192.168.8.108', '1478743754', '1478743849', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('37', 'base_YlFaTlpjMjN0a2ozUWU1bU01VlZ0WHhhQndKU1hHQmNYUUhSdUhweTY2bm9wcmVEWi9XNDlRPT0=', '1', '192.168.8.108', '1478743861', '1478764023', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"real_name\":\"\\u8d85\\u7ea7\\u7ba1\\u7406\\u5458\",\"data_scope\":\"\",\"ip\":\"192.168.8.108\"}');
INSERT INTO `tokens` VALUES ('40', 'base_bDRQOGlsekE2d1R6VEhuOFdsSGNhakNhS0dqNlJqeGVXNXNxWFRBY0s3UXc4YWxQL2ZqUGd3PT0=', '1', '127.0.0.1', '1478745689', '1478749050', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"超级管理员\",\"real_name\":\"超级管理员\",\"data_scope\":\"\",\"ip\":\"127.0.0.1\"}');
INSERT INTO `tokens` VALUES ('42', 'base_b09JbkFWQ3V5RHY0Z0szZG4vUllSVDZKZEh5Wnl4N1VTSE9tQ1BvdkR2RXZLQ2FPS0NnblFBPT0=', '1', '127.0.0.1', '1478756756', '1478757888', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"超级管理员\",\"real_name\":\"超级管理员\",\"data_scope\":\"\",\"ip\":\"127.0.0.1\"}');
INSERT INTO `tokens` VALUES ('44', 'base_WWZsN1Y3cTRzbjhHUlFCd24weDk3TjNRVCtORUxkYitpN3FoZERwVW5DaWpXRnFOajVCcTh3PT0=', '1', '127.0.0.1', '1478759100', '1478760769', '{\"id\":\"1\",\"username\":\"admin\",\"level\":\"超级管理员\",\"real_name\":\"超级管理员\",\"data_scope\":\"\",\"ip\":\"127.0.0.1\"}');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `password` varchar(50) DEFAULT NULL COMMENT '密码',
  `role_ids` text COMMENT '角色id列表',
  `status` varchar(20) DEFAULT NULL COMMENT '状态',
  `level` varchar(50) DEFAULT NULL COMMENT '用户级别(领导、研究者、工作人员)',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `tel` varchar(50) DEFAULT NULL COMMENT '电话',
  `department` varchar(50) DEFAULT NULL COMMENT '部门',
  `position` varchar(50) DEFAULT NULL COMMENT '职位',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `favorite` text COMMENT '偏好设置json',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin', 'f6fdffe48c908deb0f4c3bd36c032e72', '1,2', '正常', '超级管理员', '超级管理员', '18716435779', null, null, '1', '{\"bg\":[\"ds\",\"dss\",\"dds\",\"rr\"],\"music\":\"\\u6d6e\\u5938\",\"search\":{\"history\":[\"fd\",\"type\",\"sd\"]}}');
INSERT INTO `user` VALUES ('2', 'test', '05a671c66aefea124cc08b76ea6d30bb', '1', '锁定', '测试', '测试', '15555555', null, null, null, null);
INSERT INTO `user` VALUES ('3', 'admins', '1df07bcb21e91dd29ac01c91680ea349', '1,2', '正常', '工作人员', '刘丹', null, null, null, '1', null);

-- ----------------------------
-- Table structure for user_behavior
-- ----------------------------
DROP TABLE IF EXISTS `user_behavior`;
CREATE TABLE `user_behavior` (
  `uid` int(10) NOT NULL COMMENT '用户ID',
  `webkey` varchar(100) NOT NULL COMMENT '页面关键字',
  `behavior` text COMMENT '行为记录（json）',
  PRIMARY KEY (`uid`,`webkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户行为记录表';

-- ----------------------------
-- Records of user_behavior
-- ----------------------------
INSERT INTO `user_behavior` VALUES ('1', 'area_search', '{\"time\":\"yesterday\",\"type\":\"\\u5c55\\u67dc\",\"param\":[\"temp\",\"voc\"]}');
INSERT INTO `user_behavior` VALUES ('1', 'general_search', '{\"time\":\"yesterday\",\"type\":\"\\u5c55\\u67dc\",\"param\":[\"temp\",\"humdity\",\"light\"]}');

-- ----------------------------
-- Table structure for user_ip
-- ----------------------------
DROP TABLE IF EXISTS `user_ip`;
CREATE TABLE `user_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` int(11) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL COMMENT '登陆城市',
  `ip` varchar(50) DEFAULT NULL COMMENT '登录ip',
  `pass` varchar(50) DEFAULT NULL COMMENT '是否通过验证（是/否）',
  `code` varchar(10) DEFAULT NULL COMMENT '短信验证码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户登陆ip表';

-- ----------------------------
-- Records of user_ip
-- ----------------------------

-- ----------------------------
-- Table structure for user_login
-- ----------------------------
DROP TABLE IF EXISTS `user_login`;
CREATE TABLE `user_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `login_time` int(11) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL COMMENT '登陆城市',
  `ip` varchar(50) DEFAULT NULL COMMENT '登录ip',
  `pass` varchar(50) DEFAULT NULL COMMENT '是否通过验证（是/否）',
  `code` varchar(10) DEFAULT NULL COMMENT '短信验证码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户登录记录';

-- ----------------------------
-- Records of user_login
-- ----------------------------
