<?php
/*********************************************************************************************************/
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Ajouter des mati&egrave;res.";
/*
	Verification du droit d'acces a cette page
*/
	$codepage = "ADD_MATIERE";
/***********************************************************************************************************/
	require_once("../includes/header_inc.php");
	if(isset($_POST['codemat']))
		step2();
	else
		step1();
	require_once("../includes/footer_inc.php");
/********************************************************************************************************

	Fonctions relatives a la page
	
********************************************************************************************************/
function step1(){?>
<script>
	function step1(){
		if(document.forms['frm'].codemat.value == "" || document.forms['frm'].libelle.value == "")
			alert("Entrer tous les champs");
		else
			document.forms['frm'].submit();
	}
</script>
<div id="zonetravail"><div class="titre">AJOUT DE MATIERES.</div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="step1(); return false;" enctype="multipart/form-data" method="POST">
    <div class="cadre">
    	<fieldset><legend>Renseignement sur la mati&egrave;re.</legend>
        	<table><tr>
            	<td>Code mati&egrave;re : </td><td><input type="text" name="codemat" size="30"></td>
                <td>Libell&eacute; : </td><td><input type="text" name="libelle" size="30"></td>
            </tr></table>
        </fieldset>
    </div>
    <div class="navigation"><input type="button" onClick="rediriger('matiere.php')" value = 'Annuler'/>
    	<input type="submit" value="Valider"/>
    </div>
    </form>
</div>
<?php }
/***************************************************************************************************

		Seconde fonction de validation de donnees dans BD

***************************************************************************************************/
function step2(){
/*
	Verification de duplicata de CODEMAT
*/
	$res = mysql_query("SELECT * FROM matiere WHERE CODEMAT = '".parse($_POST['codemat'])."'") or die(mysql_error());
	if(mysql_num_rows($res)){
		print "<p class=\"infos\">Mati&egrave;re existante sous ce code : changer de code.</p>";
		step1();
		return;
	}
/*
	Valider les informations
*/
	$query = "INSERT INTO matiere(CODEMAT, LIBELLE) VALUES(\"".parse($_POST['codemat'])."\", \"".parse($_POST['libelle'])."\")";
	if(mysql_query($query))
		print "<script>rediriger('matiere.php');</script>";
	else
		die(mysql_error());
}
?>