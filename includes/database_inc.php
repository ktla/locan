<?php
/********************************************************************
			CLASSE DATABASE IMPLEMENT DE INTERFACE connectable
			FAIT LA CONNECTION A LA DATABASE AND 
			TRIGGER ANY RETRIEVING AND UPDATE		
**********************************************************************/
/**
	function utiliser pour lire le fichier
	xml contenant les configurations de la bd
*/
function xmlRead($xml){
	$arbre = NULL;
	while ($xml->read()){
		if($xml->nodeType == XMLReader::END_ELEMENT)
			return $arbre;
		else if($xml->nodeType == XMLReader::ELEMENT){
			$noeud = array();
			$noeud['noeud'] = $xml->name;
			if(!$xml->isEmptyElement){
				$fils = xmlRead($xml);
				$noeud['fils'] = $fils;
			}
			$arbre[] = $noeud;
		}else if($xml->nodeType == XMLReader::TEXT){
			$noeud = array();
			$noeud['text'] = $xml->value;
			$arbre[] = $noeud;	
		}
	}
	return $arbre;
}
/**
	Transforme le tree en tableau associatif
*/
function xmlTabAssoc($tree){
	$assoc = new ArrayObject();
	for($i=0;$i<count($tree[0]['fils']);$i++){
		$assoc[$tree[0]['fils'][$i]['noeud']] = $tree[0]['fils'][$i]['fils'][0]['text'];
	}		
	return $assoc;
}
/**
	Creer les variables de connexions
	En lisant le fichier xml
*/
if(!defined("HOST") && !defined("DBNAME")){
	$xml = new XMLReader();
	$xml->open("../configurations/config.xml");
	$tree = xmlRead($xml);
	$xml->close();
	$assoc = xmlTabAssoc($tree);
	/** affecter les valeurs */
	define("HOST", $assoc['host']);
	define("DBNAME", $assoc['bd']);
	define("DBUSER", $assoc['user']);
	//define("DBPWD", $assoc['password']);
	define("DBPWD", ""); /** pour le moment je laisse a vide parceque j'ai annule l' execution de la requete de modification dans install_inc */
}
/**
	Class that define what is a row in data object returned
	This is class is used in the class Database
*/

