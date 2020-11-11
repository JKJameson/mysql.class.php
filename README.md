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
  Connect to Database
*/
$conn = db::connect($db_info['host'], $db_info['user'], $db_info['pass'], $db_info['name']);
if ($conn!==true)
  die('Database connection error: '.$conn);	
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
  SELECT a single row
*/
$row = db::q('SELECT * FROM `table_name` LIMIT 1')[0];
echo "SELECT a single row: Found row with ID {$row['id']} and the value is {$row['column_a']} \n<br>";



```
