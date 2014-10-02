<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "ADD_ALL_NOTE";	//Concerne tous les cours enseignes
	if(!is_autorized($codepage))
		$codepage = "ADD_NOTE";		//Est restreint a ses cours enseignes
/***************************************************************************************************/
$titre = "Ajout de notes.";
require_once("../includes/header_inc.php");
/*
	Distribution des evenements : deux evenement (Choix des informations et validation)
*/
	if(isset($_POST['matiere'])){
		if(isset($_POST['step']))
			valider();
		else
			add_notes();
	}else
		selectionner($codepage);
/*
	Modele de bas de page
*/
require_once("../includes/footer_inc.php");
/***************************************************************************************************
	Fonction relative de la page
	
/***************************************************************************************************/
function selectionner($code){?>
<script>
	function selectionner(){
		var frm = document.forms['frm'];
		if(frm.classe.value == "" || frm.matiere.value == "")
			alert("-Tous les champs sont obligatoires-");
		else
			frm.submit();
	}
	function loadMatiere(){
		var frm = document.forms['frm'];
		if(frm.classe.value != ""){
			var url = "ajouter2.php?classe=" + frm.classe.value;
			callajax(url, "matiere", "loader");
		}else{
			html = "<select name='matiere'><option value=''>-Choisir la mati&egrave;re-</option></select>";
			document.getElementById("matiere").innerHTML = html;
		}
	}
</script>
<div id="zonetravail"><div class="titre">AJOUT DE NOTES</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="frm" onsubmit="selectionner(); return false;" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Informations relatives: </legend>
        <?php 
			/*
				Si l'ajout est limite a ses cours enseigner ie Droit = "ADD_NOTE" pas ADD_ALL_NOTE
				alors verifier qu'il enseigne un cours dans une classe
			*/
			
			if(!strcasecmp($code, "ADD_NOTE")){
				$res = mysql_query("SELECT PROF FROM enseigner WHERE PROF = '".parse($_SESSION['user'])."'") or die(mysql_error());
				if(!mysql_num_rows($res)){
					print "<p class = 'infos'>Aucune mati&egrave;re ne vous a &eacute;t&eacute; assign&eacute;e.<br/><br/>";
					print "Vous ne pouvez donc pas ajouter de notes</p>";
					return;
				}
			}
		?>
        	<table><tr>
            	<td>
    				Classe : </td><td>
					<?php	$query = "SELECT IDCLASSE, LIBELLE FROM classe ORDER BY IDCLASSE";
					/*
						Charger seulement les classes ou il enseigne si le droit est limitee a ses cours enseigner ie droit = ADD_NOTE
					*/
					if(!strcasecmp("ADD_NOTE", $code))
						$query = "SELECT c.IDCLASSE, c.LIBELLE FROM classe c 
						WHERE c.IDCLASSE IN (SELECT e.CLASSE FROM enseigner e WHERE e.PROF = '".parse($_SESSION['user'])."' AND e.PERIODE = '".$_SESSION['periode']."') 
						ORDER BY IDCLASSE";
					/*
						Combobox des classes concernees
					*/
					$classe = new Combo($query,"classe", 0, 1, false);
					$classe->first = "-Choisir la classe-";
					$classe->onchange = "loadMatiere();";
					$classe->view();
				?></td>
                <td>Mati&egrave;re :</td><td id="matiere"><select name="matiere"><option value="">-Choisir la mati&egrave;re-</option></select>
                </td>
                <td><img src="../images/loader.gif" id="loader" style="visibility:hidden" /></td>
            </tr><tr>
            	<?php $res = mysql_query("SELECT * FROM annee_academique WHERE ANNEEACADEMIQUE = '".$_SESSION['periode']."'") or die(mysql_error());
					$row = mysql_fetch_array($res);
					if($row['ASEQUENCE'] == 1){
            			print "<td>Choisir la s&eacute;quence : </td><td>";
						$seq = new Combo("SELECT IDSEQUENCE, LIBELLE FROM sequence WHERE ANNEEACADEMIQUE = '".$_SESSION['periode']."'", "appartient", 0, 1, false);
						$seq->first = "-Choisir la s&eacute;quence-";
						$seq->view();
						"</td>";
					}elseif(!strcmp($row['DECOUPAGE'], "Trimestre") && $row['ASEQUENCE'] == 0){
						print "<td>Choisir le trimestre : </td><td>";
						$trimestre = new Combo("SELECT IDTRIMESTRE, LIBELLE FROM trimestre WHERE ANNEEACADEMIQUE = '".$_SESSION['periode']."'", "appartient", 0, 1, false);
						$trimestre->first = "-Choisir le trimestre-";
						$trimestre->view();
						"</td>";
					}elseif(!strcmp($row['DECOUPAGE'], "Semestre") && $row['ASEQUENCE'] == 0){
						print "<td>Choisir le semestre : </td><td>";
						$semestre = new Combo("SELECT IDTRIMESTRE, LIBELLE FROM trimestre WHERE ANNEEACADEMIQUE = '".$_SESSION['periode']."'", "appartient", 0, 1, false);
						$semestre->first = "-Choisir le trimestre-";
						$semestre->view();
						"</td>";
					}
				?>
                <td colspan="2"></td>
            </tr></table>
        </fieldset>
	</div>
    <div class="navigation"><input type="button" value="Annuler" onclick="home();"/>
    	<input type="submit" value="Valider"/>
    </div>
</form>
</div>
<?php }
/*
	Fonction qui liste les eleve de la classe $_POST[classe] inscrits
	et ajoute les notes a la matiere $_POST[matiere]
*/
function add_notes(){
	$query = "SELECT LIBELLE FROM classe WHERE IDCLASSE = '".parse($_POST['classe'])."' 
	UNION (SELECT LIBELLE FROM matiere WHERE CODEMAT = '".parse($_POST['matiere'])."')";
	$res = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_array($res);
?>
	<div id = 'zonetravail'><div class = 'titre'>AJOUT DE NOTES</div>
	<form name = 'frm' action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
		<div class = 'cadre'>
        	<table><tr><?php print "<td>Classe : </td><td>".$row['LIBELLE']."</td>";
				$row = mysql_fetch_array($res);
				print "<td>Mati&egrave;re : </td><td>".$row['LIBELLE']."</td>";
				?></tr>
            <tr><td title="Note sur 20, sur 30 ou sur 50 etc">Notes sur : </td>
            	<td><input type="text" name="bareme" title="Note sur 20, sur 30 ou sur 50 etc" maxlength="5" /></td>
            	<td title="Pourcentage de la note dans le calcul de la moyenne">Pourcentage de la note : </td>
                <td><input title="Pourcentage de la note dans le calcul de la moyenne" type="text" name="pourcentage" maxlength="3"></td>
            </tr></table>
<?php
/*
	Verification des donnes envoyes
*/
	if(empty($_POST['classe']) || empty($_POST['matiere'])){
		print "<p class = 'infos'>Tous les champs listes sont obligatoires</p>";
		/*print "<script>alert('Tous les champs sont obligatoire');</script>";*/
		//selectionner();
		return;
	}
/*
	Charger les eleves inscrits
*/
	$query = "SELECT * FROM inscription i WHERE i.IDCLASSE = '".parse($_POST['classe'])."' AND i.PERIODE = '".$_SESSION['periode']."'";
	$res = mysql_query($query) or die(mysql_error());
/*
	Verifier s'il existe au moins un eleve inscrit dans cette classe
*/
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Aucun &eacute;l&egrave;ve inscrit dans la classe $_POST[classe] pour la p&eacute;riode $_SESSION[periode]</p>";
		/*print "<script>alert('Aucun eleve');</script>";*/
		//selectionner($codepage);
		return;
	}
