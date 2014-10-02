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
	private $pdo;
	private $result;
	
	function entete($val){
		$this->Cell(30, 4,utf8_decode($this->etabs->libelle).".", 0, 2, $val);
		$tel = str_replace(";", " - ", $this->etabs->tel);
		$this->Cell(30, 4,utf8_decode("Téléphone(s) : ".$tel).".", 0, 2, $val);
		$this->Cell(30, 4,utf8_decode("Adresse : ".$this->etabs->adresse).".", 0, 2, $val);
		$this->Cell(30, 4, "Email : ".utf8_decode($this->etabs->email).".", 0, 2, $val);
		$this->cell(60);
	}
	function Header(){
		$this->SetFont('Times','',8);
		$this->etabs = new Etablissement();
		$this->titre = "Liste des Elèves inscrits";
		$this->SetXY(10, 10);
		$this->entete('L');
		//IMAGE DU SITE
		$this->Image($this->etabs->logo,85 ,8, 30, 30);
		$this->SetXY(130, 10);
		$this->entete('L');
		$this->SetX(8);
		$this->Ln(12);
		$this->cell(0, 2, '________________________________________________________________________________________',0,1,'C');
		$this->cell(0, 2, '________________________________________________________________________________________',0,1,'C');
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
    	$this->Cell($w,7, utf8_decode($this->titre),0 ,2 ,'C');
		$this->Cell($w,7, utf8_decode("Classe : ".$_GET['classe']." Période : ".$_SESSION['periode']), 0, 1, 'C');
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
		try{
		/* Rechercher tous les eleves inscrit a cette periode */
			$query = "SELECT e.MATEL  
			FROM eleve e 
			INNER JOIN inscription i ON (e.MATEL = i.MATEL AND i.PERIODE = :periode AND i.IDCLASSE = :classe) 
			ORDER BY e.MATEL";
			$this->pdo = Database::connect2db();
			$this->result = $this->pdo->prepare($query);
			$this->result->execute(array(
				'classe' => $_GET['classe'],
				'periode' => $_SESSION['periode']
			));
			$this->data = $this->result->fetchAll(PDO::FETCH_ASSOC);
			$this->colonne = new ArrayObject();
			$this->colonne->append("MATRICULE");
			$this->colonne->append("NOM & PRENOM");
			$this->colonne->append("DATE NAISS");
			$this->colonne->append("TELEPHONE");
			$this->colonne->append("REDOUBLANT");
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	function BasicTable(){
		$this->SetFont('Times','',8);
		$this->LoadData();
		$w[] = 15; $w[] = 70; $w[] = 10; $w[] = 10; $w[] = 7;
		/* Afficher les colonnes */
		$i = 0;
		$this->SetFont('Times','B',8);
		foreach($this->colonne as $val){
			$this->Cell($w[$i++] + 15, 7, $val, 1);
		}
		$this->Ln();
		/* Afficher les lignes */
		$this->SetFont('Times','',8);
		foreach($this->data as $row){
			$i = 0;
			$el = new Eleve($row['MATEL']);
			
			$this->Cell($w[$i++] + 15, 7, utf8_decode($row['MATEL']), 1);
			$this->Cell($w[$i++] + 15, 7, utf8_decode($el->nom." ".$el->prenom), 1);
			$this->Cell($w[$i++] + 15, 7, utf8_decode($el->datenaiss), 1);
			$this->Cell($w[$i++] + 15, 7, utf8_decode($el->tel), 1);
			$this->Cell($w[$i++] + 15, 7, utf8_decode($el->redoublant), 1);
			/* Retour a la fin */
			$this->Ln();
		}
		$this->Ln(5);
		$d = new dateFR(date('Y-m-d', time()));
		$this->Cell(0,5, "Date de l'impression : ".utf8_decode($d->fullYear()), 0, 1, "R");
		$this->Cell(0,5, "Signature.............................................", 0, 1, "R");
		$this->Ln(5);
    	// Mention en italique
    	$this->SetFont('','I');
    	$this->Cell(0,5,"(fin de la liste)");
	}
}
$pdf = new PDF("P");
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>