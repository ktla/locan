<?php 
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
/******************************************************************************************************/
	require_once("../includes/commun_inc.php");
/******************************************************************************************************/
/*
	Verifie l'authencite de l'utilisateur
	- Redirection si utilisateur non autorise
	- Redirection si la duree de la session est terminer 
	- Confere commun_in.php pour la duree de la session dans la variable $_SESSION['timeconnect'];
*/
if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
$codepage = "FRAIS_FICHE";
//Si l'utilisateur n'est pas autoriser a voir cette page, le deconnecte
if(!is_autorized($codepage))
	return;
/*
*/
class PDF extends  FPDF{
	private $etbs;
	private $data;
	private $result;
	private $db;
	/* Stocke les resultat des requetes */
	private $classe;
	private $officiels;
	private $occasionnels;
	private $reduction;
	
	function entete($val){
		$this->Cell(30, 4,utf8_decode($this->etbs->identifiant).".", 0, 2, $val);
		$this->Cell(30, 4,utf8_decode($this->etbs->libelle).".", 0, 2, $val);
		$this->Cell(30, 4,utf8_decode("Adresse : ".$this->etbs->adresse).".", 0, 2, $val);
		$tel = str_replace(";", " - ", $this->etbs->tel);
		$tel = str_replace(":", " - ", $tel);
		if(!empty($this->etbs->tel))
			$this->Cell(30, 4,utf8_decode("Tél : ".$tel."."), 0, 2, $val);
		if(!empty($this->etbs->mobile))
			$this->Cell(30, 4, "Mobile : ".utf8_decode($this->etbs->mobile).".", 0, 2, $val);
		if(!empty($this->etbs->siteweb))
			$this->Cell(30, 4, "Site Web : ".utf8_decode($this->etbs->siteweb).".", 0, 2, $val);
		if(!empty($this->etbs->email))
			$this->Cell(30, 4, "Email : ".utf8_decode($this->etbs->email).".", 0, 2, $val);
		$this->cell(60);
	}
	function Header(){
		//Images
		$this->etbs = new etablissement();
		if(!empty($this->etbs->logo))
			if(file_exists($this->etbs->logo))
				$this->Image($this->etbs->logo,20 ,8, 25, 25);
		$this->SetXY(80, 30);
		$this->SetTextColor(0,127,255);
		$this->SetFont("Arial", "B", 15);
		$this->Cell(0, 10, "FRAIS DE CLASSE", 0, 2, "C");
		$this->Ln(2);
		if(file_exists('../images/barre02.jpg'))
			$this->Image("../images/barre02.jpg",70 ,50, 0, 0);
		$this->SetFont('Times','B',8);
		$this->SetXY(10, 33);
		$this->entete('L');
		$this->Rect(90, 10, 0, 53);
		$this->Ln(10);
	}
		// Pied de page
	function Footer(){
		$this->SetFont("Times", "I", 9);
		$this->Ln(10);
		$date = new dateFR(date("Y-m-d H:i:s", time()));
		$this->Cell(0,5, "Date : ".utf8_decode($date->fullYear()." à ".$date->getDateMessage(0)), 0, 1, "R");
		$this->Ln(5);
		// Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		// Police Arial italique 8
		$this->SetFont('Arial','I',8);
		// Numéro de page
		$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
	}
	function loadData(){
		/* Pour les infos de classe */
		$this->db = new Database("SELECT * FROM classe WHERE IDCLASSE = :classe", 0, array("classe" => $_GET['id']));
		if($this->db->query()){
			$this->classe = $this->db->fetch_array();
		}else
			die($this->db->getLog("error"));
		/* Pour les frais officiels */
		$param = array("classe" => $_GET['id'], "periode" => $_SESSION['periode'], "type" => "0");
		$query = "SELECT f.CODE, f.LIBELLE, f.DATEDEBUT, f.DATEFIN, f.MONTANT 
		FROM classe_frais f 
		WHERE f.IDCLASSE = :classe AND f.PERIODE = :periode AND f.TYPE = :type";
		$this->db->setQuery($query, $param);
		if($this->db->query()){
			$this->officiels = $this->db->fetchAll("assoc");
		}else
			die($this->db->getLog("error"));
		/* Pour les frais occasionnels */
		$param = array("classe" => $_GET['id'], "periode" => $_SESSION['periode'], "type" => "1");
		$this->db->setQuery($query, $param);
		if($this->db->query()){
			$this->occasionnels = $this->db->fetchAll("assoc");
		}else
			die($this->db->getLog("error"));
		/* Pour les reductions */
		$query = "SELECT r.CODE, r.LIBELLE, f.LIBELLE AS APPLIQUEA, 
			IF(r.TYPE = 'pourcentage', CONCAT(r.MONTANT, '%'), r.MONTANT) AS MONTANT 
			FROM classe_reduction r 
			LEFT JOIN classe_frais f ON (r.IDFRAIS = f.ID) 
			WHERE r.IDFRAIS IN 
			(SELECT f2.ID FROM classe_frais f2 WHERE f2.IDCLASSE = :classe AND f2.PERIODE = :periode)";
		$this->db->setQuery($query, array("classe" => $_GET['id'], "periode" => $_SESSION['periode']));
		if($this->db->query()){
			$this->reduction = $this->db->fetchAll("assoc");
		}else
			die($this->db->getLog("error"));
	}
	function BasicTable(){
		/*
		*/
		$this->loadData();
		//print_r(count($this->classe));
		if(!count($this->classe) || !count($this->officiels)){
			$this->Cell(200, 5, "AUCUN ENREGISTREMENT", 0, 2, 'C');
			return;
		}
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "I-CLASSE", 0, 2, 'L', 1);
		$this->Ln(2);
		$this->SetFont("Arial", '', 10);
		$this->SetX(20);
		/*****************************************************************************************************
														CLASSE
		*****************************************************************************************************/
		$this->Write(5, utf8_decode("Identifiant : ".$this->classe['IDCLASSE'].".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Libellé : ".$this->classe['LIBELLE'].".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Niveau : ".$this->classe['NIVEAU'].".\n"));$this->SetX(20);
		$this->Ln(5);
		/*****************************************************************************************************
														FRAIS DE LA CLASSE
		*****************************************************************************************************/
		if(count($this->officiels))
			$this->getFraisOfficiels();
		if(count($this->occasionnels))
			$this->getFraisOccasionnels();
		if(count($this->reduction))
			$this->getReductions();
	}
	/* Function d'affichage des frais */
	function getFraisOfficiels(){
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "III - FRAIS OFFICIELS", 0, 2, 'L', 1);
		$this->Ln(2);
		$date = new dateFR("now");
		$this->SetFont("Times", "B", 10);$this->SetX(20);
		$w = array();
		$w[] = 15; $w[] = 90; $w[] = 25; $w[] = 25; $w[] = 30;
		/*
			Entete du tableau d'enseignements
		*/
		$column = $this->officiels[0]; $i = 0;
		foreach($column as $key=>$val)
			$this->Cell($w[$i++], 7,utf8_decode($key), 1);
		/* Affichage des lignes */
		$this->SetFont("Times", "", 10);
		foreach($this->officiels as $row){
			$i = 0; $this->Ln(); $this->SetX(20);
			foreach($row as $key=>$val){
				if($i == 2 || $i == 3){
					$d = new dateFR($val);
					$val = $d->getDate()."-".$d->getMois(3)."-".$d->getYear();
				}
				$this->Cell($w[$i++], 7,utf8_decode($val), 1);
			}
		}
	}
	function getFraisOccasionnels(){
		$this->Ln(10);
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "II - FRAIS OCCASIONNELS", 0, 2, 'L', 1);
		$this->Ln(2);
		$date = new dateFR("now");
		$this->SetFont("Times", "B", 10);$this->SetX(20);
		$w = array();
		$w[] = 15; $w[] = 90; $w[] = 25; $w[] = 25; $w[] = 30;
		/*
			Entete du tableau d'enseignements
		*/
		$column = $this->occasionnels[0]; $i = 0;
		foreach($column as $key=>$val)
			$this->Cell($w[$i++], 7,utf8_decode($key), 1);
		/* Affichage des lignes */
		$this->SetFont("Times", "", 10);
		foreach($this->occasionnels as $row){
			$i = 0; $this->Ln(); $this->SetX(20);
			foreach($row as $key=>$val){
				if($i == 2 || $i == 3){
					$d = new dateFR($val);
					$val = $d->getDate()."-".$d->getMois(3)."-".$d->getYear();
				}
				$this->Cell($w[$i++], 7,utf8_decode($val), 1);
			}
		}
	}
	function getReductions(){
		$this->Ln(10);
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Ln(2);
		$this->Cell(100, 4, "II - REDUCTIONS", 0, 2, 'L', 1);
		$this->Ln(2);
		$date = new dateFR("now");
		$this->SetFont("Times", "B", 10);$this->SetX(20);
		$w = array();
		$w[] = 15; $w[] = 70; $w[] = 70; $w[] = 20;
		/*
			Entete du tableau d'enseignements
		*/
		$column = $this->reduction[0]; $i = 0;
		foreach($column as $key=>$val)
			$this->Cell($w[$i++], 7,utf8_decode($key), 1);
		/* Affichage des lignes */
		$this->SetFont("Times", "", 10);
		foreach($this->reduction as $row){
			$i = 0; $this->Ln(); $this->SetX(20);
			foreach($row as $key=>$val)
				$this->Cell($w[$i++], 7,utf8_decode($val), 1);
		}
	}
}
$pdf = new PDF("P");
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>