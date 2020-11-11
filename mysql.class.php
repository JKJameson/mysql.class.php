<?php
class db {
	static public $db;
	static public $queries = 0;

	static private $host;
	static private $user;
	static private $pass;
	static private $database;
	static function connect($host, $user, $pass, $database) {
		self::$host = $host;
		self::$user = $user;
		self::$pass = $pass;
		self::$database = $database;
		$return = true;
		try {
			self::$db = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $pass, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		} catch (PDOException $e) {
			$return .= str_replace(self::$pass, '********', $e->getMessage());
		}
		unset($GLOBALS['db_info']); // Prevent leaking database credentials
		return $return;
	}

	// The ping() will try to reconnect once if connection lost.
    static function ping() {
        try {
            @self::$db->query('SELECT 1');
        } catch (PDOException $e) {
            if (!self::$connect(self::$host, self::$user, self::$pass, self::$database)) {
            	die('Connection to MySQL database lost');
			}
        }
        return true;
    }

	static function insert($table, $params) {
		self::$queries++;
		if (!is_array($params)) {
			fatal_error('insert(): $params must be an params of keys + values');
		}

		$sql = 'INSERT INTO `'.$table.'` (';
		foreach($params as $k => $v) {
			$sql .= '`'.$k.'`, ';
		}
		$sql = substr($sql, 0, -2);

		$sql .= ') VALUES (';
		$args = [];
		foreach($params as $k => $v) {
			$sql .= '?, ';
			$args[] = $v;
		}
		$sql = substr($sql, 0, -2);
		$sql .= ')';
		
		$stmt = self::$db->prepare($sql);
		foreach($args as $k => $v)
			if ($v===null)
				$args[$k] = '';
		$stmt->execute($args);
		return self::$db->lastInsertId();
	}

	static function q() {
		self::$queries++;
		$args = func_get_args();
		$sql = array_shift($args);
		if (count($args)==0) {
			try {
				$stmt = self::$db->query($sql);
				$return = $stmt->fetchAll(PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				fatal_error('SQL Error: '.$e->getMessage());
			}
			return $return;
		} else {
			if (is_array($args[0]))
				$args = $args[0];
			foreach($args as $k => $v)
				if ($v===null)
					$args[$k] = '';
			try {
				$stmt = self::$db->prepare($sql);
				$stmt->execute($args);
				$return = $stmt->fetchAll(PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				fatal_error('SQL Error: '.$e->getMessage());
			}
			return $return;
		}
	}
}
