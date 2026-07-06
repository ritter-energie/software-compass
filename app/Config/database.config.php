<?php

declare(strict_types=1);

/*
 * Database connection configuration.
 *
 * Software Compass stores all data exclusively in MariaDB (see the project
 * project requirements. Tempest's `MysqlConfig` speaks the MySQL wire
 * protocol, which MariaDB is fully compatible with.
 *
 * All values are sourced from the environment so that no credentials are
 * ever hard-coded in the repository.
 */

use Tempest\Database\Config\MysqlConfig;

use function Tempest\env;

return new MysqlConfig(
    host: env('DB_HOST', 'localhost'),
    port: env('DB_PORT', '3306'),
    username: env('DB_USERNAME', 'software_compass'),
    password: env('DB_PASSWORD', ''),
    database: env('DB_DATABASE', 'software_compass'),
);


