<?php
header("content-type: application/json; charset=UTF-8");
$include = "dbinfo.php";
$value = include_once($include);
if (!$value){
	die(json_encode(["error"=>"error"]));
}
try {
	$pdo = new PDO(DB_DATA);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	switch ($_SERVER["REQUEST_METHOD"]){
		case "GET":
		$query = "select * from student";
		if (isset($_GET["condition"]))
		$query .= " where gno like :string or name like :string";
		$query .= " order by gno;";
		$stmt = $pdo->prepare($query);
		if (isset($_GET["condition"]))
		$stmt->bindValue(":string","%".$_GET["condition"]."%");
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(count($result)){
			$json = json_encode($result);
			echo $json;
		}
		$pdo = $stmt = null;
		break;
		case "POST":
		$query = "insert into student(gno,name) values(:gno, :name);";
		transaction($pdo,$query,"POST");
		break;
		case "PUT":
		$query = "update student set name = :name where gno = :gno;";
		transaction($pdo,$query,"PUT");
		break;
		case "DELETE":
		$query = "delete from student where gno = :gno;";
		transaction($pdo,$query,"DELETE");
		break;
	}
} catch(PDOException $e) {
	if ($_SERVER['REQUEST_METHOD'] !== "GET") {
		$pdo->rollback(); // ロールバック
	}
	die(json_encode(["Error: ".$e->getMessage().PHP_EOL]));
}

function transaction($pdo,$query,$method){
	$json_input = file_get_contents('php://input');
	$array = json_decode($json_input, true);
	$pdo->beginTransaction(); // トランザクション開始
	$stmt = $pdo->prepare($query); // プリペアードクエリ
	
	foreach ($array as $row) {
		$stmt->bindParam(':gno', $row['gno']);
		if (strcmp($method, 'DELETE') != 0) {
			$stmt->bindParam(':name', $row['name']);
		}
		$stmt->execute();
	}
	$pdo->commit(); // コミット
	$pdo = null;
}
?>