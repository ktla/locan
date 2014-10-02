<?php
/******************************************************
	CE FICHIER CONTIENT TOUTES LES FUNCTIONS
	ET CLASSES UTILISEES POUR LA GESTION DE 
	L'INSTALLATION DU LOGICIEL
******************************************************/
function returnBd(){
	$tab= explode(".",$_FILES["bd"]["name"]);
	return $tab[0];
}
/**
	fonction permettant de vider un repertoire
*/
function viderRep($chemin){
	$dir = dir($chemin);
		while( $nom = $dir->read()) 
			if(strlen($nom) > 2) 
				unlink($chemin."/".$nom);
		$dir->close() ;
}


/**
	Cette classe procede a l'installation du
	logiciel: base de donnees, fichier xml
	de configuration etc....
**/
final class Installation{
	/* Contient le username du superadmin defini dans la page d'installation */
	public $admin;
	/* Contient le password du superadmin defini dans la page d'installation */
	public $adminpwd;
	/* Profile du superadmim par defaut */
	protected $profile = "Administration";
	/* Etat du superactif, par defaut: Actif = 1 */
	protected $actif = 1;
	/* Contient les proprietes de l'etablissement  cf classe Etablissement*/
	/* La classe Etablissement se trouve dans le fichier common_inc.php. Je l'ai reporte ici pour toi */
	public $etablissement;
	/* Fichier de la bd */
	public $bdfile;
	/* Nom du fichier xml a ecrire */
	private $xmlname = "./config.xml";
	/* Nom de la base de donnees */
	public $dbname;
	/* Password defini dans la bd */
	public $dbpwd;
	/* User de la bd */
	public $dbuser = "root";
	/* Serveur qui contient la db */
	public $host;
	/* Descripteur de la connexion */
	protected $con;
	/**
		Definition des methodes de la classe
	*/
	function __construct(){
		$this->etablissement = new Etablissement();
		/* RAS */
	}
	
	
	 public static function connect2db(){
		/* Renvoi une exception si tout va mal */
		try{
			$pdo_options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
			/* Ouvrir la connexion */
			$con = new PDO("mysql:host=localhost", "root","",$pdo_options);
			$con->query("SET NAMES 'utf8'");
			return $con;
		}catch(PDOException $ex){
			die("Connection Error : ".$ex->getMessage()." Fichier ".$ex->getFile()." Ligne ".$ex->getLine());	
		}
	}
	/**
		creation de la BD ainsi ainsi que l' utilisateur et les priviileges 
	*/
	function createBd(){
		try{
			$this->con->exec("CREATE DATABASE IF NOT EXISTS $this->dbname");
			$this->con->exec("USE $this->dbname");
			//$create->bindparam(':db',$this->dbname);
			//$create->execute();
			//$create->closeCursor();
		}catch(PDOException $ex){
			die("Error to create db ".$ex->getMessage());
			return false;
		}
	}
	/**
		Insert the superadmin dans la bd 
	*/
	function insertUsers(){
		try{
			$insert = $this->con->prepare("INSERT INTO users (LOGIN, PASSWORD, PROFILE, ACTIF) VALUES (:user,:pwd,:profile,:actif)");
			$insert->execute(array(
				"user" => $this->admin,
				"pwd" => $this->adminpwd,
				"profile" => $this->profile,
				"actif" => $this->actif
			));
			$insert->closeCursor();
			return true;
		}catch(PDOException $ex){
			die("Erreur d' insertion dans la table users: ".$ex->getMessage()." Fichier ".$ex->getFile()." Ligne ".$ex->getLine());	
			return false;
		}
	}
	/**
		Insert l'etablissement 
		en utilisant la classe Etablissement defini
		dans common_lib
		La methode creer de la classe Etablissement est utiliser
		pour creer un new Etablissement
	*/
	function insertEtablissement(){
		if($this->etablissement->creer($this->con))
			return true;
		else
			die("Erreur d'insertion de l'etablissement");
	}
	/**
		copy du fichier de la bd dans le repertoire ./bd
	*/
	function loadBd(){
		if (!empty($_FILES["bd"]["name"])){
			viderRep("./bd");
			return move_uploaded_file($_FILES["bd"]['tmp_name'], "./bd/".$_FILES["bd"]['name']);
		}
	}
	/**
		copy du fichier du reglement dans le repertoire ./reglement
	*/
	function loadReglement(){
		viderRep("./reglement");
		return move_uploaded_file($_FILES["reglement"]['tmp_name'],"./reglement/".$_FILES["reglement"]['name']);

	}
	/**
		chargement de la base de donnees
	*/
	function chargementBd(){
		$desc = fopen("./bd/".$this->bdfile,"r");
		$req="";
		while($ligne = fgets($desc)){
			if(substr($ligne,strlen($ligne)-2,1)!= ";")
				$req.=" ".$ligne;
			else{
				$req.=" ".substr($ligne,0,strlen($ligne)-1);
				try{
					$this->con->exec($req);
				}catch(PDOException $ex){
					die("Erreur chargement BD: ".$ex->getMessage());	
				}
				$req="";
			}
		}
	}
	

