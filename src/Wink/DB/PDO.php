<?php
namespace Wink\DB;

class PDO extends \PDO {
    public function __construct ($dsn, $user, $pass) {
        $attributes = array(
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"'
        );

        parent::__construct($dsn, $user, $pass, $attributes);
    }
}