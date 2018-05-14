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

	//セッションの確認
	if (!isset($_SESSION['userID'])){
		header('Location:login.php');
		exit();
	}
	
	if(!isset($_SESSION['url'])){
		header('Location:growth.php');
		exit();
	}

	//セッションの再作成
	//session_regenerate_id(true);

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

		$err = "";
		if (!isset($user_flowerid)){
			$err .= "種を選んでください。\n";
		}

		if(!$err){
			// UPDATE文
			$stmt = $db->prepare("UPDATE account SET flower_id = :flower_id, seed_create_date = now() WHERE user_id = :user_id");

			// 値挿入
			$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
			$stmt->bindParam(":flower_id", $user_flowerid, PDO::PARAM_STR);

			if($stmt->execute()){
				// INSERT文
				$stmt = $db->prepare("UPDATE collect SET flower" . $user_flowerid . " = now() WHERE user_id = :user_id");

				// 値挿入
				$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);

				if($stmt->execute()){
					// 登録成功
					$_SESSION['userID'] = $user_id;
					$_SESSION['flowerID'] = $user_flowerid;
					unset($_SESSION['url']);
					header('Location: growth.php');
					exit();
				}else{
					// 登録失敗
					echo nl2br("ID登録エラー\nこのえらーがでたらほうこくしてね");
				}
			}else{
				// 登録失敗
				echo nl2br("ID登録エラー\nこのえらーがでたらほうこくしてね");
			}
		}
	}

	session_regenerate_id(true);
?>
<!DOCTYPE HTML PUBLIC" -//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type"	content="text/javascript">
	<link href='https://fonts.googleapis.com/css?family=Comfortaa' rel='stylesheet' type='text/css'>

	<title>おめでとう</title>
	<style type="text/css">
 	<!--
  		@import url(./css/finish.css);
 	 -->
  	</style>
  	<script type="text/javascript" src="./js/master.js"></script>

</head>
<body>
<div class="header_space">
	<ul id="flip" class="dropmenu">
  		<li id="click"><a>メニュー</a>
   			<ul>
   				<li><a href="mailform.php">お問い合わせ</a></li>
	   			<li><a href="collection.php">今までの記録</a></li>
      			<li><a href="logout.php">ログアウト</a></li>
    		</ul>
  		</li>
      	<li><a>　</a></li>
      	<li><a>　</a></li>
      	<li><a>　</a></li>
  		<li><a><?php echo htmlspecialchars($_SESSION['userID'],ENT_QUOTES,'UTF-8');?></a></li>
	</ul>
</div>
<div class="space"></div>
	<h1>見事なお花になったよ</h1>
	<div class="box">
		<div class="flower_image">
			<img src="image/flower/<?php echo $_SESSION['url'];?>"  class="fin_flower">
		</div>

		<div class="right">
		<div class="message">
			<h2>育成終了</h2>
			<p id="osirase">みんなに知らせよう！！</p>
			<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		</div>
		<div id="form-div">
				<h2>次の種を選んでね</h2>

				<?php
				if($err){
					echo "<font color='red'>" . nl2br($err) . "</font><br>";
				}
				?>
				<!--入力フォーム----------------->
				<form action="finish.php" method="post" class="form" id="form1">
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
				        <input type="submit" name="Update" value="Select Seed" id="button-jamp"/>
				        <div class="ease"></div>
			        </div>

			        <input type="hidden" name="token" value="<?php echo sha1(session_id())?>">
			    </form>
		</div>
		</div>
	</div>
</body>
</html>