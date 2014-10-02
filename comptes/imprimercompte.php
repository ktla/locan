<?php 
/**
	Imprimer la liste des compte
*/
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
		//Images
		if(empty($this->titre))
			$this->titre = "Liste des comptes associés";
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
		try{
			$pdo = Database::connect2db();
			$query = "SELECT c.IDCOMPTE, c.CORRESPONDANT, CONCAT(e.NOMEL, ' ', e.PRENOM) AS NOM, c.DATECREATION, c.AUTEUR 
				FROM compte c 
				INNER JOIN eleve e ON (e.MATEL = c.CORRESPONDANT) 
				UNION
				SELECT c.IDCOMPTE, c.CORRESPONDANT, CONCAT(p.NOMPROF, ' ', p.PRENOM) AS NOM, c.DATECREATION, c.AUTEUR 
				FROM compte c 
				INNER JOIN professeur p ON (p.IDPROF = c.CORRESPONDANT) 
				UNION 
				SELECT c.IDCOMPTE, c.CORRESPONDANT, CONCAT(s.NOM, ' ', s.PRENOM) AS NOM, c.DATECREATION, c.AUTEUR 
				FROM compte c 
				INNER JOIN staff s ON (s.IDSTAFF = c.CORRESPONDANT)";
			$this->result = $pdo->prepare($query);
			$this->result->execute();
			$this->data = $this->result->fetchAll(PDO::FETCH_NUM);
			$this->colonne = new ArrayObject();
			$this->colonne->append("COMPTE");
			$this->colonne->append("MATRICULE");
			$this->colonne->append("NOM & PRENOM");
			$this->colonne->append("DATE CREATION");
			$this->colonne->append("AUTEUR");
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." ".$e->getLine()." ".$e->getFile());
		}
	}
	function BasicTable(){
		$this->LoadData();
		$w = array();
		$w[] = 20; $w[] = 15; $w[] = 60; $w[] = 10; $w[] = 6;
		/* Afficher les colonnes */
		$this->SetFont('Times','B',8);
		/* Afficher les entete de la table */
		for($i = 0; $i < $this->colonne->count(); $i++){
			$this->Cell($w[$i] + 15, 7,utf8_decode($this->colonne->offsetGet($i)), 1);
		}
		$this->SetFont('Times','',8);
		$this->Ln();
		/* Afficher les lignes */
		foreach($this->data as $row){
			$i = 0;
			foreach($row as $val){
				if($i == 3){
					$d = new dateFR($val);
					$val = $d->getDateMessage(3);
				}
				$this->Cell($w[$i++] + 15, 7,utf8_decode($val), 1);
			}
			$this->Ln();
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
$pdf->SetAuthor($_SESSION['user']);
$pdf->AliasNbPages;
$pdf->AddPage();
$pdf->BasicTable();
$pdf->Output();
?>