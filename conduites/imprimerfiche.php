<?php 
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
include_once("../includes/commun_inc.php");
class PDF extends  FPDF{
function entete($val){
		//ADRESSE DE L'EGLISE
		$res = mysql_query("SELECT * FROM locan");
		$ligne = mysql_fetch_array($res);
		$this->Cell(30, 4,utf8_decode($ligne['NOM']).".", 0, 2, $val);
		$this->Cell(30, 4,utf8_decode("Adresse : ".$ligne['ADRESSE']).".", 0, 2, $val);
		$tel = str_replace(";", " - ", $ligne['TEL']);
		$this->Cell(30, 4,"Tel : ".utf8_decode($tel).".", 0, 2, $val);
		$this->Cell(30, 4, "Site Web : ".utf8_decode($ligne['SITEWEB']).".", 0, 2, $val);
		$this->Cell(30, 4, "Email : ".utf8_decode($ligne['EMAIL']).".", 0, 2, $val);
		$this->cell(60);
	}
	function Header(){
		//Images
		$this->Image("../images/logouac.jpg",20 ,8, 25, 25);
		$this->SetXY(80, 30);
		$this->SetTextColor(0,127,255);
		$this->SetFont("Arial", "B", 15);
		$this->Cell(0, 10, "FICHE DE RENSEIGNEMEMTS", 0, 2, "C");
		$this->Cell(0, 10, "DE L'ELEVE", 0, 2, "C");
		$this->Ln(2);
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
		$this->Cell(0,5, "Date : ".$date->fullYear().utf8_decode(" à ").$date->getDateMessage(0), 0, 1, "R");
		$this->Ln(5);
		// Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		// Police Arial italique 8
		$this->SetFont('Arial','I',8);
		// Numéro de page
		$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
	}
function BasicTable(){
	$date = new dateFR("now");
	/*****************************************************************************************************
													ETAT CIVIL
	*****************************************************************************************************/
	$res = mysql_query("SELECT e.MATEL, e.NOMEL, e.DATENAISS, e.SEXE, e.NOMPERE, e.NOMMERE, e.ADRESSE, e.TEL, e.IMAGE,
	(SELECT f.LIBELLE FROM etablissement f WHERE f.IDETS = e.ANCETBS) AS ANC,
	(SELECT f.CLASSE FROM frequenter f WHERE f.MATEL = e.MATEL AND f.PERIODE = '".parse($_GET['periode'])."') AS REDOUBLANT,
	(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = (SELECT f.CLASSE FROM frequenter f WHERE f.MATEL = e.MATEL AND f.PERIODE = '".parse($_GET['periode'])."')) AS CLASS,
	(SELECT r.LIBELLE FROM religion r WHERE r.IDRELIGION = e.RELIGION) AS RELIGION
	 FROM eleve e WHERE e.MATEL = '".parse($_GET['matel'])."'") or die(mysql_error());
	$ligne = mysql_fetch_array($res);
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "I-INFORMATIONS PERSONNELLES", 0, 2, 'L', 1);
	$this->Ln(2);
	if($ligne['IMAGE'] != NULL && !isset($_GET['image']))
		$this->Image($ligne['IMAGE'],$this->GetX() + 125, $this->GetY(), 50, 50);
	$this->SetFont("Times", '', 10);
	$this->SetX(20);
	$this->Write(5, utf8_decode("MATRICULE : ".$ligne['MATEL'].".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("NOM & PRENOM : ".$ligne['NOMEL'].".\n"));$this->SetX(20);
	$date->setSource($ligne['DATENAISS']);
	$this->Write(5, utf8_decode("DATE DE NAISSANCE: ".$date->fullYear(0).".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("LIEU DE NAISSANCE: ".$ligne['LIEUNAISS'].".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("SEXE : ".$ligne['SEXE']).".\n");$this->SetX(20);
	$this->Write(5, utf8_decode("CLASSE : ".$ligne['CLASS']).".\n");$this->SetX(20);
	$this->Write(5, utf8_decode("PERIODE : ".$_GET['periode']).".\n");
	$this->Ln(5);
	/*****************************************************************************************************
													INFORMATIONS RELATIVES
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "II-INFORMATIONS RELATIVES", 0, 2, 'L', 1);
	$this->Ln(2);
	$this->SetFont("Times", '', 10);
	$this->SetX(20);
	$this->Write(5, utf8_decode("NOM PERE : ".$ligne['NOMPERE'].".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("NOM MERE : ".$ligne['NOMMERE']).".\n");$this->SetX(20);
	$this->Write(5, utf8_decode("ADRESSE : ".$ligne['ADRESSE']).".\n");$this->SetX(20);
	$val = str_replace(";", " - ", $ligne['TEL']);
	$this->Write(5, utf8_decode("TELEPHONE : ".$val.".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode(", EMAIL : ".$ligne['EMAIL']).".\n");
	$this->Ln(5);
	/*****************************************************************************************************
												AUTRES INFORMATIONS
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "III- AUTRES INFORMATIONS", 0, 2, 'L', 1);
	$this->Ln(2);
	$this->SetFont("Times", "", 10);
	$this->SetX(20);
	$this->Write(5, utf8_decode("ANCIEN ETABLISSEMENT : ".$ligne['ANC'].".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("RELIGION : ".$ligne['RELIGION'].".\n"));$this->SetX(20);
	$r = mysql_query("SELECT COUNT(SUBSTR(f.PERIODE, 1, 9)) AS NBRE
		FROM frequenter f
		WHERE f.MATEL = '".parse($_GET['matel'])."' AND f.CLASSE = '".parse($ligne['REDOUBLANT'])."'") or die(mysql_error());
		$l = mysql_fetch_array($r);
	if($l['NBRE'] > 1)
		$this->Write(5, utf8_decode("OUI (".$l['NBRE']."). \n"));
	else
		$this->Write(5, utf8_decode("NON.\n"));
	$this->SetX(20);
	$date->setSource($ligne['DATEAJOUT']);
	$this->Write(5, utf8_decode("DATE D'AJOUT:".$date->fullYear(0).".\n"));
}
}
$pdf = new PDF("P");
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>