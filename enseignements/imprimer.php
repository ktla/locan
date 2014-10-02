<?php 
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
include_once("../includes/commun_inc.php");
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
//Si l'utilisateur n'est pas autoriser a voir cette page, le deconnecte
if(!is_autorized("PRINT_ENSEIGNEMENT"))
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
		$this->Image($this->etbs->logo,20 ,15, 25, 25);
	$this->SetXY(80, 37);
	$this->SetTextColor(0,127,255);
	$this->SetFont("Arial", "B", 15);
	$this->Cell(0, 10, "LES ENSEIGNEMENTS", 0, 2, "C");
	$this->Cell(0, 10, "DE L'ETABLISSEMENT", 0, 2, "C");
	$this->Ln(2);
	$this->Image("../images/barre02.jpg",70 ,57, 0, 0);
	$this->SetFont('Times','B',8);
	$this->SetXY(10, 40);
	$this->entete('L');
	$this->Rect(90, 10, 0, 57);
	$this->Ln(10);
}
	// Pied de page
function Footer(){
	$this->SetFont("Times", "I", 9);
	$this->Ln(10);
	$date = new dateFR(date("Y-m-d H:i:s", time()));
	$this->Cell(0,5, "Date : ".utf8_decode($date->fullYear()."  ".$date->getDateMessage(0)), 0, 1, "R");
	$this->Ln(5);
	// Positionnement à 1,5 cm du bas
	$this->SetY(-15);
	// Police Arial italique 8
	$this->SetFont('Arial','I',8);
	// Numéro de page
	$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
}
function loadData(){
	$query = "SELECT e.IDENSEIGNEMENT AS ID, 
		(SELECT m.LIBELLE FROM matiere m WHERE e.CODEMAT = m.CODEMAT) AS MATIERE, 
		(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = e.CLASSE) AS CLASSE, 
		(SELECT CONCAT(p.NOMPROF,' ', p.PRENOM) FROM professeur p WHERE e.PROF = p.IDPROF) AS PROFESSEUR, e.COEFF 
		 FROM enseigner e 
		 WHERE e.PERIODE = '".$_SESSION['periode']."' ORDER BY e.CODEMAT";
	$this->result = mysql_query($query) or die(mysql_error());
	if(!$this->result)
		die("Erreur de la requete ".mysql_error());
	$this->data = new ArrayObject();
	while($row = mysql_fetch_row($this->result)){
		$this->data->append($row);
	}
}
function BasicTable(){
	$this->loadData();
	if(!mysql_num_rows($this->result)){
		$this->Cell(200, 5, "AUCUN ENREGISTREMENT", 0, 2, 'C');
		return;
	}
	$w = array();
	$w[] = 10; $w[] = 50; $w[] = 50; $w[] = 60; $w[] = 15;
	$this->SetFont("Times", 'B', 9);
	for($i = 0; $i < mysql_num_fields($this->result); $i++)
		$this->Cell($w[$i], 7,utf8_decode(mysql_field_name($this->result, $i)), 1);
	$this->Ln();
	$this->SetFont("Times", '', 9);
	foreach($this->data as $row){
		$i = 0;
		foreach($row as $col)
			$this->Cell($w[$i++], 7,utf8_decode($col), 1);
		$this->Ln();
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