<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
$titre = "Ajout des enseignements";
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "ADD_ENSEIGNEMENT";
/***************************************************************************************************/
require_once("../includes/header_inc.php");
if(isset($_POST['classe']))
	lister_matiere();
else
	choisir();
require_once("../includes/footer_inc.php");
/***************************************************************************************************
	Fonction relative de la page
	
/***************************************************************************************************/
function choisir(){
	$res = mysql_query("SELECT IDPROF FROM professeur") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Veuillez d'abord cr&eacute;er les professeurs <a href = '../professeurs/ajouter.php'>Ici</a></p>";
		return;
	}
	$res = mysql_query("SELECT CODEMAT FROM matiere") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Veuillez d'abord cr&eacute;er les mati&egrave;res  <a href = '../matieres/ajouter.php'>Ici</a></p>";
		return;
	}
	$res = mysql_query("SELECT IDCLASSE FROM classe") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Veuillez d'abord cr&eacute;er les classes <a href = '../classes/ajouter.php'>Ici</a></p>";
		return;
	}
	$res = mysql_query("SELECT * FROM trimestre WHERE ANNEEACADEMIQUE = '".parse($_SESSION['periode'])."'") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Aucun [Trimestre | S&eacute;quence] n'a &eacute;t&eacute; cr&eacute;&eacute; pour cette ann&eacute;e acad&eacute;mique</br>";
		print "Cr&eacute;er les Trimestres et S&eacute;quences <a href = '../periodes/trimestre.php'>ICI</a></p>";
		return;
	}
?>
<script>
	function choisir(){
		var obj = document.forms['frm'];
		if(obj.classe.value == "")
			alert("-Veuillez choisir une classe-");
		else
			obj.submit();
	}
</script>
<div id="zonetravail"><div class = 'titre'>EDITION DES ENSEIGNEMENTS</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frm" enctype="multipart/form-data" onsubmit="choisir(); return false;" method="POST">
    	<div class = 'cadre'>
    	<fieldset><legend>Affecter les enseignements</legend>
        <table>
        <tr><td>Choisir la classe concern&eacute;e :</td>
        <td><?php $combo = new combo("SELECT * FROM classe ORDER BY NIVEAU", "classe", 0, 1, false);
			$combo->first = 'Veuillez Choisir une classe';
			$combo->view();
		?></td><td><input type="submit" value="Valider"/></td>
        </tr></table>
        </fieldset>
    	</div>
    </form>
</div>
<?php }
function lister_matiere(){
	$res = mysql_query("SELECT * FROM classe WHERE IDCLASSE = '".parse($_POST['classe'])."'") or die(mysql_error());
	$row = mysql_fetch_array($res);
?>
<script>
	function saveEnseignement(){
		var frm = document.forms['frm'];
		if(frm.mat.value == "" || frm.prof.value == "" || frm.coeff.value == ""){
			alert(decodeURIComponent("- Veuillez entrer tous les paramètres - "));
			return;
		}
		if(isNaN(frm.coeff.value) || parseInt(frm.coeff.value) < 0){
			alert("-Entrer un nombre positif dans la zone coefficient-");
			return;
		}
		var id = "mat=" + frm.mat.value + "&prof=" + frm.prof.value + "&coeff=" + frm.coeff.value + "&classe=" + frm.classe.value;
		var url = "ajouter2.php?"+ id;
		callajax(url,"enseignement", "loader");
		//Mise a jour des cours
		url = "ajouter2.php?chargercours=val&classe=" + frm.classe.value;
		callajax(url, "matiere", "loader");
	}
</script>
<div id="zonetravail"><div class="titre"><strong>CLASSE CHOISIE : <?php echo $row['LIBELLE']; ?></strong></div>
<form name="frm" action="enseignement.php" method="POST" enctype="multipart/form-data">
	<div class="cadre">
        <div>
        	<table width="100%">
            <tr>
            	<td><?php
                	$prof = new combo("SELECT IDPROF, CONCAT (NOMPROF,' ', PRENOM) FROM professeur ORDER BY NOMPROF", "prof",0, 1, false);
					$prof->first= '-Choisir un professeur-';
					$prof->view();?>
				</td>
                <td>
                	<?php $query = "SELECT m.CODEMAT, m.LIBELLE FROM matiere m WHERE m.CODEMAT NOT IN 
							(SELECT e.CODEMAT FROM enseigner e WHERE e.CLASSE = '".parse($_POST['classe'])."' 
							AND e.PERIODE = '".$_SESSION['periode']."') ORDER BY m.CODEMAT";
							$res = mysql_query($query) or die(mysql_error());
							print "<select name=\"mat\" id=\"matiere\"><option value = ''>-Choisir une mati&egrave;re-</option>";
							while($row = mysql_fetch_array($res))
								print "<option value = \"".$row['CODEMAT']."\">".$row['LIBELLE']."</option>";
							print "</select></td>";
					?>
                <td><input type = 'text' size = '4' maxlenght = '4' name = 'coeff' title="Co&eacute;fficient de la mati&egrave;re"/>
                	<input type="hidden" value="<?php echo $_POST['classe']; ?>" name="classe"/>
                </td>
				<td><img src="../images/save.png" title="Enr&eacute;gistrer..." onclick="saveEnseignement();" style="cursor:pointer;" />
                <img id="loader" style="visibility:hidden" src="../images/loader.gif"/></td>
           </tr></table><hr/>
        </div>
    	<div style="max-height:500px; overflow:auto;padding:2px;">
   	<table class="grid" width="100%">
            <thead>
            	<th>N°</th><th>PROFESSEUR</th><th>MATIERE</th><th>COEFF.</th><th>ETAT</th>
            </thead>
            <tbody id="enseignement">
            	<?php 
					$i = 1;
					$query = "SELECT e.IDENSEIGNEMENT, e.PROF, 
					(SELECT m.LIBELLE FROM matiere m WHERE m.CODEMAT = e.CODEMAT) AS MATLIB,
					(SELECT CONCAT(p.NOMPROF,' ', p.PRENOM) FROM professeur p WHERE e.PROF = p.IDPROF) AS PROFESS,
					e.COEFF, IF(e.ACTIF = 1, 'ACTIF', 'NON ACTIF') AS ETAT 
					FROM enseigner e 
					WHERE e.CLASSE = '".parse($_POST['classe'])."' AND e.PERIODE = '".$_SESSION['periode']."' ORDER BY e.CODEMAT";
					//print $q;
					$r = mysql_query($query) or die(mysql_error());
					if(mysql_num_rows($r)){
						while($lig = mysql_fetch_array($r)){
							print "<tr><td>".($i++)."</td>";
							print "<td><a href = '../professeurs/fiche.php?prof=".$lig['PROF']."'>".$lig['PROFESS']."</a></td>";
							print "<td>".$lig['MATLIB']."</td><td>".$lig['COEFF']."</td><td>".$lig['ETAT']."</td></tr>";
						}
					}else
						print "<tr><td colspan=\"5\" align=\"center\">AUCUN COURS ENREGISTRE...</td></tr>";
				?>
                </tbody>
            	<tfoot><tr><td colspan="5"></td></tr></tfoot>
            </table>
       </div>
	</div>
    <div class="navigation"><input type="button" value="Retour" onclick="rediriger('ajouter.php');"/>
    	<input type="submit" value="Terminer"/>
    </div>
</form>
</div>
<?php }
?>