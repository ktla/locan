<?php
session_start();
/**
	verification de l' existence du fichier de configuration
*/
function repaint(){
	if (file_exists("./config.xml"))
		header("location:../identifiant/index.php");
}

/**
	test de l' existence d' un logo dans le le repertoire logo
*/
function existFile($rep){
	$trouve=0;
	$dir = dir($rep);
	while($nom = $dir->read())
	  if(file_exists($rep."/".$nom) && strlen($nom) > 2)		
		$trouve=1;
	$dir->close();
	return $trouve;
}
/**
	fonction permettant de renvoyer un fichier
*/
function returnFile($chemin){
	$name="";
	$dir = dir($chemin);
	while($nom = $dir->read())
	   if(file_exists($chemin."/".$nom) && strlen($nom) > 2)		
		     $name=$nom;
	$dir->close();
	return $name;
}

function test(){
	return (isset($_POST['user']) && isset($_POST['pwd']) && isset($_POST['identifiant']) && isset($_POST['libelle']) && isset($_POST['adresse']) && isset($_POST['principal']) && isset($_FILES['bd']["name"]));
}
/**
	Validation des parametres
*/

if (test() && !file_exists("./config.xml")){
	require_once("install_inc.php");
	$inst = new Installation();
	$inst->admin = $_POST["user"];
	$inst->adminpwd = $_POST["pwd"];	
	$inst->dbname = strtolower(returnBd());
	$inst->bdfile = $_FILES["bd"]['name'];
	$inst->host="localhost";
	/**
		Remplir les parametres de l'etablissement
	*/
	$inst->etablissement->identifiant = $_POST["identifiant"];
	$inst->etablissement->libelle = $_POST["libelle"];
	$inst->etablissement->adresse = $_POST["adresse"];
	$inst->etablissement->principal = $_POST["principal"];
	$inst->etablissement->tel = (isset($_POST["tel"]))?$_POST["tel"]:"";
	$inst->etablissement->mobile = (isset($_POST["mobile"]))?$_POST["mobile"]:"";
	$inst->etablissement->email = (isset($_POST["email"]))?$_POST["email"]:"";
	$inst->etablissement->siteweb = (isset($_POST["siteweb"]))?$_POST["siteweb"]:"";
	$inst->etablissement->cptebancaire = (isset($_POST["cptebancaire"]))? $_POST["cptebancaire"]:"";
	$inst->etablissement->autorisation = (isset($_POST["autorisation"]))? $_POST["autorisation"]:"";
	$inst->etablissement->logo = existFile("./logo")?"../configurations/logo/".returnFile("./logo"):"../images/logo_default.png";
	$inst->etablissement->datecreation = date("Y-m-d H:i:s");
	if($inst->validate()){
		$_SESSION["actif"] = 2;
	}
}
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
.reglement{
	position:relative;
    width: 65px;
    height: 20px;
    overflow:hidden;
	-moz-border-radius:4px 4px 4px 4px;
	-webkit-border-radius:4px 4px 4px 4px;
	border-radius:4px 4px 4px 4px;
	background:yellow;
}
.reglement:active{
	background-color:#F4F400;
}

