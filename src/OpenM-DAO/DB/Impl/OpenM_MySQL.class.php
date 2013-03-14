<?php

Import::php("OpenM-DAO.DB.OpenM_DB");

/**
 * classe de connexion MySql sur un SGBD MySql
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB\Impl 
 * @author Gael SAUNIER
 */
class OpenM_MySQL extends OpenM_DB {

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

    /**
     * @desc permet de se connecter a la base de donn�e 
     * @return bool
     */
    public function connect() {
        if ($this->isConnected())
            return true;

        if (!$connexion = @mysql_connect($this->hostname . ":" . $this->port, $this->username, $this->password))
            throw new OpenM_DBException("DB connection error on: " . $this->hostname . ":" . $this->port, $this->username, $this->password);
        if (!@mysql_select_db($this->base_de_donnee, $connexion))
            throw new OpenM_DBException("DB connection error on: " . $this->base_de_donnee . "\nWith mysql_connect(" . $this->hostname . ":" . $this->port, $this->username, $this->password . ")");
        $this->connexion = $connexion;

        return $this->isConnected();
    }

    /**
     * @desc envoie une requette retourne le resultat ou false si erreur
     * @return string
     */
    public function request($request) {
        OpenM_Log::debug("request=$request", __CLASS__, __METHOD__, __LINE__);
        if (!String::isString($request))
            throw new InvalidArgumentException("request must be a string");
        if (!$this->connect())
            return false;
        if (!$return = @mysql_query($request, $this->connexion))
            throw new OpenM_DBException(mysql_error($this->connexion));
        return $return;
    }

    public function fetch_array($result) {
        if (!is_resource($result))
            throw new InvalidArgumentException("result must be a resource");

        $return = @mysql_fetch_array($result);
        return $return;
    }

    /**
     * @desc debranche cette connexion 
     * @return void
     */
    public function disconnect() {
        if (is_resource($this->connexion) && $this->isConnected())
            @mysql_close($this->connexion);
        $this->connexion = null;
    }

    public function escape($string) {
        return mysql_escape_string($string);
    }

    public function concat($termList) {
        $return = "CONCAT(";
        foreach ($termList as $value)
            $return .= "$value, ";

        if (sizeof($termList) > 0)
            $return = substr($return, 0, -2);

        return $return . ")";
    }

    public function limit($request, $limit, $offset = null) {
        return "SELECT * FROM ($request) l LIMIT ".(($offset!=null)?"$offset,":"")." $limit";
    }

}

?>