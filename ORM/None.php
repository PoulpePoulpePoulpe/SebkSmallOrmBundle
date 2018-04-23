<?php

namespace Sebk\SmallOrmBundle\ORM;

/**
 * Class None
 * @package Sebk\SmallOrmBundle\ORM
 *
 * This class is used when you don't need to connect to the Database. The main use case is when you need to
 * do some unit tests.
 * This will prevent PDO to fail for every request.
 *
 * You can also use it to overwrite PDO methods to get any result.
 */
class None
{
    /**
     * Generic prepare method
     *
     * @param mixed $arguments  Arguments, can be anything
     *
     * @return None $this   Return self object to allow other call
     */
    public function prepare($arguments)
    {
        return $this;
    }

    /**
     * This is a catch-all method in order to get a predictable PDO error.
     *
     * @param string    $name
     * @param mixed     $arguments
     *
     * @throws \PDOException
     */
    public function __call($name, $arguments)
    {
        throw new \PDOException('None DB Exception: ' . $name . ' does not exist.');
    }
}