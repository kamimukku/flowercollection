<?php
session_start();

if (!isset($_SESSION['userID'])){
	//セッションにユーザIDがある＝ログイン済
	//indexに遷移する
	header('Location: login.php');
	exit();
}




 //ログアウトボタン押下時
	if (isset($_POST['Logout'])){
		session_destroy();
		header('Location: login.php');
		exit();
	}

	//メール
	$err = "";

	if(isset($_POST['soushin'])){


//mail半角以外NG
if(isset($_POST['accountname'])){
	if (!preg_match("/^[_a-zA-Z0-9]+$/",$_POST['accountname'])){
		$err.="Nameには半角英数字以外を入れられません。<br>";
			}
		}
		if($_POST['comment']==""){
			$err.="コメントを入力してください<br>";
		}

		if(!$err){
			if(mb_send_mail("denkitype@yahoo.co.jp","花育成コメント", $_POST['comment'], "From:". $_POST['accountname']."@example.com")){
				echo "送った";
			}else{
				echo "送ってない";
			}
		}
		 elseif ($err){
		 $err.="メールの送信に失敗しました。<br>";
		 }

	}
	?>

<!DOCTYPE HTML PUBLIC" -//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type"	content="text/javascript">
	<title>お問い合わせ</title>
	<style type="text/css">
 	<!--
  		@import url(./css/mailform.css);

 	//-->

  	</style>
</head>
	<body>

<form action="./growth.php" method="post" name="Form1">

<div class="header_space">
	<ul id="flip" class="dropmenu">
  		<li id="click"><a>メニュー</a>
   			<ul>
      			<li><a href="javascript:history.back();">前の画面に戻る</a></li>
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
		</form>


<div class="mailform">
	<form action="./mailform.php" method="post">

	<div><font color="#333333">
	<?php if($err){echo $err;
	}elseif ($_POST['soushin'] && !$err){echo "メールが送信されました。";}?></font></div>
    <div>
        <label for="name">Name:</label>
        <input type="text" name="accountname" value="<?php echo htmlspecialchars($_SESSION['userID'],ENT_QUOTES,'UTF-8');?>"/>
    </div>
    <div >
        <label for="msg">Message:</label>
        <textarea name="comment" value="" ></textarea>
    </div>

    <div class="button">
        <input type="submit" name="soushin" value="送信">
    </div>

</form>
</div>
	</body>
</html>