.reglement-button{
	font-size:10px;
	font-weight:bold;
	cursor:pointer;
	position: absolute;
    width: 100%;
    height: 100%;
	padding-top:2px;
	z-index:1;
	text-align:center;
}
.attachment-button-file{
	font: 500px monospace;
    opacity:0;
    filter: alpha(opacity=0);
    position: absolute;
    z-index:2;
    top:0;
    right:0;
    padding:0;
    margin: 0;
	cursor:pointer;
}
</style>
</head>
<body>
	<div id="container">
  		<div id="header" style="border-bottom:dotted 2px #CCC;">
		<img src="../images/logo_default.png" height="100" width="250" title="LOGO DU LOGICIEL PAR DEFAUT" alt="LOGO DU SITE">
       	<p style="position:relative;float:right; margin-right:5px; bottom:-74px; font-size:12px; font-style:italic; color:#777;">Connexion au syst&egrave;me Logesta.</p>
        <div style="background-color:#CCC; top:0px; height:2px;"></div>
        </div>
        <!-- Zone de la creation d1 etablissement -->
        <div style="position:relative; top:25px; margin-left:10px;">
        <div id="zonetravail" style="width:700px;"><div class="titre" align="center"><font style="font-size:12px; font-weight:bolder; font-variant:small-caps; letter-spacing:1px;">CONFIGURATIONS SYSTEME</font></div>
		<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="POST">
    	<div class="cadre">
        	<fieldset style="border-color:transparent;"><legend style="color:#888; font-style:italic;">Informations sur l'Etablissement.</legend>
            <p style="border-bottom:#CCC 1px solid; position:relative; margin-top:-2px;"></p>
            	<table cellspacing="5" style="border-bottom:#CCC 1px solid;"><tr>
                	<td>Identifiant <font color="red">*</font> : </td><td><input type="text" name="identifiant" id="identifiant" maxlength="25"/></td>
                    <td>Libell&eacute; <font color="red">*</font> : </td><td colspan="2"><input type="text" id="libelle" name="libelle"/></td>
                </tr><tr>
                	<td>Adresse <font color="red">*</font> : </td><td><input type="text" id="adresse" name="adresse" maxlength="250"/></td>
                    <td>Principal <font color="red">*</font> : </td><td colspan="2"><input type="text" id="principal" name="principal" maxlength="250"/></td>
                </tr><tr>
                	<td>T&eacute;l&eacute;phone : <br/><span class = 'astuce' title="S&eacute;parateur : Point-virgule">S&eacute;parer les num&eacute;ros par ;</span></td>
                    <td><input type="text" name="tel" maxlength="250"/></td>
                    <td>Mobile :</td><td colspan="2"><input type="text" name="mobile" maxlength="50"/></td>
                </tr><tr>
                	<td>E-mail : </td><td><input type="text" name="email" maxlength="50"/></td>
                    <td></td>
                    <td rowspan="3" colspan="3" >
                    	<p style="margin:0px; font-size:10px;font-weight:bold; color:#888; font-style:italic;">Logo (png,jpg,gif,jpeg)</p>
                    	<div id="chargerlogo" style="height:70px; width:100px; background-color:#CCC; text-align:center; font-size:10px;">
                        	<?php 
							if (existFile("./logo"))
								echo "<img src=\"./logo/".returnFile("./logo")."\" width=\"100px\" height=\"70px\" />";
							else
								echo "<strong style=\"position:relative; top:35%;\">Pas de Logo</strong>";							
							?>
                        </div>
                    </td>
                </tr><tr>
                    <td>Site Web : </td><td><input type="text" name="siteweb" maxlength="100"/></td>
                </tr><tr>
                	<td>Compte Bancaire : </td><td><input type="text" name="cptebancaire" maxlength="250"/></td>
                </tr><tr>
                	<td>N° Autorisation : </td><td><input type="text" name="autorisation" maxlength="250"/></td>
                    <td></td>
                    <td>
                    <div class="reglement" style="float:left;">
						<a class="reglement-button" >Selectionner</a>
						<a id="button-jointure">
                        	<input  type="file" id="logo" name="logo" class="attachment-button-file" onchange="selectionImgClick();" />
                        </a>			         
					</div>
                   	<img title="Supprimer image" style="margin-left:5px; margin-top:3px; cursor:pointer;" src="../images/icons/drop.png" onclick="clearImage();"/>
                    </td>
                    <td id="erreurImage" style="font-size:10px;color:red;font-style:italic;"></td>
                </tr><tr>
                	<td colspan="2" style="font-size:10px; text-align:center; color:#FF0000; ">Tous les &eacute;l&eacute;ments marqu&eacute;s par * sont obligatoires.</td>
                </tr>
                </table>
                <table style="position:relative; margin-left:22px; ">
                <tr>
                	<td>R&eacute;glement : <br/><span class = 'astuce' title="Extentions autoris&eacute;es">Txt,Doc,Docx,Pdf.</span></td>
                    <td>
                    <div class="reglement">
						<a class="reglement-button" >Selectionner</a>
						<a id="button-jointure">
                        	<input  type="file" id="reglement" name="reglement" class="attachment-button-file" onchange="selectionReglementClick();" />
                        </a>			         
					</div></td>
                    <td id="reglement_print" colspan="3" align="left" style="font-size:11px;font-style:italic;"></td>
                </tr>
                <tr>
                <td>Base de donn&eacute;es <font color="red">*</font> : <br/><span class = 'astuce' title="Extentions autoris&eacute;es">Sql.</span></td>
                 <td>
                    <div class="reglement">
						<a class="reglement-button" >Selectionner</a>
						<a id="button-jointure">
                        	<input  type="file" id="bd" name="bd" class="attachment-button-file" onchange="selectionBdClick();" />
                        </a>			         
					</div></td>
                    <td id="bd_print" colspan="3" align="left" style="font-size:11px;font-style:italic;"></td>	
                </tr>
                <tr>
                <td>Nom d&rsquo; utisateur <font color="red">*</font> : <br/><span class = 'astuce' title="Extentions autoris&eacute;es">Administrateur UID</span></td><td><input type="text" id="user" name="user" maxlength="250"/></td>
                <td>Mot de Passe <font color="red">*</font> : <br/><span class = 'astuce' title="Extentions autoris&eacute;es">Administrateur BD</span></td><td><input type="password" id="pwd" name="pwd" maxlength="250"/></td>
                </tr>
                </table>
            </fieldset>
            <b style="position:absolute; font-size:11px; text-align:center;margin-top:27px; color:red; text-decoration:blink;" id="error"></b>
        </div>
        <div class="navigation"><input type="button" value="Suivant" onclick="step2(this)" /></div>
    </form>
    <iframe name="hiddeniframe" style="display:none;" src="about:blank"></iframe>
</div>
</div>
        
         <div id="pied" style="margin-top:60px;">
            <p class="trait"></p> 
            <label class="cop">&copy;Copyright Administration LOGESTA</label>
            <label class="lien">Logiciel de Gestion des Travaux Acad&eacute;miques...</label>
          </div>
      </div>
   </body>
</html>

<?php
	if(isset($_SESSION["actif"])){
		unset($_SESSION["actif"]);
		session_destroy();
	}
	repaint();
?>