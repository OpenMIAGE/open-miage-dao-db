<?php

Import::php("util.OpenM_Log");

/**
 * Description of SQLException
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB 
 * @author Gael SAUNIER
 */
class OpenM_DBException extends Exception {
    
    public function __construct($message) {
        OpenM_Log::error("$message", __CLASS__, __METHOD__, __LINE__);
        parent::__construct($message);
    }
}
?>