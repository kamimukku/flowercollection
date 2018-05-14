<?php
	session_start();

	if (isset($_SESSION["userID"])) {
  		$errorMessage = "ログアウトしました。";

	}else {
  		header('Location: login.php');
	}

	// セッション変数のクリア
	$_SESSION = array();

	// クッキーの破棄
	if (isset($_COOKIE[session_name()])) {
    	setcookie(session_name(), '', time()-42000, '/');
	}

	// セッションの破棄
	session_destroy();

	header("refresh:5;url=login.php")

?>


<!DOCTYPE HTML PUBLIC" -//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" content="text/css">

	<title><?php echo $errorMessage;?></title>
		<style type="text/css">
 		<!--
  			@import url(./css/logout.css);
 	 	-->
  		</style>
</head>

<body class="logoutall">
	<form action="logout.php" method="post" name="Form1">

	<div>
		<br>
		<h2 id="matane">また明日！！！</h2>
		<table id="logoutfont">
			<tr>
				<td><?php echo $errorMessage;?></td>
			</tr>

			<tr>
		 		<td><a href="login.php">ログイン画面に戻る(数秒後に自動で遷移します)</a></td>
		 	</tr>
		 </table>
	</div>

	</form>
</body>
</html>