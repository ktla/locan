<?php
header('Content-Type: text/html; charset=UTF-8');
/**********************************************************************************************
	BIBLIOTHEQUE CONTENANT TOUTES LES FONCTIONS COMMUNES 
	UTILISEES DANS LA PLUSPART DES FICHIERS


***********************************************************************************************/
/*
	Debuter la session pour toutes les pages
*/
@session_start();
/**
	Si le fichier de configuration n'existe pas
	rediriger dans la page de configuration
*/
if(!file_exists("../configurations/config.xml"))
	@header("location: ../configurations/index.php");
/*
	Variables globales du logiciel
	Ces variables sont modifier dans chaque page du projet
	- $titre : Titre afficher dans la zone de travail
	- $config :
	- $codepage : Code de page utile pour la verification des droits d'acces
		Les droit de modification commence par EDIT_ suivit du nom menu (EDIT_PROFESSEUR, EDIT_CLASSE, EDIT_PERIODE...)
		Les droit de supression commence par DEL_ suivit du nom menu (DEL_PROFESSEUR, DEL_CLASSE, DEL_PERIODE...)
		....
	- $listedroit : Tableau contenant la liste des droits du profile.
		Pour acceder a une page de code $codepage, on recherche dans $listedroit si ce code existe.
*/
global $titre;
global $config;
global $codepage;
global $listedroit;
$listedroit = array();
/****************************************
	INCLUSION DES AUTRES FICHIERS
	
	
	
*****************************************/
require_once("database_inc.php");
/***********************************************************************************************/
/*
	Conservation de l'url de la page active
*/
if($_SERVER['PHP_SELF'] != "/locan/utilisateurs/connexion.php")
	//$_SESSION['activeurl'] = substr($_SERVER['PHP_SELF'], 0, (strlen($_SERVER['PHP_SELF']) - strpos($_SERVER['PHP_SELF'], "?")));
	$_SESSION['activeurl'] = $_SERVER['PHP_SELF'];
/*
*/
/*
	Connexion a la base de donnees
*/
function connexionDB($host, $pwd, $bd, $uid){
	if(mysql_connect($host,$uid,$pwd)){
		mysql_select_db($bd) or die("Erreur de connexion a la BD ".mysql_error());
	}else
		die("Erreur de connexion au serveur ".mysql_error());
} 
function parse($ch){
	if(isset($ch)){
		$ch = utf8_decode($ch);
		$ch = trim($ch);
		$ch = addslashes($ch);
		return utf8_encode($ch);
	}else
		return "";
}

/**
	Permet l'affichage des chiffre (3 chiffre + espace
	Par exemple 10000 = 10 000.00#
	0 = Montant en chiffre,
	1 = Montant afficher en rouge = alerte montant
	2 = Texte en gras et rouge
	Definir autre type pour adapter 
*/
function format($str, $type = 0){
	$style = "";
	if($type == 1)
		$style = "color:red";
	if($type == 0 || $type == 1){
		$val = "";
		$str .= "";
		$concat = "";
		$len = strlen($str);
	
		for($i = ($len - 1); $i >= 0; $i--){
			$val .= $str[$i];
			if((strlen($val) % 3) == 0){
				$concat .= $val." ";
				$val = "";
			}
		}
		if(strlen($val)!= 3)
			$concat .= $val." ";
		return "<font style=\"font-weight:bold;".$style.";\">".strrev($concat)." FCFA</font>";
	}elseif($type == 2){
		return "<font style=\"font-weight:bold;color:red\">".$str."</font>";
	}else
		return "Format non impl&eacute;ment&eacute;";
}
/*
	IF avec valeur true et false defini
	Identique au iif de VB
*/
function _if($condition, $true, $false = ""){
	if($condition)
		return $true;
	else
		return $false;
}
function is_autoriser($pwd, $login){
	$db = new Database("SELECT * FROM users WHERE LOGIN = :login", 0, array("login" => $login));
	if($db->select()){
		if(!$db->length)
			return -1;
		else{
			$row = $db->getRow();
			if(!strcmp($pwd, $row->item('PASSWORD'))){
				$_SESSION['user'] = parse($row->item('LOGIN'));
				$_SESSION['nom'] = decode($row->item('NOM'));
				$_SESSION['prenom'] = decode($row->item('PRENOM'));
				$_SESSION['profile'] = parse($row->item('PROFILE'));
				$_SESSION['timeconnect'] = time() + 3600; //15min = 900 de connexion
				return 1;
			}else
				return 0;
		}
	}else{
		die($db->getLog('error'));
		return false;
	}
}
class column{
	private $id;
	public $text; //le libelle de la colonne
	public $datafield;  //le champ de la DS liee a cette colonne
	public $maxwidth = "0px";  //largeur maximale de la conne (px,pt,em...)
	public $visible; //definit si la colonne sera visible ou non;

