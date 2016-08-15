<?php
require_once("dbinfo.php");

class DBAccess{
	private $pdo;
	public function __construct(){
		try {
			$this->pdo = new PDO(DB_DATA);
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(Exception $e){
			if ($_SERVER['REQUEST_METHOD'] !== "GET") {
				$this->pdo->rollback();
			}
			die(json_encode(["error"=>$e->getMessage()]));
		}
	}
	
	public function insertUser($user){
		try{
			$this->pdo->beginTransaction();
			$stmt = $this->pdo->prepare(
			$user->getIsEmailLoginUserData()?
			"insert into users(name,email,password,admin) values(:name, :email, :password, :admin);":
			"insert into users(name,token,tokenSecret,service,admin) values(:name, :token, :secret, :service, :admin);"
			);
			$stmt->bindParam(':id', $user->getId());
			$stmt->bindParam(':name', $user->getName());
			$stmt->bindParam(':email', $user->getEmail());
			$stmt->bindParam(':password', $user->getPassword());
			$stmt->bindParam(':token', $user->getToken());
			$stmt->bindParam(':secret', $user->getTokenSecret());
			$stmt->bindParam(':service', $user->getService());
			$stmt->bindParam(':admin', $user->getAdmin());
			$stmt->execute();
			$this->pdo->commit();
			return true;
		}catch(Exception $e){
			if ($_SERVER['REQUEST_METHOD'] !== "GET") {
				$this->pdo->rollback();
			}
			return false;
		}
	}
	
	public function getUser($id){
		try{
			$output = null;
			$query = "select * from users where id=:id;";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindParam(":id",$id);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach($result as $out){
				$userdata = null;
				if (isset($out["email"])&&isset($out["password"])){
					$userdata = new EmailLoginUserData($out["email"],$out["password"]);
				}else{
					$userdata = new ServiceLoginUserData($out["token"],$out["tokenSecret"],$out["service"]);
				}
				$output = new User($out["id"],$out["name"],$userdata,$out["admin"]);
				break;
			}
			$stmt = null;
			return $output;
		}catch(Exception $e){
			if ($_SERVER['REQUEST_METHOD'] !== "GET") {
				$this->pdo->rollback();
			}
			return null;
		}
	}
	
	/*	spotテーブルのアクセスメソッド	
		insertSpot($spot)
			引数に渡したSpotインスタンスのデータを登録
			戻り値 登録成功時 true、失敗時 false
		updateSpot($spot)
			引数に渡したSpotインスタンスのデータに更新
			戻り値 更新成功時 true、失敗時 false
		getSpotList()
			全データを配列で取得
			戻り値 全データのspotインスタンスの配列
		getSpot($id)
			引数で指定したidのデータのSpotインスタンスを取得
			戻り値 spotインスタンス、データが無い場合はnull
		deleteSpot($id)
			引数で指定したidのデータを削除
			戻り値 削除成功時 true、失敗時 false
	*/
	
	public function insertSpot($spot){
		try{
			$this->pdo->beginTransaction();
			$sql = 'INSERT INTO spot(name, address, description, lat, lng, category_id, user_id)
				VALUES(:name, :address, :description, :lat, :lng, :category_id, :user_id)';
			$state = $this->pdo->prepare($sql);
			$state->bindParam(':name', $spot->getName());
			$state->bindParam(':address', $spot->getAddress());
			$state->bindParam(':description', $spot->getDescription());
			$state->bindParam(':lat', $spot->getLat());
			$state->bindParam(':lng', $spot->getLng());
			$state->bindParam(':category_id', $spot->getCategoryId());
			$state->bindParam(':user_id', $spot->getUserId());
			$state->execute();
			$this->pdo->commit();
			return true;
		}catch(Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}
	
	public function updateSpot($spot){
		try{
			$this->pdo->beginTransaction();
			$sql = 'UPDATE spot
				SET name = :name,
					address = :address,
					description = :description
					lat = :lat,
					lng = :lng,
					category_id = :category_id,
					user_id = :user_id
				WHERE id = :id';
			$state = $this->pdo->prepare($sql);
			$state->bindParam(':id', $spot->getId());
			$state->bindParam(':name', $spot->getName());
			$state->bindParam(':address', $spot->getAddress());
			$state->bindParam(':description', $spot->getDescription());
			$state->bindParam(':lat', $spot->getLat());
			$state->bindParam(':lng', $spot->getLng());
			$state->bindParam(':category_id', $spot->getCategoryId());
			$state->bindParam(':user_id', $spot->getUserId());
			$state->execute();
			$this->pdo->commit();
			return true;
		}catch(Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}
	
	public function getSpotList(){
		$spotList = array();
		// リストにデータのインスタンスを追加する処理
		try{
			$sql = 'SELECT id, name, address, description, lat, lng, category_id, user_id
				FROM spot
				ORDER BY id';
			$state = $this->pdo->prepare($sql);
			$state->execute();
			$result = $stmt->fetchAll();
			foreach($result as $row){
				$spotList[] = 
				new Spot(
				$row['id'],
				$row['name'],
				$row['address'],
				$row['description'],
				$row['lat'],
				$row['lng'],
				$row['category_id'],
				$row['user_id']
				);
			}
			return $spotList;
		}catch(Exception $e){
			return array();
		}
	}
	
	public function getSpot($id){
		try{
			$sql = 'SELECT id, name, address, description, lat, lng, category_id, user_id
				FROM spot
				WHERE id = :id';
			$state = $this->pdo->prepare($sql);
			$state->bindParam(':id', $id);
			$state->execute();
			$row = $state->fetch();	
			return new	Spot(
			$row['id'],
			$row['name'],
			$row['address'],
			$row['description'],
			$row['lat'],
			$row['lng'],
			$row['category_id'],
			$row['user_id']
			);
		}catch(Exception $e){
			return null;
		}
	}
	
	public function deleteSpot($id){
		try{
			$sql = 'DELETE FROM spot
					WHERE id = :id';
			$state = $this->pdo->prepare($sql);
			$state->bindParam(':id', $id);
			$state->execute();
			return $state->rowCount() !== 0;
		}catch(Exception $e){
			return false;
		}
	}
	
	/*	spot_imagesテーブルのアクセスメソッド
		insertSpotImage($spotImage)
			引数に渡したSpotImageインスタンスのデータを登録
			戻り値 登録成功時 true、失敗時 false
		updateSpotImage($spotImage)
			引数に渡したSpotImageインスタンスのデータに更新
			戻り値 登録成功時 true、失敗時 false
		getSpotImage($id)
			引数で指定したidのデータを配列で取得
			戻り値 指定したidのSpotImageインスタンス配列、データが存在しない場合は空の配列
		deleteSpotImage($spotImage)
			引数に渡したspotImageのデータを削除
			戻り値 削除成功時 true、失敗時 false
	*/
	
	public function insertSpotImage($spotImage){
	
	}
	
	public function updateSpotImage($spotImage){
	
	}
	
	public function getSpotImage($id){
		try{
			$output = [];
			$query = "select * from spot_images where id = :id;";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindParam(":id",$id);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach($result as $row){
				$output[] = new SpotImage($row["id"],$row["path"]);
			}
			return $output;
		}catch(Exception $e){
			return [];
		}
	}
	
	public function deleteSpotImage($spotImage){
	
	}
	
	/*	category_nameテーブルのアクセスメソッド
		insertCategoryName($categoryName)
			引数で渡したcategoryNameのデータを登録
			戻り値 登録成功時 true、失敗時false
		updateCategoryName($categoryName)
			引数で渡したcategoryNameのデータに更新
			戻り値 更新成功時 true、失敗時 false
		getCategoryNameList()
			全データを取得
			戻り値 全データのCategoryNameインスタンスの配列
		getCategoryName($id)
			引数で指定したidのデータを取得
			戻り値 CategoryNameインスタンス、データが無い場合はnull
		deleteCategoryName($id)
			引数で指定したidのデータを削除
			戻り値 削除成功時 true、失敗時 false	
	*/
	
	/*	spot_categoryテーブルのアクセスメソッド
		insertSpotCategory($spotCategory)
			引数で渡したspotCategoryのデータを登録
			戻り値 登録成功時 true、失敗時 false
		updateSpotCategory($spotCategory)
			引数で渡したspotCategoryのデータに更新
			戻り値 更新成功時 true、失敗時 false
		getSpotCategoryBySID($spotId)
			引数で指定したspotIdのデータを配列で取得
			戻り値 SpotCategoryインスタンスの配列
		getSpotCategoryByCID($categoryId)
			引数で指定したcategoryIdのデータを配列で取得
			戻り値 SpotCategoryインスタンスの配列
		deleteSpotCategory($spotCategory)
			引数で渡したspotCategoryのデータを削除
			戻り値 削除成功時 true、失敗時 false
	*/
	
	/*	spot_likeテーブルのアクセスメソッド
		insertSpotLike($spotLike)
			引数で渡したspotLikeのデータを登録
			戻り値 登録成功時 true、失敗時 false
		updateSpotLike($spotLike)
			引数で渡したspotLikeのデータに更新
			戻り値 更新成功時 true、失敗時 false
		getSpotLikeByUID($userId)
			引数で指定したuserIdのデータを配列で取得
			戻り値 SpotLikeインスタンスの配列
		getSpotLikeBySID($spotId)
			引数で指定したspotIdのデータを配列で取得
			戻り値 SpotLikeインスタンスの配列
		deleteSpotLike($spotLike)
			引数で渡したspotLikeのデータを削除
			戻り値 削除成功時 true、失敗時 false
	*/
}
?>