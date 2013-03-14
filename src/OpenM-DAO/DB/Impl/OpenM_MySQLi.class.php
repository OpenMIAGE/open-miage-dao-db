<?php

Import::php("OpenM-DAO.DB.OpenM_DB");

/**
 * classe de connexion MySql sur un SGBD MySql
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB\Impl 
 * @author Gael SAUNIER
 */
class OpenM_MySQLi extends OpenM_DB {

    /**
     * @desc Constructeur de MySql
     * @param base_de_donnee
     * @param hostname
     * @param port
     * @param username
     * @param password
     * @return void 
     */
    public function __construct($host, $port, $bdname, $user, $pass) {
        parent::__construct($host, $port, $bdname, $user, $pass);
    }

    public function connect() {
        if ($this->isConnected())
            return true;

        if (!$connexion = @mysqli_connect($this->hostname, $this->username, $this->password, $this->port))
            throw new OpenM_DBException("DB connection error on: " . $this->hostname . ":" . $this->port, $this->username, $this->password);
        if (!@mysqli_select_db($this->base_de_donnee, $connexion))
            throw new OpenM_DBException("DB connection error on: " . $this->base_de_donnee . "\nWith mysqli_connect(" . $this->hostname . ":" . $this->port, $this->username, $this->password . ")");
        $this->connexion = $connexion;

        return $this->isConnected();
    }

    public function request($request) {
        OpenM_Log::debug("request=$request", __CLASS__, __METHOD__, __LINE__);
        if (!String::isString($request))
            throw new InvalidArgumentException("request must be a string");
        if (!$this->connect())
            return false;
        if (!$return = @mysqli_query($this->connexion, $request))
            throw new OpenM_DBException(mysqli_error($this->connexion));
        return $return;
    }

    public function fetch_array($result) {
        if (!is_resource($result))
            throw new InvalidArgumentException("result must be a resource");

        $return = @mysqli_fetch_array($result);
        return $return;
    }

    /**
     * @desc debranche cette connexion 
     * @return void
     */
    public function disconnect() {
        if (is_resource($this->connexion) && $this->isConnected())
            @mysqli_close($this->connexion);
        $this->connexion = null;
    }

    public function escape($string) {
        return mysqli_escape_string($this->connexion, $string);
    }

    public function concat($termList) {
        throw new Exception("not implemented");
    }

    public function limit($request, $limit, $offset = null) {
        throw new Exception("not implemented");
    }

}

?>