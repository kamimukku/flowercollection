<?php
	function getFileList($dir, $rootdir) {
		$files = glob(rtrim($dir, '/') . '/*');
		foreach ($files as $file) {
			$filename = str_replace($rootdir, "", $file);
			if (is_file($file)) {
				echo "<tr>";
				echo "<td><div><img border='1' src='" . $file ."' width='64' height='64' onclick=selectFlower('" . $filename . "')></div></td>";
				echo "<td><div><a onclick=selectFlower('" . $filename . "')>" . $filename . "</a></div></td>";
				echo "</tr>";
			}
			if (is_dir($file)) {
				getFileList($file, $dir);
			}
		}
	}

	session_start();

	// データベースに接続
	include 'connect.php';
	$connect = new DBConnect();
	if(!$db = $connect->dbConnectName("denki")){
	//	header('Location: index.php');
	//	exit();
		$err .= "データベースに接続できませんでした。\n";
	}


	// 登録・登録更新ボタン押下
	if($_POST['Insert'] || $_POST['Refresh']){
		if($_POST['token'] != sha1(session_id())){
			header("Location: importdb.php");
			exit();
		}
		if(!$_POST['final']){
			$_POST['final'] = false;
		}

		if(!$_POST['day']){
			$err .= "経過日数がありません\n";
		}
		if(!$_POST['url']){
			$err .= "URLがありません\n";
		}

		if(!$err){
			$stmt = $db->prepare("SELECT flower_key FROM flower WHERE flower_id = :flower AND day = :day");
			$stmt->bindParam(":flower", $_POST['flowerID'], PDO::PARAM_STR);
			$stmt->bindParam(":day", $_POST['day'], PDO::PARAM_STR);
			$stmt->execute();

			if(!($row = $stmt->fetch())){
				$stmt = $db->prepare("INSERT INTO flower(flower_id, day, url, isfinal) VALUE (:flower, :day, :url, :isfinal)");
				$stmt->bindParam(":flower", $_POST['flowerID'], PDO::PARAM_STR);
				$stmt->bindParam(":day", $_POST['day'], PDO::PARAM_STR);
				$stmt->bindParam(":url", $_POST['url'], PDO::PARAM_STR);
				$stmt->bindParam(":isfinal", $_POST['final'], PDO::PARAM_STR);
				if($stmt->execute()){
					$err = "追加しました";
				}else{
					$err .= "追加失敗しました\n";
					$err .= var_dump($stmt->errorInfo());
				}
			}else{
				if($_POST['Insert']){
					$err .= "既に指定してあります。";
				}else{
					$stmt = $db->prepare("UPDATE flower SET flower_id = :flower, day = :day, url = :url, isfinal = :isfinal WHERE flower_key = :flower_key");
					$stmt->bindParam(":flower_key", $row['flower_key'], PDO::PARAM_STR);
					$stmt->bindParam(":flower", $_POST['flowerID'], PDO::PARAM_STR);
					$stmt->bindParam(":day", $_POST['day'], PDO::PARAM_STR);
					$stmt->bindParam(":url", $_POST['url'], PDO::PARAM_STR);
					$stmt->bindParam(":isfinal", $_POST['final'], PDO::PARAM_STR);
					if($stmt->execute()){
						$err .= "更新しました";
					}else{
						$err .= "更新失敗しました\n";
						$err .= var_dump($stmt->errorInfo());
					}
				}
			}
		}
	}

	// 「growthへ」・アカウントログインボタン押下
	if($_POST['Growth'] || $_POST['LoginID']){
		if($_POST['LoginID']){
			$_SESSION['userID'] = $_POST['LoginID'];
			$stmt = $db->prepare("SELECT flower_id from account where user_id = :user_id");
			$stmt->bindParam(":user_id", $_POST['LoginID'], PDO::PARAM_STR);
			if($stmt->execute()){
				$row = $stmt->fetch();
				$_SESSION['flowerID'] = $row['flower_id'];
			}
		}else{
			$_SESSION['userID'] = "debug";
			if($_SESSION['create_date']){
				$time = date("Y-m-d H:i:s", $_SESSION['create_date']);
			}else{
				$time = date("Y-m-d H:i:s");
			}
			$stmt = $db->prepare("UPDATE account SET create_date = :cda, seed_create_date = :cdb WHERE user_id = :debug");
			$stmt->bindParam(":cda", $time, PDO::PARAM_STR);
			$stmt->bindParam(":cdb", $time, PDO::PARAM_STR);
			$stmt->bindParam(":debug", $_SESSION['userID'], PDO::PARAM_STR);
			$stmt->execute();
			if(!$_SESSION['flowerID']){
				$_SESSION['flowerID'] = "0";
			}
		}
		header("Location: growth.php");
		exit();
	}

	//削除ボタン
	if($_POST['Delete']){
		if($_POST['token'] != sha1(session_id())){
			header("Location: importdb.php");
			exit();
		}

		$stmt = $db->prepare("DELETE FROM flower WHERE flower_key = :flower_key");
		$stmt->bindParam(":flower_key", $_POST['Delete'], PDO::PARAM_STR);
		if($stmt->execute()){
			$err = "削除しました";
		}else{
			$err .= "削除失敗しました\n";
			$err .= var_dump($stmt->errorInfo());
		}
	}

	//アカウント削除ボタン
	if($_POST['DeleteID']){
		if($_POST['token'] != sha1(session_id())){
			header("Location: importdb.php");
			exit();
		}

		$stmt = $db->prepare("DELETE FROM account WHERE user_id = :user_id");
		$stmt->bindParam(":user_id", $_POST['DeleteID'], PDO::PARAM_STR);
		if($stmt->execute()){
			$stmt = $db->prepare("DELETE FROM collect WHERE user_id = :user_id");
			$stmt->bindParam(":user_id", $_POST['DeleteID'], PDO::PARAM_STR);
			$stmt->execute();
			$err = "ID:" . htmlspecialchars($_POST['DeleteID']) . "を削除しました";
		}else{
			$err .= "削除に失敗しました\n";
			$err .= var_dump($stmt->errorInfo());
		}
	}

	// 「entryへ」ボタン押下
	if($_POST['entry']){
		session_destroy();
		header("Location: entry.php");
		exit();
	}

	//セッション再作成
	session_regenerate_id(true);

	// flower_idの表示関連のボタン押下
	if(is_numeric($_POST['Length'])){
		$stmt = $db->prepare("SELECT * FROM flower WHERE flower_id = :flower ORDER BY day ASC");
		$stmt->bindParam(":flower", $_POST['Length'], PDO::PARAM_STR);
		$check = "flower_id = " . $_POST['Length'];
		$_SESSION['flowerID'] = $_POST['Length'];

	// dayの表示関連のボタン押下
	}else if(is_numeric($_POST['days'])){
		$stmt = $db->prepare("SELECT * FROM flower WHERE day <= :day ORDER BY flower_id ASC, day DESC");
		$stmt->bindParam(":day", $_POST['daycheck'], PDO::PARAM_STR);
		$check = $_POST['daycheck'] . "日目の表示";
		$datetime = time() - (86400 * ($_POST['daycheck'] - 1));
		$_SESSION['create_date'] = $datetime;

	// リセット、更新ボタン時と初期表示
	}else{
		$stmt = $db->prepare("SELECT * FROM flower ORDER BY flower_id ASC, day ASC");
	}
	$stmt->execute();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" 		content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Style-Type" 	content="text/css">
	<meta http-equiv="Content-Script-Type"	content="text/javascript">
	<title>いれるところ</title>
	<style type="text/css">
	<!--
	@import url(./css/master.css);
	-->
	</style>
	<script type="text/javascript" src="./js/master.js"></script>