	//constructeur permet d'initaliser les membres dde la classe
	function __construct($idv, $textv="", $fieldv = "", $isvisible = TRUE){
		$this->id = $idv;
		$this->text = $textv;
		$this->datafield = $fieldv;
		$this->visible = $isvisible;
	}
	function getid(){
		return $this->id;
	}

}

/*construit le tableau et affiche les lignes sur un font different*/
class Grid {
	/** Connexion a la bd */
	private $db;
	private $etat;
	public $coldate;
	/* la requete */
	private $source;
	/** Donnees renvoyer par la db */
	private $data;
	private $colonnes; //Tableau de colonnne
	public $editbutton = FALSE;
	public $deletebutton = FALSE;
	public $editbuttontext;
	public $deletebuttontext;
	public $selectbutton = FALSE;
	public $id;
	public $checkedbutton;	//Tableau de button deja cocher, liste des check a cocher pendant la construction
	public $formatdate = "short"; //short : Date sous trois format 01 Sept 2011 , long : Date sous tous les format 01 Septembre 2011
	/** Parametre qui accompagnent la requete */
	public $param = array();
	/* Determine si on doit afficher les button d'action delete et edit dans une colonne */
	public $actionbutton = true;
	public $target;
	
	function __construct($query, $id = 0){
		$this->id = $id;
		$this->coldate = array();
		$this->etat = 0;
		$this->source = $query;
		/* Definition des object */
		$this->data = new ArrayObject();
		$this->colonnes = new ArrayObject();
		$this->checkedbutton = array();
		$this->checkedbutton[] = -1;
	}
	private function contains($idcol){
		foreach($this->colonnes as $col){
			if ($col->getid() == $idcol)
				return TRUE;
		}
		return FALSE;
	}
	/**
		Utiliser le meme object pour plusieurs requete
	*/
	public function setQuery($query, $param){
		$this->etat = 0;
		$this->source = $query;
		$this->data = new ArrayObject();
		$this->param = $param;
	}
	public function setColDate($i){
		$this->coldate[count($this->coldate)] = $i;
	}
	private function loadData(){
		/* Ouvre la connexion a la bd*/
		$this->db = new Database($this->source, $this->id, $this->param);
		if($this->db->select()){
			if(!$this->db->length){
				print "<p class=\"infos\">AUCUN ENREGISTREMENT.</p>";
				return false;
			}
			$this->data = $this->db->getData();
			return true;
		}else{
			die($this->db->getLog("error"));
			return false;
		}
	}
	/**
		param : $id = Id de la colonne, doit etre differente pour chque colonne
			$txt = text tel qu'il doit etre afficher et vue par l'utilisateur
			$field = bind colonne, identique a celle defini dans la BD, peut etre numerique ou string
			$visible = valeur boolean, defini si la colonne doit etre hidden ou visible
	*/
	public function addcolonne($id, $txt, $field, $visible = TRUE){
		if(!$this->contains($id)){
			$colonne = new column($id, $txt, $field, $visible);
			$this->colonnes->append($colonne);
		}else
			die("la table contient deja cette colonne id : ".$id);
	}
	function getNbColonneVisible(){
		$i = 0;
		foreach($this->colonnes as $col)
			if($col->visible)
				$i++;
		return $i;
	}
	function display($largeur = '100%', $hauteur = '500px'){
		$this->loadData();
		if(!$this->data->count())
			return;
		echo "<div style=\"border:1px solid #CCC;max-height:".$hauteur.";overflow:auto; width:100%;padding:2px;max-width:".$largeur."\">";
		/* table de donnees */
		print "<table class=\"grid\">";
		//AFFICHAGE DES COLONNES
		print "<thead><tr>";
		if($this->selectbutton)
			print "<th><input type=\"checkbox\" onchange=\"checkall()\" id = 'chkall'></th>";
		foreach($this->colonnes as $col){
			if($col->visible)
				print "<th>".$col->text."</th>";
		}
		if($this->actionbutton){
			if($this->editbutton || $this->deletebutton)
				print "<th>ACTIONS</th>";
		}
		print "</tr></thead><tbody>";
		//AFFICHAGE DES LIGNES
		foreach($this->data as $line){
			if ($this->etat & 1)
				$line_color='ligne1';
			else	
				$line_color='ligne2';
			print "<tr class=\"".$line_color."\">";
			$j = 0;
			if($this->selectbutton){
				if(in_array($line->item($this->id), $this->checkedbutton))
					print "<td><input type=\"checkbox\" name=\"chk[]\" checked = 'checked' value = \"".$line->item($this->id)."\" /></td>";
				else
					print "<td><input type=\"checkbox\" name=\"chk[]\" value = \"".$line->item($this->id)."\" /></td>";
			}
			foreach($this->colonnes as $col){
				if($col->visible){
					if(in_array($j, $this->coldate)){
						$d = new dateFR($line->item($col->datafield));
						if($this->formatdate == "long")
							echo "<td>".$d->fullYear()."</td>";
						else
							echo "<td>".$d->getDateMessage(3)."</td>";
					}else
						echo "<td>".$line->item($col->datafield)."</td>";
				}
				$j++;
			}
			if($this->actionbutton){
				if($this->editbutton || $this->deletebutton){
					print "<td>";
					if($this->editbutton){
						echo "<a href=\"javascript:editbutton('".$_SERVER['PHP_SELF']."?action=edit&line=". parse($line->item($this->id))."')\">";
						echo "<img src = '../images/edit.png' title = \"".$this->editbuttontext."\" /></a>&nbsp;&nbsp;&nbsp;";
					}if ($this->deletebutton){
						if(!isset($this->target))
							echo "<a href=\"javascript:suppression('".$_SERVER['PHP_SELF']."?action=delete&line=".parse($line->item($this->id))."');\">";
						else
							echo "<a href=\"javascript:suppression('".$_SERVER['PHP_SELF']."?action=delete&line=".parse($line->item($this->id))."&periode=".$this->target."');\">";
						echo "<img src = '../images/supprimer.png' title = \"".$this->deletebuttontext."\" /></a>";
					}
					print "</td>";
				}
			}
			$this->etat++;
			print "</tr>";
		}
		$nb = $this->getNbColonneVisible();
		if($this->editbutton || $this->deletebutton) $nb += 1;
		if($this->selectbutton) $nb += 1;
		print "</tbody><tfoot><tr><td colspan = \"".$nb."\"></td></tr></tfoot>";
		print "</table></div>";
	}
	/**
		Renvoit le nbre de donnees de la requete
	*/
	function length(){
		if($this->data instanceof ArrayObject)
			return $this->data->count();
		else
			return 0;
	}
}
////////////////////////////////////////////
	function parseDate($f){
		if(isset($f)){
			if(strstr($f, "/") != FALSE){
				list($m, $d, $y) = explode("/", $f);
				$fl = $y."-".$m."-".$d;
				return $fl;
			}else
				return $f;
		}else
			return "0000-00-00";
	}
	
