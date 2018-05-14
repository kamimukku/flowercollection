<?php

	session_start();

	// データベースに接続
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

	// ユーザーID確認
	if(isset($_SESSION['userID'])){
//		unset($_SESSION['userID']);
		header('Location: growth.php');
		exit();
	}

	// ログイン押下
	if(isset($_POST['Login'])){

		$err = "";
		if(!$_POST['userID']){
			$err .= "「ID」";
		}
		if(!$_POST['password']){
			$err .= "「パスワード」";
		}

		if(!$err){
			// 受け取る
			$user_id = $_POST['userID'];
			$user_pass = $_POST['password'];

			// idとpassが一致した場合出力するSQLのSELECT文
			$stmt = $db->prepare("SELECT * FROM account WHERE user_id = :user_id and password = :user_pass");

			// 割り当て
			$stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$stmt->bindParam(':user_pass', $user_pass, PDO::PARAM_STR);

			// 実行
			$stmt->execute();

			// user_idとuser_passが一致したら$stmt->fetch()に一致した行が入る。
			// 一致してない場合空白になる。
			if($row = $stmt->fetch()){
				// ログイン成功
				$_SESSION['userID'] = $_POST['userID'];
				$_SESSION['flowerID'] = $row['flower_id'];
				header('Location: growth.php');
				exit();
//			echo "<pre>";
//			var_dump($row);
//			echo  "</pre>";
			}else{
				// ログイン失敗
				$err .= "「ID」「パスワード」が存在しません。";
// 				header('Location: login.php');
// 				exit();
			}
		}else{
			$err .= "が入力されていません。";
		}
	}


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Style-Type" content="text/css">
  <meta http-equiv="Content-Script-Type" content="text/javascript">
  <link href='https://fonts.googleapis.com/css?family=Dancing+Script' rel='stylesheet' type='text/css'>
  <title>はなコレ</title>
  <style type="text/css">
  <!--
  	@import url(./css/login.css);
  -->
  </style>
</head>
<body>

<div id="form-main">
	<div id="form-div">
		<div>
			<p id ="hanakore">はなコレ</p>
			<p id="hana_collection">flower collection</p>

		<!-- エラーメッセージ -->
		<div class="error">
			<?php echo  nl2br($err); ?>
		</div>
		<!-- ログインフォーム -->
		<form action="login.php" method="post" class="form" id="form1">
			<p class="name">
		        <input name="userID" type="text" class="validate[required,custom[onlyLetter],length[0,100]] feedback-input" placeholder="Name" id="name" />
	 		</p>
			<p class="password">
		        <input type="password" name="password" class="validate[required,custom[email]] feedback-input" id="password" placeholder="Password" />
	        </p>
	        <div class="submit">
		        <input type="submit" name="Login" value="Login" id="button-blue"/>
		        <div class="ease"></div>
	        </div>
	    </form>
	    </div>
	    <!-- 登録ボタン -->
		<div class="submit">
			<button type="button"onclick="location.href='entry.php'" id="button-red" >Register</button>
			<div class="ease"></div>
    	</div>

	    <!-- Import
    	<br>
		<div class="submit">
			<button type="button"onclick="location.href='importdb.php'" id="button-blue" >Import</button>
			<div class="ease"></div>
    	</div>
    	-->

    </div>
</div>
</body>
</html>
