<?php 
header('Content-Type: text/html; charset=UTF-8');
include_once("../fpdf17/fpdf.php");
include_once("../includes/commun_inc.php");
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
//Si l'utilisateur n'est pas autoriser a voir cette page, le deconnecte
if(!is_autorized("PRINT_CLASSE")){
	header("location:../utilisateurs/connexion.php");
}
class PDF extends  FPDF{
	private $etbs;
	private $data;
	private $result;
	private $colonne;
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
		$this->Cell(0, 10, "CLASSES ACTIVES", 0, 2, "C");
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
		$this->Cell(0,5, utf8_decode("Date : ".$date->fullYear()." à ".$date->getDateMessage(0)), 0, 1, "R");
		$this->Ln(5);
		// Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		// Police Arial italique 8
		$this->SetFont('Arial','I',8);
		// Numéro de page
		$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
	}
	function loadData(){
		try{
			$pdo = Database::connect2db();
			/* Selectionner les classe actives */
			$query = "SELECT c.IDCLASSE 
			FROM classe c, classe_parametre p 
			WHERE p.ACTIF = :vrai AND p.PERIODE = :periode AND p.IDCLASSE = c.IDCLASSE";
			/* Execute la requete fetchAll methode */
			$this->result = $pdo->prepare($query);
			$this->result->bindValue('vrai', 1, PDO::PARAM_INT);
			$this->result->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				$this->Cell(200, 5, "AUCUN ENREGISTREMENT", 0, 2, 'C');
				return;
			}
			$this->data = $this->result->fetchAll(PDO::FETCH_ASSOC);
			/* Definir les titres des colonnes = Entete de la table */
			$this->colonne = new ArrayObject();
			$this->colonne->append("ID");
			$this->colonne->append("LIBELLE");
			$this->colonne->append("INSCRIT");
			$this->colonne->append("INSCRIPTION");
			$this->colonne->append("CAPACITE");
			$this->colonne->append("FRAIS OBLI");
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
		}
	}
	/*function getWidth(){
		$w = array();
		foreach($this->colonne as $val)
			$w[] = strlen($val);
		
		foreach($this->data as $row){
			$j = 0;
			foreach($row as $cel){
				if($w[$j] < strlen($cel))
					$w[$j] = strlen($cel);
				$j++;
			}
		}
		return $w;
	}*/
	function BasicTable(){
		$this->SetFont("Times", 'B', 9);
		$this->loadData();
		$w = array();
		$w[] = 7; $w[] = 60; $w[] = 5; $w[] = 15; $w[] = 5; $w[] = 7;
		/* Afficher les entete de la table */
		for($i = 0; $i < $this->colonne->count(); $i++){
			$this->Cell($w[$i] + 15, 7,utf8_decode($this->colonne->offsetGet($i)), 1);
		}
		$this->Ln();
		$this->SetFont("Times", '', 9);
		/* Afficher les donnees */
		foreach($this->data as $row){
			$i = 0;
			$classe = new Classe($row['IDCLASSE']);
			$this->Cell($w[$i++] + 15, 7,utf8_decode($row['IDCLASSE']), 1);
			$this->Cell($w[$i++] + 15, 7,utf8_decode($classe->libelle), 1);
			$this->Cell($w[$i++] + 15, 7,utf8_decode($classe->getNbInscrit()), 1);
			$this->Cell($w[$i++] + 15, 7,utf8_decode($classe->montantInscription), 1);
			$this->Cell($w[$i++] + 15, 7,utf8_decode($classe->tailleMax), 1);
			$this->Cell($w[$i++] + 15, 7,utf8_decode($classe->getTotalFaisObligatoires()), 1);
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