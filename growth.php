<?php
session_start();

//データベースにつなぐ
include 'connect.php';

// Connectをインスタンス
$connect = new DBConnect();

// dbConnect()を呼び、mySQLの位置(?)を$dbに受け取る
// この時$dbに入っていれば正常
if(!$db = $connect->dbConnectName("denki")){
	//	header('Location: login.php');
	//	exit();
	$err .= "データベースに接続できませんでした。";
		exit();
}



if (!isset($_SESSION['userID'])){
	//セッションにユーザIDがある＝ログイン済
	//indexに遷移する
	header('Location: login.php');
	exit();
}


$stmt = $db->prepare("SELECT create_date, seed_create_date FROM account WHERE user_id = :user_id");
$stmt->bindparam(':user_id', $_SESSION['userID'], PDO::PARAM_STR);
if($stmt->execute()){
	$row = $stmt->fetch();
	$timestamp = floor((time() - strtotime($row['seed_create_date'])) / 86400) + 1;
	// 全体日数
	$timestampAll = floor((time() - strtotime($row['create_date'])) / 86400) + 1;
	//echo $timestamp;



	 $stmt = $db->prepare("SELECT url,isfinal FROM flower WHERE flower_id = :flower_id AND day <= :timestamp ORDER BY day DESC LIMIT 1");

	 $stmt->bindparam(':flower_id', $_SESSION['flowerID'], PDO::PARAM_STR);
	 $stmt->bindparam(':timestamp', $timestamp, PDO::PARAM_STR);


	 $stmt->execute();

	 $row = $stmt->fetch();
	//echo var_dump($_SESSION);

	if ($row['isfinal']== "1"){
	$_SESSION['url'] = $row['url'];
	header('Location: finish.php');
	exit();
	}
}



//ログアウトボタン押下時
	if (isset($_POST['Logout'])){
		session_destroy();
		header('Location: login.php');
		exit();
	}

?>

<!DOCTYPE HTML PUBLIC" -//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" content="text/css">

<TITLE>はなコレ/<?php echo $timestampAll; ?>日目</TITLE>
	<style type="text/css">
 	<!--
  		@import url(./css/growth.css);
 	 -->
 	 @import url(https://fonts.googleapis.com/css?family=Poiret+One);
  	</style>
</HEAD>
<BODY>
	<div class="header_space">
			<ul id="flip" class="dropmenu">
  				<li id="click"><a>メニュー</a>
   					<ul>
	   					<li><a href="collection.php">今までの記録</a></li>
      					<li><a href="mailform.php">お問い合わせ</a></li>
      					<li><a href="logout.php">ログアウト</a></li>
    				</ul>
  				</li>
      			<li><a>　</a></li>
      			<li><a>　</a></li>
      			<li><a>　</a></li>
  				<li><a><?php echo htmlspecialchars($_SESSION['userID'],ENT_QUOTES,'UTF-8');?></a></li>
			</ul>
	</div>
	<div class="box">
	<div class="space"></div>


		<div class="day">
			<p><font size="30" color="#333333">Day <?php  echo $timestampAll; ?>&nbsp;&nbsp;</font></p>
		</div>
		<div class="flower_image">
		 	<img src="image/flower/<?php echo $row['url']; ?>" class="fin_flower">
		 		 	</div>
	</div>
</BODY>
</HTML>