/*
	Grille des eleves
*/
	print "<hr/>";
	echo "<div style=\"border:1px solid #CCC;max-height:300px;overflow:auto; width:100%;padding:2px;\">";
	print "<table class = 'grid'>";
	while($row = mysql_fetch_array($res)){
		print "<tr><td bgcolor=\"#CCC\">".$row['MATEL']."</td><td bgcolor=\"#CCC\"><input type = 'text' name = 'note".$row['MATEL']."' size = '5'/></td>";
		if($row = mysql_fetch_array($res))
			print "<td  bgcolor=\"#FFFFCC\">".$row['MATEL']."</td><td  bgcolor=\"#FFFFCC\"><input type = 'text' name = 'note".$row['MATEL']."' size = '5'/></td>";
		else{
			print "<td colspan=\"6\"></td></tr>";
			break;
		}
		if($row = mysql_fetch_array($res))
			print "<td bgcolor=\"#CCC\">".$row['MATEL']."</td><td bgcolor=\"#CCC\"><input type = 'text' name = 'note".$row['MATEL']."' size = '5'/></td>";
		else{
			print "<td colspan=\"4\"></td></tr>";
			break;
		}
		if($row = mysql_fetch_array($res))
			print "<td  bgcolor=\"#FFFFCC\">".$row['MATEL']."</td><td  bgcolor=\"#FFFFCC\"><input type = 'text' name = 'note".$row['MATEL']."' size = '5'/></td>";
		else{
			print "<td colspan=\"2\"></td></tr>";
			break;
		}
	}
	print "</table></div>";
	?>
	</div>
	<div class = 'navigation'><input type = 'button' value = 'Retour' onclick = "rediriger('ajouter.php');"/>
	<input type="submit" value = 'Valider'/></div>
	<!--
		Variables a transmettre
	-->
	<input type="hidden" name = 'classe' value = "<?php echo $_POST['classe']; ?>" />
	<input type="hidden" name = 'matiere' value = "<?php echo $_POST['matiere']; ?>" />
    <input type="hidden" name = 'appartient' value = "<?php echo $_POST['appartient']; ?>" />
    <input type="hidden" name = 'step' value = "final" />
	</form>
