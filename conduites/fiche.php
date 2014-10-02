<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Fiche de l'&eacute;l&egrave;ve.";
	require_once("../includes/header_inc.php");
	if(isset($_GET['matel'])){
		fiche();
	}else
		selectionner();
	require_once("../includes/footer_inc.php");
function selectionner(){
?>
<script>
	function selectionner(){
		if(document.forms['frm'].matel.value == "")
			alert("-Choisir un eleve-");
		else
			document.forms['frm'].submit();
	}
</script>
<div id="zonetravail"><div class="titre">FICHE DES ELEVES.</div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="selectionner(); return false;" method="GET">
    	<div class="cadre">
        	<fieldset><legend>S&eacute;lection de l'&eacute;l&egrave;ve.</legend><table><tr>
            	<td>Matricule : </td><td>
                <?php $q = "SELECT e.MATEL, CONCAT(e.MATEL, '-', e.NOMEL) FROM eleve e ORDER BY MATEL";
						$combo = new combo($q, "matel", 0, 1);
						$combo->first = "-Choisir un &eacute;l&egrave;ve-";
						$combo->view();
				?></td>
                </tr></table>
            </fieldset>
        </div>
        <div class="navigation">
        <input type="button" onclick="document.location = '../accueil/index.php',;" value="Annuler" />
        <input type="submit" value="Fiche" /></div>
    </form>
</div>
<?php }
function fiche(){
	$res = mysql_query("SELECT * FROM frequenter WHERE MATEL = '".parse($_GET['matel'])."' AND PERIODE = '".parse($_GET['periode'])."'");
	/*if(!mysql_num_rows($res)){
		print "<p class=\"infos\">".$_GET['matel']." N'EST PAS INSCRIT(E) A CETTE PERIODE.</p>";
		selectionner();
		return;
	}*/
	$res = mysql_query("SELECT e.*,
	(SELECT f.LIBELLE FROM ancien_etablissement f WHERE f.IDETS = e.ANCETBS) AS ANC,
	(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = (SELECT f.IDCLASSE FROM inscription f WHERE f.MATEL = e.MATEL AND f.PERIODE = '".parse($_GET['periode'])."')) AS CLASS,
	(SELECT r.LIBELLE FROM religion r WHERE r.IDRELIGION = e.RELIGION) AS RELIGION
	 FROM eleve e WHERE e.MATEL = '".parse($_GET['matel'])."'") or die(mysql_error());
	$row = mysql_fetch_array($res);
?>
<div id="zonetravail"><div class="titre">FICHE DE L'ELEVE <?php echo $row['NOMEL']; ?></div><div class="cadre">
<?php 
	print "<img class = 'fichephoto' src=\"".$row['IMAGE']."\" title=\"Photo de ".$row['NOMEL']."\"/>";
	print "<span><a href = 'imprimerfiche.php?matel=".$_GET['matel']."&periode=".$_GET['periode']."' target = '_blank' title = 'Imprimer la fiche'>";
	print "<img src = '../images/icon-pdf2.png' title = 'Imprimer avec image'></a>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";
	print "<span><a href = 'imprimerfiche.php?matel=".$_GET['matel']."&periode=".$_GET['periode']."&image=false' target = '_blank' title = 'Imprimer la fiche sans image'>
	<img src = '../images/icon-pdf.gif' title = 'Imprimer sans image'></a></span>";
	print "<div class = 'fiche'>";
		$d = new dateFR($row['DATENAISS']);
		print "<div class=\"titrefiche\">INFORMATIONS PERSONNELLES.</div><div class = 'fichecontent'>";
		print "<label>MATRICULE:</label>".$row['MATEL'].".<br/>";
		print "<label>NOM & PRENOM:</label>".$row['NOMEL'].".<br/>";
		print "<label>DATE DE NAISSANCE:</label>".$d->fullYear().".<br/>";
		print "<label>LIEU DE NAISSANCE:</label>".$row['LIEUNAISS'].".<br/>";
		print "<label>SEXE:</label>".$row['SEXE'].".<br/>";
		print "<label>CLASSE:</label>".$row['CLASS'].".<br/>";
		print "<label>PERIODE:</label>".$_GET['periode'].".<br/>";
		print "</div>";
		print "<div class=\"titrefiche\">INFORMATIONS RELATIVES.</div><div class = 'fichecontent'>";
		print "<label>NOM PERE:</label>".$row['NOMPERE'].".<br/>";
		print "<label>NOM MERE:</label>".$row['NOMMERE'].".<br/>";
		print "<label>ADRESSE:</label>".$row['ADRESSE'].".<br/>";
		print "<label>TELEPHONE:</label>".$row['TEL'].".<br/>";
		print "<label>EMAIL:</label>".$row['EMAIL'].".<br/>";
		print "</div>";
		print "<div class=\"titrefiche\">AUTRES INFORMATIONS.</div><div class = 'fichecontent'>";
		print "<label>ANCIEN ETABLISSEMENT:</label>".$row['ANC'].".<br/>";
		print "<label>RELIGION:</label>".$row['RELIGION'].".<br/>";
		//Compter le nbre de fois qu'eleve a frequenter la classe choisie pour la periode
		$r = mysql_query("SELECT COUNT(SUBSTR(f.PERIODE, 1, 9)) AS NBRE
		FROM frequenter f
		WHERE f.MATEL = '".parse($_GET['matel'])."' AND f.CLASSE = '".parse($row['REDOUBLANT'])."'") or die(mysql_error());
		$l = mysql_fetch_array($r);
		if($l['NBRE'] > 1)
			print "<label>REDOUBLANT:</label>OUI (".$l['NBRE']." fois).<br/>";
		else
			print "<label>REDOUBLANT:</label>NON<br/>";
		$d->setSource($row['DATEAJOUT']);
		print "<label>DATE D'AJOUT DE L'ELEVE:</label>".$d->fullYear().".<br/>";
		print "</div>";
	print "</div>";
		?>
        </div>
    </div>
</div>
<?php }
?>