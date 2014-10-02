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
	- $codepage : Code de page utile pour la verification des droits d'acces
		Les droit de modification commence par EDIT_ suivit du nom menu (EDIT_PROFESSEUR, EDIT_CLASSE, EDIT_PERIODE...)
		Les droit de supression commence par DEL_ suivit du nom menu (DEL_PROFESSEUR, DEL_CLASSE, DEL_PERIODE...)
*/
	$codepage = "ADD_PROFESSEUR";
/********************************************************************************************************/
	$titre = "Ajouter des professeurs.";
	require_once("../includes/header_inc.php");
	if(isset($_POST['step'])){
		switch($_POST['step']){
			case 1:step1();break;
			case 2:step2();break;
			case 3:step3();break;
		}
	}else
		step1();
	require_once("../includes/footer_inc.php");
/*function step1(){?>
<script>
	function step1(){
		if(document.forms['frm'].idprof.value == "")
			alert("Ce champ est obligatoire");
		else
			document.forms['frm'].submit();
	}
</script>
<div id="zonetravail"><div class="titre">AJOUT DE PROFESSEURS.</div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="step1(); return false;" enctype="multipart/form-data" method="POST">
    <div class="cadre">
    	<fieldset><legend>Identification du professeur.</legend>
        	<table><tr>
            	<td>Identifiant ou Matricule : </td><td><input type="text" maxlength="10" name="idprof" size="30"></td>
            </tr></table>
        </fieldset>
    </div>
    <div class="navigation"><input type="button" onClick="home();" value = 'Annuler'/>
    	<input type="submit" value="Suivant"/><input type="hidden" value="2" name="step"/>
    </div>
    </form>
</div>
<?php }*/
function step1(){
?>
<script>
	function step2(){
		if(document.frm.nomprof.value == "" || document.frm.tel.value == "" || document.frm.datenaiss.value == "" || document.frm.lieunaiss.value == "" || document.frm.adresse.value == "" || document.frm.sexe.value == "" || document.frm.religion.value == "" || document.frm.profile.value == "")
			alert(decodeURIComponent("Les champs marqués sont obligatoires"));
		else
			document.forms['frm'].submit();
	}
	function preciserReligion(){
		var sms = "Veuillez préciser la religion du Professeur\n Dans le champ texte qui s'affichera à droite--";
		preciserOption("cibleRel", "religion", sms, "otherReligion");
	}
	
	
	
	var loadImage = function(lien){
	balise = '<img src="'+lien+'" width="210px" height="132px" />';
	document.getElementById("chargerphoto").innerHTML = balise; 
	document.getElementById("erreurImage").innerHTML = "";
    }
	
	/*
	Function permettant le chargement du reglement
*/
var loadReglement = function(id,mes){
	switch (parseInt(id)){
	case 0 :balise = '<p style="float:left;" >'+mes+'</p><img title="Supprimer reglement" height="10px" width="10px" style="margin-left:5px; margin-top:13px; cursor:pointer;" src="../images/icons/cancel.png" onclick="clearReglement();"/>';
	break;
	default : balise = '<p style="float:left; color:red;" >'+mes+'</p><img title="Supprimer reglement" height="10px" width="10px" style="margin-left:5px; margin-top:13px; cursor:pointer;" src="../images/icons/cancel.png" onclick="clearReglement();"/>';
	
	}
	document.getElementById("reglement_print").innerHTML = balise; 
}

	
	
	function selectionImgClick(){ // actionner le onchange de l' input image
		var tab = document.getElementsByName("frm");
		action = tab.item(0).action;
		tab.item(0).target="hiddeniframe";
		tab.item(0).action="./chargementImg.php";
		tab.item(0).submit();
		tab.item(0).target="";
		tab.item(0).action = action;
	}
	
	function selectionReglementClick(){ // actionner le onchange de l' input reglement
		var tab = document.getElementsByName("frm");
		action = tab.item(0).action;
		tab.item(0).target="hiddeniframe";
		tab.item(0).action="./chargementReglement.php";
		tab.item(0).submit();
		tab.item(0).target="";
		tab.item(0).action = action;
	}
	
	var CA = {}; // Controle Ajax permettant de verifier l' image uploade
	CA.UploadAjax = function(){};
	CA.UploadAjax.callBack = function (message){
	document.getElementById('photos').value="";
	tab = message.split(';');
	if(tab[0]== 0)	
		loadImage(tab[1]);
	else
		document.getElementById("erreurImage").innerHTML = tab[1];
	}

	var CD = {}; // Controle Ajax permettant de verifier le reglement uploade
	CD.UploadAjax = function(){};
	CD.UploadAjax.callBack = function (message){
		tab = message.split(';');
		loadReglement(tab[0],tab[1]);
	}


function clearImage(){
	balise = '<strong style="position:relative; top:35%;">Aucune Image</strong>';
	$.ajax({
		type:'POST',
		url:'./clearImage.php',
		datatype:'text',
		error: function() {alert('Erreur serveur');}
	});
	document.getElementById("chargerphoto").innerHTML = balise; 
	document.getElementById("erreurImage").innerHTML = "";
}
function clearReglement(){
	$.ajax({
		type:'POST',
		url:'./clearReglement.php',
		datatype:'text',
		error: function() {alert('Erreur serveur');}
	});
	document.getElementById("reglement_print").innerHTML =""; 
	document.getElementById("reglement").value = "";
}
	
</script>

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
	padding-top:4px;
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

<div id="zonetravail"><div class="titre">AJOUT DE PROFESSEUR.</div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="step2(); return false;" enctype="multipart/form-data" method="POST">
    <div class="cadre">
            <fieldset><legend style="position:relative;margin-left:38%;font-weight:bold;text-transform:capitalize;">Renseignement sur le Professeur.</legend>
            <table>
            <tr>
            	<td class="lib"><label>Nom : <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" value="<?php echo isset($_POST['nomprof']) ?decode($_POST['nomprof']):""; ?>" name="nomprof" maxlength="250"/></td>
                <td  class="lib"><label>Date de Naiss. : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($_POST['datenaiss']) ? $_POST['datenaiss']:""; ?>" type="text" name="datenaiss" id="datenaiss"/></td>
            </tr><tr>
            	<td class="lib"><label>Pr&eacute;nom : </label></td>
                <td><input type="text" value="<?php echo isset($_POST['prenom']) ? decode($_POST['prenom']):""; ?>" name="prenom" maxlength="250"/></td>
                <td class="lib"><label>Lieu de Naiss. <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" name="lieunaiss" maxlength="50" value="<?php echo isset($_POST['lieunaiss']) ? decode($_POST['lieunaiss']) :""; ?>"/></td>
            </tr><tr>
                <td class="lib"><label>T&eacute;l&eacute;phone : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($_POST['tel'])? $_POST['tel']:""; ?>" type="text" name="tel" maxlength="15"/></td>
                <td>
                	<div class="reglement" style="float:left;">
						<a class="reglement-button" >Image</a>
						<a id="button-jointure">
                        	<input  type="file" id="photos" name="photos" class="attachment-button-file" onchange="selectionImgClick();" />
                        </a>			         
					</div>
                   	<img title="Supprimer image" style="margin-left:5px; margin-top:3px; cursor:pointer;" src="../images/icons/drop.png" onclick="clearImage();"/>
                	<p style="font-size:10px;color:#818181;margin-top:2px;position:absolute;" >Max 1280x800 pixel</p>
                </td>
                <td id="chargerphoto" rowspan="5" bgcolor="#CCC" align="center"><?php echo isset($_POST['image']) ?  "<img src=\"./photos/".$_POST['image']."\" width=\"210px\" height=\"132px\" />":"<strong style=\"position:relative; top:35%;\">Aucune Image</strong>"; ?></td>
            </tr><tr>
            <td class="lib"><label>E-mail : </label></td>
                <td><input value="<?php echo isset($_POST['email'])? decode($_POST['email']):""; ?>" type="text" name="email" maxlength="50"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse Professeur :<span class = 'asterisque'>*</span> </label></td>
                <td><input type="text" name="adresse" value="<?php echo isset($_POST['adresse']) ? decode($_POST['adresse']) :""; ?>" maxlength="100"/></td>
            </tr><tr>
            	<td class="lib"><label>Contact en cas d'urgence.</label></td>
                <td><input type="text" name="nomcontact" maxlength="50" value="<?php echo isset($_POST['nomcontact']) ? decode($_POST['nomcontact']) :""; ?>"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse du contact : </label></td>
                <td><input type="text" name="addressecontact" maxlength="150" value="<?php echo isset($_POST['addressecontact']) ? decode($_POST['addressecontact']) : ""; ?>"/></td>
            </tr><tr>
                <td class="lib"><label>Religion : <span class = 'asterisque'>*</span> </label></td>
                <td><?php $q = "SELECT * FROM religion ORDER BY LIBELLE";
							$combo = new Combo($q, "religion", 0, 1, isset($_POST['religion']) ? true: false);
							$combo->first = "-Choisir une religion-";
							$combo->other = true;
							$combo->selectedid = isset($_POST['religion'])? $_POST['religion'] : "";
							$combo->onchange = "preciserReligion();";
							$combo->view('210px');
				?></td>
                 <td colspan="2"><div id="cibleRel"></div></td>
           </tr><tr>
           </tr><tr>
           		<td class="lib"><label>Sexe : <span class = 'asterisque'>*</span></label></td>
                <td><select name = 'sexe' style="width:210px;">
                	<option value="Masculin">Masculin</option>
                	<option value="Feminin">F&eacute;minin</option>
                    </select>
                 </td>
                <td></td>
                <td style="text-align:center; font-size:10px; color:red" id="erreurImage"></td>	
           </tr> 
           <tr>
           		<td class="lib"><label>Profile : <span class = 'asterisque'>*</span> </label></td>
                <td><?php $q = "SELECT * FROM profile ORDER BY LIBELLE";
							$combo = new Combo($q, "profile", 0, 0, isset($_POST['profile']) ? true: false);
							$combo->first = "-Choisir un Profile-";
							$combo->selectedid = isset($_POST['profile'])? $_POST['profile'] : "";
							$combo->view('210px');
				?></td>
           </tr>
           <tr>
                	<td class="lib">Curriculum Vitae : <br/><span class = 'astuce' title="Extentions autoris&eacute;es">Txt,Doc,Docx,Pdf.</span></td>
                    <td>
                    <div class="reglement" style="margin-left:4px;">
						<a class="reglement-button" >Selectionner</a>
						<a id="button-jointure">
                        	<input  type="file" id="reglement" name="reglement" class="attachment-button-file" onchange="selectionReglementClick();" />
                        </a>			         
					</div></td>
                    <td id="reglement_print" colspan="3" align="left" style="font-size:11px;font-style:italic;"><?php echo isset($_POST["reglement"])? "<p style=\"float:left; color:red;\" >".$_POST["reglement"]."</p><img title=\"Supprimer reglement\" height=\"10px\" width=\"10px\" style=\"margin-left:5px; margin-top:13px; cursor:pointer;\" src=\"../images/icons/cancel.png\" onclick=\"clearReglement();\"/>":""?></td>
                </tr><tr>
           		<td colspan="4" style="text-align:center; font-size:10px; color:red">Les champs marqu&eacute;s par * sont obligatoires</td>
           </tr></table>
            </fieldset>
            
            
           <!-- Variables de formulaire -->
         	<div>
                <input type="hidden" name="step" value="2"/>
           </div>
    </div>
    <div class="navigation"><input type="button" onClick="rediriger('ajouter.php');" value = 'Annuler'/>
    	<input type="submit" value="Valider"/>
    </div>
    </form>
    <iframe name="hiddeniframe" style="display:none;" src="about:blank"></iframe>

</div>
<?php }

