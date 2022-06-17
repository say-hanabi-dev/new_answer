<?php
session_start();
header('Content-Type:text/html;charset=utf-8');
function add($content, $pmail){
  try{
  $token = $_SESSION['token'];
  $pdo = createpdo();
  $sql = 'set names utf8;INSERT INTO `pre_hanabi_answer`(`content`, `mail`, `ip`, `token`) VALUES (?, ?, ?, ?)';
  $insert = $pdo->prepare($sql);
  $insert->bindParam(1, $content);
  $insert->bindParam(2, $pmail);
  $insert->bindParam(3, $_SERVER['HTTP_CF_CONNECTING_IP']);
  $insert->bindParam(4, $token);
  
  if(!$insert->execute()){
      $token = "";
  }
  }catch(PDOException $e){
    echo "Error: " . $e->getMessage();
  }
  return $token;
}

function createpdo() {
  require '../config/config_global.php';

  $dbms='mysql';     //数据库类型
  $host=$_config['db']['1']['dbhost']; //数据库主机名
  $dbName=$_config['db']['1']['dbname'];    //使用的数据库
  $user=$_config['db']['1']['dbuser'];      //数据库连接用户名
  $pass=$_config['db']['1']['dbpw'];          //对应的密码
  $dsn="$dbms:host=$host;dbname=$dbName";
  $db = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true));
  return $db;
}

function invite($id) {
  $inviteuid = '39900';
  $inviteip = $_SERVER['HTTP_CF_CONNECTING_IP'];
  $invitecode = generate_invite_code();
  $validperid = 1 * 60 * 60;
  $currtime = time();
  $expiretime = $currtime + $validperid;

  $pdo = createpdo();

  $sql = 'INSERT INTO `pre_common_invite`(`uid`, `code`, `inviteip`, `dateline`, `endtime`) VALUES (?, ?, ?, ?, ?)';
  $insert = $pdo->prepare($sql);
  $insert->bindParam(1, $inviteuid);
  $insert->bindParam(2, $invitecode);
  $insert->bindParam(3, $inviteip);
  $insert->bindParam(4, $currtime);
  $insert->bindParam(5, $expiretime);
  $insert->execute();
  
  $sql = 'UPDATE `pre_hanabi_answer` SET `invitecode` = ? WHERE `id` = ?';
  $update = $pdo->prepare($sql);
  $update->bindParam(1, $invitecode);
  $update->bindParam(2, $id);
  $update->execute();

  $pdo = null;

  return $invitecode;
  
}

function generate_invite_code() {
  $code_length = 8;
  $characters = [
    'a', 'b', 'c', 'd', 'e', 'f', 'j', 'h', 'i', 'j', 'k',
    'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
    'y', 'z', '2', '3', '4', '5', '6', '7', '8', '9'
  ];
  $code = '';
  for ($i = 0; $i < $code_length; $i++) {
    $index = mt_rand(0, sizeof($characters) - 1);
    $code .= $characters[$index];
  }
  return $code;
}

?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0" />

<title>花火考场</title>
<img style="align:left" width="270px" src="/static/image/common/sayhanabi_header.png"> 
<b><a href="?"><font size="6">花火考场</font></a> | <a href="?action=query">成绩查询</a> | <a href="review.php">阅卷中心</a></b>
<hr /><div style="text-align:center">
<?php if(!isset($_GET["action"])){ 
$_SESSION["token"] = md5(uniqid(mt_rand(), true));
?>
<h3>请在以下文本框中谈谈你游玩过的一部Galgame，内容可以是游戏剧情、原画、配音以及你游玩的感想等：</h3>（请以自己的话来谈谈感想，勿抄袭、洗稿、重复提交）
<form action="?action=commit" method="post">
<input type="hidden" name="token" value="<?php echo $_SESSION["token"]; ?>">
<p><textarea rows="20" cols="100" name="answer"></textarea></p>
<p>请输入提醒邮箱📫（可选）：<input type="text" name="mail" /></p>
<input type="submit" />
<?php 
} else {
    switch($_GET["action"]){
        case "commit":
            if($_SERVER['REQUEST_METHOD'] == "POST"){
                if(!isset($_SESSION["token"]) || $_SESSION["token"] != $_POST["token"]) die("非法请求！");
                if(!isset($_POST['answer'])) die("请求错误");
                $answer = htmlspecialchars($_POST['answer']);
                if(strlen($answer) > 10 && strlen($answer) < 262140) {
                    $pmail = NULL;
                    if(isset($_POST['mail'])){
                        preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $_POST['mail'], $usermail);
                        if($usermail){
                            $pmail = $usermail[0];
                        }
                    }
                    $pdo = createpdo();
                    $sql = "SELECT count(id) count from `pre_hanabi_answer` where ip = '".addslashes($_SERVER['HTTP_CF_CONNECTING_IP'])."'";
                    if($pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['count'] < 10){
                        echo "提交成功！请过一段时间使用“".add($answer, $pmail)."”查看成绩与邀请码<br />注意：请妥善保存此Token。";
                    }else{
                        echo "该IP提交超出次数限制";
                    }
                } else {
                    echo "提交内容超出限制";
                }
            }
            break;
        case "query":
        ?>
        请输入查询Token：
<form action="?action=doquery" method="post">
<p><input type="text" name="token" /></p>
<input type="submit" />
        <?php
        break;
        case "doquery":
            $pdo = createpdo();
            if(isset($_POST['token'])){
                preg_match('/[a-f0-9]{32}/i', $_POST['token'], $token);
                if($token){
                    $sql = "SELECT `id`, `status`, `invitecode` from `pre_hanabi_answer` where token = '".$token[0]."'";
                    $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
                    if($result){
                        echo "当前状态：".array(0=>"投票中，请稍候再进行查询", 1=>"已通过", 2=>"未通过，请尝试重新回答")[$result["status"]];
                        if($result["status"] == 1) {
                            if($result["invitecode"]){
                                echo "，邀请码为：".$result["invitecode"]."，当前Token已查询。";
                            }else{
                                echo "，邀请码为：".invite($result["id"])."，请尽快使用。";
                            }
                        }
                    }else{
                        echo "没有找到该Token";
                    }
                }else{
                    echo "格式错误，请检查";
                }
            }
        break;
    }
} ?>

</form></div>
<hr><center>©2022 <a href="https://mayx.eu.org">Mayx</a> & <a href="https://nekomoyi.com">NekoMoYi</a> & <a href="https://www.sayhuahuo.com/">SayHanabi</a></center>
