<?php	
include_once("../includes/commun_inc.php");
if(!isset($_SESSION['user']))
	header("location:../utilisateurs/connexion.php");
$titre = "Mise &agrave; jour de son profil utilisateur.";
$codepage = "PROFILE";
require_once("../includes/header_inc.php");
if(isset($_POST['ancpwd']))
	valider();
profile();
require_once("../includes/footer_inc.php");
function profile(){
?>
<script>
	function profile(){
		if(document.forms[0].login.value == "" || document.forms[0].ancpwd.value == "" || 
		document.forms[0].mdp1.value == "" || document.forms[0].mdp2.value == "" || document.forms[0].nom.value == "" || document.forms[0].prenom.value == "" )
			alert("Tous les champs sont obligatoires");
		else{
			if(document.forms[0].mdp1.value != document.forms[0].mdp2.value)
				alert("Les nouveaux mots de passe ne correspondent pas");
			else{
				document.forms[0].action = "../utilisateurs/profile.php";
				document.forms[0].submit();
			}
		}
	}
</script>
<div id="zonetravail"><div class= 'titre'>MODIFICATION DE PROFILE</div>
    <form action="" method="post" enctype="multipart/form-data">
    <div class="cadre">
    	<fieldset><legend>Renseignement sur le profile.</legend>
			<table>
            	<tr><td><label>Login/Username : </label></td>
                	<td><input type="text" size="20" name="login" readonly = 'readonly' value="<?php echo $_SESSION['user']; ?>" /></td>
                    <td><label>Anc. mot de passe : </label></td>
                    <td><input type="password" name="ancpwd" maxlength="30" size="20"/></td>
                </tr>
                <tr>
                  <td><label>Nouv. mot de passe : </label></td>
                  <td><input type="password" name="mdp1" maxlength="30" size="20"/></td>
                  <td><label>Retaper nouv. mot de passe : </label></td>
                  <td><input   type="password" name="mdp2" maxlength="30" size="20"/></td>
      			<tr>
                <tr>
                  <td><label>Nom : </label></td>
                  <td><input type="text" name="nom" maxlength="30" size="20" value="<?php echo $_SESSION['nom']; ?>"/></td>
                  <td><label>Prenom : </label></td>
                  <td><input   type="text" name="prenom" maxlength="30" size="20" value="<?php echo $_SESSION['prenom']; ?>"/></td>
      			<tr>
   			</table>
         </fieldset>
   	 		<div style="font-size:10px; color:red; text-align:center; margin:5px;">
        		Vous serez redirig&eacute;(e) vers la page de connexion pour authentification d&egrave;s validation du formulaire.
        	</div>
    </div>
  	<div class="navigation">
        <input type="button" onclick="document.location = '../accueil/index.php'" style="cursor:pointer;" value="Annuler"/>
        <input type="button" onclick="profile();" style="cursor:pointer;" value="Valider"/>
   </div>
 </form>
</div>
<?php }
function valider(){
	if(strcmp($_POST['mdp1'], $_POST['mdp2'])){
		print "<p class=\"infos\">Les mots de passe ne correspondent pas.</p>";
		return;
	}
	$res = mysql_query("SELECT * FROM users WHERE LOGIN = '".parse($_SESSION['user'])."'") or die(mysql_error());
	$row = mysql_fetch_array($res);
	if(!mysql_num_rows($res)){
		print "<p class=\"infos\">Utilisateur du systeme inexistent.</p>";
		return;
	}
	if(strcmp($_POST['ancpwd'], $row['PASSWORD'])){
		print "<p class=\"infos\">Ancien mot de passe incorrect.</p>";
		return;
	}
	if(mysql_query("UPDATE users SET PASSWORD = '".parse($_POST['mdp1'])."' , NOM = '".encode($_POST['nom'])."' , PRENOM = '".encode($_POST['prenom'])."' WHERE LOGIN ='".parse($_SESSION['user'])."'"))
		header("location:../utilisateurs/connexion.php");
	else
		die(mysql_error());
}
?>