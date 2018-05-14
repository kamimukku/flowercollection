
<?php
	session_start();

	// データベースに接続
	include 'connect.php';
	// Connectをインスタンス
	$connect = new DBConnect();

	// dbConnect()を呼び、mySQLの位置(?)を$dbに受け取る
	// この時$dbに入っていれば正常
	if(!$db = $connect->dbConnectName("denki")){
	//	header('Location: index.php');
	//	exit();
		$err .= "データベースに接続できませんでした。";
		exit();
	}

	// ユーザーID確認
	if(isset($_SESSION['userID'])){
//		unset($_SESSION['userID']);
		header('Location: login.php');
		exit();
	}

	// ログイン押下
	if(isset($_POST['Insert'])){
		//トークン確認
		if($_POST['token'] != sha1(session_id())){
			session_destroy();
			header("Location: login.php");
			exit();
		}

		// 受け取る
		$user_id = $_POST['userID'];
		$user_pass = $_POST['password'];
		$user_flowerid = $_POST['flowerID'];

		$err = "";
		if(!$user_id){
			$err .= "名前を入れてください。\n";
		}else{
			// idが一致した場合出力するSQLのSELECT文
			$stmt = $db->prepare("SELECT * FROM account WHERE user_id = :user_id");

			// 割り当て
			$stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);

			// 実行
			$stmt->execute();

			// user_idとuser_passが一致したら$stmt->fetch()に一致した行が入る。
			// 一致してない場合空白になる。
			if($row = $stmt->fetch()){
				$err .= "この名前はすでに使われています。\n";
			}
		}

		if (!isset($user_flowerid)){
			$err .= "種を選んでください。\n";
		}
		if(mb_strlen($user_pass) < 8 || mb_strlen($user_pass) > 16){
			$err .= "パスワードの文字数は8文字以上16文字以内です。\n";
		}else if(!preg_match("/^[_a-zA-Z0-9]+$/", $user_pass) ){
			$err .= "パスワードは英数字のみ可能です。\n";
		}

		if(!$err){
			// INSERT文
			$stmt = $db->prepare("INSERT INTO account(user_id, password, flower_id, create_date, seed_create_date) VALUE (:user_id, :pass, :flower_id, now(), now())");

			// 値挿入
			$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
			$stmt->bindParam(":pass", $user_pass, PDO::PARAM_STR);
			$stmt->bindParam(":flower_id", $user_flowerid, PDO::PARAM_STR);

			if($stmt->execute()){
				// INSERT文
				$stmt = $db->prepare("INSERT INTO collect(user_id, flower" . $user_flowerid . ") VALUE (:user_id, now())");

				// 値挿入
				$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
				if($stmt->execute()){
					// 登録成功
					$_SESSION['userID'] = $user_id;
					$_SESSION['flowerID'] = $user_flowerid;
					//var_dump($_SESSION);
					header('Location: growth.php');
					exit();
				}else {
					// 登録失敗
					echo nl2br("ID登録エラー\nこのえらーがでたらほうこくしてね");
				}
			}else{
				// 登録失敗
				echo nl2br("ID登録エラー\nこのえらーがでたらほうこくしてね");
			}
		}
	}

	echo $user_flowerid;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" 		content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" 	content="text/css">
	<meta http-equiv="Content-Script-Type"	content="text/javascript">
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet' type='text/css'>
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
		<h2>新しい種を育てよう</h2>
		<?php
		if($err){
			echo "<font color='red'>" . nl2br($err) . "</font><br>";
		}
		?>
		<!--入力フォーム----------------->
		<form action="entry.php" method="post" class="form" id="form1">
			<p class="name">
		        <input name="userID" type="text" class="feedback-input" placeholder="ID" id="name" />
	 		</p>
			<p class="password">
		        <input type="password" name="password" class="feedback-input" id="password" placeholder="Password" />
	        </p>
	        <div class="sample">
		        <input type="radio" id="seed_label" disabled>
			    <label for="seed_label">Seed</label>
			    <input type="radio" name="flowerID" id="color_0" value="0" >
			    <label for="color_0">Warm</label>
			    <input type="radio" name="flowerID" id="color_1" value="1">
			    <label for="color_1">Cool</label>
			    <input type="radio" name="flowerID" id="color_2" value="2">
			    <label for="color_2">Funny</label>
			    <div class="ease"></div>
			</div>
	        <div class="submit">
		        <input type="submit" name="Insert" value="Create Account" id="button-blue"/>
		        <div class="ease"></div>
	        </div>
	        <input type="hidden" name="token" value="<?php echo sha1(session_id())?>">
	    </form>
	    <div class="submit">
			<button type="button"onclick="location.href='login.php'" id="button-red" >Back to Login</button>
			<div class="ease"></div>
		</div>
	</div>
	</div>
</div>
</body>
</html>