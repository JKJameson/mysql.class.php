<?php
class db {
	static public $db;
	static public $queries = 0;

	static function connect($file) {
		self::$db = new \PDO('sqlite:'.$file, '', '', array(
		    \PDO::ATTR_EMULATE_PREPARES => false,
		    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
		));
		if (!self::$db)
			die('Failed to init database');
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
