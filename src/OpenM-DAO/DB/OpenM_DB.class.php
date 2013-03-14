<?php

Import::php("OpenM-DAO.DB.OpenM_DBException");
Import::php('util.HashtableString');
Import::php('util.ArrayList');
Import::php("util.OpenM_Log");

/**
 * classe de connexion OpenM_DB
 * @package OpenM 
 * @subpackage OpenM\OpenM-DAO\DB 
 * @author Gael SAUNIER
 */
abstract class OpenM_DB {

    /**
     * @var (String)
     * @desc nom de la base de donnée
     */
    protected $base_de_donnee;

    /**
     * @var (String)
     * @desc le nom du serveur
     */
    protected $hostname;

    /**
     * @var (String)
     * @desc le port du serveur
     */
    protected $port;

    /**
     * @var (String)
     * @desc nom de l'utilisateur
     */
    protected $username;

    /**
     * @var (String)
     * @desc pass de l'utilisateur
     */
    protected $password;

    /**
     * @var (pointeur)
     * @desc connexion sql
     */
    protected $connexion = null;

    /**
     * @desc constructeur de Sql
     * @param base_de_donnee
     * @param hostname
     * @param port
     * @param username
     * @param password
     * @return void 
     */
    public function __construct($host, $port, $bdname, $user, $pass) {
        if (!is_string($host))
            throw new InvalidArgumentException("le premier argument de " . $this->getClassName() . " doit être un String (hostname)");
        $this->hostname = $host;
        if (!is_string($port) && !is_int($port) && $port != null)
            throw new InvalidArgumentException("le deuxieme argument de " . $this->getClassName() . " doit �tre un String/int/null (port)");
        $this->port = $port;
        if (!is_string($bdname))
            throw new InvalidArgumentException("le troisi�me argument de " . $this->getClassName() . " doit �tre un String\n(nom de la base de donn�e)");
        $this->base_de_donnee = $bdname;
        if (!is_string($bdname))
            throw new InvalidArgumentException("le quatri�me argument de " . $this->getClassName() . " doit �tre un String\n(nom d'utilisateur de la base de donn�e)");
        $this->username = $user;
        if (!is_string($bdname))
            throw new InvalidArgumentException("le cinquieme argument de " . $this->getClassName() . " doit �tre un String\n(mot de passe de l'utilisateur de la base de donn�e)");
        $this->password = $pass;

        OpenM_Log::debug("connection $host:$port $bdname, $user, ***", __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * @desc se connecte a la base de donnée 
     * @name Sql::connect()
     * @return bool
     */
    public abstract function connect();

    public abstract function escape($string);

    /**
     * @desc retourne true si la connexion est établie
     * @name Sql::isConnected()
     * @return bool
     */
    public function isConnected() {
        return is_resource($this->connexion);
    }

    /**
     * @desc envoie une requette retourne le resultat ou false si erreur
     * @name Sql::request()
     * @return string
     */
    public abstract function request($request);

    public static function select($table, $where = null, $scope = null) {
        if (!String::isString($table))
            throw new InvalidArgumentException("table must be a string");
        if (!self::isValid($table))
            throw new InvalidArgumentException("table is not a valid table name");
        if (!ArrayList::isArrayOrNull($scope))
            throw new InvalidArgumentException("scope must be an array or an ArrayList");
        if ($scope instanceof ArrayList)
            $scope = $scope->toArray();
        if (!ArrayList::isArrayOrNull($where))
            throw new InvalidArgumentException("where must be an array or an ArrayList");
        if ($where instanceof ArrayList)
            $where = $where->toArray();

        $request = "SELECT";
        if ($scope == null)
            $request .= " * ";
        else {
            foreach ($scope as $value)
                $request .= " $value,";
            if (sizeof($scope) > 0)
                $request = substr($request, 0, -1) . " ";
        }

        $request .= "FROM $table";

        if ($where != null && sizeof($where) > 0) {
            $request .= " WHERE ";
            foreach ($where as $key => $value) {
                if (!self::isValid($key))
                    throw new InvalidArgumentException("all where keys must be valid");
                $request .= "$key=" . (is_numeric($value) ? "$value" : "'$value'") . " AND ";
            }
            $request = substr($request, 0, -5);
        }

        OpenM_Log::debug("select=$request", __CLASS__, __METHOD__, __LINE__);
        return $request;
    }

    public static function insert($table, $values) {
        if (!String::isString($table))
            throw new InvalidArgumentException("table must be a string");

        if (!self::isValid($table))
            throw new InvalidArgumentException("table is not a valid table name");

        $request = "INSERT INTO $table";

        if (!ArrayList::isArrayOrNull($values))
            throw new InvalidArgumentException("values must be an array or an ArrayList");

        if ($values instanceof ArrayList)
            $values = $values->toArray();

        if (sizeof($values) == 0)
            throw new InvalidArgumentException("values must be an array that containt at least one value");

        $request .= " (";
        foreach ($values as $key => $value) {
            if (!self::isValid($key))
                throw new InvalidArgumentException("all where keys must be valid");
            $request .= " $key, ";
        }
        $request = substr($request, 0, -2) . ") ";

        $request .= " VALUES (";
        foreach ($values as $value) {
            $request .= (is_numeric($value) ? "$value" : "'$value'") . ", ";
        }
        $request = substr($request, 0, -2) . ") ";
        OpenM_Log::debug("insert=$request", __CLASS__, __METHOD__, __LINE__);
        return $request;
    }

    public static function delete($table, $where = null) {
        if (!String::isString($table))
            throw new InvalidArgumentException("table must be a string");

        if (!self::isValid($table))
            throw new InvalidArgumentException("table is not a valid table name");

        $request = "DELETE FROM $table";

        if (!ArrayList::isArrayOrNull($where))
            throw new InvalidArgumentException("values must be an array or an ArrayList");

        if ($where instanceof ArrayList)
            $where = $where->toArray();

        if ($where != null && sizeof($where) > 0) {
            $request .= " WHERE";
            foreach ($where as $key => $value) {
                $request .= " $key=" . (is_numeric($value) ? "$value" : "'$value'") . " AND ";
            }
            $request = substr($request, 0, -5);
        }
        OpenM_Log::debug("delete=$request", __CLASS__, __METHOD__, __LINE__);
        return $request;
    }

    private static function isValid($value) {
        if (RegExp::ereg("^([a-zA-Z0-9]|_)+$", $value))
            return true;

        return false;
    }

    /**
     * Crée un requete sql d'update SIMPLE selon les parametres
     * @param String $table
     * @param array $setVal
     * @param array $where
     * @throws InvalidArgumentException
     */
    public static function update($table, $setVal = NULL, $where = NULL) {
        if (!String::isString($table))
            throw new InvalidArgumentException("table must be a string");

        if (!self::isValid($table))
            throw new InvalidArgumentException("table is not a valid table name");

        $request = "UPDATE $table";

        if (!ArrayList::isArrayOrNull($setVal))
            throw new InvalidArgumentException("values to set must be an array or an ArrayList");

        if ($setVal instanceof ArrayList)
            $setVal = $setVal->toArray();

        if ($setVal != null && sizeof($setVal) > 0) {
            $request .= " SET ";
            foreach ($setVal as $key => $value) {
                $request .= " $key=" . (is_numeric($value) ? "$value" : "'$value'") . " ,";
            }
            $request = substr($request, 0, -2);
        }

        if (!ArrayList::isArrayOrNull($where))
            throw new InvalidArgumentException("values must be an array or an ArrayList");

        if ($where instanceof ArrayList)
            $where = $where->toArray();

        if ($where != null && sizeof($where) > 0) {
            $request .= " WHERE";
            foreach ($where as $key => $value) {
                $request .= " $key=" . (is_numeric($value) ? "$value" : "'$value'") . " AND ";
            }
            $request = substr($request, 0, -5);
        }
        OpenM_Log::debug("update=$request", __CLASS__, __METHOD__, __LINE__);
        return $request;
    }

    /**
     * @desc envoie une requette et retourne le resultat
     * sous forme de tableau ou false si erreur
     * @return array
     */
    public function request_fetch_array($request) {
        return $this->fetch_array($this->request($request));
    }

    /**
     * 
     * @param type $request
     * @return HashtableString
     */
    public function request_fetch_HashtableString($request) {
        $return = $this->request_fetch_array($request);
        if (!$return)
            return null;
        else
            return HashtableString::from($return);
    }

    /**
     * @desc retourne le resultat d'une requette sous forme d'un tableau
     * @param ressource $result
     * @return array
     */
    public abstract function fetch_array($result);

    /**
     * @desc debranche cette connexion 
     * @name Sql::deconnexion()
     * @return string
     */
    public abstract function disconnect();

    /**
     * @desc 
     * d'un Array d'HashtableString
     * @param $request
     * @return ArrayList<HashtableString<String>>
     */
    public function request_ArrayList($request) {
        $res = $this->request($request);
        $return = new ArrayList("HashtableString");
        while ($row = $this->fetch_array($res)) {
            $return->add(HashtableString::from($row, "String"));
        }
        return $return;
    }

    /**
     * @desc m�thode permetant
     * de r�cup�rer le r�sultat sous la forme
     * d'un HashtableString d'HashtableString
     * sur une cl� unique pass� en param�tre
     * @param $request
     * @return HashtableString<HashtableString<String>>
     */
    public function request_HashtableString($request, $uniqueKey = null) {
        if ($uniqueKey !== null && !String::isString($uniqueKey))
            throw new InvalidArgumentException("la méthode request_HashtableString de " . $this->getClassName() . " prend un string en 2�me param�tre");

        if ($uniqueKey === null)
            return HashtableString::from($this->request_fetch_array($request));

        $res = $this->request($request);
        $return = new HashtableString("HashtableString");
        while ($row = $this->fetch_array($res)) {    
            $ligne = HashtableString::from($row, "String");
            $return->put($ligne->get($uniqueKey), $ligne);
        }
        return $return;
    }

    /**
     * @desc méthode permetant
     * de récupérer le résultat sous la forme
     * d'un HashtableString d'HashtableString
     * group by sur une clé passé en paramètre
     * @param $request
     * @return HashtableString<HashtableString<String>>
     */
    public function request_HashtableString_groupBy($request, $groupBy) {
        if (!String::isString($groupBy))
            throw new InvalidArgumentException("la méthode request_HashtableString_groupBy de " . $this->getClassName() . " prend un string en 2�me param�tre");

        $res = $this->request($request);
        $return = new HashtableString("ArrayList");
        while ($row = $this->fetch_array($res)) {
            $ligne = HashtableString::from($row, "String");
            $liste = $return->get($ligne->get($groupBy));
            if ($liste == null) {
                $liste = new ArrayList("HashtableString");
                $return->put($ligne->get($groupBy), $liste);
            }
            $liste->add($ligne);
        }
        return $return;
    }

    /**
     * @desc destructeur de Sql
     * @return void
     */
    public function __destruct() {
        $this->disconnect();
    }

    public abstract function concat($termList);

    public abstract function limit($request, $limit, $offset = null);
}

?>