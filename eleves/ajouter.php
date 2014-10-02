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
	$codepage = "ADD_ELEVE";
/********************************************************************************************************/

function test_param_insertion(){
return	(isset($_POST["nomel"]) && isset($_POST["prenom"]) && isset($_POST["datenaiss"]) && isset($_POST["tel"]));
}

	$titre = "Nouvel ajout";
	require_once("../includes/header_inc.php");
	if(test_param_insertion()){
		switch($_POST['step']){
			case 2:step2();break;
			case 3:step3();break;
		}
	}else
		step2();
	require_once("../includes/footer_inc.php");
function step1(){
?>
<script>
	function step1(){
		var obj = document.getElementsByName("frm");
		if(obj.item(0).matel.value == "")
			alert("Entrer un matricule");
		else
			obj.item(0).submit();
	}
</script>
<div id="zonetravail"><div class="titre">NOUVEL AJOUT</div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="step1(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>Identification de l'&eacute;l&egrave;ve.Etape 1</legend><table><tr>
            	<td><label>Matricule de l'&eacute;l&egrave;ve : </label></td>
                <td><input type="text" size="30" maxlength="23" name="matel" value="<?php echo isset($_POST['matel'])? $_POST['matel'] : ""; ?>"/></td>
                </tr></table>
            </fieldset>
        </div>
        <div class="navigation">
        <!-- variable de formualire -->
        	<input type="hidden" value="2" name="step" />
        <!-- Fin de variable -->
        	<input type="button" value="Annuler" /><input type="submit" value="Suivant" /></div>
    </form>
</div>
<?php }
function step2(){
?>
<script>
	function step2(){
		var obj = document.forms['frm'];
		if(obj.nomel.value == "" || obj.prenom.value == "" || obj.datenaiss.value == "" || obj.tel.value == "" || obj.lieunaiss.value == "" || obj.religion.value == "" || obj.sexe.value == "" || obj.ancetbs.value == "")
			alert("-Entrer tous les parametres obligatoires-");
		else
			obj.submit();
	}
	function preciserReligion(){
		var sms = "Veuillez préciser la religion de l'élève\n Dans le champ texte qui s'affichera à droite--";
		preciserOption("cibleRel", "religion", sms, "otherReligion");
	}
	function preciserEts(){
		var sms = "Veuillez préciser l'établissement de provenance de l'élève\n Dans le champ texte qui s'affichera à droite--";
		preciserOption("cibleEts", "ancetbs", sms, "otherEts");
	}
	
	var loadImage = function(lien){
	balise = '<img src="'+lien+'" width="210px" height="132px" />';
	document.getElementById("chargerphoto").innerHTML = balise; 
	document.getElementById("erreurImage").innerHTML = "";
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

<div id="zonetravail"><div class="titre">AJOUT D'UN ELEVE.</div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="step2(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend style="position:relative;margin-left:38%;font-weight:bold;text-transform:capitalize;">Renseignement sur l'&eacute;l&egrave;ve.</legend>
            <table>
            <tr>
            	<td class="lib"><label>Nom : <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" value="<?php echo isset($_POST['nomel']) ?decode($_POST['nomel']):""; ?>" name="nomel" maxlength="250"/></td>
                <td  class="lib"><label>Date de Naiss. : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($_POST['datenaiss']) ? $_POST['datenaiss']:""; ?>" type="text" name="datenaiss" id="datenaiss"/></td>
            </tr><tr>
            	<td class="lib"><label>Pr&eacute;nom : <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" value="<?php echo isset($_POST['prenom']) ? decode($_POST['prenom']):""; ?>" name="prenom" maxlength="250"/></td>
                <td class="lib"><label>T&eacute;l&eacute;phone : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($_POST['tel'])? $_POST['tel']:""; ?>" type="text" name="tel" maxlength="15"/></td>
            </tr><tr>
            	<td class="lib"><label>Lieu de Naiss. <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" name="lieunaiss" maxlength="50" value="<?php echo isset($_POST['lieunaiss']) ? decode($_POST['lieunaiss']) :""; ?>"/></td>
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
            	<td class="lib"><label>Adresse Eleve </label></td>
                <td><input type="text" name="adresse" value="<?php echo isset($_POST['adresse']) ? decode($_POST['adresse']) :""; ?>" maxlength="100"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse Tuteur.</label></td>
                <td><input type="text" name="addresstuteur" maxlength="50" value="<?php echo isset($_POST['addresstuteur']) ? decode($_POST['addresstuteur']) :""; ?>"/></td>
            </tr><tr>
            	<td class="lib"><label>Nom Tuteur : </label></td>
                <td><input type="text" name="parent" maxlength="150" value="<?php echo isset($_POST['parent']) ? decode($_POST['parent']) : ""; ?>"/></td>
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
                <td class="lib"><label>Anc. Etabl. : <span class = 'asterisque'>*</span></label></td>
                <td><?php $q = "SELECT * FROM ancien_etablissement ORDER BY LIBELLE";
						 $combo = new Combo($q, "ancetbs", 0, 1, isset($_POST['ancetbs']) ? true : false);
						 	$combo->first = "-Ancien &eacute;tablissement-";
							$combo->other = true;
							$combo->selectedid = isset($_POST['ancetbs'])? $_POST['ancetbs'] : "";
							$combo->onchange = "preciserEts();";
							$combo->view('210px');
						  ?>
                 </td>
                 <td colspan="2"><div id="cibleEts"></div></td>
           </tr><tr>
           		<td class="lib"><label>Sexe : <span class = 'asterisque'>*</span></label></td>
                <td><select name = 'sexe' style="width:210px;">
                	<option value="Masculin">Masculin</option>
                	<option value="Feminin">F&eacute;minin</option>
                    </select>
                 </td>
                <td></td>
                <td style="text-align:center; font-size:10px; color:red" id="erreurImage"></td>	
           </tr><tr>
           		<td class="lib"><label>Nom de la M&egrave;re </label></td>
                <td><input type="text" name="mere" maxlength="150" value="<?php echo isset($_POST['mere']) ? decode($_POST['mere']) : ""; ?>"/></td>
				<td class="lib"><label>Nom du P&egrave;re </label></td>
                <td><input type="text" name="pere" maxlength="150" value="<?php echo isset($_POST['pere']) ? decode($_POST['pere']) : ""; ?>"/></td>
       		 </tr><tr>
             	<td class="lib"><label>Adresse de la M&egrave;re </label></td>
                <td><input type="text" name="addressmere" maxlength="150" value="<?php echo isset($_POST['addressmere']) ? decode($_POST['addressmere']) : ""; ?>"/></td>
                <td class="lib"><label>Adresse du P&egrave;re </label></td>
                <td><input type="text" name="addresspere" maxlength="150" value="<?php echo isset($_POST['addresspere']) ? decode($_POST['addresspere']) : ""; ?>"/></td>
           </tr><tr>
           		<td colspan="4" style="text-align:center; font-size:10px; color:red">Les champs marqu&eacute;s par * sont obligatoires</td>
           </tr></table>
            </fieldset>
        </div>
        <div class="navigation">
        	<!-- Variable de formulaire -->
            <input type="hidden" value="3" name="step" />
            <!-- Fin des variables -->
        	<input type="button" value="Annuler" onClick="rediriger('index.php')" /><input type="submit" value="Valider" />
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
function exists_eleve($nom, $prenom, $datenaiss, $lieunaiss, $tel, $rel, $ancetbs, $sexe,$adresse){
	try{
		$pdo = Database::connect2db();
		//(NOMEL,PRENOM,DATENAISS,LIEUNAISS,NOMPERE,ADDRESSEPERE,NOMMERE,ADDRESSEMERE,ADDRESSETUTEUR,TUTEUR,TEL,ADRESSE,EMAIL,SEXE,RELIGION,ANCETBS,DAT
		$query = "SELECT * FROM eleve WHERE NOMEL='$nom' AND PRENOM='$prenom' AND DATENAISS='$datenaiss' AND LIEUNAISS='$lieunaiss' AND TEL = '$tel' AND RELIGION = $rel AND ANCETBS = $ancetbs AND SEXE = '$sexe' AND ADRESSE = '$adresse' ";
		$res = $pdo->query($query);
		return ($res->rowCount() > 0);
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
				 
}
/*
	function de validation de donnees
*/
function step3(){
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
		//Ancien etablissement
		if(!strcmp($_POST['ancetbs'], "other")){
			$res = $pdo->prepare("INSERT INTO ancien_etablissement(LIBELLE) VALUES(:anc)");
			$res->bindValue("anc", encode($_POST['otherEts']), PDO::PARAM_STR);
			$res->execute();
			$_POST['ancetbs'] = $pdo->lastInsertId();
		}
		
			//parametre de la methode exists_eleve : nom, prenom , datenaiss,lieunaiss, telephone,religion , ancienets et sexe
		if(!exists_eleve( encode($_POST['nomel']),encode($_POST['prenom']), parseDate($_POST['datenaiss']),encode($_POST['lieunaiss']),encode($_POST['tel']), $_POST['religion'],$_POST['ancetbs'],$_POST['sexe'],encode($_POST['adresse']))){
			/*
				Insertion de l'eleve meme
			*/
			
			
			$query = "INSERT INTO           				eleve(NOMEL,PRENOM,DATENAISS,LIEUNAISS,NOMPERE,ADDRESSEPERE,NOMMERE,ADDRESSEMERE,ADDRESSETUTEUR,TUTEUR,TEL,ADRESSE,EMAIL,SEXE,RELIGION,ANCETBS,DATE) 
	VALUES(:nomel, :prenom, :datenaiss,:lieunaiss,:pere,:addresspere,:mere,:addressmere,:addresstuteur,:tuteur,:tel, :addr, :email, :sexe,:rel, :ancetbs,:date)";
			$res = $pdo->prepare($query);
			
			$param = array(
				"nomel" => encode($_POST['nomel']),
				"prenom" => encode($_POST['prenom']),
				"datenaiss" => parseDate($_POST['datenaiss']),
				"lieunaiss" => encode($_POST['lieunaiss']),
				"pere" => encode($_POST['pere']),
				"addresspere" => encode($_POST['addresspere']),
				"mere" => encode($_POST['mere']),
				"addressmere" => encode($_POST['addressmere']),
				"addresstuteur" => encode($_POST['addresstuteur']),
				"tuteur" => encode($_POST['parent']),
				"tel" => $_POST['tel'],
				"addr" => encode($_POST['adresse']),
				"email" => encode($_POST['email']),
				"sexe" => $_POST['sexe'],
				"rel" => $_POST['religion'],
				"ancetbs" => $_POST['ancetbs'],
				"date" => parseDate(date("Y-m-d"))
			);
			$res->execute($param);
			$lastid = $pdo->lastInsertId();
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
				$pdo->exec(" UPDATE eleve SET IMAGE = '".$picture."' WHERE ID = $lastid ");
			}
			$tab = explode("-",$_SESSION['periode']);
			$periode = $tab[0].$tab[1];
			$pdo->exec(" UPDATE eleve SET MATRICULE = CONCAT(MATEL,ID) WHERE ID = $lastid");
			//$pdo->exec(" INSERT INTO appartenance_eleve_periode (IDELEVE,PERIODE) VALUES($lastid,'".$_SESSION["periode"]."') ");
			$res->closeCursor();
			  /*
			  /*
			  */
				/* print "<script>rediriger('../eleves/fiche.php?id=".parse($_POST['matel'])."');</script>";*/
			@header("location:../eleves/fiche.php?matel=".$lastid);
			
		}else
			@header("location:../eleves/fiche.php?matel=-1");
			
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
}
?>