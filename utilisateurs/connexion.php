<?php
	require_once("../includes/commun_inc.php");
	if(isset($_SESSION['user'])){
		unset($_SESSION['user']);
		unset($_SESSION['profile']);
		unset($_SESSION['eglise']);
		session_destroy();
	}
	if(isset($_POST['login'])){
		$autoriser = is_autoriser($_POST['pwd'], $_POST['login']);
		if($autoriser == 1){
			$_SESSION['periode'] = parse($_POST['periode']);
			if(isset($_SESSION['activeurl']))
				@header("location:".$_SESSION['activeurl']);
			else
				@header("location:../accueil/index.php");
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"s>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Logiciel de Gestion des Travaux Acad&eacute;miques</title>
<link rel="stylesheet" media="screen" type="text/css" title="Design" href="../css/connexion.css" />
<link rel="stylesheet" media="screen" type="text/css" href="../css/design.css"/>
<link rel="icon" href="../images/favicon.ico" type="image/x-icon" />

<script language="javascript" src="../js/script.js"></script>
</head>
<body>
	<div id="container">
  		<div id="header">
        <?php 
		if(file_exists("../configurations/config.xml")){
			require_once('../includes/database_inc.php');
			$db = new Database("SELECT LOGO, IDENTIFIANT, HAUTEURLOGO, LARGEURLOGO FROM etablissement");
			if($db->select()){
				$row = $db->getRow();
				print "<img src=\"".$row->item('LOGO')."\" height=\"".$row->item('HAUTEURLOGO')."\" width=\"".$row->item('LARGEURLOGO')."\" 
				title=\"LOGO ETABLISSEMENT ".$row->item('IDENTIFIANT')."\" alt=\"LOGO ETABLISSEMENT ".$row->item('IDENTIFIANT')."\" />";
			}else
				print "<img src=\"../images/logo_default.png\" height=\"100\" width=\"250\" title=\"LOGO DU LOGICIEL PAR DEFAUT\" alt=\"LOGO DU SITE\"/>";
		}else
			@header("location:../configurations/index.php");
		?>
        <!-- div style="background-image:url(../images/barre.png); background-repeat:repeat-x; height:20px;"></div -->
        </div>
        <div align="center" style="margin:25px;"><div id="submenu" style="text-shadow:1px 1px 2px #808040; font-variant:small-caps;font-weight:bold;border-top-style:solid;">Connexion au syst&egrave;me Logesta.</div></div>
        <div id="content" style="margin-top:50px;">
            <form method="post" name = 'frmconnect' enctype="multipart/form-data" action="../utilisateurs/connexion.php" onsubmit="connexion(); return false;">
     			<div class="contenu">
                    <p class="titleconnexion">Administration LOGESTA</p>
                    <p class="trait"></p>
                    <div>
                        <table border="0" cellspacing="5" style="margin:auto;">
                            <tr><td><label class="txt" title="Nom Utilisateur ou matricule">Nom Utilisateur :</label></td>
                            <td><input type="text" maxlength="100" size="30" name="login" /></td></tr>
                            <tr><td><label class="txt">Mot de Passe  :</label></td>
                            <td><input type="password" maxlength="100" size="30" name="pwd"  accesskey="enter"/></td></tr>
                            <tr><td><label class="txt" title="Ann&eacute;e Acad&eacute;mique">Ann&eacute;e Acad. : </label></td>
                            <td>
                                <?php
                                    $periode = new Combo("SELECT * FROM annee_academique ORDER BY ANNEEACADEMIQUE", 'periode', 0, 0); 
                                    $periode->first = "-Choisir une p&eacute;riode-";
                                    $periode->view();
                                ?>
                            </td></tr>
                            <tr><td colspan="2"><div id="erreur">*</div></td></tr>
                            <tr><td colspan="2" align="right"><input type="submit" id="but" value="Connexion" accesskey="enter"/></td></tr>
                        </table>
                    </div>
             </div>
            </form>
         </div>
         <div id="pied" style="position:relative; width:40%; text-align:center;margin-top:-50px;" >
    		<p class="trait"></p> 
        	<p class="cop">&copy;Copyright Administration LOGESTA  <br /><em>Logiciel de Gestion des Travaux Acad&eacute;miques...</em></p>
			
    	  </div>
      </div>
      <script>
	  	document.frmconnect.login.focus();
		function connexion(){
			if(document.forms[0].login.value == "" || document.forms[0].pwd.value == "" || document.forms[0].periode.value == ""){
				printError(3);
			}else{
				document.forms[0].action = "connexion.php";
				document.forms[0].submit();
			}
		}
	</script>
  	<?php
  		if(isset($autoriser)){
			if($autoriser != 1)
				print "<script>printError(".$autoriser.")</script>";
		}
  	?>
   </body>
</html>