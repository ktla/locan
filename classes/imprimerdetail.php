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
$codepage = "DETAILS_CLASSE";
//Si l'utilisateur n'est pas autoriser a voir cette page, le deconnecte
if(!is_autorized($codepage))
	return;
/*
*/
class PDF extends  FPDF{
	private $etbs;
	private $data;
	private $result;
	private $pdo;
	
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
		$this->Cell(0, 10, "DETAILS DE CLASSE", 0, 2, "C");
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
		$this->Cell(0,5, "Date : ".$date->fullYear().utf8_decode(" à ".$date->getDateMessage(0)), 0, 1, "R");
		$this->Ln(5);
		// Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		// Police Arial italique 8
		$this->SetFont('Arial','I',8);
		// Numéro de page
		$this->Cell(0,10, 'Page '.$this->PageNo(),0,0,'C');
	}
	function BasicTable(){
		/*
		*/
		$id = $_GET['id'];
		$classe = new Classe($_GET['id']);
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "I-CLASSE", 0, 2, 'L', 1);
		$this->Ln(2);
		$this->SetFont("Arial", '', 10);
		$this->SetX(20);
		/*****************************************************************************************************
														CLASSE
		*****************************************************************************************************/
		$this->Write(5, utf8_decode("Identifiant : ".$id.".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Libellé : ".$classe->libelle.".\n"));$this->SetX(20);
		$this->Write(5, utf8_decode("Niveau : ".$classe->niveau.".\n"));$this->SetX(20);
		$this->Ln(5);
		/*****************************************************************************************************
														PARAMETRES DE LA CLASSE
		*****************************************************************************************************/
		$this->SetFont("Arial", "B", 10);
		$this->SetFillColor(255,255,51);
		$this->Cell(100, 4, "II - PARAMETRES", 0, 2, 'L', 1);
		$this->Ln(2);
		$this->SetFont("Arial", '', 10);
		$this->SetX(20);
		if($classe->hasProfPrincipal){
			$this->Write(5, utf8_decode("Professeur Principal : ".$classe->profPrincipal->nom." ".$classe->profPrincipal->prenom.".\n"));$this->SetX(20);
		}else{
			$this->Write(5, utf8_decode("Professeur Principal : Aucun Professeur.\n"));$this->SetX(20);
		}
		$this->Write(5, utf8_decode("Taille maximale : ".$classe->tailleMax).".\n");$this->SetX(20);
		$this->Write(5, utf8_decode("Elèves inscrits : ".$classe->getNbInscrit()).".\n");$this->SetX(20);
		$this->Write(5, utf8_decode(($classe->actif == 1) ? "Actif  : OUI" : "Actif : NON").".\n");$this->SetX(20);
		$this->Write(5, utf8_decode("Montant inscription : ".$classe->montantInscription).".\n");$this->SetX(20);
		$this->Ln(5);
		/*****************************************************************************************************
													ENSEIGNEMENTS
		*****************************************************************************************************/
		/*
					Lister les enseignements
		*/
		
		if($classe->getEnseignements()){
			$this->SetFont("Arial", "B", 10);
			$this->SetFillColor(255,255,51);
			$this->Cell(100, 4, "IV - ENSEIGNEMENTS", 0, 2, 'L', 1);
			$this->Ln(2);
			$date = new dateFR("now");
			$this->SetFont("Times", "B", 10);$this->SetX(20);
			/*
				Entete du tableau d'enseignements
			*/
			$colonne = new ArrayObject();
			$colonne->append('MATIERE');
			$colonne->append('PROFESSEUR');
			$colonne->append('COEFF.');
			$colonne->append('ACTIF');
			$w = array();
			$w[] = 70; $w[] = 70; $w[] = 20; $w[] = 20; $w[] = 20; 
			for($i = 0; $i < $colonne->count(); $i++)
				$this->Cell($w[$i], 7,utf8_decode($colonne->offsetGet($i)), 1);
			/*
				Colonne d'enseignements
			*/
			$this->SetFont("Times", "", 10);
			foreach($classe->enseignement as $ens){
				$i = 0;
				$this->Ln();$this->SetX(20);
				$this->Cell($w[$i++], 7,utf8_decode($ens->matiere->libelle), 1);
				$this->Cell($w[$i++], 7,utf8_decode($ens->professeur->nom." ".$ens->professeur->prenom), 1);
				$this->Cell($w[$i++], 7,utf8_decode($ens->coefficient), 1);
				$this->Cell($w[$i++], 7,utf8_decode($ens->actif), 1);
			}
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