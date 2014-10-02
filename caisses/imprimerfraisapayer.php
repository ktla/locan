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
$codepage = "CAISSE_FRAIS";
//Si l'utilisateur n'est pas autoriser a voir cette page, le deconnecte
if(!is_autorized($codepage))
	return;
/*
*/
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
			if(file_exists($this->etbs->logo))
				$this->Image($this->etbs->logo,20 ,8, 25, 25);
		$this->SetXY(80, 30);
		$this->SetTextColor(0,127,255);
		$this->SetFont("Arial", "B", 15);
		$this->Cell(0, 10, "FRAIS DE CLASSE", 0, 2, "C");
		$this->Ln(2);
		if(file_exists('../images/barre02.jpg'))
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
		$this->Cell(0,5, "Date : ".utf8_decode($date->fullYear()." à ".$date->getDateMessage(0)), 0, 1, "R");
		$this->Ln(5);
		// Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		// Police Arial italique 8
		$this->SetFont('Arial','I',8);
		// Numéro de page
		$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
	}
	function LoadData(){
		try{
			$query = "
			-- Frais d inscription 
			SELECT p.ID, 'Montant Inscription' AS LIBELLE, p.MONTANTINSCRIPTION AS MONTANT, 'Indefini' AS DATEOP,
			'Payer' AS STATUT  
			FROM classe_parametre p 
			LEFT JOIN inscription i ON (i.PERIODE = :periode AND i.MATEL = :matel AND p.PERIODE = i.PERIODE) 
			WHERE p.IDCLASSE = i.IDCLASSE AND p.PERIODE = :periode 
			UNION 
			SELECT p.ID, f.LIBELLE, f.MONTANT, p.DATEOP, 
			IF(p.STATUT = 0, 'Non Payer', 'Payer') AS STATUT  
			FROM frais_apayer p 
			LEFT JOIN classe_frais f ON (f.ID = p.IDFRAIS)
			WHERE p.MATEL = :matel AND p.IDINSCRIPTION = (SELECT i.IDINSCRIPTION FROM inscription i WHERE i.PERIODE = :periode AND i.MATEL = p.MATEL) 
			UNION 
			SELECT o.ID, r.LIBELLE, IF(r.TYPE = 'pourcentage', CONCAT(r.MONTANT,' ', '%'), r.MONTANT) AS MONTANT, o.DATEOP, 
			IF(o.STATUT = 0, 'Non Payer', 'Payer') AS STATUT 
			FROM reduction_obtenue o 
			LEFT JOIN classe_reduction r ON (r.ID = o.IDREDUCTION) 
			WHERE o.MATEL = :matel AND o.IDINSCRIPTION = (SELECT i.IDINSCRIPTION FROM inscription i WHERE i.PERIODE = :periode AND i.MATEL = o.MATEL)";
			$pdo = Database::connect2db();
			$this->result = $pdo->prepare($query);
			$this->result->bindValue('periode', $_SESSION['periode'], PDO::PARAM_STR);
			$this->result->bindValue('matel', $_GET['id'], PDO::PARAM_STR);
			$this->result->execute();
			$this->data = $this->result->fetchAll(PDO::FETCH_ASSOC);
			$this->colonne = new ArrayObject();
			$this->colonne->append('ID');
			$this->colonne->append('LIBELLE');
			$this->colonne->append('MONTANT');
			$this->colonne->append('DATE OPERATION');
			$this->colonne->append('ETAT');
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
	}
	function BasicTable(){
		$this->LoadData();
		if(!$this->result->rowCount()){
			$this->Cell(200, 5, "AUCUN ENREGISTREMENT", 0, 2, 'C');
			return;
		}
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "I - INFORMATION SUR L'ELEVE", 0, 2, 'L', 1);
		$this->Ln(2);
		$this->SetFont("Arial", '', 10);
		$this->SetX(20);
		$eleve = new Eleve($_GET['id']);
		/*****************************************************************************************************
														CLASSE
		*****************************************************************************************************/
		$this->Write(5, utf8_decode("Matricule : ".$_GET['id'].".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Nom et Prénom : ".$eleve->nom." ".$eleve->prenom.".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Date de Naissance : ".$eleve->datenaiss.".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Classe actuelle : ".$eleve->getClasse().".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Redoublant : ".($eleve->isRedoublant()?'OUI':'NON').".\n"));$this->SetX(20);
		$this->Ln(5);
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "II - FRAIS ET REDUCTIONS SUR L'ELEVE", 0, 2, 'L', 1);
		$this->Ln(5);
		/* Entete du tableau */
		$this->SetFont("Times", "B", 10);
		$w = array();
		$w[] = 10; $w[] = 80; $w[] = 25; $w[] = 45; $w[] = 25;
		$i = 0;
		foreach($this->colonne as $val)
			$this->Cell($w[$i++], 7,utf8_decode($val), 1);
		/* Affichage des lignes */
		$this->SetFont("Times", "", 10);
		$this->Ln();
		foreach($this->data as $row){
			$i = 0;
			foreach($row as $val){
				if($i == 3){
					$d = new dateFR($val);
					$val = $d->getDate()."-".$d->getMois(3)."-".$d->getYear();
				}
				$this->Cell($w[$i++], 7,utf8_decode($val), 1);
			}
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