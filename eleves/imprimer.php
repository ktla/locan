<?php  
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
/******************************************************************************************************/
	require_once("../includes/commun_inc.php");
/******************************************************************************************************/
class PDF extends FPDF{
	private $val;
	private $colonne;
	private $fin;
	private $titre;
	private $etabs;
	private $data;
	private $result;
	
	function entete($val,$x=0){
		$this->Cell(30+$x, 4,utf8_decode($this->etabs->libelle).".", 0, 2, $val);
		$tel = str_replace(";", " - ", $this->etabs->tel);
		$this->Cell(30+$x, 4,utf8_decode("Téléphone(s) : ".$tel).".", 0, 2, $val);
		$this->Cell(30+$x, 4,utf8_decode("Adresse : ".$this->etabs->adresse).".", 0, 2, $val);
		$this->Cell(30+$x, 4, "Email : ".utf8_decode($this->etabs->email).".", 0, 2, $val);
		$this->cell(60);
	}
	
	function Header(){
		$this->SetFont('Times','',8);
		$this->etabs = new Etablissement();
		//Images
		if(empty($this->titre))
			$this->titre = "Liste de tous les élèves";
		$this->SetXY(10, 10);
		$this->entete('L');
		//IMAGE DU SITE
		$this->Image($this->etabs->logo,90 ,8, 30, 30);
		$this->SetXY(170, 10);
		$this->entete('R');
		$this->SetX(8);
		$this->Ln(12);
		$this->cell(0, 2, '*********************************************************************************************',0,1,'C');
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
    	$this->SetLineWidth(1);
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
	function getWidth(){
		$w = array();
		for($i = 0; $i <= $this->result->columnCount(); $i++){
			$arr = $this->result->getColumnMeta($i);
			if(in_array($arr['name'], $this->colonne))
				$w[] = strlen($arr['name']);
		}
		foreach($this->data as $row){
			$j = 0; $i = 0;
			foreach($row as $cel){
				$arr = $this->result->getColumnMeta($i);
				if(in_array($arr['name'], $this->colonne)){
					if($w[$j] < strlen($cel))
						$w[$j] = strlen($cel);
					$j++;
				}
				$i++;
			}
		}
		return $w;
	}
	function LoadData(){
		try{
			/* Se baser sur les colones cocher */
			//$this->colonne = array('NOMEL', 'PRENOM', 'DATENAISS', 'LIEUNAISS', 'NOMPERE', 'ADDRESSEPERE','NOMMERE','ADDRESSEMERE','ADDRESSETUTEUR','TUTEUR','TEL','ADRESSE','EMAIL','RELIGION','ANCETBS','DATE','MATRICULE');
			
			if(isset($_POST['colonne']))
				$this->colonne = $_POST['colonne'];
			/* Requete _if = confere commun_inc.php */
			$query = "SELECT e.* 
			FROM eleve e 
			ORDER BY e.MATEL";
			//print $query;
			$pdo = Database::connect2db();
			$this->result = $pdo->prepare($query);
			$this->result->execute();
			if(!$this->result->rowCount()){
				$this->Cell(0, 10, 'AUCUN ENREGISTREMENT', 1);
				return;
			}
			$this->data = $this->result->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	function BasicTable(){
		$this->LoadData();
		$j = 0;
		$w = $this->getWidth();
		for($k = 0; $k < count($w); $k++)
			$j = $j + $w[$k] + 15;
		/* Center le tableau */
		if($j < 210)
    		$this->SetX((210-$j)/2);
		/* Afficher les colonnes */
		$this->SetFont('Times','B',8);
		$k = 0;
		$this->Cell(7, 7, utf8_decode('N°'), 1);
		for($i = 0; $i < $this->result->columnCount(); $i++){
			$arr = $this->result->getColumnMeta($i);
			if(in_array($arr['name'], $this->colonne))
				$this->Cell($w[$k++] + 15, 7, $arr['name'], 1);
		}
		$this->SetFont('Times','',8);
		$this->Ln();
		if($j < 210)
    		$this->SetX((210-$j)/2);
		/* Afficher les lignes */
		$k = 1;
		foreach($this->data as $row){
			$i = 0;
			$this->Cell(7, 7, $k++, 1);
			$el = new Eleve($row['ID']);
			in_array('NOMEL', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->nom), 1):"";
			in_array('PRENOM', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->prenom), 1):"";
			in_array('DATENAISS', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->datenaiss), 1):"";
			in_array('LIEUNAISS', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->lieunaiss), 1):"";
			in_array('NOMPERE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->nompere), 1):"";
			in_array('ADDRESSEPERE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->addresspere), 1):"";
			in_array('NOMMERE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->mere), 1):"";
			in_array('ADDRESSEMERE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->addressmere), 1):"";
			in_array('TUTEUR', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->tuteur), 1):"";
			in_array('ADDRESSETUTEUR', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->addresstuteur), 1):"";
			in_array('TEL', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, $el->tel, 1):"";
			in_array('ADRESSE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->adresse), 1):"";
			in_array('EMAIL', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->email), 1):"";
			in_array('SEXE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->sexe), 1):"";
			in_array('RELIGION', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->religion), 1):"";
			in_array('ANCETBS', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->ancienEts), 1):"";
			in_array('DATE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->dateajout), 1) :"";
			in_array('MATRICULE', $this->colonne) ? $this->Cell($w[$i++] + 15, 7, decode($el->matricule), 1) :"";
			/* Retour a la ligne */
			$this->Ln();
			if($j < 210)
				$this->SetX((210-$j)/2);
			$i++;
		}
		$this->Ln(5);
		$d = new dateFR(date('Y-m-d', time()));
		$this->Cell(0,5, utf8_decode("Date de l'impression : ".$d->fullYear()), 0, 1, "R");
		$this->Cell(0,5, "Signature.............................................", 0, 1, "R");
		$this->Ln(5);
    	// Mention en italique
    	$this->SetFont('','I');
    	$this->Cell(0,5,"(fin de la liste)");
	}
}
$pdf = new PDF("P");
$pdf->SetAuthor($_SESSION['nom']." ".$_SESSION['nom']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>