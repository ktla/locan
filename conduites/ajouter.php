<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
	$titre = "Ajout d'un nouvel eleve";
	require_once("../includes/header_inc.php");
	if(isset($_POST['matel'])){
		switch($_POST['step']){
			case 2:step2();break;
			case 3:step3();break;
		}
	}else
		step1();
	require_once("../includes/footer_inc.php");
function step1(){
?>
<script>
	function step1(){
		var obj = document.getElementsByName("frm");
		if(obj.item(0).matel.value == "")
			alert("Entrer un matricule");
		else
			obj.item(0).submit();
	}
</script>
<div id="zonetravail"><div class="titre">AJOUT D'UN ELEVE. ETAPE 1</div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="step1(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>Identification de l'&eacute;l&egrave;ve.</legend><table><tr>
            	<td>Matricule de l'&eacute;l&egrave;ve : </td>
                <td><input type="text" size="30" name="matel"/></td>
                </tr></table>
            </fieldset>
        </div>
        <div class="navigation"><input type="hidden" value="2" name="step" /><input type="button" value="Annuler" /><input type="submit" value="Suivant" /></div>
    </form>
</div>
<?php }
function step2(){
	$res = mysql_query("SELECT * FROM eleve WHERE MATEL = '".parse($_POST['matel'])."'") or die(mysql_error());
	if(mysql_num_rows($res)){
		print "<p class=\"infos\">Eleve existent sous cet identifiant : ".$_POST['matel']."</p>";
		step1();
		return;
	}
?>
<script>
	function step2(){
		var obj = document.forms['frm'];
		if(obj.nomel.value == "" || obj.datenaiss.value == "" || obj.nompere.value == "" || obj.tel.value == "")
			alert("-Entrer tous les parametres obligatoires-");
		else
			obj.submit();
	}
</script>
<div id="zonetravail"><div class="titre">AJOUT D'UN ELEVE. ETAPE 2</div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="step2(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>Renseignement sur l'&eacute;l&egrave;ve.</legend><table cellspacing="5"><tr>
            	<td>Nom Pr&eacute;nom *: </td><td><input type="text" size="30" name="nomel"/></td>
                <td>Date de naissance *: </td><td><input type="text" size="30" name="datenaiss" id="datenaiss"/></td></tr>
                <tr><td>Nom P&egrave;re *: </td><td><input type="text" size="30" name="nompere"/></td>
                <td>Nom m&egrave;re : </td><td><input type="text" size="30" name="nommere"/></td></tr>
                <tr><td>Adresse </td><td><input type="text" size="30" name="adresse"/><input type="hidden" value="3" name="step"></td>
                <td>T&eacute;l&eacute;phone *: </td><td><input type="text" size="30" name="tel"/><input type="hidden" value="<?php echo $_POST['matel']; ?>" name="matel"></td></tr>
                <tr><td colspan="4"><hr/></td></tr>
    			<tr>
                 <td>Religion </td><td>
                 	<?php $q = "SELECT * FROM religion ORDER BY LIBELLE";
							$combo = new combo($q, "religion", 0, 1);
							$combo->first = "-Choisir une religion-";
							$combo->view();
					?></td>
                 <td>Anc. Etabl. : </td>
                 	<td><?php $q = "SELECT * FROM etablissement ORDER BY LIBELLE";
						 $combo = new combo($q, "ancetbs", 0, 1);
						 	$combo->first = "-Ancien &eacute;tablissement-";
							$combo->view();
						  ?></td>
                 </tr>
                 <tr><td>Image : </td><td colspan="3"><input type="file" size="70" name="image" /></td></tr>
                <tr><td>Sexe : </td><td><input type="radio" name="sexe" value="Masculin" checked = "checked"/>Masculin&nbsp;&nbsp;
                <input type="radio" name="sexe" value="Feminin"/>F&eacute;minin</td>
                <td>Redoublant : </td><td><input type="radio" checked="checked" name="redoublant" value="0"/>NON&nbsp;&nbsp;<input type="radio" name="redoublant" value="1"/>OUI</td></tr>
                </table>
            </fieldset>
        </div>
        <div class="navigation"><input type="button" value="Annuler" onClick="document.location = 'nouvelle.php'" /><input type="submit" value="Valider" /></div>
    </form>
</div>
<?php }
function step3(){
	$res = mysql_query("SELECT * FROM eleve WHERE MATEL = '".parse($_POST['matel'])."'") or die(mysql_error());
	if(mysql_num_rows($res)){
		print "<p class=\"infos\">Eleve existent sous cet identifiant : ".$_POST['matel']."</p>";
		step1();
		return;
	}
	$url = "";
	//4 = NO FILE UPLOAD
	if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE){
		$url = "../photos/".$_POST['matel']."-".$_FILES['image']['name'];
		if(!move_uploaded_file($_FILES['image']['tmp_name'], $url))
			print "<p class=\"infos\">Erreur d'uploading de l'image.</p>";
			step2();
			return;
	}
	$query = "INSERT INTO eleve(MATEL, NOMEL, DATENAISS, NOMPERE, NOMMERE, ADRESSE, TEL, RELIGION, SEXE, ANCETBS, REDOUBLANT, IMAGE, DATEAJOUT)
	 VALUES('".parse($_POST['matel'])."', '".parse($_POST['nomel'])."', '".parseDate($_POST['datenaiss'])."', '".parse($_POST['nompere'])."',
	  '".parse($_POST['nommere'])."', '".parse($_POST['adresse'])."', '".parse($_POST['tel'])."', '".parse($_POST['religion'])."', 
	  '".parse($_POST['sexe'])."', '".parse($_POST['ancetbs'])."', '".parse($_POST['redoublant'])."', '".parse($url)."' , '".date("Y-m-d", time())."')";
	if(mysql_query($query))
		print "<script>document.location = '../eleves/fiche.php?id=".parse($_POST['matel'])."';</script>";
	else
		die(mysql_error());
}
?>