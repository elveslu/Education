-- 添加学员资料表
-- 添加院校表
-- 添加专业类目表
-- 添加专业表
-- 添加财务表
-- 添加成考流程表
-- 添加学员流程记录表



-- 添加学员资料表
ALTER TABLE `cmf_user` ADD `school` int(10) NOT NULL DEFAULT '0' COMMENT '所选学校id';
ALTER TABLE `cmf_user` ADD `major` int(10) NOT NULL DEFAULT '0' COMMENT '所选专业id';
ALTER TABLE `cmf_user` ADD `degree` int(10) NOT NULL DEFAULT '0' COMMENT '是否需要学位  0：不需要；1：需要';
ALTER TABLE `cmf_user` ADD `Recommender` int(10) NOT NULL DEFAULT '0' COMMENT '推荐人';
ALTER TABLE `cmf_user` ADD `age` int(10) NOT NULL DEFAULT '0' COMMENT '年龄';
ALTER TABLE `cmf_user` ADD `location` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '参考地点';
ALTER TABLE `cmf_user` ADD `highest_education` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '目前最高学历';
ALTER TABLE `cmf_user` ADD `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通信地址';
ALTER TABLE `cmf_user` ADD `nation` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '民族';
ALTER TABLE `cmf_user` ADD `work_unit` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '工作单位';
ALTER TABLE `cmf_user` ADD `id_card` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证号';
ALTER TABLE `cmf_user` ADD `political_outlook` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '政治面貌';
ALTER TABLE `cmf_user` ADD `authentication` int(10) NOT NULL DEFAULT '0' COMMENT '是否学信网认证 0：认证；1：未认证';
ALTER TABLE `cmf_user` ADD `current_coordinates` int(10) NOT NULL DEFAULT '1' COMMENT '当前流程';
ALTER TABLE `cmf_user` ADD `current_coordinates_status` int(10) NOT NULL DEFAULT '0' COMMENT '当前流程状态：  0：待处理；1：处理中未完成；2：处理完成';
ALTER TABLE `cmf_user` ADD `memo` longtext COMMENT '备注';


-- 添加院校表
DROP TABLE IF EXISTS `cmf_university`;
CREATE TABLE `cmf_university` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '院校名称',
  `qizhong_time` int(10) NOT NULL DEFAULT '0' COMMENT '期中考试',
  `qimo_time` int(10) NOT NULL DEFAULT '0' COMMENT '期末考试',
  `dissertation_time` int(10) NOT NULL DEFAULT '0' COMMENT '毕业论文时间',
  `finish_time` int(10) NOT NULL DEFAULT '0' COMMENT '领证时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `memo` longtext COMMENT '备注',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '状态：1：可用；0：不可用',
  `year` varchar(200) NOT NULL DEFAULT '' COMMENT '年度',
  `type` int(10) NOT NULL DEFAULT '0' COMMENT '类型：0：本科；1：大专',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='院校表';

-- 添加专业类目表
DROP TABLE IF EXISTS `cmf_professional_category`;
CREATE TABLE `cmf_professional_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类目名称',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专业类目表';


-- 添加专业表
DROP TABLE IF EXISTS `cmf_professional`;
CREATE TABLE `cmf_professional` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `university_id` int(10) NOT NULL DEFAULT '0' COMMENT '院校id',
  `category_id` int(10) NOT NULL DEFAULT '0' COMMENT '类目id',
  `price` int(10) NOT NULL DEFAULT '0' COMMENT '标准售价',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '专业名称',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `memo` longtext COMMENT '备注',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '状态：1：可用；0：不可用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专业表';

-- 添加成考流程表
DROP TABLE IF EXISTS `cmf_examination_process`;
CREATE TABLE `cmf_examination_process` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '顺序',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '入学状态   0：入学前；1：入学后',
  `category` varchar(200) NOT NULL DEFAULT '0' COMMENT '特殊类目需要，默认为0,0位所有类目需要',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '步骤名称',
  `memo` longtext COMMENT '备注',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `time` int(10) NOT NULL DEFAULT '0' COMMENT '流程时间',
  `is_accurate` int(10) NOT NULL DEFAULT '0' COMMENT '是否精确： 0：不精确：1：精确',
  `is_school` int(10) NOT NULL DEFAULT '0' COMMENT '是否关联院校： 0：不关联：1：关联',
  `is_show` int(10) NOT NULL DEFAULT '0' COMMENT '是否前台展示： 0：不展示：1：展示',
  `before_memo` longtext COMMENT '前台展示内容',
  `after_memo` longtext COMMENT '后台展示内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='成考流程表';


-- 添加学员流程记录表
DROP TABLE IF EXISTS `cmf_process_log`;
CREATE TABLE `cmf_process_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '学员',
  `process_id` int(10) NOT NULL DEFAULT '0' COMMENT '流程id',
  `status` int(10) NOT NULL DEFAULT '1' COMMENT '处理状态   0：待处理；1：处理中；2：处理完成',
  `memo` longtext COMMENT '备注',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学员流程记录表';


-- 添加财务表
DROP TABLE IF EXISTS `cmf_finance`;
CREATE TABLE `cmf_finance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `price` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '标准售价',
  `real_price` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '实际售价',
  `base_price` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '成本价',
  `amount` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '实收金额',
  `collection_amount` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '待收金额',
  `payment_slip` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '支出费用',
  `user_id` varchar(100) DEFAULT NULL COMMENT '会员id',
  `memo` longtext COMMENT '单据备注',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学员单据表';

-- 添加财务明细表
DROP TABLE IF EXISTS `cmf_finance_bound`;
CREATE TABLE `cmf_finance_bound` (
  `bound_id` varchar(20) NOT NULL DEFAULT '' COMMENT '单据id',
  `money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '单据金额',
  `amount` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '最终金额',
  `finance_id` varchar(100) DEFAULT NULL COMMENT '财务id',
  `type` varchar(100) NOT NULL DEFAULT 'inBound' COMMENT '单据类型-inBound,outBound',
  `memo` longtext COMMENT '单据备注',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`bound_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='财务明细表';


ALTER TABLE `cmf_examination_process` ADD `use_status` int(10) NOT NULL DEFAULT '1' COMMENT ' 0：弃用；1：使用中';
ALTER TABLE `cmf_professional` ADD `base_price` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '成本价';