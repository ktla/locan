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
function creer(){
	if(isset($_POST['profile'])){
		//Enregistrer les infos dans la bd
		$resultat = mysql_query("SELECT * FROM profile WHERE LIBELLE='".parse($_POST['profile'])."'") or die(mysql_error());
		if(mysql_num_rows($resultat) != 0)
			echo "<p class = \"infos\">Profile existant d&#233;j&#224; sur le nom : ".$_POST['profile']."</p>";
		else{
			$query = "INSERT INTO profile(LIBELLE, DROIT) VALUES('".parse($_POST['profile'])."','a:0:{}')";
			if(mysql_query($query))
				echo "<p class=\"infos\">Cr&#233;ation du profile ".($_POST['profile'])." effectu&#233;e avec succ&#232;s</p>";
			else
				die("Erreur de creation du profile ".mysql_error());
		}
		afficher();
	}else{
?>
<script>
function addprofile(){
	var obj = document.forms['frm'];
	if(obj.profile.value == "")
		alert("-Entrer un nom de profile-");
	else
		obj.submit();
}
</script>
<div id = 'zonetravail'><div class = 'titre'>CREATION DE PROFILES UTILISATEURS</div>
<form name='frm' onsubmit="addprofile(); return false;" enctype="multipart/form-data" method="POST">
	<div class="cadre">
        <fieldset><legend>Informations sur le profile</legend>
        	<table><tr>
            	<td>Nom du Profile</td>
           		<td><input maxlength="25" type="text" name="profile" size="70"/></td>
            </tr></table>
        </fieldset>
      </div>
      <div class = 'navigation'><input type="button" value="Annuler" onclick="document.location = '../accueil/index.php'"><input type="submit" value="Valider" />
      </div>
</form>
</div>
<?php
	}	//Fin du else
}//Fin fonction creer