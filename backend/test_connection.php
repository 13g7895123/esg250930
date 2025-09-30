<?php
echo 'Testing MySQL connection...'.PHP_EOL;
$mysqli = new mysqli('mysql', 'esg_user', 'esg_pass', 'esg_db', 3306);
if($mysqli->connect_error) {
    echo 'Connection failed: ' . $mysqli->connect_error . PHP_EOL;
} else {
    echo 'Connection successful!' . PHP_EOL;
}
$mysqli->close();