<?php
/**************************************
*
*	Classe classe
*
**************************************/
class Classe{
	/* Liste des eleves inscrits dans la classe */
	public $inscrits;
	private $identifiant;
	public $libelle;
	public $niveau;
	public $tailleMax;
	public $profPrincipal;
	public $actif;
	public $montantInscription;
	private $pdo;
	private $result;
	public $fraisObligatoires;
	public $fraisOccasionnels;
	public $reduction;
	/* Array d'enseignement */
	public $enseignement;
	/* Definir s i cette classe possede un prof principal */
	public $hasProfPrincipal;
	
	function __construct($idclasse){
		try{
			$this->pdo = Database::connect2db();
			$query = "SELECT c.*, p.* 
			FROM classe c 
			LEFT JOIN classe_parametre p ON (c.IDCLASSE = p.IDCLASSE AND p.PERIODE = :periode) 
			WHERE c.IDCLASSE = :classe";
			$this->identifiant = $idclasse;
			$this->result = $this->pdo->prepare($query);
			$this->result->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$this->result->bindValue('classe', $this->identifiant, PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				die('Aucun enregistrement avec IDCLASSE = '.$this->identifiant);
				return;
			}
			$row = $this->result->fetch(PDO::FETCH_ASSOC);
			$this->libelle = $row['LIBELLE'];
			$this->niveau = $row['NIVEAU'];
			$this->tailleMax = $row['TAILLEMAX'];
			$this->actif = $row['ACTIF'];
			if(!empty($row['PROFPRINCIPAL'])){
				$this->profPrincipal = new Professeur($row['PROFPRINCIPAL']);
				$this->hasProfPrincipal = true;
			}else{
				$this->hasProfPrincipal = false;
			}	
			$this->actif = $row['ACTIF'];
			$this->montantInscription = $this->getMontantInscription($row['MONTANTINSCRIPTION']);
			$this->result->closeCursor();
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
		
	}
	/**
		Rempli le montant de l'inscription
	*/
	function getMontantInscription($id){
		try{
			$res = $this->pdo->prepare("SELECT MONTANT FROM classe_frais 
			WHERE ID = :id");
			$res->execute(array("id" => $id));
			if($res->rowCount()){
				$row = $res->fetch(PDO::FETCH_ASSOC);
				$res->closeCursor();
				return $row['MONTANT'];
			}else{
				$res->closeCursor();
				return 'Montant indefini';
			}
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
	}
	/* Function renvoyant un tableau de eleve inscrits 
		Renvoit un tableau de New Eleve();, confere fichier 
		eleve_inc.php
	*/
	function getInscrits(){
		$this->inscrits = new ArrayObject();
		$query = "SELECT i.MATEL 
		FROM inscription i 
		WHERE i.IDCLASSE = :idclasse AND i.PERIODE = :periode";
		$res = $this->pdo->prepare($query);
		$res->bindValue('idclasse', $this->identifiant, PDO::PARAM_STR);
		$res->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
		$res->execute();
		if(!$res->rowCount()){
			return false;
		}
		while($row = $res->fetch(PDO::FETCH_ASSOC)){
			$el = new Eleve($row['MATEL']);
			$this->inscrits->append($el);
			return true;
		}
	}
	function getNbInscrit(){
		if($this->inscrits instanceof ArrayObject)
			return $this->inscrits->count();
		else{
			if($this->getInscrits())
				return $this->inscrits->count();
			else
				return 0;
		}
	}
	/**
		Function pour obtenir les montants 
		obligatoire d'une classe, sous la forme d'un new Frais();
		et on peut faire getTotalFrais pour obtenir la somme total
		Confere caisse pour la classe frais_inc.php
	*/
	function getFraisObligatoires(){
		try{
			$query = "SELECT f.ID 
			FROM classe_frais f 
			WHERE f.IDCLASSE = :idclasse AND f.PERIODE = :periode AND TYPE = :type";
			$res = $this->pdo->prepare($query);
			$res->bindValue('idclasse', $this->identifiant, PDO::PARAM_STR);
			$res->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$res->bindValue('type', 0, PDO::PARAM_INT);
			$res->execute();
			if(!$res->rowCount()){
				return false;
			}
			$this->fraisObligatoires = new ArrayObject();
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				$frais = new Frais($row['ID']);
				$this->fraisObligatoires->append($frais);
			}
			$res->closeCursor();
			return true;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	/**
		Renvoit la somme des frais obligatoires
	*/
	function getTotalFaisObligatoires(){
		if($this->getFraisObligatoires()){
			$sum = 0;
			for($i = 0; $i < $this->fraisObligatoires->count(); $i++){
				$frais = $this->fraisObligatoires->offsetGet($i);
				$sum += $frais->montant;
			}
			return $sum;
		}else
			return 0;
	}
	/**
		Rempli le tableau des frais occasionnel
	*/
	function getFraisOccasionnels(){
		try{
			$query = "SELECT f.ID 
			FROM classe_frais f 
			WHERE f.IDCLASSE = :idclasse AND f.PERIODE = :periode AND TYPE = :type";
			$res = $this->pdo->prepare($query);
			$res->bindValue('idclasse', $this->identifiant, PDO::PARAM_STR);
			$res->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$res->bindValue('type', 1, PDO::PARAM_INT);
			$res->execute();
			if(!$res->rowCount()){
				return false;
			}
			$this->fraisOccasionnels = new ArrayObject();
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				$frais = new Frais($row['ID']);
				$this->fraisOccasionnels->append($frais);
			}
			$res->closeCursor();
			return true;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	/**
		Obtenir tous les enseignements
	*/
	function getEnseignements(){
		try{
			$query = "SELECT IDENSEIGNEMENT 
			FROM enseigner 
			WHERE PERIODE = :periode AND CLASSE = :classe";
			$res = $this->pdo->prepare($query);
			$res->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$res->bindValue('classe', $this->identifiant, PDO::PARAM_STR);
			$res->execute();
			if(!$res->rowCount()){
				$res->closeCursor();
				return false;
			}
			$this->enseignement = new ArrayObject();
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				$ens = new Enseignement($row['IDENSEIGNEMENT']);
				$this->enseignement->append($ens);
			}
			$res->closeCursor();
			return true;
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
			$res = $db->prepare("SELECT * FROM classe_parametre WHERE PERIODE = :periode");
			$res->bindValue("periode", $ancienneperiode, PDO::PARAM_STR);
			$res->execute();
			$add = $db->prepare("INSERT INTO classe_parametre(IDCLASSE, MONTANTINSCRIPTION, PERIODE, TAILLEMAX, ACTIF, PROFPRINCIPAL) 
			VALUES(:idclasse, :montantinscription, :periode, :taillemax, :actif, :prof)");
			$add->bindValue('periode', $newperiode, PDO::PARAM_STR);
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				$add->bindValue('periode', $newperiode, PDO::PARAM_STR);
				$add->bindValue('idclasse', $row['IDCLASSE'], PDO::PARAM_STR);
				$add->bindValue('montantinscription', $row['MONTANTINSCRIPTION'], PDO::PARAM_INT);
				$add->bindValue('taillemax', $row['TAILLEMAX'], PDO::PARAM_INT);
				$add->bindValue('actif', $row['ACTIF'], PDO::PARAM_INT);
				$add->bindValue('prof', $row['PROFPRINCIPAL'], PDO::PARAM_STR);
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