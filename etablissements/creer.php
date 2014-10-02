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
$codepage = "ADD_ETABLISSEMENT";
/***************************************************************************************************/
	$titre = "Cr&eacute;ation de l'Etablissement.";
	require_once("../includes/header_inc.php");
	if(isset($_POST['identifiant']))
		step2();
	else
		step1();
	require_once("../includes/footer_inc.php");
function step1(){?>
<script language="javascript">
	function step1(){
		var obj = document.forms['frm'];
		if(obj.identifiant.value == "" || obj.principal.value == "" || obj.libelle.value == "" || obj.adresse.value == "")
			alert("-Veuillez entrer tous les champs obligatoires-");
		else
			obj.submit();
	}
	function chargerlogo(){
		//alert("je suis");
		var id = document.getElementById("logosrc");
		var obj = document.getElementById("chargerlogo");
		var img = document.createElement("img");
		//alert(id.value);
		//var value = 
		img.setAttribute("src", id.value);
		obj.removeChild(obj.childNodes.item(0));
		obj.appendChild(img);
	}
</script>
<div id="zonetravail"><div class="titre">CREATION DE L'ETABLISSEMENT.</div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="POST" onsubmit="step1(); return false;">
    	<div class="cadre">
        	<fieldset><legend>Informations sur l'Etablissement.</legend>
            	<table cellspacing="2"><tr>
                	<td>Identifiant * : </td><td><input type="text" name="identifiant" maxlength="25"/></td>
                    <td>Libell&eacute; * : </td><td><input type="text" name="libelle"/></td>
                </tr><tr>
                	<td>Adresse * : </td><td><input type="text" name="adresse" maxlength="250"/></td>
                    <td>Principal * : </td><td><input type="text" name="principal" maxlength="250"/></td>
                </tr><tr>
                	<td>T&eacute;l&eacute;phone : <br/><span class = 'astuce' title="S&eacute;parateur : Point-virgule">S&eacute;parer les num&eacute;ros par ;</span></td>
                    <td><input type="text" name="tel" maxlength="250"/></td>
                    <td>Mobile : </td><td><input type="text" name="mobile" maxlength="50"/></td>
                </tr><tr>
                	<td>E-mail : </td><td><input type="text" name="email" maxlength="50"/></td>
                    <td bgcolor="#CCCCCC" rowspan="6" colspan="2" id="chargerlogo">Pas de Logo!!!</td>
                </tr><tr>
                    <td>Site Web : </td><td><input type="text" name="siteweb" maxlength="100"/></td>
                </tr><tr>
                	<td>Compte Bancaire : </td><td><input type="text" name="cptebancaire" maxlength="250"/></td>
                </tr><tr>
                	<td>N° Autorisation : </td><td><input type="text" name="autorisation" maxlength="250"/></td>
                </tr><tr>
                	<td>R&eacute;glement : </td><td><input type="file" name="reglement"/></td>
                </tr><tr>
                	<td>Logo : </td><td><input type="file" onchange="chargerlogo();" id="logosrc" accept="image/*" name="logo" maxlength="250"/></td>
                </tr><tr>
                	<td colspan="2" style="font-size:10px; text-align:center; color:#FF0000;">Tous les &eacute;l&eacute;ments marqu&eacute;s par * sont obligatoires.</td>
                </tr></table>
            </fieldset>
        </div>
        <div class="navigation"><input type="submit" value="Valider" />
        </div>
    </form>
</div>
<?php }
function step2(){
	//Stocker les informations et rediriger vers la page de la fiche de l'etablissement
	//Files storage
	$logo = ""; $reglement = "";
	if(isset($_FILES['logo'])){
		$logo = "../etablissements/".$_FILES['logo']['name'];
		if(move_uploaded_file($_FILES['logo']['tmp_name'], $logo));
		else{
			print "<p class = 'infos'>Erreur de chargement du logo...</p>";
			return;
		}
	}
	if(isset($_FILES['reglement'])){
		$reglement = "../etablissements/".$_FILES['reglement']['name'];
		if(move_uploaded_file($_FILES['reglement']['tmp_name'], $reglement));
		else{
			print "<p class=\"infos\">Erreur de chargement du reglement...</p>";
			return;
		}
	}
	//Informations storage
	$query = "INSERT INTO etablissement(IDENTIFIANT, LIBELLE, ADRESSE, PRINCIPAL, LOGO, EMAIL, TEL, MOBILE, SITEWEB, REGLEMENT, AUTORISATION, CPTEBANCAIRE) 
	VALUES('".parse($_POST['identifiant'])."', '".parse($_POST['libelle'])."', '".parse($_POST['adresse'])."', '".parse($_POST['principal'])."', '".parse($logo)."',
	 '".parse($_POST['email'])."', '".parse($_POST['tel'])."' , '".parse($_POST['mobile'])."', '".parse($_POST['siteweb'])."', '".parse($reglement)."',
	  '".parse($_POST['autorisation'])."', '".parse($_POST['cptebancaire'])."')";
	 if(mysql_query($query))
	 	print "<script>rediriger('fiche.php');</script>";
	else
		die("Erreur d'enregistrement de l'etablissement ".mysql_error());
}
?>