	/**
		Cree le fichier specifier par xmlname
		contenant la configuration du projet
		Est ce qu il ya pas une exception renvoyer
		Si oui, utilise try catch pour recuperer l'erreur 
		et retourner true ou false
	*/
	function writeXml(){
		$this->xmlfile = new XMLWriter();
		$this->xmlfile->openUri($this->xmlname);
		$this->xmlfile->setIndent("true");
		$this->xmlfile->startDocument("1.0","utf8");
		$this->xmlfile->startElement("configuration");
		$this->xmlfile->writeElement("user", $this->dbuser);
		$this->xmlfile->writeElement("password", $this->adminpwd);
		$this->xmlfile->writeElement("host", $this->host);
		$this->xmlfile->writeElement("bd", $this->dbname);
		$this->xmlfile->endElement();
		$this->xmlfile->endDocument();
		return true;
	}
	/**
		Function de validation de l'installation
		procede a l'appel de toutes les function et return true si ok
	*/
	function validate(){
		/* Il faut d'abord ecrire le fichier xml */
		if($this->writeXml()){
			if (isset($_FILES["reglement"]["name"])){
				$this->loadReglement();
			}
			$this->etablissement->reglement = existFile("./reglement")?"../configuration/reglement/".returnFile("./reglement"):"";
			/* Ouvre la connexion a la BD */
			$this->con = Installation::connect2db();
			try{
				//$this->con->exec("UPDATE mysql.user SET Password = PASSWORD('$this->adminpwd') WHERE User = '$this->dbuser' AND Host = '$this->host'");
			}catch(PDOException $ex){
				die("Erreur Update user: ".$ex->getMessage());
			}
			$this->createBd();
			if($this->loadBd()){
				$this->chargementBd();
				$this->insertEtablissement();
				$this->insertUsers();
				$this->etablissement->periode($this->con);
			}
			return true;
		}else
		 	return false;
	}
	
}
class Etablissement{
	public $identifiant;
	public $libelle;
	public $adresse;
	public $principal;
	public $logo;		//Logo de l'etablissement, la source de l'image src
	public $email;
	public $tel;
	public $mobile;
	public $siteweb;
	public $reglement;	//Le lien du reglement ou est stocke le fichier
	public $autorisation;
	public $cptebancaire;
	public $datecreation;		//Date de creation de l'etablissement
	/**
	Definition des methodes de la classe Etablissement
	*/
	
	/**
		Creation d'un nouvel Etablissement
	*/
	function creer($con){
		try{
	$sql = "insert into etablissement (IDENTIFIANT, LIBELLE, ADRESSE, DATECREATION, PRINCIPAL, LOGO, EMAIL, TEL, MOBILE, SITEWEB, REGLEMENT, AUTORISATION, CPTEBANCAIRE, HAUTEURLOGO, LARGEURLOGO) values(:id,:lib,:addr,:date,:pri,:logo,:email,:tel,:mobil,:site,:reg,:aut,:ban,100,100)";
		$stmt = $con->prepare($sql);
		$stmt->BindParam(":id",$this->identifiant);
		$stmt->BindParam(":lib",$this->libelle);
		$stmt->BindParam(":addr",$this->adresse);
		$stmt->BindParam(":date",$this->datecreation);
		$stmt->BindParam(":pri",$this->principal);
		$stmt->BindParam(":logo",$this->logo);
		$stmt->BindParam(":email",$this->email);
		$stmt->BindParam(":tel",$this->tel);
		$stmt->BindParam(":mobil",$this->mobile);
		$stmt->BindParam(":site",$this->siteweb);
		$stmt->BindParam(":reg",$this->reglement);
		$stmt->BindParam(":aut",$this->autorisation);
		$stmt->BindParam(":ban",$this->cptebancaire);
		$stmt->execute();
		return true;
		}catch(PDOException $ex){
			die("Erreur d' insertion dans la table etablissement".$ex->getMessage()." Fichier ".$ex->getFile()." Ligne ".$ex->getLine());	
			return false;
		}
	}
	
