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
		echo "データベースに接続できませんでした。";
		exit();
	}

	if (!isset($_SESSION['userID'])){
		//セッションにユーザIDがある＝ログイン済
		//indexに遷移する
		header('Location: login.php');
		exit();
	}

	$stmt = $db->prepare("SELECT flower0, flower1, flower2 FROM collect WHERE user_id = :user_id");
	$stmt->bindParam(":user_id", $_SESSION['userID'], pdo::PARAM_STR);
	if(!$stmt->execute()){
		echo "データが不正です。";
		exit();
	}
	$row = $stmt->fetch(pdo::FETCH_ASSOC);
	if(!$row){
		echo "データが不正です。";
		exit();
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Style-Type" content="text/css">
  <meta http-equiv="Content-Script-Type" content="text/javascript">
  <title>はなコレギャラリー</title>
  <style type="text/css">
  <!--
  	@import url(./css/collection.css);
  -->
  </style>
  <script type="text/javascript" src="./js/master.js"></script>
</head>
<body>
<div class="header_space">
	<ul id="flip" class="dropmenu">
  		<li id="click"><a>メニュー</a>
   			<ul>
	   			<li><a href="javascript:history.back();">前の画面に戻る</a></li>
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

<div class="side_bar">
<?php
	$flowername = array("warm", "cool", "funny");
	for($i=0; $i<3; $i++){
		if($row['flower' . $i] != "0000-00-00 00:00:00"){
?>
			<div class="sidebar_contents" onclick='schrollToTopOfFlower("<?php echo $flowername[$i];?>")'>
				<p><?php echo $flowername[$i];?></p>
			</div>
<?php
		}
	}
?>
</div>
<!-- 種 -->
<div class="flower_field">
<?php
	for($i=0; $i<3; $i++){
		if($row['flower' . $i] != "0000-00-00 00:00:00"){
			$name = "flower" . $i;
?>
		<table class="flower_table" id="<?php echo $flowername[$i];?>" cellspacing="30">
			<thead>
				<tr>
				<th colspan="4"><?php echo $flowername[$i];?></th>
				</tr>
			</thead>
<?php
			$daycheck = floor((time() - strtotime($row[$name])) / 86400) + 1;

			$stmt = $db->prepare("SELECT url FROM flower WHERE flower_id = :flower_id AND day > 0 AND day <= :day ORDER BY day ASC");
			$stmt->bindParam(":flower_id", $i, PDO::PARAM_STR);
			$stmt->bindParam(":day", $daycheck, PDO::PARAM_STR);
			$stmt->execute();

			$rowimage = "a";
			while ($rowimage){
				echo "<tr>";
				for($trpoint = 0; $trpoint<4; $trpoint++){
	 				$rowimage = $stmt->fetch(PDO::FETCH_ASSOC);
					if($rowimage){
						echo '<td class="flower_image">';
						echo '<img src="image/flower/' . $rowimage["url"] . '"  class="fin_flower">';
						echo '</td>';
					} else {
						echo '<td class="flower_image" style="visibility: hidden;"></td>';
					}
	 			}
				echo "</tr>";
			}?>
		</table>
		<hr>
<?php
		}
	}
?>
</div>
</body>
</html>