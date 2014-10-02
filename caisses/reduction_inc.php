<?php
/*****************************************
*
*	La classe reduction confere table 
*	classe_reduction
*
*****************************************/
class Reduction{
	public $montant;
	public $code;
	public $id;
	public $libelle;
	private $pdo;
	private $result;
	public $type; // Soit pourcentage soi en valeur
	/* Frais surlaquelle s'applique la reduction */
	public $frais;
	/* Consructor */
	function __construct($id){
		try{
			$this->id = $id;
			$query = "SELECT * FROM classe_reduction WHERE ID = :id";
			$this->pdo = Database::connect2db();
			$this->result = $this->pdo->prepare($query);
			$this->result->bindValue('id', $this->id, PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				die('Aucune reduction ayant cet ID '.$this->id);
				return;
			}
			$row = $this->result->fetch(PDO::FETCH_ASSOC);
			$this->montant = $row['MONTANT'];
			$this->code = $row['CODE'];
			$this->libelle = $row['LIBELLE'];
			$this->type = $row['TYPE'];
			$this->frais = new Frais($row['IDFRAIS']);
			$this->result->closeCursor();
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
	}
	/**
		Function permettant d'effectuer une interperiode
		Il s'agit de copier les parametre d'une periode
		a la periode actuelle
	*/
	public static function interperiode($ancienneperiode){
		try{
			$newperiode = $_SESSION['periode'];
			$db = Database::connect2db();
			$res = $db->prepare("SELECT * FROM classe_reduction WHERE PERIODE = :periode");
			$res->bindValue('periode', $ancienneperiode, PDO::PARAM_STR);
			$res->execute();
			$add = $db->prepare("INSERT INTO classe_reduction () 
			VALUES()");
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				
			}
			$res->closeCursor();
			$add->closeCursor();
			return true;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
			return false;
		}
	}
	
	public function getMontant(){
		if(!strcmp($this->type,"pourcentage"))
			return (($this->montant * $this->frais->montant) / 100);
		else
			if(!strcmp($this->type,"valeur"))
				return $this->montant;
	}	
}
?>