function getExt($file){
	$tab = explode(".",$file);
	return $tab[count($tab) - 1];	
}


//definition de la methode exists_eleve : nom, prenom , datenaiss,lieunaiss, telephone,religion , ancienets et sexe
function exists_prof($nomprof, $prenom, $datenaiss, $lieunaiss, $tel, $rel, $adresse, $sexe){
	try{
		$pdo = Database::connect2db();
		//(NOMEL,PRENOM,DATENAISS,LIEUNAISS,NOMPERE,ADDRESSEPERE,NOMMERE,ADDRESSEMERE,ADDRESSETUTEUR,TUTEUR,TEL,ADRESSE,EMAIL,SEXE,RELIGION,ANCETBS,DAT
		$query = "SELECT * FROM professeur WHERE NOMPROF='$nomprof' AND PRENOM='$prenom' AND DATENAISS='$datenaiss' AND LIEUNAISS='$lieunaiss' AND TEL = '$tel' AND RELIGION = $rel AND ADRESSE = '$adresse' AND SEXE = '$sexe' ";
		$res = $pdo->query($query);
		print $res->rowCount();
		return ($res->rowCount() > 0);
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
				 
}
/*
	function de validation de donnees
*/



function step2(){
	try{
		$pdo = Database::connect2db();
		
		/*
			Insertion dans ancien etablissement ou religion
			si c'est un nouveau etablissement et renvoi de la cle
		*/
		//Relition
		if(!strcmp($_POST['religion'], "other")){
			$res = $pdo->prepare("INSERT INTO religion(LIBELLE) VALUES(:rel)");
			$res->bindValue("rel", encode($_POST['otherReligion']), PDO::PARAM_STR);
			$res->execute();
			$_POST['religion'] = $pdo->lastInsertId();
		}
		
			//parametre de la methode exists_eleve : nom, prenom , datenaiss,lieunaiss, telephone,religion , ancienets et sexe
		if(!exists_prof( encode($_POST['nomprof']),encode($_POST['prenom']), parseDate($_POST['datenaiss']),encode($_POST['lieunaiss']),encode($_POST['tel']), $_POST['religion'],encode($_POST['adresse']),$_POST['sexe'])){
			/*
				Insertion de l'eleve meme
			*/
			
			$query = "INSERT INTO professeur(NOMPROF,PRENOM,DATENAISS,TEL,ADRESSE,EMAIL,SEXE,RELIGION,DATE,LIEUNAISS,NOMCONTACT,ADDRESSECONTACT) 
	VALUES(:nomprof, :prenom, :datenaiss,:tel,:adresse,:email,:sexe,:rel,:date,:lieunaiss,:nomcontact,:addressecontact)";
			$res = $pdo->prepare($query);
			
			$param = array(
				"nomprof" => encode($_POST['nomprof']),
				"prenom" => encode($_POST['prenom']),
				"datenaiss" => parseDate($_POST['datenaiss']),
				"lieunaiss" => encode($_POST['lieunaiss']),
				"adresse" => encode($_POST['adresse']),
				"nomcontact" => encode($_POST['nomcontact']),
				"addressecontact" => encode($_POST['addressecontact']),
				"tel" => encode($_POST['tel']),
				"email" => encode($_POST['email']),
				"sexe" => $_POST['sexe'],
				"rel" => $_POST['religion'],
				"date" => parseDate(date("Y-m-d"))
			);
			$res->execute($param);
			
			$lastid = $pdo->lastInsertId();
			// lecture de la photo
			$dir = dir('./photos/tmp/'); 
			$file="";
			while($nom = $dir->read() ) {
				if(strlen($nom) > 2) 
					$file = $nom;
			}
			if(!empty($file)){
				$ext = getExt($file);
				rename("./photos/tmp/".$file,"./photos/".$lastid.".".$ext);
				$picture = $lastid.".".$ext;
				$pdo->exec(" UPDATE professeur SET PHOTO = '".$picture."' WHERE ID = $lastid ");
			}
			// lecture du curriculum
			
			$dir = dir('./curriculums/tmp/'); 
			$file="";
			while($nom = $dir->read() ) {
				if(strlen($nom) > 2) 
					$file = $nom;
			}
			if(!empty($file)){
				$ext = getExt($file);
				rename("./curriculums/tmp/".$file,"./curriculums/".$lastid.".".$ext);
				$cv = $lastid.".".$ext;
				$pdo->exec(" UPDATE professeur SET CURRICULUM = '".$cv."' WHERE ID = $lastid ");
			}
			$tab = explode("-",$_SESSION['periode']);
			$periode = $tab[0].$tab[1];
			$pdo->exec(" UPDATE professeur SET MATRICULE = CONCAT(IDPROF,ID) WHERE ID = $lastid");
			//$pdo->exec(" INSERT INTO appartenance_professeur_periode (IDPROF,PERIODE) VALUES($lastid,'".$_SESSION["periode"]."') ");
			$prof = new Professeur($lastid,true);
			$pdo->exec(" INSERT INTO users (LOGIN,PASSWORD,NOM,PRENOM,PROFILE,ACTIF) VALUES('".encode($prof->matricule)."','".encode($prof->matricule)."','".encode($prof->nomprof)."','".encode($prof->prenom)."','".$_POST['profile']."',1) ");
			$res->closeCursor();
			  /*
			  /*
			  */
				/* print "<script>rediriger('../eleves/fiche.php?id=".parse($_POST['matel'])."');</script>";*/
			@header("location:../professeurs/fiche.php?id=".$lastid);
			
		}else
			@header("location:../professeurs/fiche.php?id=-1");
			// -1 : Signifie que le professeur existe dans la base de donnees
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
		
		
		
		
		
		
		
}
?>