</head>
<body>
	<div>
		<?php
		if($err){
			echo "<font color='red'>" . nl2br($err) . "</font><br>";
		}
		?>
		<!--入力フォーム----------------->
		<form action="" method="post" name="Form1" style="margin: 0px;">

			<table border="0" summary=" ">
				<tr>
					<td><div>種タイプ</div></td>
					<td>
					<label for="color_0">warm</label>
					<input type="radio" name="flowerID" value=0 id="color_0" <?php if($_POST['flowerID'] == 0) echo checked;?> class="radio">
					<label for="color_1">cool</label>
					<input type="radio" name="flowerID" value=1 id="color_1" <?php if($_POST['flowerID'] == 1) echo checked;?> class="radio">
					<label for="color_2">funny</label>
					<input type="radio" name="flowerID" value=2 id="color_2" <?php if($_POST['flowerID'] == 2) echo checked;?> class="radio">
					</td>
				</tr>
				<tr>
					<td><div>経過日数</div></td>
					<td><div><input type="text" name="day" value=<?php if($_POST['day']){ echo $_POST['day'];} else{ echo "1";}?> style="width: 30px; text-align:right;">日後から</div></td>
				</tr>
				<tr>
					<td><div>花URL:../trunk/image/flower/</div></td>
					<td><div><input type="text" name="url" value=""></div></td>
				</tr>
				<tr>
					<td><div>最後？</div></td>
					<td><div><input type="checkbox" name="final" value="1" class="radio"></div></td>
				</tr>
			</table>
			<div>
				<input type="submit" name="Insert" value="登録" class="button">
				<input type="submit" name="Refresh" value="登録更新" class="button">
				<input type="hidden" name="token" value="<?php echo sha1(session_id())?>">
			</div>

		<br>
			<div>
				flower_idの表示：
				<button type="submit" name="Length" value="0">0</button>
				<button type="submit" name="Length" value="1">1</button>
				<button type="submit" name="Length" value="2">2</button>
			</div>
		</form>

			<div style="position: absolute; top: 120px; left: 270px;">
				<!-- 折り畳み展開ポインタ -->
				<div onclick="obj=document.getElementById('open').style; obj.display=(obj.display=='none')?'block':'none';">
					<a style="cursor:pointer;">▼ データベース展開</a>
				</div>
				<!--// 折り畳み展開ポインタ -->

				<!-- 折り畳み展開ポインタ -->
				<div onclick="obj=document.getElementById('open2').style; obj.display=(obj.display=='none')?'block':'none';">
					<a style="cursor:pointer;">▼ 画像展開(../trunk/image/flower/)</a>
				</div>
				<!--// 折り畳み展開ポインタ -->

				<!-- 折り畳み展開ポインタ -->
				<div onclick="obj=document.getElementById('open3').style; obj.display=(obj.display=='none')?'block':'none';">
					<a style="cursor:pointer;">▼ ユーザーID展開</a>
				</div>
				<!--// 折り畳み展開ポインタ -->
			</div>

		<form action="" method="post" name="Form1">
			<div>
				dayの表示：
				<input type="text" name="daycheck" value=1 style="width: 30px; text-align:right;">日
				<button type="submit" name="days" value="0">表示</button>
			</div>
			<input type="submit" name="Ref" value="リセット・更新" style="width: 120px; height: 25px;">
			<input type="submit" name="Growth" value="growthへ" style="width: 120px; height: 25px;">
			(flower_id:<?php echo $_SESSION['flowerID'];?>  date:<?php echo $_SESSION['create_date']?>)
			<hr>
			<?php if($check) echo $check;?>

			<!-- 折り畳まれ部分 -->
			<div id="open" style="display:none;clear:both;">
				<table border="0" summary=" ">
					<tr>
						<td width="80"><div>del</div></td>
						<td width="80"><div>flower_id</div></td>
						<td width="80"><div>day</div></td>
						<td width="80"><div>isfinal</div></td>
						<td><div>image</div></td>
						<td><div>url</div></td>
					</tr>
				<?php
					$pos = 0;
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
						if(!isset($_POST['days']) || $pos == $row['flower_id']){
				?>
					<tr>
						<td><div><button type="submit" name="Delete" value="<?php echo $row['flower_key']?>">削除</button></div></td>
						<td><div><?php echo $row['flower_id']?></div></td>
						<td><div><?php echo $row['day']?></div></td>
						<td><div><?php echo ($row['isfinal'] == 1 ? 'true' : 'false')?></div></td>
						<td><div><img border='1' src='<?php echo "image/flower/" . $row['url']?>' width='64' height='64' onclick='selectFlower("<?php echo $row['url'];?>")'></div></td>
						<td><div><a href='<?php echo "image/flower/" . $row['url']?>' target='_blank'><?php echo $row['url'];?></a></div></td>
					</tr>
				<?php
							$pos++;
						}
					}
				?>
				</table>
			</div>
			<!--// 折り畳まれ部分 -->

			<!-- 折り畳まれ部分 -->
			<div id="open2" style="display:none;clear:both;">

				<table border="0" summary=" ">
					<tr>
						<td><div>image</div></td>
						<td><div>url</div></td>
					</tr>
				<?php
					// ディレクトリのパスを記述
					$dir = "../trunk/image/flower/" ;
					getFileList($dir, $dir);
				?>
				</table>
			</div>
			<!--// 折り畳まれ部分 -->

			<!-- 折り畳まれ部分 -->
			<div id="open3" style="display:none;clear:both;">

				<table border="0" summary=" ">
					<tr>
						<td width="80"><div>login</div></td>
						<td width="80"><div>user_id</div></td>
						<td width="80"><div>password</div></td>
						<td width="80"><div>flower_id</div></td>
						<td width="80"><div>create_date</div></td>
						<td width="80"><div>seed_create_date</div></td>
						<td width="80"><div>flower1</div></td>
						<td width="80"><div>flower2</div></td>
						<td width="80"><div>flower3</div></td>
						<td width="80"><div>del</div></td>
					</tr>
				<?php
					$stmt = $db->prepare("SELECT * FROM account inner join collect using(user_id) ORDER BY account_id ASC");
					$stmt->execute();
					$pos = 0;
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				?>
					<tr>
						<td><div><button type="submit" name="LoginID" value="<?php echo htmlspecialchars($row['user_id']);?>">ログイン</button></div></td>
						<td><div><?php echo htmlspecialchars($row['user_id']);?></div></td>
						<td><div><?php echo htmlspecialchars($row['password']);?></div></td>
						<td><div><?php echo $row['flower_id'];?></div></td>
						<td><div><?php echo $row['create_date'];?></div></td>
						<td><div><?php echo $row['seed_create_date'];?></div></td>
						<td><div><?php echo $row['flower0'];?></div></td>
						<td><div><?php echo $row['flower1'];?></div></td>
						<td><div><?php echo $row['flower2'];?></div></td>
						<td><div><button type="submit" name="DeleteID" value="<?php echo htmlspecialchars($row['user_id'])?>">削除</button></div></td>
					</tr>
				<?php
					}
				?>
				</table>
				<input type="submit" name="entry" value="entryへ" style="width: 120px; height: 25px;">
			</div>
			<!--// 折り畳まれ部分 -->
			<!--<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script> -->
		<input type="hidden" name="token" value="<?php echo sha1(session_id())?>">
		</form>
	</div>
</body>
</html>