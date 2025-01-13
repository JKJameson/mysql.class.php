<?php
class db {
	static public $db;
	static public $connected = false;
	static public $queries = 0;
	static public $nextFetchSingle = false;

	static private $host;
	static private $user;
	static private $pass;
	static private $database;
	static function auth($host, $user, $pass, $database) {
		self::$host = $host;
		self::$user = $user;
		self::$pass = $pass;
		self::$database = $database;
	}

	static function connect() {
		$return = true;
		try {
			self::$db = new PDO("mysql:host=".self::$host.";dbname=".self::$database.";charset=utf8mb4", self::$user, self::$pass, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			self::$connected = true;
		} catch (PDOException $e) {
			$return .= str_replace(self::$pass, '********', $e->getMessage());
		}
		return $return;
	}

	// The ping() will try to reconnect once if connection lost.
    static function ping() {
        try {
            @self::$db->query('SELECT 1');
        } catch (PDOException $e) {
            if (!self::$connect()) err('Connection to MySQL database lost');
        }
        return true;
    }

	static function insert($table, $params) {
		self::$queries++;
		if (!self::$connected) self::connect() or err('Failed to connect to database');
		if (!is_array($params)) {
			err('insert(): $params must be an params of keys + values');
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
		if (!self::$connected) self::connect() or err('Failed to connect to database');
		$args = func_get_args();
		$sql = array_shift($args);
		if (self::$nextFetchSingle) {
			self::$nextFetchSingle = false;
			$func = 'fetch';
		} else {
			$func = 'fetchAll';
		}
		if (count($args)==0) {
			try {
				$stmt = self::$db->query($sql);
				$return = $stmt->$func(PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				err('SQL Error: '.$e->getMessage());
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
				$return = $stmt->$func(PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				err('SQL Error: '.$e->getMessage());
			}
			return $return;
		}
	}

	static function q1() {
		$args = func_get_args();
		self::$nextFetchSingle = true;
		return call_user_func_array(['db', 'q'], $args);
	}

	static function count() {
		$args = func_get_args();
		$table = array_shift($args);
		$WHEREquery = array_shift($args);
		$WHEREargs = $args;

		$args = ['SELECT COUNT(*) FROM `'.$table.'`'];
		if ($WHEREquery!=='') {
			$args[0] .= ' WHERE '.$WHEREquery;
			$args = array_merge($args, $WHEREargs);
		}
		$row = call_user_func_array(['db', 'q1'], $args);
		return (int)$row['COUNT(*)'];
	}
}
