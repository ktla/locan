<?php
/************************************************
*
*	Class Eleve
*	
************************************************/

class Eleve{
	public $matricule;
	public $matel;
	public $adresse;
	public $nompere;
	public $ancienEts;
	public $idancienEts;
	public $datenaiss;
	public $naissdate;
	public $addresspere;
	public $email;
	public $id;
	public $mere;
	public $addressmere;
	public $addresstuteur;
	public $dateajout;
	public $nom;
	public $prenom;
	public $lieunaiss;
	public $image;
	public $sexe;
	public $tel;
	public $tuteur;
	private $pdo;
	private $result;
	private $frequente; // Libelle de la classe frequentee
	private $idclasse;
	public $religion;
	public $idreligion;
	public $redoublant;
	public $periode;
	public $classe;
	/* Si eleve est inscrit; contient la liste des frais apayer par l'eleve */
	public $isInscrit = false;
	public $fraisAPayer;
	public $reductionObtenue;
	
	/**
		Constructeur
	*/
	function __construct($id,$autogenerate = true,$get = ""){
		if(!isset($id) || empty($id)){
			die('Le Matricule est obligatoire pour la classe Eleve');
			return;
		}
		$this->id = $id;
		try{
			$this->pdo = Database::connect2db();
			if($autogenerate){
				$query = "SELECT e.*, r.LIBELLE AS REL, a.LIBELLE AS ETBS  
				FROM eleve e  
				JOIN religion r ON (r.IDRELIGION = e.RELIGION) 
				JOIN ancien_etablissement a ON (a.IDETS = e.ANCETBS)
				WHERE e.ID = :id";
				$this->result = $this->pdo->prepare($query);
				$this->result->bindValue('id', $this->id, PDO::PARAM_STR);
				$this->result->execute();
				$row = $this->result->fetch(PDO::FETCH_ASSOC);
				/** Affecter les valeurs au champ */
				$this->matel = $row['MATEL'];
				$this->matricule = $row['MATRICULE'];
				$this->nom = decode($row['NOMEL']);
				$this->prenom = decode($row['PRENOM']);
				$this->naissdate = $row['DATENAISS'];
				$d = new dateFR($row['DATENAISS']);
				$this->datenaiss = $d->fullYear(3);
				$this->lieunaiss = decode($row['LIEUNAISS']);
				$this->image = $row['IMAGE'];
				$this->tel = $row['TEL'];
				$this->email = decode($row['EMAIL']);
				$this->sexe = $row['SEXE'];
				$this->nompere = decode($row['NOMPERE']);
				$this->addresspere = decode($row['ADDRESSEPERE']);
				$this->mere = decode($row['NOMMERE']);
				$this->addressmere = decode($row['ADDRESSEMERE']);
				$this->adresse = decode($row['ADRESSE']);
				$this->tuteur = decode($row['TUTEUR']);
				$this->addresstuteur = decode($row['ADDRESSETUTEUR']);
				$d->setSource($row['DATE']);
				$this->dateajout = $d->fullYear(3);
				$this->religion = decode($row['REL']);
				$this->idreligion = $row['RELIGION'];
				$this->ancienEts = decode($row['ETBS']);
				$this->idancienEts = decode($row['ANCETBS']);
				$this->periode = $_SESSION['periode'];
				/* modifie l'attribut is inscrit et renvoi l'id de la classe */
				$idclasse = $this->getClasseEncours();
				if($this->isInscrit || !empty($get) ){
					$this->classe = new Classe(!empty($get)?$get:$idclasse);
					$bool = $this->isRedoublant();
				}
			}
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	function delete(){
		try{
			$this->result = $this->pdo->prepare("DELETE FROM eleve WHERE ID = :id");
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	
	
	function redouble(){
		switch($this->redoublant){
			case 0: return "Non"; break;
			case 1: return "Oui"; break;
			default: return "Non";
		}	
	}
	/* Obtient la classe frequenter par l'eleve */
	function getClasse(){
		try{
			if(isset($this->frequente))
				return $this->frequente;
			else{
				$query = "SELECT c.LIBELLE 
				FROM classe c 
				WHERE c.IDCLASSE IN (
				SELECT IDCLASSE FROM inscription i WHERE i.PERIODE = :periode AND i.MATEL = :id)";
				$this->result = $this->pdo->prepare($query);
				$this->result->bindValue('id', $this->id, PDO::PARAM_STR);
				$this->result->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
				$this->result->execute();
				$row = $this->result->fetch(PDO::FETCH_ASSOC);
				$this->frequente = decode($row['LIBELLE']);
				if(empty($this->frequente))
					return 'Aucune classe';
				return $this->frequente;
			}
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	/**
		Identique a la function getClasse
		Sauf qu'il rempli la champ classe = new Classe()
	*/
	function getClasseEncours(){
		try{
			$query = "SELECT c.IDCLASSE, c.LIBELLE 
			FROM classe c 
			WHERE c.IDCLASSE IN (
			SELECT IDCLASSE FROM inscription i WHERE i.PERIODE = :periode AND i.MATEL = :id)";
			$result = $this->pdo->prepare($query);
			$result->bindValue('id', $this->id, PDO::PARAM_STR);
			$result->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$result->execute();
			if($result->rowCount()){
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$this->frequente = decode($row['LIBELLE']);
				$this->idclasse = $row['IDCLASSE'];
				$this->isInscrit = true;
				return $row['IDCLASSE'];
			}else{
				$this->isInscrit = false;
				return "";
			}
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	/**
		Renvoit vrai ou faux s il est redoublant
	*/
	function isRedoublant(){
		/** Si la periode est differente alors compter le nombre d'inscription
			strcmp != 0 si false, et = 0 si true;
		 */
		 try{
				$query = "SELECT *
				FROM inscription i 
				WHERE i.MATEL = :id AND i.PERIODE = :periode)";
				$res = $this->pdo->prepare($query);
				$res->bindValue('matel', $this->id, PDO::PARAM_STR);
				$res->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
				$res->execute();
				$bool = false;
				$row = $res->fetch(PDO::FETCH_ASSOC); 
				if(intval($row['REDOUBLE']) == 1){
					$this->redoublant = 1;
					$bool = true;
				}else{
					$this->redoublant = 0;
					$bool = false;
				}
				$res->closeCursor();
				return $bool;
		 }catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
		}
	}
	/**
		Obtient sous forme de arrayObject la liste des
		frais a payer
		Return vrai et rempli le array Object fraisAPayer s'il existe un frais
		return false si aucun frais n'existe
	*/
	function getFraisAPayer(){
		if(!$this->isInscrit){
			die('Eleve non inscrit a une classe a cette periode ');
			return;
		}
		try{
			$query = "SELECT f.IDFRAIS 
			FROM frais_apayer f 
			INNER JOIN inscription i ON (i.IDINSCRIPTION  = f.IDINSCRIPTION AND i.MATEL = f.MATEL ) 
			WHERE f.MATEL = :matel
			AND i.PERIODE = :periode )";
			$res = $this->pdo->prepare($query);
			$res->execute(array(
				'matel' => $this->id,
				'periode' => $_SESSION['periode']
			));
			if($res->rowCount()){
				$this->fraisAPayer = new ArrayObject();
				while($row = $res->fetch(PDO::FETCH_ASSOC)){
					$f = new Frais($row['IDFRAIS']);
					$this->fraisAPayer->append($f);
				}
				return true;
			}else
				return false;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
		}
	}
	/**
		Rempli le arrayObject reductionobtenue et renvoi
		vrai s'il existe une reductionobtenue et 
		false sinon
	*/
	function getReductionObtenue(){
		if(!$this->isInscrit){
			die('Eleve non inscrit a une classe a cette periode ');
			return;
		}
		try{
			$query = "SELECT o.IDREDUCTION 
			FROM reduction_obtenue o 
			INNER JOIN inscription i ON (i.MATEL = o.MATEL AND i.IDINSCRIPTION = o.IDINSCRIPTION) 
			WHERE o.MATEL = :matel
			AND i.PERIODE = :periode";
			$res = $this->pdo->prepare($query);
			$res->execute(array(
				'matel' => $this->id,
				'periode' => $_SESSION['periode']
			));
			if($res->rowCount()){
				$this->reductionObtenue = new ArrayObject();
				while($row = $res->fetch(PDO::FETCH_ASSOC)){
					$f = new Reduction($row['IDREDUCTION']);
					$this->reductionObtenue->append($f);
				}
				return true;
			}else
				return false;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());	
		}
	}
	/**
		Renvoit le total des frais a payer
	*/
	function getTotalFraisAPayer(){
		if($this->fraisAPayer instanceof ArrayObject){
			$len = $this->fraisAPayer->count();
			$sum = 0;
			for($i = 0; $i < $len; $i++){
				$frais = $this->fraisAPayer->offsetGet($i);
				$sum += $frais->montant;
			}
			return $sum;
		}else{
			print "<p class = 'infos'>Appeler d'abord la methode getFraisAPayer() qui retourne true or false</p>";
			return 0;
		}
	}
	/**
		Renvoit le total des reduction obtenue
	*/
	function getTotalReductionObtenue(){
		if($this->reductionObtenue instanceof ArrayObject){
			$sum = 0; $len = $this->reductionObtenue->count();
			for($i = 0; $i < $len; $i++){
				$reduc = $this->reductionObtenue->offsetGet($i);
				$sum += $reduc->getMontant();
			}
			return $sum;
		}else{
			print "<p class = 'infos'>Appeler d'abord la methode getReductionObtenue() qui retourne true or false</p>";
			return 0;
		}
	}
	/**
		Retourne le dernier etablissement frequentes
		avant de venir chez nous
	*/
	function ancienEts(){
		return $this->ancienEts;
	}
}
?>