function encode($str){
	if(isset($str)){
		$str = trim($str);	
		$str = addslashes($str);
		$str = utf8_encode($str);
	}	
	return $str;
}
function decode($str){
	if(isset($str)){		
		$str = stripslashes($str);
		$str = utf8_decode($str);
	}	
	return $str;
}
class Row{
	/* A unique id identifying the row */
	private $id;
	/* The row containing a set of cell */
	private $row;
	/* Activate or not the debug mode */
	public $debug = FALSE;
	/* Le nombre de colonne */
	public $length;
	/* The constructor of the class */
	function __construct($id, $row){
		$this->id = $id;
		$this->row = $row;
		$this->length = count($row);
	}
	function getId(){
		return $this->id;
	}
	/**
		param $i position of the element
		$show what to do when the element does not exist : return an error or handle it
		Return the ith item of the row
	*/
	public function item($i = 0, $show = TRUE){
		if($this->debug){
			print_r($this->row);
		}
		if(isset($this->row[$i]))
			return decode($this->row[$i]);
		if(array_key_exists($i, $this->row)){
			if($this->row[$i] == NULL)
				return "NULL";
			else
				return "Undefined";
		}elseif($show)
			return "Unknown cell $i";
	}
	/**
		Return the length of the row = number of the cells in the row
	*/
	public function length(){
		return count($this->row);
	}
	/**
		Print the element in the ith position
	*/
	public function _print($i){
		if(isset($this->row[$i]))
			print $this->row[$i];
	}
}
final class Database{
	/**
		PROPERTIES OR ATTRIBUTES OF THE CLASS
	*/
	private $pdo;
	/* Tableau contenant les elements a defini par PDO */
	private $param;
	/* Nombre d'enregistrement renvoyer par un select ou le nombre */
	public $length;
	/* The source of the query. It is the string of query */
	private $source;
	/* The result of the execution of the query */
	private $result;
	/* The Data object that contain the resultset of the query */
	public $data;
	/* The number that refers to one column of the query stated as the principale key */
	private $_key;
	/* Log all process */
	private $_log;
	/* Activate or not the debug mode */
	public $debug = false;
	/* Nb ligne affected par le update ou insert */
	public $affectedRows;
	/* Contient le resultat de la requete select */
	private $resultSet;
	/**
		METHODES OF THE CLASS
	*/
	/**
		Function static utiliser par les classes qui effectue
		une connexion a la bd independant de cette classe
		Param $dbunknown : precise si le db est inconnu, utile pour la creation
		du bd. Pour creer une nouvelle bd, mettre $dbunknown a true
	*/
	public static function connect2db(){
		/* Renvoi une exception si tout va mal */
		try{
			$pdo_options = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
			/** connection informations 
			*	utiliser les variable defined pour ne pas relire 
			*	le fichier xml chaque fois, confere debut de la page
			*/
			/* Ouvrir la connexion */
			$db = new PDO("mysql:host=".HOST.";dbname=".DBNAME, DBUSER, DBPWD, $pdo_options);
			/*return $db;*/
			$db->query("SET NAMES 'utf8'");
			return $db;
		}catch(PDOException $ex){
			var_dump($ex->getTrace());
			die("Connection Error : ".$ex->getMessage()."<br/> Fichier ".$ex->getFile()." Ligne ".$ex->getLine());	
		}
	}
	/**
		The constructor of the class, make the connection and execute the query
		@param : query = the string of the query,
			param = les paramettre sous forme de tableau associatif
			defini par array();
	*/
	function __construct($query = "", $id = 0,  $param = array()){
		/* Ouvre la connexion a la bd */
		$this->pdo = Database::connect2db();
		$this->source = $query;
		/* By default, the first column in the query string is the principal _key of the string */
		$this->_key = $id;
		$this->param = $param;
		/* Definir un tableau qui va stocker les log pour debogage */
		$this->_log = new ArrayObject();
		$this->_log['query'] = $this->source;
		$this->_log['pdo_param'] = $this->param;
	}
	/**
		Bind les parametre passer a l'objet PDO
	*/
	function setParam($arr){
		$this->param = $arr;
		$this->_log['param'] = $this->param;
	}
	/**
		Function uses to execute a select statement
		Type of the request is SELECT
	*/
	function select(){
		try{
			$this->resultSet = $this->pdo->prepare($this->source);
			if(is_array($this->param)){
				if(count($this->param)){
					$this->resultSet->execute($this->param);
				}else
					$this->resultSet->execute();
			}else
				$this->resultSet->execute();
			$this->length = $this->resultSet->rowCount();
			$this->affectedRows = $this->resultSet->rowCount();
			if(!$this->length){
				$this->_log['error'] = "PAS D'ENREGISTREMENT";
				return true;
			}
			/* If everything is okay load the resultset into the data object */
			$i = 0;
			$this->data = new ArrayObject();
			while($line = $this->resultSet->fetch(PDO::FETCH_BOTH)){
				$row = new Row($i, $line);
				$this->data->append($row);
				$i++;
			}
			return true;
		}catch(PDOException $ex){
			var_dump($ex->getTrace());
			$this->_log['error'] = "Error select : ".$ex->getMessage()."<br/> Fichier ".$ex->getFile()." Ligne ".$ex->getLine();	
			return false;
		}
	}
	/** Function identique a select sof que ca ne renvoi pas l'objet data 
		Pour parourir les donnees, utilser while($row = $db->fetch_xxx()))
		Identique au parcourt de mysql_fetch_xxx
	*/
	function query(){
		try{
			$this->resultSet = $this->pdo->prepare($this->source);
			if(is_array($this->param)){
				if(count($this->param)){
					$this->resultSet->execute($this->param);
				}else
					$this->resultSet->execute();
			}else
				$this->resultSet->execute();
			$this->length = $this->resultSet->rowCount();
			$this->affectedRows = $this->resultSet->rowCount();
			return true;
		}catch(PDOException $ex){
			var_dump($ex->getTrace());
			$this->_log['error'] = "Error select : ".$ex->getMessage()."<br/> Fichier ".$ex->getFile()." Ligne ".$ex->getLine();	
			return false;
		}
	}
	/* Function mysql-fetch array pour la classe */
	function fetch_array(){
		return $this->resultSet->fetch(PDO::FETCH_BOTH);
	}
	/* Function mysql fetch row pour la classe */
	function fetch_row(){
		return $this->resultSet->fetch(PDO::FETCH_NUM);
	}
	/* function mysql fetch assoc pour la classe */
	function fetch_assoc(){
		return $this->resultSet->fetch(PDO::FETCH_ASSOC);
	}
	function fetchAll($param = "both"){
		if(!strcmp("assoc", $param))
			return $this->resultSet->fetchAll(PDO::FETCH_ASSOC);
		elseif(!strcmp("num", $Param))
			return $this->resultSet->fetchAll(PDO::FETCH_NUM);
		else
			return $this->resultSet->fetchAll(PDO::FETCH_BOTH);
	}
	/**
		Function uses to execute statement of the
		type UPDATE, INSERT, DELETE in a database
	*/
	function update(){
		try{
			$update = $this->pdo->prepare($this->source);
			/* Execute la requete */
			if(is_array($this->param)){
				if(count($this->param)){
					$update->execute($this->param);
				}else
					$update->execute();
			}else
				$update->execute();
			$this->affectedRows = $update->rowCount();
			$this->length = $update->rowCount();
			/* Close le cursor */
			$update->closeCursor();
			return true;
		}catch(PDOException $ex){
			var_dump($ex->getTrace());
			$this->_log['error'] = "Error update : ".$ex->getMessage()."<br/> Fichier ".$ex->getFile()." Ligne ".$ex->getLine();	
			return false;
		}
	}
	/** 
		Juste pour commoditee, ces function sont identique le update 
		J'ai voulu utiliser call_user_func, mais ca marche pas
		si tu peux resoudre ca
	*/
	function insert(){
		//call_user_func("update");
		return $this->update();
	}
	function delete(){
		//call_user_func('update');
		return $this->update();
	}
	/**
		Get the last insert id 
	*/
	function lastInsertId(){
		return $this->pdo->lastInsertId();
	}
	/**
		Get the log content after an execution
	*/
	function getLog($err){
		if(isset($this->_log[$err])){
			if(!strcasecmp('pdo_param', $err))
				return print_r($this->_log['pdo_param']."<br/>".$this->source,true);
			else
				return $this->_log[$err]."<br/>".$this->source;
		}else
			return $this->_log['error']."<br/>".$this->source;
	}
	/**
		Return the row
	*/
	public function getRow($i = 0){
		if($this->debug){
			print_r($this->data);
		}
		if(isset($this->data[$i])){
			return $this->data[$i];
		}else{
			$this->_log['error'] = "Unknow row $i";
			print "Unknow row $i";
		}
		return false;
	}
	/**
		Return the attribut data
	*/
	public function getData(){
		return $this->data;
	}
	/**
		Changer la requete et donc la source de donnees
	*/
	public function setQuery($query = "", $param = array()){
		$this->source = $query;
		$this->_log['query'] = $this->source;
		$this->param = $param;
		$this->_log['pdo_param'] = $this->param;
	}
	/**
		Close de la connexion
		Je cherche encore comment fermer l'object pdo
		mais j'ai l'impression qu on ferme seulement
		les resultset
	*/
	public function close(){
		//$this->pdo->closeCursor();
	}
	/**
		Return the length of data object
	*/
	public function length(){
		return $this->length;
	}
	public function record(){
		return $this->length;
	}
	/**
		Modifier of the private field key
	*/
	public function setKey($key){
		if(!empty($key))
			$this->_key = $key;
	}
	/**
		Accessor for the private field key
	*/
	public function getKey(){
		return $this->_key;
	}
	/**
		Print the row in the ith position
	*/
	public function _print($i){
		if(isset($this->data[$i])){
			$line = $this->data[$i];
			for($j = 0; $i < $line->length(); $j++)
				$line->_print($line->row[$j]);
		}
	}
}		
?>