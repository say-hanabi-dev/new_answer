<?php
define('CURSCRIPT', 'review');
define('PASSSCORE', 2);         //通过分数
define('REJECTSCORE', -3);      //不通过分数
require '../source/class/class_core.php';
C::app()->init();
if (!in_array($_G["groupid"], array(1, 2, 3, 196, 197))) die("您无权访问此页");

?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<title>花火考场 - 阅卷中心</title>
<img style="align:left" width="270px" src="/static/image/common/sayhanabi_header.png"> <b>
    <font size="6">阅卷中心</font> | <a href="index.php">花火考场</a> | <a href="show.php">作文鉴赏</a>
</b> | 欢迎，<?php echo $_G["username"]; ?>
<hr />
<div style="text-align:center">
    <?php
    if ($_GET['action'] == 'add' || $_GET['action'] == 'min') {
        $score = 0;
        if ($_GET['action'] == 'add') {
            $score = 1;
        } else if ($_GET['action'] == 'min') {
            $score = -1;
        }
        try {
            if(DB::fetch_first("SELECT `status` FROM `pre_hanabi_answer` WHERE `id` = %d", array($_GET['aid']))['status'] == 0){
                DB::query("INSERT INTO `pre_hanabi_answer_vote` (`aid`, `uid`, `username`, `score`) VALUES (%d, %d, %s, %d) ON DUPLICATE KEY UPDATE score = %d", array($_GET['aid'], $_G['uid'], $_G['username'], $score, $score));
                echo (($score == 1) ? '加分' : '减分') . '成功';
                $score_sum = DB::fetch_first("SELECT SUM(`score`) AS score_sum FROM pre_hanabi_answer_vote where `aid` = %d", array($_GET['aid']))["score_sum"];
                if($score_sum >= constant("PASSSCORE")){
                    // 分数达标
                    DB::query("UPDATE pre_hanabi_answer SET `status` = 1 where `id` = %d", array($_GET['aid']));
                }else if($score_sum <= constant("REJECTSCORE")){
                    // 分数过低
                    DB::query("UPDATE pre_hanabi_answer SET `status` = 2 where `id` = %d", array($_GET['aid']));
                }
            }
        } catch (Exception $e) {
            echo '操作失败';
        }
    }
    ?>
    <table border="1">
        <tr>
            <th>回答内容</th>
            <th>回答者IP</th>
            <th>回答时间</th>
            <th>操作</th>
        </tr>
        <?php
        foreach (DB::fetch_all("SELECT `id`,`content`,`answer_time`,`ip` FROM `pre_hanabi_answer` WHERE `id` NOT IN (SELECT `aid` FROM `pre_hanabi_answer_vote` WHERE `uid` = %d) and `status` = 0", array($_G['uid'])) as $answer) {
            echo '<tr><td width="75%">' . str_replace(PHP_EOL, '<br />', $answer["content"]) . "</td><td>" . $answer["ip"] . "</td><td>" . $answer["answer_time"] . '</td><td><a href="?aid=' . $answer['id'] . '&action=add">加分</a><br /><a href="?aid=' . $answer['id'] . '&action=min">减分</a></td></tr>';
        }
        ?>
    </table>
</div>
<hr>
<center>©2022 <a href="https://mayx.eu.org">Mayx</a> & <a href="https://www.sayhuahuo.com/">SayHanabi</a></center>
