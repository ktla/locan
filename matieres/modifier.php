<?php
/**********************************************************************************************************/
	require_once("../includes/commun_inc.php");
/*
	Redirection si la session de l'utilisateur est expiree
*/
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Modification des mati&egrave;res.";
/*
	Verifier le droit d'acces de l'utilisateur a cette page
	Empeche de proceder a une copie d'url pour acceder a la page
*/
	$codepage = "EDIT_MATIERE";
/**********************************************************************************************************/
	require_once("../includes/header_inc.php");
	if(isset($_POST['codemat']))
		step2();
	else
		step1();
	require_once("../includes/footer_inc.php");
/***********************************************************************************************************
	
		Fonction relative de la page
		
************************************************************************************************************/
/*
	Fonction de presentation du formulaire
*/
function step1(){
/*
	Verifier l'authencite de l'information transmise par GET
*/
	$res = mysql_query("SELECT * FROM matiere WHERE CODEMAT = '".parse($_GET['line'])."'") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Mati&egrave;re inexistante sous l'ID : $_GET[line]</p>";
		return;
	}
	$row = mysql_fetch_array($res);
?>
<script>
	function step1(){
		if(document.forms['frm'].codemat.value == "" || document.forms['frm'].libelle.value == "")
			alert("Entrer tous les champs");
		else
			document.forms['frm'].submit();
	}
</script>
<div id="zonetravail"><div class="titre">MODIFIER LA MATIERE : <?php echo $row['CODEMAT']."-".$row['LIBELLE']; ?></div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="step1(); return false;" enctype="multipart/form-data" method="POST">
    <div class="cadre">
    	<fieldset><legend>Renseignement sur la mati&egrave;re.</legend>
        	<table><tr>
            	<td>Code mati&egrave;re : </td><td><input type="text" value="<?php echo $row['CODEMAT']; ?>" name="codemat" size="30"></td>
                <td>Libell&eacute; : </td><td><input type="text" value="<?php echo $row['LIBELLE']; ?>" name="libelle" size="30"></td>
            </tr></table>
        </fieldset>
    </div>
    <!-- Variable de formulaire -->
    <input type="hidden" name="code" value="<?php echo $_GET['line']; ?>" />
    <div class="navigation"><input type="button" onClick="rediriger('matiere.php')" value = 'Annuler'/>
    	<input type="submit" value="Valider"/>
    </div>
    </form>
</div>
<?php }
/*
	Fonction de validation de donnees dans BD
*/
function step2(){
/*
	Verification de duplication d'information dans la BD si l'ancien code est different du nouveau
	ancien code : $_POST['code'] 
	nouveau code : $_POST['codemat']
*/
	if(strcmp($_POST['code'], $_POST['codemat'])){
		$res = mysql_query("SELECT * FROM matiere WHERE CODEMAT = '".parse($_POST['codemat'])."'") or die(mysql_error());
		if(mysql_num_rows($res)){
			print "<p class=\"infos\">Mati&egrave;re existante sous ce nouveau code : $_POST[codemat]; <br/>changer votre nouveau code de mati&egrave;re.</p>";
			$_GET['line'] = $_POST['code'];
			step1();
			return;
		}
	}
	$query = "UPDATE matiere SET CODEMAT = '".parse($_POST['codemat'])."', LIBELLE = '".parse($_POST['libelle'])."' WHERE CODEMAT = '".parse($_POST['code'])."'";
	if(mysql_query($query))
		print "<script>rediriger('matiere.php');</script>";
	else
		die(mysql_error());
}
?>