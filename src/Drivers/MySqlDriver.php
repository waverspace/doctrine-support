<?php

namespace Larapack\DoctrineSupport\Drivers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Illuminate\Database\PDO\Concerns\ConnectsToDatabase;

class MySqlDriver extends Driver
{
    use ConnectsToDatabase;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_mysql';
    }
}
