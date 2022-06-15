<?php
session_start();
header('Content-Type:text/html;charset=utf-8');
function add($content){
  try{
  $token = $_SESSION['token'];
  $pdo = createpdo();
  $sql = 'set names utf8;INSERT INTO `pre_hanabi_answer`(`content`, `ip`, `token`) VALUES (?, ?, ?)';
  $insert = $pdo->prepare($sql);
  $insert->bindParam(1, $content);
  $insert->bindParam(2, $_SERVER['HTTP_CF_CONNECTING_IP']);
  $insert->bindParam(3, $token);
  
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
<input type="submit" />
<?php 
} else {
    switch($_GET["action"]){
        case "commit":
            if($_SERVER['REQUEST_METHOD'] == "POST"){
                if(!isset($_SESSION["token"]) || $_SESSION["token"] != $_POST["token"]){
                    die("非法请求！");
                }
                $answer = htmlspecialchars($_POST['answer']);
                if(strlen($answer) > 10 && strlen($answer) < 262140) {
                    $pdo = createpdo();
                    $sql = "SELECT count(id) count from `pre_hanabi_answer` where ip = '".addslashes($_SERVER['HTTP_CF_CONNECTING_IP'])."'";
                    if($pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['count'] < 10){
                        echo "提交成功！,请过一段时间使用“".add($answer)."”查看成绩与邀请码<br />注意：请妥善保存此Token。";
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
                    $sql = "SELECT `status`, `invitecode` from `pre_hanabi_answer` where token = '".$token[0]."'";
                    $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
                    if($result){
                        echo "当前状态：".array(0=>"投票中", 1=>"已通过", 2=>"未通过")[$result["status"]];
                        if($result["status"] == 1) echo "，邀请码为：".$result["invitecode"];
                        else if($result["status"] == 0) echo "，请稍候再进行查询";
                        else if($result["status"] == 2) echo "，请尝试重新回答";
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