<?php }
/*
	Fonction de validation des notes entrees
*/
function valider(){
	if(empty($_POST['bareme']) || empty($_POST['pourcentage'])){
		print "<p class = 'infos'>'Note sur' et Pourcentage sont obligatoires </p>";
		print "<script>alert('Pourcentage et Note sur obligatoires');</script>";
		add_notes();
		return;
	}
/*
	Sauvegardes des parametres des note dans la tables note_parametre
*/
	$query = "INSERT INTO note_parametre (BAREME, POURCENTAGE) VALUES ('".parse($_POST['bareme'])."', '".parse($_POST['pourcentage'])."')";
	mysql_query($query) or die("Erreur d'insertion des parametre ".mysql_error());
	$param = mysql_insert_id();
/*
	Sauvegardes des notes dans la bd
*/
	$query = "SELECT * FROM inscription i WHERE i.IDCLASSE = '".parse($_POST['classe'])."' AND i.PERIODE = '".$_SESSION['periode']."'";
	echo $query;
	echo $_POST['matiere'];
	$res = mysql_query($query) or die(mysql_error());
	print mysql_num_rows($res);
	while($row = mysql_fetch_array($res)){
		$query = "INSERT INTO NOTE (MATEL, IDMATIERE, APPARTIENT, NOTE, PARAMETRE) VALUES('".parse($row['MATEL'])."' , '".parse($_POST['matiere'])."', 
		'".parse($_POST['appartient'])."', '".parse($_POST['note'.$row['MATEL']])."', '".$param."')";
		mysql_query($query) or die("Insertion de la note de Eleve : $row[MATEL] ".mysql_error());
	}
	print "<script>rediriger('visualiser.php?classe=".$_POST['classe']."&matiere=".$_POST['matiere']."');</script>";
}
?>