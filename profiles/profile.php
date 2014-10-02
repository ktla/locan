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
	$codepage = "GESTION_PROFILE";
	/*if(isset($_GET['action'])){
		if(!strcmp($_GET['action'], "edit"))
			$codepage = "EDIT_PERIODE";
		elseif(!strcmp($_GET['action'], "delete") || !strcmp($_GET['action'], "deleteall"))
			$codepage = "DEL_PERIODE";
	}*/
/********************************************************************************************************/
$titre = "Gestion des Profiles et des Droits";
include_once("../includes/header_inc.php");
display_content();
include_once("../includes/footer_inc.php");

function display_content(){
	if(isset($_GET['action'])){
		switch($_GET['action']){
			  case 'delete':supprimer();break;
			   case 'deleteall':deleteall();break;
			  case 'edit':edit();break;
		  }
	}else
		afficher();
}
function afficher(){?>
<div id = "zonetravail"><div class="titre">PROFILES ET DROITS</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmgrid" method="post" enctype="multipart/form-data">
	<div class="cadre">
    	<fieldset><legend>Listes des profiles.</legend>
        	<?php 
			$query = "SELECT p.LIBELLE, 
			(SELECT COUNT(u.PROFILE) FROM users u WHERE u.PROFILE = p.LIBELLE)
			FROM profile p";
			$grid = new grid($query);
			$grid->addcolonne(0, "Libell&eacute; du Profile", "0", true);
			$grid->addcolonne(1, "Nombre d'utilisateur", "1", true);
			$grid->editbutton = true;
			$grid->editbuttontext = "Editer les droits";
			$grid->deletebutton = true;
			$grid->deletebuttontext = "Supprimer";
			$grid->selectbutton = true;
			$grid->display();
			?>
        </fieldset>
    </div>
    <div class = 'navigation'>
     	<input type = 'button' onclick = "deletecheck();" value = 'Supprimer' />
        <input type = 'button' onclick = "rediriger('ajouter.php');" value = 'Ajouter'/>
    </div>
</form>
</div>
<?php
}
function supprimer(){
/*
	Verifier la donnee recu par $_GET['line'];
*/
	$res = mysql_query("SELECT * FROM profile WHERE LIBELLE = '".parse($_GET['line'])."'") or die(mysql_error());
	if(!mysql_num_rows($res)){
		print "<p class = 'infos'>Profile de nom : $_GET[line] inconnu dans le syst&egrave;me</p>";
		print "<script>alert('Profile inconnu');</script>";
		afficher();
		return;
	}
/*
	Interdiction de supprimer le profile avec lequel on est connect2
*/
	if(!strcasecmp($_SESSION['profile'], $_GET['line'])){
		print "<p class=\"infos\"><blink>Impossible de  supprimer le profile sous lequel vous &ecirc;tes connect&#233;s</blink></p>";
		print "<script>alert('Suppression impossible');</script>";
		afficher();
		return;
	}
/*
	Deplacement des users vers le profile par defaut : default
*/
		mysql_query("UPDATE users SET PROFILE = 'Default' WHERE PROFILE='".parse($_GET['line'])."'") or die("Erreur de d&#233;placement des users ".mysql_error());
		if(mysql_affected_rows())
			echo "<p class=\"infos\">".mysql_affected_rows()." utilisateurs d&eacute;plac&eacute;s vers le profile par Default<br/><br/>";
		else
			echo "<p class=\"infos\">Aucun utilisateur d&eacute;plac&eacute; vers le profile Default<br/><br/>";
/*
	Suppression du profile proprement dit
*/
		mysql_query("DELETE FROM profile WHERE LIBELLE = '".parse($_GET['line'])."'") or die("Erreur de suppression du profile ".mysql_error());
		if(mysql_affected_rows())
			echo "Profile ".$_GET['line']." supprim&#233; avec succ&#232;s</p>";
		else
			echo "Aucun profile supprim&#233;</p>";
	afficher();
}
/*
*/
function edit(){
/*
	Validation des informations : mise a jour des droits du profiles
*/
	if(isset($_POST['profile'])){
		mysql_query("DELETE FROM listedroit WHERE PROFILE = '".$_POST['profile']."'") or die(mysql_error());
		print $_POST['profile'];
		if(is_array($_POST['chk'])){
			foreach($_POST['chk'] as $val){
				$query = "INSERT INTO listedroit (IDDROIT, PROFILE) VALUES ('".$val."', '".parse($_POST['profile'])."')";
				mysql_query($query) or die(mysql_error());
			}
			print "<p class = 'infos'>Mise a jour de droits de profile : $_POST[profile]<br/> effectu&eacute;e avec succ&#232;s</p>";
		}
		afficher();
	}
/*
	Affichage des droits : des cases a cocher pour attribuer les droits
*/
	else{
	/*
		Verification des informations transmises par $_GET[line]
	*/
		$res = mysql_query("SELECT * FROM profile WHERE LIBELLE = '".parse($_GET['line'])."'") or die(mysql_error());
		if(!mysql_num_rows($res)){
			print "<p class = 'infos'>Profile de nom : $_GET[line] inconnu dans le syst&egrave;me</p>";
			print "<script>alert('Profile inconnu');</script>";
			afficher();
			return;
		}
		echo "<div id=\"zonetravail\"><div class = 'titre'>MODIFICATION DES DROITS</div>";
		print "<form name=\"frmgrid\" action=\"profile.php?action=edit\" enctype=\"multipart/form-data\" method=\"POST\">";
		print "<div class = 'cadre'>";
	/*
		Obtenir les anciens droits du profile
	*/
		$res = mysql_query("SELECT IDDROIT FROM listedroit WHERE PROFILE = '".parse($_GET['line'])."'") or die(mysql_error());
		$tableau = array();
		while($row = mysql_fetch_array($res))
			$tableau[] = $row['IDDROIT'];
	/*
		Grid des droits a cocher
	*/
		print "<fieldset><legend>Cocher les droits du profile : ".$_GET['line']."</legend>";
		/*$query = "SELECT d.IDDROIT, 
		CONCAT (d.LIBELLE, ' [', (SELECT h.LIBELLE FROM header_menu h WHERE h.IDHEADER = 
		(SELECT m.HEADER FROM menu m WHERE m.IDMENU = d.IDMENU)), ']') AS LIBELLEDROIT 
		FROM droit d ORDER BY IDMENU";*/
		$query = "SELECT d.IDDROIT, d.LIBELLE, 
		(SELECT h.LIBELLE FROM header_menu h WHERE h.IDHEADER = (SELECT m.HEADER FROM menu m WHERE m.IDMENU = d.IDMENU)) AS PARENT   
		FROM droit d 
		ORDER BY PARENT, IDMENU";
		$grid = new grid($query);
		$grid->addcolonne(0, "ID", "0", false);
		$grid->addcolonne(1, "LIBELLE DU DROIT", "1", true);
		$grid->addcolonne(2, "MENU PARENT", "2", true);
		$grid->selectbutton = true;
		$grid->checkedbutton = $tableau;
		$grid->display();
		print "</fieldset>";
		echo "</div>";
		echo "<div class = 'navigation'><input type=\"button\" onclick=\"rediriger('profile.php')\" value=\"Retour\">";
		echo "<input type='submit' value='Valider'/>";
		echo "<input type=\"hidden\" value=\"".$_GET['line']."\" name=\"profile\"></div>
		</form></div>";
	}//Fin du else
}

?>