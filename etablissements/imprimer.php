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
if(!is_autorized("PRINT_ETABLISSEMENT"))
	return;
/***************************************************************************************************/
/*
	Classe FPDF
*/
class PDF extends  FPDF{
	private $etbs;
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
		$this->Image($this->etbs->logo,20 ,8, 25, 25);
	$this->SetXY(80, 30);
	$this->SetTextColor(0,127,255);
	$this->SetFont("Arial", "B", 15);
	$this->Cell(0, 10, "FICHE DE RENSEIGNEMEMTS", 0, 2, "C");
	$this->Cell(0, 10, "DE L'ETABLISSEMENT", 0, 2, "C");
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
													IDENTITE
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "I-IDENTITE", 0, 2, 'L', 1);
	$this->Ln(2);
	//LOGO DU SITE
	if(!strcmp($_GET['image'], "true"))
		if(!empty($this->etbs->logo))
			$this->Image($this->etbs->logo,$this->GetX() + 125, $this->GetY(), 50, 50);
		else{
			$this->Cell(180, 7, utf8_decode("AUCUNE IMAGE OU LOGO\n"), "", 1, "R");$this->SetX(20);}	
	$this->SetFont("Times", '', 10);
	$this->SetX(20);
	$this->Write(5, utf8_decode("IDENTIFIANT : ".$this->etbs->identifiant.".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("APPELLATION | NOM : ".$this->etbs->libelle.".\n"));$this->SetX(20);
	$date->setSource($this->etbs->datecreation);
	$this->Write(5, utf8_decode("ADRESSE : ".$this->etbs->adresse.".\n"));$this->SetX(20);
	if(!empty($this->etbs->datecreation))
		{$this->Write(5, utf8_decode("DATE DE CREATION : ".$date->fullYear(0).".\n"));$this->SetX(20);}
	$this->Ln(5);
	/*****************************************************************************************************
													INFOS LINES
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "II-INFOSLINES", 0, 2, 'L', 1);
	$this->Ln(2);
	$this->SetFont("Times", '', 10);
	$this->SetX(20);
	$val = str_replace(";", " - ", $this->etbs->tel);
	if(!empty($this->etbs->tel))
		{$this->Write(5, utf8_decode("TELEPHONE : ".$val.".\n"));$this->SetX(20);}
	if(!empty($this->etbs->mobile))
		{$this->Write(5, utf8_decode("MOBILE : ".$this->etbs->mobile).".\n");$this->SetX(20);}
	if(!empty($this->etbs->siteweb))
		{$this->Write(5, utf8_decode("SITE WEB : ".$this->etbs->siteweb).".\n");$this->SetX(20);}
	if(!empty($this->etbs->email))
		{$this->Write(5, utf8_decode("E-MAIL : ".$this->etbs->email).".\n");$this->SetX(20);}
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
	if(!empty($this->etbs->principal))
		{$this->Write(5, utf8_decode("PRINCIPAL : ".$this->etbs->principal).".\n");$this->SetX(20);}
	if(!empty($this->etbs->autorisation))
		{$this->Write(5, utf8_decode("NUMERO D'AUTORISATION : ".$this->etbs->autorisation).".\n");$this->SetX(20);}
	if(!empty($this->etbs->cptebancaire))
		{$this->Write(5, utf8_decode("COMPTE BANCAIRE : ".$this->etbs->cptebancaire).".\n");$this->SetX(20);}
}
}
$pdf = new PDF("P");
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>