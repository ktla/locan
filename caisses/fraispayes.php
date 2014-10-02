<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
$titre = "Frais pay&eacute;s";
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "CAISSE_FRAIS";
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
<div id="zonetravail"><div class="titre">Frais pay&eacute;s</div>
<form action="fraispayes.php" method="GET" enctype="multipart/form-data" onSubmit="lister(); return false">
	<div class="cadre">
 		<fieldset><legend>Choisir l'&eacute;l&egrave;ve</legend>
        	<?php
			try{
				$pdo = Database::connect2db();
				$res = $pdo->prepare("SELECT f.MATEL  
				FROM frais_apayer f 
				INNER JOIN inscription i ON (i.IDINSCRIPTION = f.IDINSCRIPTION AND i.PERIODE = :periode AND i.MATEL = f.MATEL) 
				UNION 
				SELECT o.MATEL  
				FROM reduction_obtenue o 
				INNER JOIN inscription i ON (i.IDINSCRIPTION = o.IDINSCRIPTION AND i.PERIODE = :periode AND i.MATEL = o.MATEL)");
				$res->execute(array(
					'periode' => $_SESSION['periode']
				));
				if(!$res->rowCount()){
					print "<p class = 'infos'>Aucune op&eacute;ration en cours de comptabilisation pour cette p&eacute;riode</p>";
					return;
				}
			}catch(PDOException $e){
				var_dump($e->getTrace());
				die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
			}?>
            <table><tr><td class = 'lib'>Choisir l'&eacute;l&egrave;ve </td><td>
            <?php 
			$query = "SELECT DISTINCT(f.MATEL) 
			FROM frais_apayer f 
			INNER JOIN inscription i ON (i.IDINSCRIPTION = f.IDINSCRIPTION AND i.MATEL = f.MATEL AND i.PERIODE = :periode) 
			UNION  
			SELECT DISTINCT(o.MATEL) 
			FROM reduction_obtenue o 
			INNER JOIN inscription i ON (i.IDINSCRIPTION = o.IDINSCRIPTION AND i.MATEL = o.MATEL AND i.PERIODE = :periode)";
			$eleve = new Combo($query, "matel", 0, 0, false);
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
	$pdo = Database::connect2db();
	if(isset($_GET['bloquer']) && isset($_GET['terminer'])){
		if(intval($_GET['bloquer']) == 1){
			if(!id_exist($_GET['matel'], 'CONCERNER', 'operation_attente')){
				$res = $pdo->prepare("INSERT INTO operation_attente(CONCERNER, PERIODE) VALUES(:matel, :periode)");
				$res->execute(array(
					'matel' => $_GET['matel'],
					'periode' => $_SESSION['periode']
				));
				$_SESSION['infos'] = $_GET['matel']." a &eacute;t&eacute; bloqu&eacute; de toute operation comptable";
			}
		}
		@header("Location:attente.php");
	}
?>
<div id="zonetravail"><div class="titre">Frais pay&eacute; par <?php echo $_GET['matel']; ?></div>
<form action="fraispayes.php" method="GET" enctype="multipart/form-data" name="frm">
	<div class="cadre">
    <?php
	$print = new printlink('imprimerfraisapayer.php?id='.$_GET['matel'], false);
	$print->display();
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
    <div style='border:1px solid #EEE; margin:5px;text-align:center'>
        <span>Bloquer cet &eacute;l&egrave;ve de toute op&eacute;ration comptable</span>
        <span><input type="radio" checked="checked" name="bloquer" value="0" />Non&nbsp;&nbsp;&nbsp;
        <input type="radio" name="bloquer" value="1" />Oui</span>
    </div>
    <div class="navigation">
    	<input type="button" value="Retour" onclick="home();" />
        <input type="hidden" value="<?php echo $_GET['matel'] ?>" name="matel" />
        <input type="submit" value="Terminer" name="terminer" />
    </div>
</form>
</div>
<?php
}
/******************************************************
*
*	Integration du model de page
*
*******************************************************/
require_once("../includes/footer_inc.php");
?>