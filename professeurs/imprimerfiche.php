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
$codepage = "PRINT_FICHE_PROFESSEUR";
//Si l'utilisateur n'est pas autoriser a voir cette page, le deconnecte
if(!is_autorized($codepage))
	return;
/*
*/
class PDF extends  FPDF{
	private $etbs;
	private $data;
	private $result;
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
	$this->Cell(0, 10, "FICHE DE RENSEIGNEMENT", 0, 2, "C");
	$this->Cell(0, 10, "DU PROFESSEUR", 0, 2, "C");
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
function loadData(){
	  $query = "SELECT p.*, r.LIBELLE AS LIBELLERELIGION, d.LIBELLE AS LIBELLEDIPLOME 
	  FROM professeur p 
	  LEFT JOIN religion r ON (r.IDRELIGION = p.RELIGION) 
	  LEFT JOIN diplome d ON (d.IDDIPLOME = p.DIPLOME) 
	  WHERE IDPROF = '".parse($_GET['id'])."'";
	 $this->result = mysql_query($query) or die(mysql_error());
	 if(mysql_num_rows($this->result))
		$this->data = mysql_fetch_array($this->result);
}
function BasicTable(){
	$date = new dateFR("now");
	/*****************************************************************************************************
													ETAT CIVIL
	*****************************************************************************************************/
	$this->loadData();
	if(!mysql_num_rows($this->result)){
		$this->Cell(200, 5, "AUCUN ENREGISTREMENT", 0, 2, 'C');
		return;
	}
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "I-IDENTITE", 0, 2, 'L', 1);
	$this->Ln(2);
	if(!strcmp("true", $_GET['image'])){
		if(!empty($this->data['PHOTO'])){
			$this->Image($this->data['PHOTO'],$this->GetX() + 125, $this->GetY(), 50, 50);
		}else{
			$this->Cell(180, 7, utf8_decode("AUCUNE IMAGE DU PROFESSEUR"), "", 1, "R");$this->SetX(20);}	
	}
	$this->SetFont("Arial", '', 10);
	$this->SetX(20);
	$this->Write(5, utf8_decode("MATRICULE : ".$this->data['IDPROF'].".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("NOM : ".$this->data['NOMPROF'].".\n"));$this->SetX(20);
	if(!empty($this->data['PRENOM'])){
		$this->Write(5, utf8_decode("PRENOM : ".$this->data['PRENOM'].".\n"));$this->SetX(20);
	}
	if(!empty($this->data['DATENAISS']) && strcmp("0000-00-00", $this->data['DATENAISS'])){
		$date->setSource($this->data['DATENAISS']);
		$this->Write(5, utf8_decode("DATE DE NAISSANCE: ".$date->fullYear(0).".\n"));$this->SetX(20);
	}
	$this->Write(5, utf8_decode("SEXE : ".$this->data['SEXE']).".\n");$this->SetX(20);
	$this->Ln(5);
	/*****************************************************************************************************
													INFORMATIONS RELATIVES
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "II-INFOLINES", 0, 2, 'L', 1);
	$this->Ln(2);
	$this->SetFont("Arial", '', 10);
	$this->SetX(20);
	if(!empty($this->data['ADRESSE'])){
		$this->Write(5, utf8_decode("ADRESSE : ".$this->data['ADRESSE'].".\n"));$this->SetX(20);
	}
	if(!empty($this->data['TEL'])){
		$this->Write(5, utf8_decode("TELEPHONE : ".$this->data['TEL']).".\n");$this->SetX(20);
	}
	if(!empty($this->data['EMAIL'])){
		$this->Write(5, utf8_decode("E-MAIL : ".$this->data['EMAIL']).".\n");$this->SetX(20);
	}
	$this->Ln(5);
	/*****************************************************************************************************
												AUTRES INFORMATIONS
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "III- AUTRES INFORMATIONS", 0, 2, 'L', 1);
	$this->Ln(2);
	$this->SetFont("Arial", "", 10);
	$this->SetX(20);
	if(!empty($this->data['DATEDEBUT']) && strcmp("0000-00-00", $this->data['DATEDEBUT'])){
		$date->setSource($this->data['DATEDEBUT']);
		$this->Write(5, utf8_decode("ANCIENNETE : ".$date->fullYear().".\n"));$this->SetX(20);
	}
	if(!empty($this->data['DIPLOME'])){
		$this->Write(5, utf8_decode("DIPLOME : ".$this->data['LIBELLEDIPLOME'].".\n"));$this->SetX(20);
	}
	if(!empty($this->data['RELIGION'])){
		$this->Write(5, utf8_decode("RELIGION : ".$this->data['LIBELLERELIGION'].".\n"));$this->SetX(20);
	}
	if($this->data['ACTIF'] == 1){
		$this->Write(5, utf8_decode("ETAT : Actif.\n"));$this->SetX(20);
	}else{
		$this->Write(5, utf8_decode("ETAT : Bloqué.\n"));$this->SetX(20);
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