////////////////////////////////////////////
	function unParseDate($f){
		if(isset($f)){
			if(strstr($f, "-") != FALSE){
				list($y, $m, $d) = explode("-", $f);
				$fl = $m."/".$d."/".$y;
				return $fl;
			}else
				return $f;
		}else
			return "0000-00-00";
	}
/*function shortmenu(){
	print '<div id="shortmenu"><a href="../utilisateurs/connexion.php" style="color:#FFFFFF;">Deconnexion</a></div>';
}*/
function resetconnexion(){
	if(isset($_SESSION['user'])){
		if($_SESSION['timeconnect'] > time())
			//$_SESSION['timeconnect'] = time() + 900;
			$_SESSION['timeconnect'] = time() + 3600;
		else
			unset($_SESSION['user']);
	}
} 
function id_exist($id, $cletable, $table){
	$db = new Database("SELECT * FROM $table WHERE $cletable = :id", 0,  array("id"=>$id));
	if($db->select())
		return $db->length;
	else
		die($db->getLog('error'));
}
class dateFR{
	private $year;
	private $day;
	private $month;
	private $strtime;		//Date sous forme de chaine renvoyer par strtotime();
	private $jrsFR;
	private $moisFR;
	
	function __construct($date){
		$this->strtime = strtotime($date);
		$this->jrsFR = array("Mon"=>"Lundi", "Tue"=>"Mardi", "Wed"=>"Mercredi", "Thu"=>"Jeudi", "Fri"=>"Vendredi", "Sat"=>"Samedi", "Sun"=>"Dimanche");
		$this->moisFR = array("1"=>"Janvier", "Fevrier","Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Decembre");
		$this->year = date("Y", $this->strtime);
		$this->month = date("n", $this->strtime);
		$this->day = date("D", $this->strtime);
	}
	
