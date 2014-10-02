<?php
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
$codepage = "FICHE_ELEVE";
/*
*/
	$titre = "Fiche de l'&eacute;l&egrave;ve.";
	require_once("../includes/header_inc.php");
	if(isset($_GET['matel'])){
		if($_GET['matel'] == -1)
			exist_student();
		else
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
            	<td class="lib">Matricule : </td><td>
                <?php $q = "SELECT e.ID, CONCAT(e.NOMEL, ' ' ,e.PRENOM,' _ ',e.MATRICULE) FROM eleve e 
				            ORDER BY e.NOMEL ASC";
						$combo = new Combo($q, "matel", 0, 1);
						$combo->first = "-Choisir un &eacute;l&egrave;ve-";
						$combo->view('300px');
				?></td>
                </tr></table>
            </fieldset>
        </div>
        <div class="navigation">
        <input type="button" onclick="document.location = '../eleve/index.php',;" value="Annuler" />
        <input type="submit" value="Fiche" /></div>
    </form>
</div>
<?php }
function fiche(){
	$el = new Eleve($_GET['matel']);
?>
<div id="zonetravail"><div class="titre">FICHE ELEVE MATRICULE : <font color="#000"><?php echo $el->matricule; ?></font></div><div class="cadre"> 
	<div class = "icon-pdf">
		<span><a href = 'imprimerfiche.php?matel=<?php echo $_GET['matel'];?>&image=true' target = '_blank' title = 'Imprimer la fiche'>
		<img src = '../images/icon-pdf2.png' title = 'Imprimer avec image'></a>&nbsp;&nbsp;|&nbsp;&nbsp;</span>
		<span><a href = 'imprimerfiche.php?matel=<?php echo $_GET['matel']; ?>' target = '_blank' title = 'Imprimer la fiche sans image'>
		<img src = '../images/icon-pdf.gif' title = 'Imprimer sans image'></a></span>
    </div>
<?php

		if(!empty($el->image) && file_exists("./photos/".$el->image))
			print "<img class = 'fichephoto' src=\"./photos/".$el->image."\" title=\"Photo de ".$el->matricule."\"/>";
		else
			print "<img class = 'fichephoto' style=\"font-weight:bold;\" alt=\"PAS DE PHOTO\" />";

	print "<div class = 'fiche'>";
		$dob = (isset($el->datenaiss) ? $el->datenaiss : "");
		print "<div class=\"titrefiche\">INFORMATIONS PERSONNELLES.</div><div class = 'fichecontent'>";
		print "<label>MATRICULE : </label>".$el->matricule.".<br/>";
		print "<label>NOM & PRENOM : </label>".( (isset($el->nom) && isset($el->prenom) )?$el->nom." ".$el->prenom : "").".<br/>";
		print "<label>DATE DE NAISSANCE : </label>".$el->datenaiss.".<br/>";
		if(!empty($el->lieunaiss))
			print "<label>LIEU DE NAISSANCE : </label>".$el->lieunaiss.".<br/>";
		if(!empty($el->sexe))
			print "<label>SEXE : </label>".$el->sexe.".<br/>";
		$class = $el->getClasse();
		if(!empty($class) && !strcmp($class,"Aucune classe") != 1 )
			print "<label>CLASSE : </label>".$class.".<br/>";
		if(isset($el->periode))
			print "<label>PERIODE : </label>".$el->periode.".<br/>";
		print "</div>";
		print "<div class=\"titrefiche\">INFORMATIONS RELATIVES.</div><div class = 'fichecontent'>";
		if(!empty($el->adresse))
			print "<label>ADRESSE ELEVE: </label>".$el->adresse.".<br/>";
		if(!empty($el->tel))
			print "<label>TELEPHONE ELEVE: </label>".$el->tel.".<br/>";
		if(!empty($el->email))
			print "<label>EMAIL ELEVE: </label>".$el->email.".<br/>";
		if(!empty($el->mere))
			print "<label>NOM MERE : </label>".$el->mere.".<br/>";
		if(!empty($el->addressmere))
			print "<label>ADDRESSE MERE : </label>".$el->addressmere.".<br/>";
		if(!empty($el->nompere))
			print "<label>NOM PERE : </label>".$el->nompere.".<br/>";
		if(!empty($el->addresspere))
			print "<label>ADDRESSE PERE : </label>".$el->addresspere.".<br/>";
		if(!empty($el->tuteur))
			print "<label>TUTEUR : </label>".$el->tuteur.".<br/>";	
		if(!empty($el->addresstuteur))
			print "<label>ADDRESSE TUTEUR : </label>".$el->addresstuteur.".<br/>";
		print "</div>";
		print "<div class=\"titrefiche\">AUTRES INFORMATIONS.</div><div class = 'fichecontent'>";
		if(isset($el->ancienEts))
			print "<label>ANCIEN ETABLISSEMENT:</label>".$el->ancienEts.".<br/>";
		if(isset($el->religion))
			print "<label>RELIGION:</label>".$el->religion.".<br/>";
		if(!strcmp($class,"Aucune classe") != 1)
			print "<label>REDOUBLANT:</label>".$class."<br/>";
		print "<label>DATE D'AJOUT DE L'ELEVE:</label>".$el->dateajout.".<br/>";
		print "</div>";
	print "</div>";
		?>
        </div>
    <div class="navigation">
    	<input type="button" value="Annuler" onClick="rediriger('index.php')" />
    	<input type="button" value="Ajouter" onclick="rediriger('ajouter.php')" />
    </div>
</div>
<?php }
function exist_student(){
?>
<div id="zonetravail"><div class="titre" align="center"><font color="red">ELEVE EXISTANT DANS LA BASE DE DONNEES</font></div> 
<div class="navigation">
    <input type="button" value="Retour" onclick="rediriger('eleve.php')" />
</div>
</div>
<?php } 
?>