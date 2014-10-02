<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
$titre = "Modification d'enseignement";
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "EDIT_ENSEIGNEMENT";
/***************************************************************************************************/
require_once("../includes/header_inc.php");
/*
	Distribution des evenements : deux evenement (Choix des informations et validation)
*/
if(isset($_POST['classe']))
	validerEnseignement();
else
	choisirEnseignement();
require_once("../includes/footer_inc.php");
/***************************************************************************************************
	Fonction relative de la page
	
/***************************************************************************************************/
/*
	Fonction permettant d'entrer les nouvelles valeurs de l'enseignement
*/
function choisirEnseignement(){
/*
	Verification des entites qui doivent prexister avant de proceder
	a l'ajout d'un enseignemt , ce sont :
	-Le professeur
	-La matiere
	-La classe
	-Et l'annne Academique (ici defini dans une session)
*/
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
	/*
		Select des informations concernant l'ancien enseignement
	*/
	$query = "SELECT e.*, 
		(SELECT m.LIBELLE FROM matiere m WHERE e.CODEMAT = m.CODEMAT) AS LIBMAT, 
		(SELECT c.LIBELLE FROM classe c WHERE c.IDCLASSE = e.CLASSE) AS LIBCLASS, 
		(SELECT CONCAT(p.IDPROF,' ',p.NOMPROF,' ', p.PRENOM) FROM professeur p WHERE e.PROF = p.IDPROF) AS PROFESS, 
		 IF(e.ACTIF = 1, 'ACTIF', 'NON ACTIF') AS ETAT 
		 FROM enseigner e 
		 WHERE e.PERIODE = '".$_SESSION['periode']."' AND e.IDENSEIGNEMENT = '".parse($_GET['id'])."' ORDER BY e.CODEMAT";
	$res = mysql_query($query) or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Aucun Enseignement existant sous cet ID : $_GET[id]</p>";
		return;
	}
	$row = mysql_fetch_array($res);
?>
<script>
	function choisir(){
		var obj = document.forms['frm'];
		if(obj.classe.value == "" || obj.mat.value == "" || obj.prof.value == "" || obj.coeff.value == "")
			alert("-Veuillez choisir tous les champs-");
		else{
			if(isNaN(obj.coeff.value) || parseInt(obj.coeff.value) < 0)
				alert("-Entrer un nombre positif dans la zone coefficient-");
			else
				obj.submit();
		}
	}
</script>
<div id="zonetravail"><div class = 'titre'>MODIFICATION DE L'ENSEIGNEMENT</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frm" enctype="multipart/form-data" onsubmit="choisir(); return false;" method="POST">
    	<div class = 'cadre'>
        <table cellspacing="0" cellpadding="5" border="2">
        	<caption>Anciennes informations de l'enseignement</caption>
        <thead>
        	<th>Professeur</th><th>Mati&egrave;re</th><th>Classe</th><th>Coeff</th><th>Etat</th>
        </thead>
        <tbody>
        	<?php echo "<tr><td>$row[PROFESS]</td><td>$row[LIBMAT]</td><td>$row[LIBCLASS]</td><td>$row[COEFF]</td><td>$row[ETAT]</td></tr>"; ?>
        </tbody>
       	</table><br/><br/>
        <!-- -->
        <fieldset><legend>Modifier</legend>
        <table cellspacing="0" cellpadding="5" border="2">
       		<caption>Nouvelles informations de l'enseignement</caption>
        <thead>
        	<th>Professeur</th><th>Mati&egrave;re</th><th>Classe</th><th>Coeff</th>
        </thead>
        <tbody><tr><?php
        	echo "<td>"; $prof = new combo("SELECT IDPROF, NOMPROF FROM professeur ORDER BY NOMPROF", "prof", 0, 1, true);
			$prof->selectedid = $row['PROF'];
			$prof->view();
			print "</td><td>";
			$mat = new combo("SELECT * FROM matiere ORDER BY CODEMAT", "mat", 0, 1, true);
            $mat->selectedid = $row['CODEMAT'];
            $mat->view();
			print "</td><td>";
			$mat = new combo("SELECT * FROM classe ORDER BY NIVEAU", "classe", 0, 1, true);
            $mat->selectedid = $row['CLASSE'];
            $mat->view();
            ?></td><td><input type="text" name="coeff" value="<?php echo $row['COEFF']; ?>" maxlength="4" size="6"/></td>
        </tr></tbody>
       </table></fieldset>
    </div>
    <!-- Variable de formulaire -->
    <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
    <div class="navigation"><input type="button" value="Retour" onclick="rediriger('enseignement.php');"/>
    	<input type="submit" value="Valider" />
    </div>
</form>
</div>
<?php }
/*
	Fonction de validation de donnees dans BD
*/
function validerEnseignement(){
/*
	Verification des donnees postes
*/
	if(empty($_POST['prof']) || empty($_POST['mat']) || empty($_POST['classe']) || empty($_POST['coeff'])){
		print "<p class = 'infos'>Les donnees des champs sont tous obligatoires</p>";
		$_GET['id'] = $_POST['id'];
		choisirEnseignement();
		return;
	}
	if(!is_numeric($_POST['coeff']) || intval($_POST['coeff']) < 0){
		print "<p class = 'infos'>Le coefficient doit &ecirc;tre un nombre positif</p>";
		$_GET['id'] = $_POST['id'];
		choisirEnseignement();
		return;
	}
/*
	Tous s'est bien deroules, valider
*/
	$query = "UPDATE enseigner SET PROF = '".parse($_POST['prof'])."', CODEMAT = '".parse($_POST['mat'])."', CLASSE = '".parse($_POST['classe'])."',
	 COEFF = '".parse($_POST['coeff'])."' 
	 WHERE IDENSEIGNEMENT = '".parse($_POST['id'])."'";
	if(mysql_query($query))
		print "<script>rediriger('enseignement.php');</script>";
	else
		die(mysql_error());
}
?>