	//Renvoi le jour en francais sous $len caractere. si 0 = tous les caractere. Lun-Dim
	function getJour($len = 0){
		if($len == 0)
			return $this->jrsFR[$this->day];
		else
			return substr($this->jrsFR[$this->day], 0, $len);
	}
	//Renvoi le mois en francais sous $len caractere. si 0 = tous les caractere. Jan - Dec
	function getMois($len = 0){
		if($len == 0)
			return $this->moisFR[$this->month];
		else
			return substr($this->moisFR[$this->month], 0, $len);
	}
	//Renvoi la date du jour du mois.1 - 31
	function getDate(){
		return date("d", $this->strtime);
	}
	//Renvoi l'anne en francais sous $len caractere. si 0 = tous les caractere
	function getYear($len = 0){
		if($len == 0)
			return $this->year;
		else
			return substr($this->year, 0, $len);
	}
	function getTime(){
		return date("H:i", $this->strtime);
	}
	
	function setSource($date){
		$this->strtime = strtotime($date);
		$this->year = date("Y", $this->strtime);
		$this->month = date("n", $this->strtime);
		$this->day = date("D", $this->strtime);
	}
	function fullYear($len = 0){
		return $this->getDate()." ".$this->getMois($len)." ".$this->year;
	}
	//Affiche un format de date normal qui varie en fonction du jrs, le mois et l'annee
	function getDateMessage($len){
		//Si c'est l'annee actuelle format AAAA
		if(date("Y", time()) == $this->year){
			//Si c'est le mois actuel. Format 1-12
			if(date("n" , time()) == $this->month){
				//Si c'est le jour actuel. Format 01-31
				if(date("d", time()) == $this->getDate()){
					if(strcmp(date("H:i", $this->strtime), "00:00"))
						return date("H:i" , $this->strtime);
					else
						return $this->getDate()." ".$this->getMois($len);
				}
				if(date("d", time()) == $this->getDate()+1)
					return "Hier ".date("H:i", $this->strtime);
				return $this->getDate()." ".$this->getMois($len);
			}//Si c'est pas le mois actuel
			return $this->getDate()." ".$this->getMois($len);
		}//Si c'est pas l'annee actuelle alors afficher
		return $this->getDate()." ".$this->getMois($len)." ".$this->year;
	}
}

