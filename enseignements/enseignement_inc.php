<?php
/*******************************************
*
*	Classe enseignement
*
********************************************/
class Enseignement{
	public $matiere;
	public $professeur;
	public $classe;
	private $id;
	public $coefficient;
	public $actif;
	private $pdo;
	private $result;
	function __construct($id){
		try{
			$this->pdo = Database::connect2db();
			$this->id = $id;
			$this->result = $this->pdo->prepare("SELECT * FROM enseigner WHERE IDENSEIGNEMENT = :id");
			$this->result->bindValue('id', $this->id, PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				die('Aucun enseignement ayant cet Id '.$this->id);
				return;
			}
			$row = $this->result->fetch(PDO::FETCH_ASSOC);
			$this->matiere = new Matiere($row['CODEMAT']);
			$this->professeur = new Professeur($row['PROF']);
			$this->classe = new Classe($row['CLASSE']);
			$this->coefficient = $row['COEFF'];
			$this->actif = intval($row['ACTIF']) == 0 ? 'NON' : 'OUI';
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
			/* nouvelle periode est la periode avec laquelle nous soes connectes */
			$newperiode = $_SESSION['periode'];
			$db = Database::connect2db();
			$res = $db->prepare("SELECT * FROM enseigner");
			$res->bindValue("periode", $ancienneperiode, PDO::PARAM_STR);
			$res->execute();
			$add = $db->prepare("INSERT INTO enseigner(CODEMAT, CLASSE, PROF, PERIODE, COEFF, ACTIF) 
			VALUES(:codemat, :classe, :prof, :periode, :coeff, :actif)");
			$add->bindValue('periode', $newperiode, PDO::PARAM_STR);
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				$add->bindValue('codemat', $row['CODEMAT'], PDO::PARAM_STR);
				$add->bindValue('classe', $row['CLASSE'], PDO::PARAM_STR);
				$add->bindValue('prof', $row['PROF'], PDO::PARAM_STR);
				$add->bindValue('coeff', $row['COEFF'], PDO::PARAM_INT);
				$add->bindValue('actif', $row['ACTIF'], PDO::PARAM_INT);
				$add->execute();
			}
			$add->closeCursor();
			$res->closeCursor();
			return true;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
			return false;
		}
	}
}
?>