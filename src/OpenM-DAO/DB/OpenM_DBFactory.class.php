<?php
Import::php("OpenM-DAO.DB.OpenM_DB");
Import::php("util.Properties");

/**
 * Description of OpenM_DBFactory
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB 
 * @author Gaël Saunier
 */
class OpenM_DBFactory {

    const MySQL = "OpenM_MySQL";
    const MySQLi = "OpenM_MySQLi";
    const PostGreSQL = "OpenM_PostGreSQL";
    
    const OpenM_BD_TYPE = "OpenM_BD.type";
    const OpenM_BD_HOST = "OpenM_BD.host";
    const OpenM_BD_PORT = "OpenM_BD.port";
    const OpenM_BD_BD_NAME = "OpenM_BD.bd.name";
    const OpenM_BD_BD_LOGIN = "OpenM_BD.bd.login";
    const OpenM_BD_BD_PASSWORD = "OpenM_BD.bd.password";
    
    public function create($type, $host, $port, $bdname, $bduser, $bdpass) {
        if(!String::isString($type))
            throw new InvalidArgumentException("type must be a string");
        if(!String::isString($host))
            throw new InvalidArgumentException("host must be a string");
        if(!String::isStringOrNull($port))
            throw new InvalidArgumentException("port must be a string");
        if(!String::isString($bdname))
            throw new InvalidArgumentException("bdname must be a string");
        if(!String::isString($bduser))
            throw new InvalidArgumentException("bduser must be a string");
        if(!String::isString($bdpass))
            throw new InvalidArgumentException("bdpass must be a string");
        if($type instanceof String)
            $type .= "";
        if($host instanceof String)
            $host .= "";
        if($port instanceof String)
            $port .= "";
        if($bdname instanceof String)
            $bdname .= "";
        if($bduser instanceof String)
            $bduser .= "";
        if($bdpass instanceof String)
            $bdpass .= "";
        if (!$this->isValidType($type))
            throw new InvalidArgumentException("type must be a predefined type of OpenM_BD");

        Import::php("OpenM-DAO.DB.Impl.$type");
        return new $type($host, $port, $bdname, $bduser, $bdpass);
    }

    public function getTypes() {
        return array(
            "MySQL" => self::MySQL,
            "MySQLi" => self::MySQLi,
            "PostGreSQL" => self::PostGreSQL,
        );
    }

    public function isValidType($type) {
        if (!String::isString($type))
            throw new InvalidArgumentException("type must be a string");

        return in_array($type, $this->getTypes());
    }
    
    public function createFromProperties($propertyFilePath){
        if(!String::isString($propertyFilePath))
            throw new InvalidArgumentException("propertyFilePath must be a string");
        $property = Properties::fromFile($propertyFilePath);
        return $this->create($property->get(self::OpenM_BD_TYPE), $property->get(self::OpenM_BD_HOST), $property->get("OpenM_BD.port"), $property->get(self::OpenM_BD_BD_NAME), $property->get(self::OpenM_BD_BD_LOGIN), $property->get(self::OpenM_BD_BD_PASSWORD));
    }
}
?>