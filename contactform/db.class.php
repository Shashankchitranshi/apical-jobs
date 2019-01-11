
<?php
/*
Author: Deepanshu Srivastava
Description: database Wrapper
Version: 0.1
 */

class Database {
	public $conn;
	private $host;
	private $user;
	private $password;
	private $baseName;
	private $port;
	private $Debug;

	/**
	 * { public function_description }
	 *
	 * @param      array  $params  The parameters
	 */
	function __construct($params = array()) {
		$this->conn = false;
		$this->host = 'localhost';
		$this->user = 'root';
		$this->password = 'root';
		$this->dbname = 'apical';
		$this->port = '3306';
		$this->debug = true;
		$this->connect();
	}

	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * { public function_description }
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function connect() {
		if (!$this->conn) {
			try {
				$this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname . '', $this->user, $this->password, array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				));
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			} catch (Exception $e) {
				die('Error : ' . $e->getMessage());
			}

			if (!$this->conn) {
				$this->status_fatal = true;
				echo 'Connection failed';
				die();
			} else {
				$this->status_fatal = false;
			}
		}

		return $this->conn;
	}

	public function disconnect() {
		if ($this->conn) {
			$this->conn = null;
		}
	}

	/**
	 * { public function_description }
	 *
	 * @param      string   $table  The table
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function table_exists($table) {
		try {
			$result = $this->conn->prepare("SELECT 1 FROM $table");
			$ret = $result->execute();
			if (!$ret) {
				return false;
			}

		} catch (PDOException $e) {
			return false;
		}
		return true;
	}
	/**
	 * Gets the result.
	 *
	 * @param      string  $table   The table
	 * @param      array  $column  The column
	 *
	 * @return     array  The result.
	 */
	public function getResult($table, $column) {
		if (!$this->table_exists($table)) {
			die("Error Executing Query");
		}
		$conditions = array();

		$sql = "SELECT * FROM " . $table;
		foreach ($column as $key => $value) {
			$conditions[] = $key . " = ?";
			$params[] = $value;
		}
		if (count($conditions)) {
			$sql .= " WHERE " . implode(" AND ", $conditions);
		}
		$result = $this->conn->prepare($sql);

		$ret = $result->execute($params);

		if (!$ret) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $sql;
			die();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$reponse = $result->fetchAll();

		return $reponse;
	}

	/**
	 * Gets all result.
	 *
	 * @param      string  $table_name  The table name
	 *
	 * @return     array  All result.
	 */
	public function getAllResult($table_name) {
		$query = "SELECT * FROM " . $table_name;
		$result = $this->conn->prepare($query);
		$ret = $result->execute();
		if (!$ret) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $query;
			die();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$reponse = $result->fetchAll();

		return $reponse;
	}

	/**
	 * { public function_description }
	 *
	 * @param      string  $query  The query
	 *
	 * @return     string  ( description_of_the_return_value )
	 */
	public function executeQuery($query) {
		$res = $this->conn->query($query);
		if (!$res) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $query;
			die();
		}
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$response = $res->fetchAll();
		return $response;
	}
	private function bindFields($fields) {
		end($fields);
		$lastField = key($fields);
		$bindString = ' ';
		foreach ($fields as $field => $data) {
			$bindString .= $field . '=:' . $field;
			$bindString .= ($field === $lastField ? ' ' : ',');
		}
		return $bindString;
	}

	private function bindFieldsWhere($fields) {
		end($fields);
		$lastField = key($fields);
		$bindString = ' ';
		foreach ($fields as $field => $data) {
			$bindString .= $field . '=:' . $field;
			$bindString .= ($field === $lastField ? ' ' : ' AND ');
		}
		return $bindString;
	}

	/**
	 * insert data
	 *
	 * @param      string  $table_name  The table name
	 * @param      array  $values      The values
	 *
	 * @return     booleab  ( description_of_the_return_value )
	 */
	public function insert($table_name, $values) {
		$query = "INSERT INTO " . $table_name . " SET " . $this->bindFields($values);
		$result = $this->conn->prepare($query);
		$ret = $result->execute($values);
		if (!$ret) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $query;
			die();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		return $this->conn->lastInsertId();
	}

	/**
	 * get data  with where condition
	 *
	 * @param      string   $table_name  The table name
	 * @param      <type>   $values      The values
	 * @param      <type>   $condition   The condition
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function getData($table_name, $condition) {
		$query = "SELECT * FROM " . $table_name . " WHERE " . $this->bindFieldsWhere($condition);
		$params = array();
		foreach ($condition as $key => $value) {
			$params[$key] = $value;
		}
		//$arr = array_merge($values, $params);
		$result = $this->conn->prepare($query);
		$ret = $result->execute($params);
		if (!$ret) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $query;
			die();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$reponse = $result->fetch();
		return $reponse;
	}

	/**
	 * update
	 *
	 * @param      string   $table_name  The table name
	 * @param      <type>   $values      The values
	 * @param      <type>   $condition   The condition
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function update($table_name, $values, $condition) {
		$query = "UPDATE " . $table_name . " SET " . $this->bindFields($values) . "WHERE " . $this->bindFieldsWhere($condition);
		$params = array();
		foreach ($condition as $key => $value) {
			$params[$key] = $value;
		}
		$arr = array_merge($values, $params);
		$result = $this->conn->prepare($query);
		$ret = $result->execute($arr);
		if (!$ret) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $query;
			die();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$reponse = $result->fetch();
		return true;
	}

	/**
	 * { function_description }
	 *
	 * @param      string   $table_name  The table name
	 * @param      <type>   $condition   The condition
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function delete($table_name, $condition) {
		$query = "DELETE FROM  " . $table_name . " WHERE " . $this->bindFieldsWhere($condition);
		$result = $this->conn->prepare($query);
		$ret = $result->execute($condition);
		if (!$ret) {
			echo 'PDO::errorInfo():';
			echo '<br />';
			echo 'error SQL: ' . $query;
			die();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$reponse = $result->fetch();
		return true;
	}

}
?>