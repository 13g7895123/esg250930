<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => '127.0.0.1',  // 使用 IP 地址而非 localhost
        'username'     => 'esg_user',
        'password'     => 'esg_pass',
        'database'     => 'esg_db',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_unicode_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,  // MySQL 預設端口
        'numberNative' => false,
        'socketPath'   => '',
    ];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => 'localhost',
        'username'    => 'esg_user',
        'password'    => 'esg_pass',
        'database'    => 'esg_db_test',
        'DBDriver'    => 'MySQLi',
        'DBPrefix'    => '',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8mb4',
        'DBCollat'    => 'utf8mb4_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Override default config with environment variables if available
        if (getenv('DB_HOSTNAME')) {
            $this->default['hostname'] = getenv('DB_HOSTNAME');
        }
        if (getenv('DB_USERNAME')) {
            $this->default['username'] = getenv('DB_USERNAME');
        }
        if (getenv('DB_PASSWORD')) {
            $this->default['password'] = getenv('DB_PASSWORD');
        }
        if (getenv('DB_DATABASE')) {
            $this->default['database'] = getenv('DB_DATABASE');
        }

        // Apply same environment variables to tests config
        if (getenv('DB_HOSTNAME')) {
            $this->tests['hostname'] = getenv('DB_HOSTNAME');
        }
        if (getenv('DB_USERNAME')) {
            $this->tests['username'] = getenv('DB_USERNAME');
        }
        if (getenv('DB_PASSWORD')) {
            $this->tests['password'] = getenv('DB_PASSWORD');
        }
        if (getenv('DB_DATABASE')) {
            $this->tests['database'] = getenv('DB_DATABASE') . '_test';
        }

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
