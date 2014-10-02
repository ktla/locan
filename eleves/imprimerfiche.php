<?php 
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
include_once("../includes/commun_inc.php");
class PDF extends  FPDF{
	private $etabs;
	private $result;
	private $data;
	private $colonne;
	function entete($val){
		$this->Cell(30, 4,utf8_decode($this->etabs->libelle), 0, 2, $val);
		$this->Cell(30, 4,utf8_decode("Adresse : ".$this->etabs->adresse), 0, 2, $val);
		$tel = str_replace(";", " - ", $this->etabs->tel);
		$this->Cell(30, 4,"Tel : ".utf8_decode($tel).".", 0, 2, $val);
		$this->Cell(30, 4, "Site Web : ".utf8_decode($this->etabs->siteweb).".", 0, 2, $val);
		$this->Cell(30, 4, "Email : ".utf8_decode($this->etabs->email).".", 0, 2, $val);
		$this->cell(60);
	}
	function Header(){
		//Images
		$this->etabs = new Etablissement();
		if(file_exists($this->etabs->logo))
			$this->Image($this->etabs->logo, 20 ,8, 25, 25);
		$this->SetXY(80, 30);
		$this->SetFont("Arial", "", 15);
		$this->SetTextColor(0,127,255);
		$this->Cell(0, 10, "FICHE DE RENSEIGNEMEMTS", 0, 2, "C");
		$this->Cell(0, 10, "DE L'ELEVE", 0, 2, "C");
		$this->Ln(2);
		if(file_exists("../images/barre02.jpg"))
			$this->Image("../images/barre02.jpg", 70 ,50, 0, 0);
		
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
function BasicTable(){
	$el = new Eleve($_GET['matel']);
	$date = new dateFR("now");
	/*****************************************************************************************************
													ETAT CIVIL
	*****************************************************************************************************/
	$this->SetFont("Arial", "B", 10);
	$this->SetFillColor(255,255,51);
	$this->Cell(100, 4, "I-INFORMATIONS PERSONNELLES", 0, 2, 'L', 1);
	$this->Ln(2);
	/* Afficher l image */
	//print_r($this->data);
	if(isset($el->image) && isset($_GET['image']))
		if(file_exists($el->image))
			$this->Image($el->image,$this->GetX() + 125, $this->GetY(), 50, 50);
	/* Afficher d autres infos */
	$this->SetFont("Times", '', 10);
	$this->SetX(20);
	$this->Write(5, utf8_decode("MATRICULE : ".$_GET['matel'].".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("NOM & PRENOM : ".$el->nom." ".$el->prenom.".\n"));$this->SetX(20);
	$date->setSource($el->datenaiss);
	$this->Write(5, utf8_decode("DATE DE NAISSANCE: ".$date->fullYear(0).".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("LIEU DE NAISSANCE: ".$el->lieunaiss.".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("SEXE : ".$el->sexe).".\n");$this->SetX(20);
	$this->Write(5, utf8_decode("CLASSE : ".$el->getClasse()).".\n");$this->SetX(20);
	$this->Write(5, utf8_decode("PERIODE : ".$_SESSION['periode']).".\n");
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
	$this->Write(5, utf8_decode("TUTEUR : ".$el->tuteur.".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("ADRESSE : ".$el->adresse).".\n");$this->SetX(20);
	$val = str_replace(";", " - ", $el->tel);
	$this->Write(5, utf8_decode("TELEPHONE : ".$val.".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("EMAIL : ".$el->email).".\n");
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
	$this->Write(5, utf8_decode("ANCIEN ETABLISSEMENT : ".$el->ancienEts.".\n"));$this->SetX(20);
	$this->Write(5, utf8_decode("RELIGION : ".$el->religion.".\n"));$this->SetX(20);
	/* Savoir s il a redoubler la classe actuelle */
	$this->Write(5, utf8_decode("REDOUBLANT : ".$el->redoublant." \n"));
	$this->SetX(20);
	$date->setSource($el->dateajout);
	$this->Write(5, utf8_decode("DATE D'AJOUT :".utf8_decode($date->fullYear(0)).".\n"));
}
}
$pdf = new PDF("P");
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>