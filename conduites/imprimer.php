<?php 
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
include_once("../includes/commun_inc.php");
class PDF extends  FPDF{
	private $val;
	private $colonne;
	private $fin;
	private $titre;
	function cheickColonne(){
		$this->colonne = "";
		$this->val = 1;
		if(!isset($_GET['action'])){
			$this->colonne = "CONCAT (NOM,'  ',PRENOM) AS 'NOM&PRENOM',STATUS,ETATMATR,PROF ,TEL,EMAIL ";
			$this->val = 6;
		}
		elseif(!is_array($_POST['colonne']))
			$this->colonne = $_POST['colonne'];
		else{
			for($i = 0; $i < count($_POST['colonne']) - 1; $i++){
				$this->colonne .= $_POST['colonne'][$i].",";
				$this->val++;
			}
			$this->colonne .= $_POST['colonne'][$i];
		}
	}
	function entete($val){
		//ADRESSE DE L'EGLISE
		$res = mysql_query("SELECT * FROM locan");
		$ligne = mysql_fetch_array($res);
		$this->Cell(30, 4,utf8_decode($ligne['NOM']).".", 0, 2, $val);
		$tel = str_replace(";", " - ", $ligne['TEL']);
		$this->Cell(30, 4,utf8_decode("Téléphone(s) : ".$tel).".", 0, 2, $val);
		$this->Cell(30, 4,utf8_decode("Adresse : ".$ligne['ADRESSE']).".", 0, 2, $val);
		$this->Cell(30, 4, "Email : ".utf8_decode($ligne['EMAIL']).".", 0, 2, $val);
		$this->cell(60);
	}
	function Header(){
		//Images
		if(empty($this->titre))
			$this->titre = "Liste des élèves: PERIODE ".$_SESSION['periode'];
		$this->SetFont('Times','',8);
		$this->SetXY(10, 10);
		$this->entete('L');
		//IMAGE DU SITE
		$this->Image("../images/logouac.jpg",85 ,8, 30, 30);
		$this->SetXY(130, 10);
		$this->entete('L');
		$this->SetX(8);
		$this->Ln(10);
		$this->cell(0, 2, '_____________________________________________________________________',0,1,'C');
		$this->cell(0, 2, '_____________________________________________________________________',0,1,'C');
		$this->Ln(5);
    	// Calcul de la largeur du titre et positionnement
    	$w = $this->GetStringWidth($this->titre) + 10;
    	$this->SetX((210-$w)/2);
    	// Couleurs du cadre, du fond et du texte
    	//$this->SetDrawColor(0,80,180);
    	//$this->SetFillColor(200,220,255);
		$this->SetFont("Arial", "B", 15);
    	$this->SetTextColor(0,127,255);
    	// Epaisseur du cadre (1 mm)
    	//$this->SetLineWidth(1);
    	// Titre
    	$this->Cell($w,5, utf8_decode($this->titre),0 , 1,'C');
		$this->Ln(5);
	}
	// Pied de page
	function Footer(){
		// Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		// Police Arial italique 8
		$this->SetFont('Arial','I',8);
		// Numéro de page
		$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
	}
	function LoadData(){
		$this->cheickColonne();
		$query = "SELECT MATEL AS MATRICULE, NOMEL AS 'NOM & PRENOM', TEL AS TELEPHONE,
		(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = (SELECT f.CLASSE FROM frequenter f WHERE e.MATEL = f.MATEL AND f.PERIODE = '".$_SESSION['periode']."')) AS CLASSE,
		ADRESSE
		FROM eleve e ORDER BY MATEL";
		$result = mysql_query($query) or die("Erreur de LoadData ".mysql_error());
		$this->fin = mysql_num_fields ($result);
		$data = array();
		$header = array();
		$header[] = "N°";
		for($i = 0; $i < $this->fin; $i++)
			$header[] = mysql_field_name($result, $i);
			
		$data[0] = $header;
		$i = 1;
		while($row = mysql_fetch_row($result)){
			$data[$i][0] = $i;
			foreach($row as $val)
				$data[$i][] = $val;
			$i++;
		}
		return $data;
	}
	function getWidth($res){
		$w = array();
		for($i = 0; $i < $this->val; $i++)
			$w[] = 0;
		foreach($res as $row){
			$j = 0;
			foreach($row as $cel){
				if($w[$j] < strlen($cel))
					$w[$j] = strlen($cel);
				$j++;
			}
		}
		return $w;
	}
	function BasicTable($data){
		$j = 0;
		$w = $this->getWidth($data);
		for($k = 0; $k < count($w); $k++)
			$j = $j + $w[$k] + 15;
		if($j < 210)
    		$this->SetX((210-$j)/2);
		reset($w);
		$this->SetFont("Times", "", 10);
		/*for($i = 0; $i < count($w); $i++)
			$this->Cell(15, 5, $w[$i], 1);
		$this->Ln();*/
		foreach($data as $row){
			$i = 0;
			foreach($row as $col){
				$this->Cell($w[$i++] + 15, 7,utf8_decode($col), 1);
			//$i++;
			}
			$this->Ln();
			if($j < 210)
				$this->SetX((210-$j)/2);
		}
		$this->Ln(5);
		$d = new dateFR(date('Y-m-d', time()));
		$this->Cell(0,5, "Date de l'impression : ".$d->fullYear(), 0, 1, "R");
		$this->Cell(0,5, "Signature.............................................", 0, 1, "R");
		$this->Ln(5);
    	// Mention en italique
    	$this->SetFont('','I');
    	$this->Cell(0,5,"(fin de la liste)");
	}
}
$pdf = new PDF("P");
//$pdf->SetTitle($titre);
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$data = $pdf->LoadData($query);
$pdf->BasicTable($data);
$pdf->Output();
?>