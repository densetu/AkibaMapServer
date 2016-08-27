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
			throw new AccessException($e->getMessage());
		}
	}
	
	public function __destruct(){
		$this->pdo = null;
	}

	public function insertUser($user){
		try{
			$this->pdo->beginTransaction();
			$stmt = $this->pdo->prepare(
			$user->getIsEmailLoginUserData()?
			"insert into users(name,email,password,admin) values(:name, :email, :password, :admin);":
			"insert into users(name,token,tokenSecret,service,admin) values(:name, :token, :secret, :service, :admin);"
			);
			$stmt->bindValue(':name', $user->getName());
			if($user->getIsEmailLoginUserData()){
				$stmt->bindValue(':email', $user->getEmail());
				$stmt->bindValue(':password', $user->getPassword());
			}else{
				$stmt->bindValue(':token', $user->getToken());
				$stmt->bindValue(':secret', $user->getTokenSecret());
				$stmt->bindValue(':service', $user->getService());
			}
			$stmt->bindValue(':admin', $user->getAdmin());
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		}catch(Exception $e){
			if ($_SERVER['REQUEST_METHOD'] !== "GET") {
				$this->pdo->rollback();
			}
			echo $e->getMessage();
			return false;
		}
	}

	public function getUser($id){
		try{
			$output = null;
			$query = "select * from users where id=:id;";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindValue(":id",$id);
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

	public function getUserByServiceLoginUserData($data){
		try{
			$output = null;
			if($data instanceof ServiceLoginUserData)
				return null;
			$query = "select * from users where token=:token && tokenSecret=:secret && service=:service;";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindValue(":token",$data->getToken());
			$stmt->bindValue(":secret",$data->getTokenSecret());
			$stmt->bindValue(":service",$data->getToken());
			$stmt->execute();
			$row = $stmt->fetch();
			
			$userdata = new ServiceLoginUserData($row["token"],$row["tokenSecret"],$row["service"]);
			$output = new User($row["id"],$row["name"],$userdata,$row["admin"]);
			$stmt = null;
			return $output;
		}catch(Exception $e){
			return null;
		}
	}

	public function getUserByEmailLoginUserData($data){
		try{
			$output = null;
			if($data instanceof EmailLoginUserData)
				return null;
			$query = "select * from users where email=:email && password=:password;";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindValue(":email",$data->getEmail());
			$stmt->bindValue(":password",$data->getPassword());
			$stmt->execute();
			$row = $stmt->fetch();
			
			$userdata = new EmailLoginUserData($row["email"],$row["password"]);
			$output = new User($row["id"],$row["name"],$userdata,$row["admin"]);
			$stmt = null;
			return $output;
		}catch(Exception $e){
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
			$sql =
				'INSERT INTO spot(name, address, description, lat, lng, user_id)
				 VALUES(:name, :address, :description, :lat, :lng, :category_id, :user_id)';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':name', $spot->getName());
			$stmt->bindValue(':address', $spot->getAddress());
			$stmt->bindValue(':description', $spot->getDescription());
			$stmt->bindValue(':lat', $spot->getLat());
			$stmt->bindValue(':lng', $spot->getLng());
			$stmt->bindValue(':user_id', $spot->getUserId());
			$stmt->execute();
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
			$sql =
				'UPDATE spot
				 SET	name = :name,
				 			address = :address,
							description = :description
							lat = :lat,
							lng = :lng,
							user_id = :user_id
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':name', $spot->getName());
			$stmt->bindValue(':address', $spot->getAddress());
			$stmt->bindValue(':description', $spot->getDescription());
			$stmt->bindValue(':lat', $spot->getLat());
			$stmt->bindValue(':lng', $spot->getLng());
			$stmt->bindValue(':user_id', $spot->getUserId());
			$stmt->execute();
			$this->pdo->commit();
			return true;
		}catch(Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}

	public function getSpotList(){
		$spotList = array();
		try{
			$sql =
				'SELECT id, name, address, description, lat, lng, category_id, user_id
				 FROM spot
				 ORDER BY id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach($result as $row){
				$spot = new Spot();
				$spot->setId($row['id']);
				$spot->setName($row['name']);
				$spot->setAddress($row['address']);
				$spot->setDescription($row['description']);
				$spot->setLat($row['lat']);
				$spot->setLng($row['lng']);
				$spot->setUserId($row['user_id']);
				$spotList[] = $spot;
			}
			return $spotList;
		}catch(PDOException $e){
			// throw new AccessException($e->getMessage());
			return array();
		}
	}

	public function getSpot($id){
		try{
			$sql =
				'SELECT id, name, address, description, lat, lng, category_id, user_id
				 FROM spot
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			$row = $stmt->fetch();

			$spot = new Spot();
			$spot->setId($row['id']);
			$spot->setName($row['name']);
			$spot->setAddress($row['address']);
			$spot->setDescription($row['description']);
			$spot->setLat($row['lat']);
			$spot->setLng($row['lng']);
			$spot->setUserId($row['user_id']);
			return $spot;
		}catch(PDOException $e){
			// throw new AccessException($e->getMessage());
			return null;
		}
	}

	public function deleteSpot($id){
		try{
			$this->pdo->beginTransaction();
			$sql =
				'DELETE FROM spot
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() !== 0;
		}catch(Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}

	/*	spot_imagesテーブルのアクセスメソッド
		insertSpotImage($spotImage)
			引数に渡したSpotImageインスタンスのデータを登録
			戻り値 登録成功時 true、失敗時 false
		getSpotImage($id)
			引数で指定したidのデータを配列で取得
			戻り値 指定したidのSpotImageインスタンス配列、データが存在しない場合は空の配列
		deleteSpotImage($spotImage)
			引数に渡したspotImageのデータを削除
			戻り値 削除成功時 true、失敗時 false
		deleteAllSpotImage($spotId)
			引数で指定したidのデータを全て削除
			戻り値 削除成功時 true、失敗時 false
	*/

	public function insertSpotImage($spotImage){
		$sql =
			'INSERT INTO spot_images(id, path)
			 VALUES (:id, :path)';
		try{
			$this->pdo->beginTransaction();
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $spotImage->getId());
			$stmt->bindValue(':path', $spotImage->getPath());
			$stmt->execute();
			$this->pdo->commit();
			return true;
		}catch(Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}


	public function getSpotImage($id){
		try{
			$output = [];
			$query =
				"SELECT id, path
				 FROM spot_images
				 WHERE id = :id;";
			$stmt = $this->pdo->prepare($query);
			$stmt->bindValue(":id",$id);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach($result as $row){
				$si = new SpotImage();
				$si->setId($row['id']);
				$si->setPath($row['path']);
				$output[] = $si;
			}
			return $output;
		}catch(PDOException $e){
			// throw new AccessException($e->getMessage());
			return [];
		}
	}

	public function deleteSpotImage($spotImage){
			try{
				$this->pdo->beginTransaction();
				$sql =
					'DELETE FROM spot_images
					 WHERE id = :id
					 AND path = :path';
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindValue(':id', $spotImage->getId());
				$stmt->bindValue(':path', $spotImage->getPath());
				$stmt->execute();
				$this->pdo->commit();
				/*
				if($stmt->rowCount() === 0){
					throw new Exception('削除するレコードがありません');
				}
				*/
				return $stmt->rowCount() > 0;
			} catch(Exception $e) {
				$this->pdo->rollback();
				// throw new Exception('処理中に例外が発生しました');
				return false;
			}
	}

	public function deleteAllSpotImage($spotId){
		try{
			$this->pdo->beginTransaction();
			$sql =
				'DELETE FROM spot_images
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $spotId);
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e) {
			$this->pdo->rollback;
			return false;
		}
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

	public function insertCategoryName($categoryName){
		try{
			$this->pdo->beginTransaction();
			$sql =
				'INSERT INTO category_name(id, name, parent_id)
				 VALUES (:id, :name, :p_id)';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $categoryName->getId());
			$stmt->bindValue(':name', $categoryName->getName());
			$stmt->bindValue(':p_id', $categoryName->getParentId());
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}

	public function updateCategoryName($categoryName){
		try{
			$this->pdo->beginTransaction();
			$sql =
				'UPDATE category_name
				 SET name = :name
				 		 parent_id = :p_id
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $categoryName->getId());
			$stmt->bindValue(':name', $categoryName->getName());
			$stmt->bindValue(':p_id', $categoryName->getParentId());
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}

	public function getCategoryNameList(){
		$list = array();
		try{
			$sql =
				'SELECT id, name, parent_id
				 FROM category_name';
			$stmt = $this->pdo->prepare($sql);
			$result =	$stmt->fetchAll();
			foreach ($result as $row) {
				$cn = new CategoryName();
				$cn->setId($row['id']);
				$cn->setName($row['name']);
				$cn->setParentId($row['parent_id']);
				$list[] = $cn;
			}
			return $list;
		} catch(PDOException $e){
			// throw new AccessException($e->getMessage());
			return array();
		}
	}

	public function getCategoryName($id){
		try{
			$sql =
				'SELECT id, name, parent_id
				 FROM category_name
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			$row = $stmt->fetch();

			$cn = new CategoryName();
			$cn->setId($row['id']);
			$cn->setName($row['name']);
			$cn->setParentId($row['parent_id']);
			return $cn;
		} catch (PDOException $e){
			// throw new AccessException($e->getMessage());
			return null;
		}
	}

	public function deleteCategoryName($id){
		try{
			$this->pdo->beginTransaction();
			$sql =
				'DELETE FROM category_name
				 WHERE id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e) {
			$this->pdo->rollback();
			return false;
		}
	}

	/*	spot_categoryテーブルのアクセスメソッド
		insertSpotCategory($spotCategory)
			引数で渡したspotCategoryのデータを登録
			戻り値 登録成功時 true、失敗時 false
		getSpotCategoryBySID($spotId)
			引数で指定したspotIdのデータを配列で取得
			戻り値 SpotCategoryインスタンスの配列
		getSpotCategoryByCID($categoryId)
			引数で指定したcategoryIdのデータを配列で取得
			戻り値 SpotCategoryインスタンスの配列
		deleteSpotCategory($spotCategory)
			引数で渡したspotCategoryのデータを削除
			戻り値 削除成功時 true、失敗時 false
		deleteAllSpotCategory($spotId)
			引数で指定したidのデータを全て削除
			戻り値 削除成功時 true、失敗時 false
	*/

	public function insertSpotCategory($spotCategory){
		try{
			$this->pdo->beginTransaction();
			$sql =
				'INSERT INTO spot_category(spot_id, category_id)
				 VALUES (:s_id, :c_id)';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':s_id', $spotCategory->getSpotId());
			$stmt->bindValue(':c_id', $spotCategory->getCategoryId());
			$stmt->execute();
			$this->pdo->commit();
			return true;
		} catch (Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}

	public function getSpotCategoryBySID($spotId){
		$list = array();
		try{
			$sql =
				'SELECT spot_id, category_id
				 FROM spot_category
				 WHERE spot_id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $spotId);
			$stmt->execute;
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$sc = new SpotCategory();
				$sc->setSpotId($row['spot_id']);
				$sc->setCategoryId($row['category_id']);
				$list[] = $sc;
			}
			return $list;
		} catch (PDOException $e) {
			// throw new AccessException($e->getMessage());
			return null;
		}
	}

	public function getSpotCategoryByCID($categoryId){
		$list = array();
		try{
			$sql =
				'SELECT spot_id, category_id
				 FROM spot_category
				 WHERE category_id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $categoryId);
			$stmt->execute;
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$sc = new SpotCategory();
				$sc->setSpotId($row['spot_id']);
				$sc->setCategoryId($row['category_id']);
				$list[] = $sc;
			}
			return $list;
		} catch (PDOException $e) {
			// throw new AccessException($e->getMessage());
			return null;
		}
	}

	public function deleteSpotCategory($spotCategory){
		// 引数で渡したspotCategoryのデータを削除
		// 戻り値 削除成功時 true、失敗時 false
		try{
			$this->pdo->beginTransaction();
			$sql =
				'DELETE FROM spot_category
				 WHERE spot_id = :s_id
				 AND category_id = :c_id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':s_id', $spotCategory->getSpotId());
			$stmt->bindValue(':c_id', $spotCategory->getCategoryId());
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e){
			$this->pdo->rollback();
			return false;
		}
	}

	public function deleteAllSpotCategory($spotId){
		// 引数で指定したidのデータを全て削除
		// 戻り値 削除成功時 true、失敗時 false
		try {
			$this->pdo->beginTransaction();
			$sql =
				'DELETE FROM spot_category
				 WHERE spot_id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':id', $spotId);
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e) {
			$this->pdo->rollback();
			return false;
		}

	}

	/*	spot_likeテーブルのアクセスメソッド
		insertSpotLike($spotLike)
			引数で渡したspotLikeのデータを登録
			戻り値 登録成功時 true、失敗時 false
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

	public function insertSpotLike($spotLike){
		// 引数で渡したspotLikeのデータを登録
		// 戻り値 登録成功時 true、失敗時 false
		try {
			$this->pdo->beginTransaction();
			$sql =
				'INSERT INTO spot_like(user_id, spot_id)
				 VALUES(:u_id, :s_id)';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':s_id', $spotLike->getSpotId());
			$stmt->bindValue(':u_id', $spotLike->getId());
			$stmt->execute();
			$this->pdo->commit();
			return true;
		} catch (Exception $e) {
			$this->pdo->rollback();
			return false;
		}

	}

	public function getSpotLikeByUID($userId){
		// 引数で指定したuserIdのデータを配列で取得
		// 戻り値 SpotLikeインスタンスの配列
		$list = array();
		try{
			$sql =
				'SELECT user_id, spot_id
				 FROM spot_like
				 WHERE user_id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt = bindValue(':id', $userId);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$sl = new SpotLike();
				$sl->setId($row['user_id']);
				$sl->setSpotId($row['spot_id']);
				$list[] = $sl;
			}
			return $list;
		} catch (PDOException $e){
			// throw new AccessException($e->getMessage());
			return null;
		}
	}

	public function getSpotLikeBySID($spotId){
		// 引数で指定したspotIdのデータを配列で取得
		// 戻り値 SpotLikeインスタンスの配列
		try{
			$sql =
				'SELECT user_id, spot_id
				 FROM spot_like
				 WHERE spot_id = :id';
			$stmt = $this->pdo->prepare($sql);
			$stmt = bindValue(':id', $spotId);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$sl = new SpotLike();
				$sl->setId($row['user_id']);
				$sl->setSpotId($row['spot_id']);
				$list[] = $sl;
			}
			return $list;
		} catch (PDOException $e){
			// throw new AccessException($e->getMessage());
			return null;
		}
	}

	public function deleteSpotLike($spotLike){
		try {
			$this->pdo->beginTransaction();
			$sql =
				'DELETE FROM spot_like
				 WHERE spot_id = :s_id
				 AND user_id = :u_id';
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindValue(':s_id', $spotLike->getSpotId());
			$stmt->bindValue(':u_id', $spotLike->getId());
			$stmt->execute();
			$this->pdo->commit();
			return $stmt->rowCount() > 0;
		} catch (Exception $e) {
			$this->pdo->rollback();
			return false;
		}
	}

	// private function deleteSpotLikeBySID($spotId){
	// 	try {
	// 		$this->pdo->beginTransaction();
	// 		$sql =
	// 			'DELETE FROM spot_like
	// 			 WHERE spot_id = :sid';
	// 		$stmt = $this->pdo->prepare($sql);
	// 		$stmt->bindValue(':sid', $spotId);
	// 		$stmt->execute();
	// 		$this->pdo->commit();
	// 		return $stmt->rowCount() !== 0;
	// 	} catch (Exception $e) {
	// 		$this->pdo->rollback();
	// 		return false;
	// 	}
	//}
}
?>
