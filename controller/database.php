<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */

class Database
{

    var $db;

    public function __construct()
    {
        if (database_used == true) {
            try {
                $this->db = new PDO("mysql:host=" . database_host . ";dbname=" . database_name . ";charset=" . database_charset,
                    database_user, database_pass, array(
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => true,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ));
            } catch (PDOException $e) {
                $this->db = NULL;
                die($e->getMessage());
            }
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->db = null;
    }
}