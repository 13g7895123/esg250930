<?php
require_once 'app/Config/Database.php';

echo 'Testing Database Configuration...' . PHP_EOL;

$config = new \Config\Database();
echo 'Default config:' . PHP_EOL;
print_r($config->default);

echo PHP_EOL . 'Environment: ' . ENVIRONMENT . PHP_EOL;
echo 'Testing database connection...' . PHP_EOL;

try {
    $db = \Config\Database::connect();
    $query = $db->query('SELECT 1');
    echo 'Database connection successful!' . PHP_EOL;
} catch (Exception $e) {
    echo 'Database connection failed: ' . $e->getMessage() . PHP_EOL;
}