	function getDate(){
		$tab = array();
		$tab["jour"] = substr($this->datecreation,8,2);	
		$tab["mois"] = substr($this->datecreation,5,2);
		$tab["an"] = substr($this->datecreation,0,4);
		return $tab;
	}
	
	function getDateDeb($annee){
		$tab = array();
		$tab["jour"] = 02; 	
		$tab["mois"] = 9;
		$tab["annee"] = substr($annee,0,4);
		$tab["date"] = $tab["annee"]."-".$tab["mois"]."-".$tab["jour"];
		return $tab;
	}
	
	function getDateFin($annee){
		$tab = array();
		$tab["jour"] = 25; 	
		$tab["mois"] = 06;
		$tab["annee"] = substr($annee,5,4);
		$tab["date"] = $tab["annee"]."-".$tab["mois"]."-".$tab["jour"];
		return $tab;
	}
	
	function getAnneeAcad($tab){
		$annee = "";
		if ($tab["mois"] < 7 && $tab["jour"] < 25 ){
			$annee = ($tab["an"] - 1)."-".$tab["an"];
		}else
			$annee = $tab["an"]."-".($tab["an"] + 1);
		return $annee;
	}
	
	function trimestre($mois){
		if ($mois >= 7 && $mois <=12)
			return "1er";
		else
			if($mois >= 4 && $mois <=6)
				return "3i&egrave;me";
			else 
				return "2nd";
		
	}
	
	function trimestreOrdre($trim,$annee){
		$deb = substr($annee,0,4);
		$fin = substr($annee,5,4);
		if($trim == 1){
			$tab["fin"] = $deb."-12-20";
			$tab["deb"] = $deb."-09-05";
		}else
			if($trim == 2){
				$tab["deb"] = $fin."-01-05";
				$tab["fin"] = $fin."-03-20";
			}else{
					$tab["deb"] = $fin."-04-05";
					$tab["fin"] = $fin."-06-20";
				 }
		return $tab;	
	}
	
	function periode($con){
		$date = $this->getDate();
		$datedeb = $this->getDateDeb($this->getAnneeAcad($date));
		$datefin = $this->getDateFin($this->getAnneeAcad($date));
		try{
			$insert = $con->prepare("INSERT INTO annee_academique(ANNEEACADEMIQUE,DATEDEBUT,DATEFIN) VALUES(:annee,:datedeb,:datefin)");	
			$insert->execute(array(
			"annee" => $this->getAnneeAcad($this->getDate()),
			"datedeb" => $datedeb["date"],
			"datefin" => $datefin["date"]
			));			
			
		}catch(PDOException $ex){
			die("Erreur d' insertion dans la table annee_academique".$ex->getMessage());	
		}
		/*
		$trimestre = $this->trimestreOrdre(substr($this->trimestre($date["mois"]),0,1),$this->getAnneeAcad($this->getDate()));
		try{
			$insert = $con->prepare("INSERT INTO trimestre VALUES(:trimestre,:annee,:datedeb,:datefin,:libelle)");	
			$insert->execute(array(
			"trimestre" => $this->getAnneeAcad($this->getDate())."/".substr($this->trimestre($date["mois"]),0,1), 
			"annee" => $this->getAnneeAcad($this->getDate()),
			"datedeb" => $trimestre["deb"] ,
			"datefin" => $trimestre["fin"],
			"libelle" => $this->trimestre($date["mois"])." Trimestre"
			));			
			
		}catch(PDOException $ex){
			die("Erreur d' insertion dans la table trimestre".$ex->getMessage());	
		}
		*/
	}	
	
}
?>