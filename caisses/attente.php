<?php 
/********************************************************
	Table impliquees : frais_apayer et reduction_obtenue
	et operation_attente
*********************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
$titre = "Operation en attente";
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "OPERATION_ATTENTE";
/*******************************************************/
require_once("../includes/header_inc.php");
if(isset($_GET['matel']))
	valider();
else
	lister();
/*******************************************************
	Fonction relative de la page
	
/******************************************************/
function lister(){?>
<div id="zonetravail"><div class="titre">Frais &agrave; payer</div>
<form action="attente.php" method="GET" enctype="multipart/form-data" onSubmit="lister(); return false">
	<div class="cadre">
 		<fieldset><legend>Choisir l'&eacute;l&egrave;ve</legend>
        	<?php
				$pdo = Database::connect2db();
				$res = $pdo->prepare("SELECT * FROM operation_attente WHERE PERIODE = :periode");
				$res->execute(array(
					'periode' => $_SESSION['periode']
				));
				if(!$res->rowCount()){
					print "<p class = 'infos'>Aucune op&eacute;ration en cours de comptabilisation pour cette p&eacute;riode</p>";
					return;
				}?>
				<table><tr><td class = 'lib'>Choisir l'&eacute;l&egrave;ve </td><td>
                <?php
				$eleve = new Combo("SELECT o.CONCERNER, CONCAT(e.NOMEL,' ',e.PRENOM) 
				FROM operation_attente o 
				LEFT JOIN eleve e ON (e.MATEL = o.CONCERNER) 
				WHERE o.PERIODE = :periode", "matel", 0, 1, false);
				$eleve->param = array('periode' => $_SESSION['periode']);
				$eleve->first = "--Choisir un &eacute;l&egrave;ve--";
				$eleve->view();
				?>
				</td></tr></table>
        </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" value="Retour" onClick="home();" />
        <input type="submit" value="Valider" />
    </div>
</form>
</div>
<?php
}
function valider(){
try{
	$pdo = Database::connect2db();
	if(isset($_GET['debloquer']) && isset($_GET['terminer'])){
		if(intval($_GET['debloquer']) == 1){
			$res = $pdo->prepare("DELETE FROM operation_attente WHERE CONCERNER = :matel AND PERIODE = :periode");
			$res->execute(array(
				'matel' => $_GET['matel'],
				'periode' => $_SESSION['periode']
			));
			$_SESSION['infos'] = $_GET['matel']." a &eacute;t&eacute; lib&eacute;r&eacute; de toute operation comptable";
		}
		@header("Location:attente.php");
	}
	$res = $pdo->prepare("SELECT CONCAT(e.NOMEL, ' ', e.PRENOM) AS NOMEL FROM eleve e WHERE e.MATEL = :matel");
	$res->bindValue('matel', $_GET['matel'], PDO::PARAM_STR);
	$res->execute();
	$row = $res->fetch(PDO::FETCH_BOTH);
?>
<div id="zonetravail"><div class="titre">Frais &agrave; payer par <?php echo $_GET['matel'].": ".$row['NOMEL'];?></div>
<form action="attente.php" method="GET" enctype="multipart/form-data" name="frm">
	<div class="cadre">
    <?php
	/* Selectionner les frais a payer */
	$query = "
	-- Frais d inscription 
	SELECT p.ID, 'Montant Inscription' AS LIBELLE, p.MONTANTINSCRIPTION AS MONTANT, 'Indefini' AS DATEOP,
	'Payer' AS STATUT  
	FROM classe_parametre p 
	LEFT JOIN inscription i ON (i.PERIODE = :periode AND i.MATEL = :matel AND p.PERIODE = i.PERIODE) 
	WHERE p.IDCLASSE = i.IDCLASSE AND p.PERIODE = :periode 
	UNION 
	SELECT p.ID, f.LIBELLE, f.MONTANT, p.DATEOP, 
	CONCAT(\"<a href = 'payer.php?matel=".$_GET['matel']."&type=0&id=\", p.ID, \"'>\", IF(p.STATUT = 0, 'Non Payer', 'Payer'), \"</a>\") AS STATUT  
	FROM frais_apayer p 
	LEFT JOIN classe_frais f ON (f.ID = p.IDFRAIS)
	WHERE p.MATEL = :matel AND p.IDINSCRIPTION = (SELECT i.IDINSCRIPTION FROM inscription i WHERE i.PERIODE = :periode AND i.MATEL = p.MATEL) 
	UNION 
	SELECT o.ID, r.LIBELLE, IF(r.TYPE = 'pourcentage', CONCAT(r.MONTANT,' ', '%'), r.MONTANT) AS MONTANT, o.DATEOP, 
	CONCAT(\"<a href = 'payer.php?matel=".$_GET['matel']."&type=1&id=\", o.ID, \"'>\", IF(o.STATUT = 0, 'Non Payer', 'Payer'), \"</a>\") AS STATUT 
	FROM reduction_obtenue o 
	LEFT JOIN classe_reduction r ON (r.ID = o.IDREDUCTION) 
	WHERE o.MATEL = :matel AND o.IDINSCRIPTION = (SELECT i.IDINSCRIPTION FROM inscription i WHERE i.PERIODE = :periode AND i.MATEL = o.MATEL)";
	$grid = new Grid($query, 0);
	$grid->param = array(
		'matel' => $_GET['matel'],
		'periode' => $_SESSION['periode']
	);
	$grid->addcolonne(0,'ID', 'ID', true);
	$grid->addcolonne(1,'LIBELLE', 'LIBELLE', true);
	$grid->addcolonne(2,'MONTANT', 'MONTANT', true);
	$grid->addcolonne(3,'DATE OPERATION', 'DATEOP', true);
	$grid->addcolonne(4,'ETAT', 'STATUT', true);
	$grid->setColDate(3);
	$grid->selectbutton = false;
	$grid->actionbutton = false;
	$grid->display();

	?>
    </div>
    <?php if($grid->length()){?>
    <div style='border:1px solid #EEE; margin:5px;text-align:center'>
        <span>Lib&eacute;rer | D&eacute;bloquer cet &eacute;l&egrave;ve de toute op&eacute;ration comptable</span>
        <span><input type="radio" checked="checked" name="debloquer" value="0" />Non&nbsp;&nbsp;&nbsp;
        <input type="radio" name="debloquer" value="1" />Oui</span>
    </div><?php } ?>
    <div class="navigation">
    	<input type="button" value="Retour" onclick="home();" />
        <input type="hidden" name="matel" value="<?php echo $_GET['matel']; ?>" />
        <input type="submit" name="terminer" value="Valider"/>
    </div>
</form>
</div>
<?php
}catch(PDOException $e){
	var_dump($e->getTrace());
	die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
}
}
/******************************************************
*
*	Integration du model de page
*
*******************************************************/
require_once("../includes/footer_inc.php");
?>