abstract class InputClass{
	protected $con;
	protected $table;
	protected $index;
	function __construct($table,$index){
		$this->con = Database::connect2db();
		$this->table = $table;
		$this->index = $index;	
	}
	abstract function insert($val1);
	abstract function update($val,$line);
	abstract function delete($index);
}

class Input extends InputClass{
	protected $colonne;
	function __construct($table,$index,$colonne){
		InputClass::__construct($table,$index);
		$this->colonne = $colonne;
	}
	function insert($val1){
			$val1 = utf8_encode($val1);
			$this->con->exec("INSERT INTO $this->table VALUES('','$val1')");
		}
	function update($val,$line){
		$val = utf8_encode($val);
		$this->con->exec("UPDATE religion SET $this->colonne = '$val' WHERE $this->index = $line");
		}
	function delete($index){
		$this->con->exec("DELETE FROM $this->table WHERE $this->index =".$index);
	}
	function exist($libelle){
		$sql = "SELECT * FROM $this->table WHERE $this->colonne ='$libelle'";
		$stmt = $this->con->query($sql);
		return ($stmt->rowCount() > 0)? true : false;
		}
	function getIndex($line){
		$sql = "SELECT * FROM $this->table WHERE $this->index = $line";
		$stmt = $this->con->query($sql);
		$res = $stmt->fetchAll();
		return $res[0][1];
	}
}




class Combo{
	/** Connexion a la bd*/
	private $db;
	/* la requete */
	private $source;
	private $data;	//Donne est execute par 
	public $id;	//Colonne contenant le value des <option value = row[id]>, cache a l'utilisateur
	public $show;	//Colonne visible a l'utilisateur, afficher entre les <option>row[show]<option>
	public $name;	//name du <select name = name>
	public $first;	//Information a afficher en premier dans le select (premier option)
	private $selected;	//Existe t-il un element selectionner au depart TRUE ou FALSE
	public $selectedid;	//Si selected = TRUE, indiquer l'identifiant de cet element qui devrai etre selectionner par defaut
	public $other = FALSE; 	//Precise s'il ya possibilite de choisir en dernier lieu ---Autre---
	/** Les parametre de la bd */
	public $param = array();
	/* Id utiliser, meme chose pour name */
	public $idname;
	
	public $textother = "--Autre | Pr&eacute;ciser--";	//Text a afficher s'il l'option autre existe
	public $onchange = "";		//Fonction appelee lorsque la valeur change
	
