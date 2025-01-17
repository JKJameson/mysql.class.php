# mysql.class.php
MySQL class for PHP to make queries safe and fast to write.


# How to use
```
<?php
require_once 'mysql.class.php';

/*
  Database Connection Info
*/
$db_info = [
  'host' => '127.0.0.1',
  'user' => 'database_username',
  'pass' => 'database_password',
  'name' => 'database_name',
];

/*
  Auth to Database (MySQL)
*/
db::auth($db_info['host'], $db_info['user'], $db_info['pass'], $db_info['name']);
unset($db_info); // Prevent leaking database credentials

/*
  INSERT a row
*/
$id = db::insert('table_name', [
  'column_a' => 'value 1',
  'column_b' => 'value 2',
  'column_c' => 'value 3',
]);
echo "INSERTed row {$id} into table_name \n<br>";

/*
  SELECT multiple rows
*/
foreach (db::q('SELECT * FROM `table_name`') as $row) {
  echo "SELECT multiple rows: Found row with ID {$row['id']} and the value is {$row['column_a']} \n<br>";
}

/*
  SELECT a single row (this is signified as a 1 after the q)
*/
$row = db::q1('SELECT * FROM `table_name` LIMIT 1');
echo "SELECT a single row: Found row with ID {$row['id']} and the value is {$row['column_a']} \n<br>";

/*
  SELECT multiple rows (with safe variables)
*/
$search_a = 'value 1';
$search_b = 'value 2';
foreach (db::q('SELECT * FROM `table_name` WHERE `column_a` = ? AND `column_b` = ?', $search_a, $search_b) as $row) {
  echo "SELECT multiple rows: Found row with ID {$row['id']} and the value is {$row['column_a']} \n<br>";
}

/*
  SELECT a single row  (with safe variables)
*/
$search_a = 'value 1';
$search_b = 'value 2';
$row = db::q1('SELECT * FROM `table_name` WHERE `column_a` = ? AND `column_b` = ? LIMIT 1', $search_a, $search_b);
echo "SELECT a single row: Found row with ID {$row['id']} and the value is {$row['column_a']} \n<br>";

/*
  COUNT rows
*/
$count = db::count('users', " `level` = 'admin' AND `active` = 1 ");
echo "Found $count rows \n<br>";

/*
  COUNT rows  (with safe variables)
*/
$search_a = 'value 1';
$search_b = 'value 2';
$count = db::count('table_name', ' `column_a` = ? AND  `column_b` = ? ', $search_a, $search_b);
echo "Found $count rows \n<br>";

```
