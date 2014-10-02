<?php
/**************************************
*
*	Classe Professeur
*
**************************************/
class Professeur{
	private $pdo;
	private $result;
	public $id;
	private $idprof;
	public $nomprof;
	public $prenom;
	public $adresse;
	public $tel;
	public $sexe;
	public $religion;
	public $datenaiss;
	public $lieunaiss;
	public $nomcontact;
	public $addressecontact;
	public $email;
	public $datedebut;
	public $matricule;
	public $image;
	public $actif;
	public $profile;
	public $reglement; // il s'agit du curriculum vitae
	public $periode;
		function __construct($id,$userpass = false){
		try{
			$this->pdo = Database::connect2db();
			if(!$userpass)
				$query = "SELECT p.*, r.LIBELLE AS LIBELLERELIGION, u.ACTIF AS ACTIF, u.PROFILE
				FROM professeur p 
				INNER JOIN religion r ON (r.IDRELIGION = p.RELIGION)
				INNER JOIN users u ON (u.LOGIN = p.MATRICULE)
				WHERE p.ID = :id";
			else
				$query = "SELECT p.*, r.LIBELLE AS LIBELLERELIGION
				FROM professeur p 
				INNER JOIN religion r ON (r.IDRELIGION = p.RELIGION)
				WHERE p.ID = :id";
			$this->id = $id;
			$this->periode = $_SESSION['periode'];
			$this->result = $this->pdo->prepare($query);
			$this->result->bindValue('id', $this->id, PDO::PARAM_STR);
			//$this->result->bindValue('periode', $this->periode, PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				die('Aucun enregistrement avec ID PROFESSEUR = '.$this->id);
				return;
			}
			$row = $this->result->fetch(PDO::FETCH_ASSOC);
			$this->nomprof = $row['NOMPROF'];
			$this->prenom  = $row['PRENOM'];
			$this->adresse = $row['ADRESSE'];
			$this->tel = $row['TEL'];
			$this->email = $row['EMAIL'];
			$this->idprof = $row['IDPROF'];
			$this->religion = $row['RELIGION'];
			$this->nomcontact =  $row['NOMCONTACT'];
			$this->addressecontact = $row['ADDRESSECONTACT'];
			$this->datenaiss = $row['DATENAISS'];
			$this->lieunaiss = $row['LIEUNAISS'];
			$this->sexe = $row["SEXE"];
			$this->matricule = $row['MATRICULE'];
			$this->image = 	$row['PHOTO'];
			$this->reglement = $row['CURRICULUM'];	
			$this->libellereligion = $row['LIBELLERELIGION'];
			$this->actif = (!$userpass)?$row['ACTIF']:"";
			$this->profile = (!$userpass)?$row['PROFILE']:"";
			$this->datedebut = $row['DATE'];
			$this->result->closeCursor();
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
		
	}
}
?>