	function __construct($query, $name = '', $id = 0, $show = '', $selected = FALSE,$select = 0){
		$this->source = $query;
		$this->first = "";
		$this->selected = $selected;
		$this->id = $id;
		$this->show = $show;
		$this->name = $name;
		$this->idname = $name;
		$this->selectedid = $select;
		/** Execute a partir de la bd*/
	}
	function view($width = '100%'){
		$this->db = new Database($this->source, $this->id, $this->param);
		if($this->db->select()){
			$this->data = $this->db->getData();
		}else
			die($this->db->getLog('error'));
		/* Afficher les donnees */
		if(!count($this->data) && !$this->other){
			print "<p class=\"infos\">AUCUN ENREGISTREMENT</p>";
			return;
		}
		print "<select name=\"".$this->name."\" onChange = \"".$this->onchange."\" style=\"width:".$width."\" id = '".$this->idname."'>";
		if(!empty($this->first))
			print "<option value=\"\">".$this->first."</option>";
		if(count($this->data) > 0)
		foreach($this->data as $row){
			if($this->selected &&  !strcasecmp($row->item($this->id), $this->selectedid))
				print "<option value=\"".decode($row->item($this->id))."\" selected = 'selected'>".parse($row->item($this->show))."</option>";
			else
				print "<option value=\"".decode($row->item($this->id))."\">".parse($row->item($this->show))."</option>";
		}
		if($this->other){
			print "<option value=\"other\">".$this->textother."</option>";
		}
		print "</select>";
	}
}
/*
	Fonction qui rempli la varibale globale $listedroit
	des droit du profile sous la forme de CODEPAGE
	Ces code de page son verifier  dans is_autorized pour verifier l'acces
	a chaque page. Ceci empeche d'acceder a une page par saisie d'url
*/
/*
*/
function is_autorized($code){
	if(!strcmp("ACCUEIL", $code) || !strcmp("PROFILE", $code))
		return true;
	$query = "SELECT d.CODEPAGE FROM droit d WHERE d.IDDROIT IN (SELECT l.IDDROIT FROM listedroit l WHERE l.PROFILE = '".parse($_SESSION['profile'])."')";
	$res = mysql_query($query) or die(mysql_error());
	while($row = mysql_fetch_array($res))
		if(!strcmp($code, $row['CODEPAGE']))
			return true;
	return false;
}
/**
*/
class menu{
	public $source;
	public $data;
	private $profile;
	public $header;
	private $result;
	function __construct(){
		$this->header = array();
		$this->profile = $_SESSION['profile'];
		$this->source = "SELECT m.*, h.LIBELLE AS ENTETE 
		FROM menu m LEFT JOIN header_menu h ON (m.HEADER = h.IDHEADER) 
		WHERE m.IDMENU IN (SELECT d.IDMENU FROM droit d WHERE d.IDDROIT IN (SELECT l.IDDROIT FROM listedroit l WHERE l.PROFILE = '".parse($this->profile)."')) 
		AND m.ACTIF = 1 
		ORDER BY m.HEADER";
		$this->result =  mysql_query($this->source) or die(mysql_error()); 
		$this->data = new ArrayObject();
		while($row = mysql_fetch_array($this->result)){
			$this->data->append($row);
		}
	}
	function display(){
		print "<div class=\"l\"></div><div class=\"r\"></div>";
		print "<ul class=\"art-menu\">";
		$i = 0;
		foreach($this->data as $row){
			if(!in_array($row['HEADER'], $this->header)){
				if($i != 0)
					print "</ul></li>";
				print "<li><a href=\"#\" class=\"active\"><span class=\"l\"></span><span class=\"r\"></span><span class=\"t\">".$row['ENTETE']."</span></a><ul>";
				$this->header[] = $row['HEADER'];
			}
			$i += 1;
			print "<li><a href=\"".$row['HREF']."\" onclick=\"".$row['ONCLICK']."\">".$row['LIBELLE']."</a></li>";
		}
		print "</ul>";
	}
}
/* Femer la classe menu */
class Etablissement{
	/* L'ordre des parametre dans dans le constructor est important */
	public $identifiant;
	public $libelle;
	public $adresse;
	public $principal;
	public $tel;
	public $mobile;
	public $email;
	public $siteweb;
	public $cptebancaire;
	public $datecreation;		//Date de creation de l'etablissement
	public $autorisation;
	public $logo;		//Logo de l'etablissement, la source de l'image src
	private $db;
	private $datasource;
	public $reglement;	//Le lien du reglement ou est stocke le fichier
	/**
		Constructeur;
		Demande s'il faut generer les infos a partir de la bd ou non
		if not, c'est la creation et les paramettre doivent etre passer
		La liste des parametre doit etre passer a un tableau associatif
	*/
	function __construct($autogenerate = true){
		/* Il faut generer de la bd*/
		if($autogenerate){
			$this->db = new Database("SELECT * FROM etablissement", 0);
			if($this->db->select()){
				$this->datasource = $this->db->getRow();
				$this->identifiant = $this->datasource->item('IDENTIFIANT');
				$this->libelle = $this->datasource->item('LIBELLE');
				$this->adresse = $this->datasource->item('ADRESSE');
				$this->principal = $this->datasource->item('PRINCIPAL');
				$this->logo = $this->datasource->item('LOGO');
				$this->email = $this->datasource->item('EMAIL');
				$this->tel = $this->datasource->item('TEL');
				$this->mobile = $this->datasource->item('MOBILE');
				$this->siteweb = $this->datasource->item('SITEWEB');
				$this->autorisation = $this->datasource->item('AUTORISATION');
				$this->cptebancaire = $this->datasource->item('CPTEBANCAIRE');
				$this->datecreation = $this->datasource->item('DATECREATION');
				$this->reglement = $this->datasource->item('REGLEMENT');
			}else
				die($this->db->getLog('error'));
		}else{
			/** C'est la creation, $param contient donc les attribut,
				remplir les attribut avec, param etant un tableau associatif, 
				les nom des keys dans param est important et refere au attributs
			*/
			$vars = get_class_vars(__CLASS__);
			/* Pour $i = c'est le premier parametre = generate*/
			//print_r($vars);
			$i = 0;
			$args = func_num_args();
			foreach($vars as $key=>$val){
        		//for($i = 0; $i < func_num_args(); $i++){
            	$this->{$key} = $args[$i++];
        	}
		}
	}
}
/************************************************
*	verifie si  l'etudiant est  bloque. 
*	si c le cas, renvoie le motif du   blocage a 
*	travers la variable   $motif
*************************************************/
function is_bloque($matel, &$motif){
	$param = array("eleve" => $matel, "periode" => $_SESSION['periode']);
	$db = new Database("SELECT BLOQUE, MOTIFBLOCAGE FROM eleve_parametre WHERE MATEL = :eleve AND PERIODE = :periode", 0, $param);
	if($db->query()){
		$row = $db->fetch_assoc();
		$motif = $row["MOTIFBLOCAGE"];
		return $row["BLOQUE"] == '1';
	}else
		die($db->getLog('error'));
}
/**************************************************
*
*Function qui verifie le solde de l'etudiant
*Modifie la variable passer en parametre qui est le solde
*VERIFIE la solvabilite de l'etudiant. est ce qu'il peut payer le montant $montant grace au solde $solde de son compte.
*considerant le moratoire d'un montant $montantmor
***************************************************/
function checkaccount($matel, $montant, &$solde = 0, &$montantmor = 0){
	/*$db = new Database("SELECT"
	$res = mysql_query('SELECT solde FROM comptes WHERE MATET=\''.$e.'\'');
	$row = mysql_fetch_array($res);
	$solde = $row[0];*/
	//return ($solde - $montant + $montantmor) >= 0;
	try{
		$pdo = Database::connect2db();		
		$query = "SELECT SUM(o.ACTION) AS MONTANT 
		FROM operation o 
		WHERE o.PERIODE = :periode 
		AND o.IDCOMPTE = (SELECT c.IDCOMPTE FROM compte c WHERE c.CORRESPONDANT = :matel)";
		$res = $pdo->prepare($query);
		$res->execute(array(
			"matel" => $matel,
			'periode' => $_SESSION['periode']
		));
		$row = $res->fetch(PDO::FETCH_BOTH);
		$solde = !empty($row['MONTANT']) ? $row['MONTANT'] : 0;
		return ($solde - $montant + $montantmor) >= 0;
	}catch(PDOException $e){
		die($e->getLine()." ".$e->getMessage()." ".__LINE__." ".__FILE__);
		return false;
	}
}
/**	
*	verifie l'existence du moratoire
*/
function exists_moratoire($moratoire, $matel, &$msg, &$motantmor = 0){
	$db = new Database("SELECT * FROM moratoire WHERE IDMORATOIRE = :moratoire", 0, array("moratoire" => $moratoire));
	if($db->select()){
		if ($db->length == 0){
			$msg = 'Il n\'existe aucun moratoire ayant ce numero';
			return false;
		}else{
			$row = $db->getRow(0);
			$motantmor = $row->item('MONTANT');
			if ($row->item('MATEL') != $matel){
				$msg = 'Aucun moratoire &agrave; ce numero ne vous a &eacute;t&eacute; attribu&eacute;';
				return false;
			}elseif(strtotime($row->item('DATEFIN')) < time()){
				$msg = 'Ce moratoire est expir&eacute;';
				return false;
			}elseif(strtotime($row->item('DATEDEBUT')) > time()){
				$msg = 'Ce moratoire n\'est pas encore debut&eacute;';
				return false;
			}else
				return true;
		}
	}else{
		die($db->getLog('error'));
		return false;
	}
}
/**
	Fonction getImage qui renvoit la balise img
	si le fichier existe et renvoit une balise p
	avec text inscrit pas de photo afficher au centre
	Prend en parametre l'emplacement du fichier, la largeur et la hauteur
*/
function getImage($path = "", $style = "", $w = "100%", $h = "100%"){
	/* Si le fichier exist, renvoyer la balise img */
	if(file_exists($path)){
		return "<img src = '".$path."' style = '".$style."' width = '".$w."' height = '".$h."' />";
	}else{
		return "<div style = 'height:100px; width:120px; background-color:#DDD; text-align:center;font-size:12px;top:35%;'>Pas d'image</div>";
	}
}
/************************************************
*
*	class pour afficher les liens Afin de faciliter l'impression
*	Param: idlink = le lien du fichier print.php et sont id (ex. idlink = printfiche.php?id=$_GET['eleve'],
*			twolink = afficher deux lien pour print avec image et print sans image
*************************************************/
class printlink{
	public $title1 = "Imprimer";
	public $title2 = "Imprimer sans image";
	public $idlink;
	public $twolink;
	function __construct($idlink = "", $twolink = false){
		$this->idlink = $idlink;
		$this->twolink = $twolink;
	}
	
