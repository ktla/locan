<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))
		header("location:../utilisateurs/connexion.php");
	$titre = "Modification des &eacute;l&egrave;ve.";
	require_once("../includes/header_inc.php");
	if(isset($_POST['matel']))
		valider();
	elseif(isset($_GET['id']))
		modifier();
	else
		proposer();
	require_once("../includes/footer_inc.php");
function modifier(){
	$res = mysql_query("SELECT * FROM eleve WHERE MATEL = '".parse($_GET['id'])."'") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Aucun &eacute;l&egrave;ve sous cet IDENTIFIANT : ".$_GET['id']."</p>";
		proposer();
		return;
	}
	$ligne = mysql_fetch_array($res);
?>
<div id="zonetravail"><div class="titre">MODIFICATION DE L'ELEVE : <?php echo $ligne['MATEL']; ?></div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="step1(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>Renseignement sur l'&eacute;l&egrave;ve.</legend><table cellspacing="5"><tr>
            	<td>Nom Pr&eacute;nom *: </td><td><input type="text" value="<?php echo $ligne['NOMEL']; ?>" size="30" name="nomel"/></td>
                <td>Date de naissance *: </td><td><input value="<?php echo $ligne['DATENAISS']; ?>" type="text" size="30" name="datenaiss" id="datenaiss"/></td></tr>
                <tr><td>Nom P&egrave;re *: </td><td><input value="<?php echo $ligne['NOMPERE']; ?>" type="text" size="30" name="nompere"/></td>
                <td>Nom m&egrave;re : </td><td><input value="<?php echo $ligne['NOMMERE']; ?>" type="text" size="30" name="nommere"/></td></tr>
                <tr><td>Adresse </td><td><input type="text" value="<?php echo $ligne['ADRESSE']; ?>" size="30" name="adresse"/><input type="hidden" value="3" name="step"></td>
                <td>T&eacute;l&eacute;phone *: </td><td><input value="<?php echo $ligne['TEL']; ?>" type="text" size="30" name="tel"/>
                <input type="hidden" value="<?php echo $_GET['id']; ?>" name="matel"></td></tr>
                <tr><td colspan="4"><hr/></td></tr>
    			<tr>
                 <td>Religion </td><td>
                 	<?php $q = "SELECT * FROM religion ORDER BY LIBELLE";
							$combo = new combo($q, "religion", 0, 1, true);
							$combo->selectedid = $ligne['RELIGION'];
							$combo->first = "-Choisir une religion-";
							$combo->view();
					?></td>
                  <td colspan="2" rowspan="3" align="center"><img src="<?php echo $ligne['IMAGE']; ?>" alt="Photo de l'eleve" width="150" height="150"></td>
                </tr>
                <tr>
                 <td>Anc. Etabl. : </td>
                 	<td><?php $q = "SELECT * FROM etablissement ORDER BY LIBELLE";
						 $combo = new combo($q, "ancetbs", 0, 1, true);
						 	$combo->first = "-Ancien &eacute;tablissement-";
							$combo->selectedid = $ligne['ANCETBS'];
							$combo->view();
						  ?></td>
                 </tr>
                 <tr><td>Changer photo : </td><td colspan="3"><input type="file" size="15" name="image" /></td></tr>
                <tr><td>Sexe : </td><td>
                <?php if(!strcasecmp($ligne['SEXE'], "Masculin")){
                	echo "<input type=\"radio\" name=\"sexe\" value=\"Masculin\" checked = \"checked\"/>Masculin&nbsp;&nbsp;";
					print "<input type=\"radio\" name=\"sexe\" value=\"Feminin\"/>F&eacute;minin</td>";
				}else {
					echo "<input type=\"radio\" name=\"sexe\" value=\"Masculin\" />Masculin&nbsp;&nbsp;";
                	print "<input type=\"radio\" name=\"sexe\" checked=\"checked\" value=\"Feminin\"/>F&eacute;minin</td>";
				}?>
                <td>Redoublant : </td><td>
                <?php if($ligne['REDOUBLANT'] == 1)
					print "<input type=\"radio\" name=\"redoublant\" value=\"0\"/>NON&nbsp;&nbsp;<input type=\"radio\" checked=\"checked\" name=\"redoublant\" value=\"1\"/>OUI";
				else 
					print "<input type=\"radio\" checked=\"checked\" name=\"redoublant\" value=\"0\"/>NON&nbsp;&nbsp;<input type=\"radio\" name=\"redoublant\" value=\"1\"/>OUI";
				?></td></tr>
                </table>
            </fieldset>
        </div>
        <div class="navigation"><input type="button" value="Annuler" onClick="document.location = '../accueil/index.php'" /><input type="submit" value="Valider" /></div>
    </form>
</div>
<?php }
function valider(){
	$r = mysql_query("SELECT * FROM eleve WHERE MATEL = '".parse($_POST['matel'])."'") or die(mysql_error());
	$lig = mysql_fetch_array($r);
	//4 = NO FILE UPLOAD
	$url = $lig['IMAGE'];
	if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE){
		if(file_exists($lig['IMAGE'])){
			if(!unlink($lig['IMAGE']))
				print "<p class=\"infos\">Erreur de suppression de l'ancienne photo";
		}
		$url = "../photos/".$_POST['matel']."-".$_FILES['image']['name'];
		if(!move_uploaded_file($_FILES['image']['tmp_name'], $url)){
			print "<p class=\"infos\">Erreur d'uploading de l'image.</p>";
			modifier();
			return;
		}
	}
	$query = "UPDATE eleve SET NOMEL = '".parse($_POST['nomel'])."', DATENAISS = '".parseDate($_POST['datenaiss'])."',
	 NOMPERE = '".parse($_POST['nompere'])."' , NOMMERE = '".parse($_POST['nommere'])."', ADRESSE = '".parse($_POST['adresse'])."' , TEL = '".parse($_POST['tel'])."',
	  RELIGION = '".parse($_POST['religion'])."', SEXE = '".parse($_POST['sexe'])."', ANCETBS = '".parse($_POST['ancetbs'])."',
	   REDOUBLANT = '".parse($_POST['redoublant'])."', IMAGE = '".parse($url)."', DATEAJOUT = '".date("Y-m-d", time())."' WHERE MATEL = '".parse($_POST['matel'])."'";
	if(mysql_query($query))
		print "<script>document.location = '../eleves/fiche.php?id=".parse($_POST['id'])."';</script>";
	else
		die(mysql_error());
}
function proposer(){?>
<script>
	function proposer(){
		var obj = document.forms['frm'];
		if(obj.id.value == "")
			alert("-Choisir un eleve pour modification-");
		else
			obj.submit();
	}
</script>
	<div id="zonetravail"><div class="titre">CHOIX DE L'ELEVE A MODIFIER.</div>
    <form name="frm" enctype="multipart/form-data" onsubmit="proposer(); return false;" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="cadre">
        <fieldset><legend>S&eacute;lection de l'&eacute;l&egrave;ve.</legend>
        <table><tr><td>Matricule de l'&eacute;l&egrave;ve &agrave; modifier : </td>
        <td><?php $combo = new combo("SELECT MATEL, CONCAT(MATEL,' ',NOMEL) FROM eleve ORDER BY MATEL", "id", 0, 1, false);
            $combo->first = "-Choisir un eleve-"; $combo->view();
         ?>
        </td>
        </table>
        </fieldset></div>
		<div class = 'navigation'><input type="button" value="Annuler" onclick="document.location = '../accueil/index.php'"/>
        <input type="submit" value="Valider"/></div>
   </form></div>
<?php }
?>