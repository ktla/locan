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
if(!isset($_SESSION['user']))	@header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
	$codepage = "ADD_USER";
/********************************************************************************************************/
	$titre = "Ajout d'utilisateur";
	require_once("../includes/header_inc.php");
	if(isset($_POST['login'])){
		step2();
	}else
		step1();
	require_once("../includes/footer_inc.php");
	
function step2(){
	if(id_exist($_POST['login'], "LOGIN", "users")){
		print "<p class = 'infos'>Dupplication de login. User existent sous le login : ".$_POST['login'].".</p>";
		step1();
		return;
	}
	try{
		$pdo = Database::connect2db();
		$res = $pdo->prepare("INSERT INTO users(LOGIN, PASSWORD, NOM, PRENOM, PROFILE) 
		VALUES(:login, :mdp,:nom,:prenom, :profile)");
		$res->execute(array(
			'login' => $_POST['login'],
			'mdp' => $_POST['mdp1'],
			'nom' => encode($_POST['nom']),
			'prenom' => encode($_POST['prenom']),
			'profile' => $_POST['profile']
		));
		$_SESSION['infos'] = "Utilisateur ".$_POST['login']." ajout&#233; avec succ&#232;s";
		@header("Location:utilisateur.php");
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getLine()." : ".$e->getFile());
	}
}

function step1(){
?>
<script>
function adduser(){
	var obj = document.forms['frm'];
	if(obj.login.value == "" || obj.profile.value == "" || obj.mdp1.value == "" || obj.mdp2.value == "" || obj.nom.value == "" || obj.prenom.value == ""){
		alert("-Les champs marques par * sont obligatoires-");
		return;
	}
	if(obj.mdp1.value != obj.mdp2.value){
		alert("-Les mots de passe ne correspondent pas");
		return;
	}
	obj.submit();
}
</script>
<div id="zonetravail"><div class="titre">AJOUT D'UN UTILISATEUR DU SYSTEME.</div>
<form name='frm' enctype="multipart/form-data" method="POST" onsubmit="adduser(); return false;">
      <div class="cadre">
      	<fieldset><legend>Renseignement sur l'utilisateur.</legend>
      		<table cellspacing="5">
              <tr>
                  <td class="lib">Login : <span class="asterisque">*</span></td>
                  <td><input  type="text"  name="login" value="<?php isset($_POST['login']) ? $_POST['login'] : "" ?>" size="25"/></td>
                  <td class="lib">Profile : <span class="asterisque">*</span></td>
                  <td><?php 
                    $combo = new Combo("SELECT LIBELLE FROM PROFILE ORDER BY LIBELLE", "profile", 0, 0, isset($_POST['profile']) ? true : false);
					$combo->first = "-Choisir un profile-";
					$combo->selectedid = isset($_POST['profile']) ? $_POST['profile'] : "";
					$combo->view('200px');
                  ?></td>
              <tr>
                  <td class="lib">Mot De Passe : <span class="asterisque">*</span> </td>
                  <td><input type="password" name="mdp1" size="25"/></td>
                  <td class="lib">Retaper Mot de Passe : <span class="asterisque">*</span></td>
                  <td><input   type="password" name="mdp2" size="25"/></td>
              <tr>
              <tr>
                  <td class="lib">Nom : <span class="asterisque">*</span> </td>
                  <td><input type="text" name="nom" size="25"/></td>
                  <td class="lib">Prenom : <span class="asterisque">*</span></td>
                  <td><input   type="text" name="prenom" size="25"/></td>
              <tr>
           </table>
    	</fieldset>
    </div>
   	<div class="navigation">
   		<input type="button" onclick="rediriger('utilisateur.php')" value="Retour"/>
   		<input type="submit" value="Valider"/>
  	</div>
  </form>
</div>
<?php
}
?>