	function display(){
		print "<div class = \"icon-pdf\"><span><a href = '".$this->idlink."' target = '_blank' title = 'Imprimer la fiche'>";
		print "<img src = '../images/icon-pdf2.png' title = '".$this->title1."'></span></a>";
		if($this->twolink){
			print "<span>&nbsp;&nbsp;|&nbsp;&nbsp;<a href = '".$this->idlink."&image=false' target = '_blank' title = 'Imprimer la fiche'>";
			print "<img src = '../images/icon-pdf.gif' title = '".$this->title2."'></a></span>";
		}
		print "</div>";
	}
}
/**
	Inclure les class utilisees 
*/
require_once('../eleves/eleve_inc.php');
require_once('../caisses/frais_inc.php');
require_once('../professeurs/professeur_inc.php');
require_once('../classes/classe_inc.php');
require_once('../enseignements/enseignement_inc.php');
require_once('../matieres/matiere_inc.php');
require_once('../caisses/reduction_inc.php');
/*
	Fonction qui doivent s'excuter independamment et obligatoirement
	dans la page ou ce fichier est inclus
*/
/**
	Confere les premieres lignes du fichier inclus
	database_inc.php pour voir comment les variable HOST, DBNAME etc...
	sont rempli et aide a la connexion
*/
if(defined("HOST") && defined("DBNAME")){
	/* Etablir la connexion */
	connexionDB(HOST, DBPWD, DBNAME, DBUSER);
}else
	die("Erreur d'etablissement la connexion Ligne : ".__LINE__);
resetconnexion();
?>