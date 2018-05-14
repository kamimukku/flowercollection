
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
	}



	// ユーザーID確認
	if(!isset($_SESSION['userID'])){
		header('Location: login.php');
		exit();
		//		unset($_SESSION['userID']);
		/*
		if(!isset($_SESSION['seed'])){
				header('Location: growth.php');
				exit();
		}
		*/
	}
	
	if($_POST['token'] != sha1(session_id())){
		session_destroy();
		header("Location: login.php");
		exit();
	}
	

	if(isset($_POST['Back'])){
 		//		unset($_SESSION['userID']);
		header('Location: finish.php');
		exit();
	}



	// ログイン押下
	if(isset($_POST['Update'])){
		//トークン確認
		
		if($_POST['token'] != sha1(session_id())){
			session_destroy();
			header("Location: login.php");
			exit();
		}
		

		// 受け取る
		$user_id = $_SESSION['userID'];
		$user_flowerid = $_POST['flowerID'];
		$flower_column = "flower".$user_flowerid;

		$err = "";
		if (!isset($user_flowerid)){
			$err .= "種を選んでください。\n";
		}

		if(!$err){
			// UPDATE文
			$stmt = $db->prepare("UPDATE account SET flower_id = :flower_id WHERE user_id = :user_id");

			// 値挿入
			$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
			$stmt->bindParam(":flower_id", $user_flowerid, PDO::PARAM_STR);



			if($stmt->execute()){
					// INSERT文
					$stmt = $db->prepare("UPDATE collect SET flower" . $user_flowerid . " = now() WHERE user_id = :user_id");
						
					// 値挿入
					$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
//					$stmt->bindParam(":flower_column", $flower_column, PDO::PARAM_STR);

					if($stmt->execute()){
						// 登録成功
						$_SESSION['userID'] = $user_id;
						$_SESSION['flowerID'] = $user_flowerid;
						$_SESSION['create_date'] = time();
						//var_dump($_SESSION);
						header('Location: growth.php');
						exit();
					}else{
						// 登録失敗
						echo nl2br("ID登録エラー\nこのえらーがでたらほうこくしてね");
					}

				//header('Location: growth.php');
				echo $flower_column;
				exit();

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
	<title>掲示板</title>
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
		<h2>新しい種の始まり</h2>
		<?php
		if($err){
			echo "<font color='red'>" . nl2br($err) . "</font><br>";
		}
		?>
		<!--入力フォーム----------------->
		<form action="seed.php" method="post" class="form" id="form1">
	        <div class="sample">
		        <input type="radio" id="seed_label" disabled>
			    <label for="seed_label">Seed</label>
			    <input type="radio" name="flowerID" id="color_0" value="0" >
			    <label for="color_0">Red</label>
			    <input type="radio" name="flowerID" id="color_1" value="1">
			    <label for="color_1">Blue</label>
			    <input type="radio" name="flowerID" id="color_2" value="2">
			    <label for="color_2">Yellow</label>
			    <div class="ease"></div>
			</div>
	        <div class="submit">
		        <input type="submit" name="Update" value="Select Seed" id="button-blue"/>
		        <div class="ease"></div>
	        </div>
	        <div class="submit">
		        <input type="submit" name="Back" value="Back" id="button-red"/>
		        <div class="ease"></div>
	        </div>
	        <input type="hidden" name="token" value="<?php echo sha1(session_id())?>">
	    </form>
	</div>
	</div>
</div>
</body>
</html>