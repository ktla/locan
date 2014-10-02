<?php
session_start(); 
if($_SESSION["actif"] != 2)
	header("location:../configurations/index.php");
$_SESSION["actif"] = 1; 
function test(){
	return isset($_POST["eleve"])&& !empty($_POST["eleve"])&&
	isset($_POST["prof"])&& !empty($_POST["prof"])&&
	isset($_POST["compte"])&& !empty($_POST["compte"])&&
	isset($_POST["staff"])&& !empty($_POST["staff"]);
}


if(test()){
	require_once("../includes/database_inc.php");
	$con = Database::connect2db();
	$con->exec("ALTER TABLE eleve ALTER COLUMN MATEL SET DEFAULT '".$_POST['eleve']."'");
	$con->exec("ALTER TABLE professeur ALTER COLUMN IDPROF SET DEFAULT '".$_POST['prof']."'");
	$con->exec("ALTER TABLE compte ALTER COLUMN IDCOMPTE SET DEFAULT '".$_POST['compte']."'");
	$con->exec("ALTER TABLE staff ALTER COLUMN IDSTAFF SET DEFAULT '".$_POST['staff']."'");
	//$con->exec("INSERT INTO identifiant (ELEVE,PROFESSEUR,COMPTE,STAFF) VALUES('".$_POST['eleve']."','".$_POST['prof']."','".$_POST['compte']."','".$_POST['staff']."')");
	if(isset($_SESSION["actif"]) && $_SESSION["actif"] == 1)
		session_destroy();
}
if (file_exists("../configurations/config.xml"))
		@header("location:../accueil/index.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gestion des activit&eacute;s acad&eacute;miques</title>
<link rel="stylesheet" media="screen" type="text/css" href="../css/connexion.css" />
<link rel="stylesheet" media="screen" type="text/css" href="../css/design.css"/>
<link rel="stylesheet" media="screen" type="text/css" href="../css/variation.css"/>
<link rel="stylesheet" media="screen" type="text/css" href="../css/grid.css"/>
<!--
	 UN MENU
 -->
<link rel="stylesheet" href="../css/jquery/demos.css">
<link rel="icon" href="../images/favicon.ico" type="image/x-icon" />

<!-- 
	 JAVASCRIPT PERSONNEL 
-->
<script language="javascript" src="../js/scriptjquery.js"></script>
<script language="javascript" src="../js/script.js"></script>
<script language="javascript" src="../js/scriptajax.js"></script>

<!-- 
	 JAVASCRIPT JQUERY 
-->
<script language="javascript" src="../js/jquery-1.7.1.js"></script>


<!-- 
	 SCRIPT LOCAL A LA PAGE 
-->
<script language="javascript" src="./js.js"></script>



<!-- 
	 STYLE LOCAL A LA PAGE 
-->
<style type="text/css" >
</style>
</head>	
	<body>	
		<div id="container">
            <div id="header" style="border-bottom:dotted 2px #CCC;">
            	<img src="../images/logo_default.png" height="100" width="250" title="LOGO DU LOGICIEL PAR DEFAUT" alt="LOGO DU SITE"/>
            	<p style="position:relative;float:right; margin-right:5px; bottom:-74px; font-size:12px; font-style:italic; color:#777;">Connexion au syst&egrave;me Logesta.</p>
            	<div style="background-color:#CCC; top:0px; height:2px;"></div>
            </div>
            <!-- Zone de la creation d1 etablissement -->
            	<div style="position:relative; top:25px; margin-left:10px;">
            		<div id="zonetravail" style="width:700px;">
                    	<div class="titre" align="center"><font style="font-size:12px; font-weight:bolder; font-variant:small-caps; letter-spacing:1px;">CONFIGURATIONS DES IDENTIFIANTS CLES DU SYSTEME</font></div>
                    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="POST">
                        <div class="cadre">
                        	<table>
                            	<tr>
                                	<td>Eleve <font color="red">*</font> : </td>
                                    <td><input type="text" name="eleve" id="eleve" maxlength="25"/></td>
                                    <td><em style="color:#7979FF; letter-spacing:1px; font-size:10px">Identifiant par defaut des Eleves</em></td>                                
                                </tr>
                                <tr>
                                	<td>Professeur <font color="red">*</font> : </td>
                                    <td><input type="text" name="prof" id="prof" maxlength="25"/></td>
                                    <td><em style="color:#7979FF; letter-spacing:1px; font-size:10px">Identifiant par defaut des Professeurs</em></td>                                
                                </tr>
                                <tr>
                                	<td>Compte <font color="red">*</font> : </td>
                                    <td><input type="text" name="compte" id="compte" maxlength="25"/></td>
                                    <td><em style="color:#7979FF; letter-spacing:1px; font-size:10px">Identifiant par defaut des Comptes</em></td>                                
                                </tr>
                                <tr>
                                	<td>Staff <font color="red">*</font> : </td>
                                    <td><input type="text" name="staff" id="staff" maxlength="25"/></td>
                                    <td><em style="color:#7979FF; letter-spacing:1px; font-size:10px">Identifiant par defaut du personnel</em></td>
                                </tr>
                                <tr>
                                	<td></td>
                                    <td align="center"><?php echo !test()?"<font color=\"red\"> Remplir correctement les champs !!!</font>":""; ?></td>
                                    <td></td>
                                </tr>
                                
                            </table>
                        
                        
                        
                        
                        </div>        
                        <div class="navigation"><input type="submit" value="Suivant"/></div>
                    </form>
                    </div>
                </div>
                
        </div>
 		<div id="pied" style="margin-top:60px;">
            <p class="trait"></p> 
            <label class="cop">&copy;Copyright Administration LOGESTA</label>
            <label class="lien">Logiciel de Gestion des Travaux Acad&eacute;miques...</label>
        </div>
	</body>
</html>