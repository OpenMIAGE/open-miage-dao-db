<?php

Import::php("OpenM-DAO.DB.OpenM_DBException");

/**
 * Description of OpenM_DB_Sequence
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB 
 * @author Nicolas Rouzeaud & Gaël SAUNIER
 */
class OpenM_DB_Sequence {

    private $countFile;

    public function __construct($path) {
        if (!is_file($path))
            throw new InvalidArgumentException("path must be a valid file path");

        $this->countFile = realpath($path);
    }

    /**
     * Fait +1 à la sequence
     * @return int
     * @throws OpenM_DBException
     * @throws OpenM_ServiceImplException
     */
    public function next() {
        $handle = fopen($this->countFile, "r+b");
        if ($handle === false)
            throw new OpenM_DBException($this->countFile. " not found");
        if (flock($handle, LOCK_EX)) {
            $size = filesize($this->countFile);
            $count = fread($handle, $size);
            if ($count === false)
                throw new OpenM_DBException("count number in " . $this->countFile . " not found");
            $count = (int) $count;
            $count++;
            if (fseek($handle, 0) === false)
                throw new OpenM_DBException("internal error during link move in " . $this->countFile);
            if (fwrite($handle, $count) === false)
                throw new OpenM_DBException($this->countFile . " not writable");
            if (fclose($handle) === false)
                throw new OpenM_DBException("internal error during closing file " . $this->countFile);
            OpenM_Log::debug("New id : $count", __CLASS__, __METHOD__, __LINE__);
            return $count;
        }
        else
            throw new OpenM_ServiceImplException("lock " . $this->countFile . " is not possible");
    }
    
    
    /**
     * Fait -1 à la sequence
     * @return int
     * @throws OpenM_DBException
     * @throws OpenM_ServiceImplException
     */
    public function before(){
        $handle = fopen($this->countFile, "r+b");
        if ($handle === false)
            throw new OpenM_DBException($this->countFile. " not found");
        if (flock($handle, LOCK_EX)) {
            $size = filesize($this->countFile);
            $count = fread($handle, $size);
            if ($count === false)
                throw new OpenM_DBException("count number in " . $this->countFile . " not found");
            $count = (int) $count;
            $count--;
            if (fseek($handle, 0) === false)
                throw new OpenM_DBException("internal error during link move in " . $this->countFile);
            if (fwrite($handle, $count) === false)
                throw new OpenM_DBException($this->countFile . " not writable");
            if (fclose($handle) === false)
                throw new OpenM_DBException("internal error during closing file " . $this->countFile);
            OpenM_Log::debug("New id : $count", __CLASS__, __METHOD__, __LINE__);
            return $count;
        }
        else
            throw new OpenM_ServiceImplException("lock " . $this->countFile . " is not possible"); 
    }

    public static function create($path, $initialCount=0) {
        
    }

}

?>