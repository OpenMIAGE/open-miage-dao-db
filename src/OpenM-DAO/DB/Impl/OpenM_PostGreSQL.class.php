<?php

Import::php("OpenM-DAO.DB.OpenM_DB");

/**
 * classe de connexion PosGreSql pour SGBD PosGreSql
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB\Impl 
 * @author Gael SAUNIER
 */
class OpenM_PostGreSQL extends OpenM_DB {

    /**
     * @desc constructeur de PostGreSql
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
     * @desc se connecte a la base de donn�e
     * @return bool
     */
    public function connect() {
        if ($this->isConnected())
            return true;
        
        if (!$connexion = @pg_connect("host=" . $this->hostname . " port=" . $this->port . " dbname=" . $this->base_de_donnee . " user=" . $this->username . " password=" . $this->password))
            throw new OpenM_DBException("DB connection error with pg_connect: " . $this->hostname . ":" . $this->port . ", " . $this->username . ", " . $this->password . ", on database " . $this->base_de_donnee);

        $this->connexion = $connexion;
        return $this->isConnected();
    }

    /**
     * @desc envoie une requette et retourne le resultat ou false si erreur
     * @return string
     */
    public function request($request) {
        OpenM_Log::debug("request=$request", __CLASS__, __METHOD__, __LINE__);
        if(!String::isString($request))
            throw new InvalidArgumentException("request must be a string");
        
        if (!$this->connect())
            return false;

        if (!$return = @pg_query($this->connexion, $request))
            throw new OpenM_DBException(pg_errormessage ($this->connexion));

        return $return;
    }


    /**
     * @desc retourne le resultat d'une requette sous forme d'un tableau
     * @return array
     */
    public function fetch_array($result) {
        if(!is_resource($result))
            throw new InvalidArgumentException("result must be a resource");
        
        $return = @pg_fetch_array($result);
        
        return $return;
    }

    /**
     * @desc debranche cette connexion
     * @return void
     */
    public function disconnect() {
        if (is_resource($this->connexion) && $this->isConnected())
            @pg_close($this->connexion);
        $this->connexion = null;
    }

    public function escape($string) {
        return pg_escape_string($this->connexion, $string);
    }

    public function concat($termList) {
        throw new Exception("not implemented");
    }

    public function limit($request, $limit, $offset = null) {
        throw new Exception("not implemented");
    }

}

?>