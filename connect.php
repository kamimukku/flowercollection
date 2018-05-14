<?php
	class DBConnect{
		public function dbConnect(){
			// 接続pass
			$dsn = 'mysql:host=localhost;dbname=slj;charset=utf8';
			$user = 'root';
			$password = 'sljslj';
			return $this->connect($dsn, $user, $password);
		}

		public function dbConnectName($dbname){
			// 接続pass
			$dsn = 'mysql:host=localhost;dbname=' . $dbname .';charset=utf8';
			$user = 'root';
			$password = 'sljslj';
			return $this->connect($dsn, $user, $password);
		}

		public function connect($dsn, $user, $password){

			try {
				// 接続
				$db = new PDO($dsn, $user, $password);
				$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);



				//出る
				return $db;
			} catch (PDOException $e) {
				die('エラー' . $e->getMessage());
				return NULL;
			}

		}
	}

//	$dbc = new DBConnect();
//	$dbc->dbConnect();








	/*作成
	 $stmt = $db->prepare("
	 		INSERT INTO account(user_id, user_pass)
	 		VALUES (:user_id, :user_pass)"
	 );
	//割り当て
	$stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
	//					$stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
	$stmt->bindParam(':user_pass', $user_pass, PDO::PARAM_STR);
	$stmt->bindParam(':user_pass', $user_pass, PDO::PARAM_STR);
	$stmt->execute();
	header('Location: index.php');
	*/

	/*
	 //ユーザー名表示
	echo "user:" . $user . "<br>password:********<br>";

	//テーブル名表示
	$sql = 'show tables';
	$stmt =  $db->query($sql);
	echo "<pre>";
	var_dump($stmt->fetchAll());
	echo "</pre>";
	*/
	?>