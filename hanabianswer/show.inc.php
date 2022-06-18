<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if (!in_array($_G["groupid"], array(1, 2, 3, 196, 197))) die("您无权访问此页");
$page = 0;
if (isset($_GET["page"])) $page = (int)$_GET["page"];
?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<title>花火考场 - 作文鉴赏</title>
<img style="align:left" width="270px" src="/static/image/common/sayhanabi_header.png"> <b>
    <font size="6">作文鉴赏</font> | <a href="/hanabianswer-review.html">阅卷中心</a>
</b> | 欢迎，<?php echo $_G["username"]; ?>
<hr />
<div style="text-align:center">
    <table border="1">
        <tr>
            <th>回答内容</th>
            <th>回答者IP</th>
            <th>回答时间</th>
            <th>回答者UID</th>
        </tr>
        <?php
        foreach (DB::fetch_all("SELECT
	pre_hanabi_answer.content, 
	pre_hanabi_answer.ip, 
	pre_common_invite.fuid, 
	pre_hanabi_answer.answer_time
FROM
	pre_hanabi_answer
	LEFT JOIN
	pre_common_invite
	ON 
		pre_hanabi_answer.invitecode = pre_common_invite.`code`
WHERE
	pre_hanabi_answer.`status` = 1 ORDER BY pre_hanabi_answer.`id` DESC LIMIT %d,%d", array($page, $page + 10)) as $answer) {
            echo '<tr><td width="80%">' . str_replace(PHP_EOL, '<br />', $answer["content"]) . "</td><td>" . $answer["ip"] . "</td><td>" . $answer["answer_time"] . '</td><td>' . $answer["fuid"] . '</td></tr>';
        }
        ?>
    </table>
    <?php if($page > 0){?><a href="?page=<?php echo $page - 10; ?>">上一页</a> | <?php }?><a href="?page=<?php echo $page + 10; ?>">下一页</a>
</div>
<hr />
<center>©2022 <a href="https://mayx.eu.org">Mayx</a> & <a href="https://www.sayhuahuo.com/">SayHanabi</a></center>
