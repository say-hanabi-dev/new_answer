CREATE TABLE `pre_hanabi_answer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '答题卡',
  `mail` varchar(255) DEFAULT NULL COMMENT '用户邮箱',
  `ip` varchar(255) NOT NULL COMMENT '答题者IP',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0=投票中，1=已通过，2=已删除）',
  `mark` tinyint(1) unsigned DEFAULT NULL COMMENT '标记',
  `token` char(32) NOT NULL COMMENT '查询令牌',
  `invitecode` char(20) DEFAULT NULL COMMENT '邀请码',
  `answer_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '答题时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `pre_hanabi_answer_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(11) unsigned NOT NULL COMMENT '答题ID',
  `uid` int(11) unsigned NOT NULL COMMENT '投票人ID',
  `username` char(20) NOT NULL COMMENT '投票人用户名',
  `score` tinyint(4) NOT NULL COMMENT '投票分值（±1）',
  `vote_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '投票时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `aid_uid` (`aid`,`uid`),
  CONSTRAINT `pre_hanabi_answer_vote_ibfk_1` FOREIGN KEY (`aid`) REFERENCES `pre_hanabi_answer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;