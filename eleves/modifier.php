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
	$codepage = "EDIT_ELEVE";
/********************************************************************************************************/
	require_once("../includes/header_inc.php");
	if(isset($_POST['id']))
		valider();
	elseif(isset($_GET['id']))
		modifier();

	require_once("../includes/footer_inc.php");
function modifier(){
	$eleve = new Eleve($_GET['id']);
	// vidage du repertoire tmp : emplacement des photos
	$dir = dir('./photos/tmp/'); 
	while($nom = $dir->read() ) {
		if(strlen($nom) > 2){ 
			unlink("./photos/tmp/".$nom);
		}
	}
	
?>
<script>
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

function clearImage(file){
	alert(file);
	balise = '<strong style="position:relative; top:35%;">Aucune Image</strong>';
	$.ajax({
		type:'POST',
		url:'./clearImage.php',
		data:"file="+file,
		datatype:'text',
		error: function() {alert('Erreur serveur');}
	});
	document.getElementById("chargerphoto").innerHTML = balise; 
	document.getElementById("erreurImage").innerHTML = "";
}


	function modifier(){
		var obj = document.forms['frm'];
		if(obj.nomel.value == "" || obj.prenom.value == "" || obj.datenaiss.value == "" || obj.tel.value == "")
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
	
</script>

<!-- 
	 STYLE LOCAL A LA PAGE 
-->
<style type="text/css" >
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
</style><!-- 
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
<div id="zonetravail"><div class="titre">MODIFICATION ELEVE. Matric : <?php  echo $eleve->matricule;?></div>
    <form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" onSubmit="modifier(); return false;" method="POST">
    	<div class="cadre">
        	<fieldset><legend>Renseignement sur l'&eacute;l&egrave;ve.</legend>
            <table>
            <tr>
            	<td class="lib"><label>Nom : <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" value="<?php echo isset($eleve->nom) ? $eleve->nom:""; ?>" name="nomel" maxlength="250"/></td>
                <td  class="lib"><label>Date de Naiss. : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($eleve->naissdate) ?unParseDate($eleve->naissdate):""; ?>" type="text" name="datenaiss" id="datenaiss"/></td>
            </tr><tr>
            	<td class="lib"><label>Pr&eacute;nom : <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" value="<?php echo isset($eleve->prenom) ? $eleve->prenom:""; ?>" name="prenom" maxlength="250"/></td>
                <td class="lib"><label>T&eacute;l&eacute;phone : <span class = 'asterisque'>*</span> <br/><span class = 'astuce' title="numero en chiffre sans espace">Numero en chiffre sans espace </span></label></td>
                <td><input value="<?php echo isset($eleve->tel)? $eleve->tel:""; ?>" type="text" name="tel" maxlength="15"/></td>
            </tr><tr>
            	<td class="lib"><label>Lieu de Naiss. <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" name="lieunaiss" maxlength="50" value="<?php echo isset($eleve->lieunaiss) ? $eleve->lieunaiss :""; ?>"/></td>
                <td>
                	<div class="reglement" style="float:left;">
						<a class="reglement-button" >Image</a>
						<a id="button-jointure">
                        	<input  type="file" id="photos" name="photos" class="attachment-button-file" onchange="selectionImgClick();" />
                        </a>			         
					</div>
                   	<img title="Supprimer image" style="margin-left:5px; margin-top:3px; cursor:pointer;" src="../images/icons/drop.png" onclick="clearImage('<?php echo ( isset($eleve->image) && !empty($eleve->image))? $eleve->image : "";  ?>');"/>
                	<p style="font-size:10px;color:#818181;margin-top:2px;position:absolute;" >Max 1280x800 pixel</p>
                </td>
                <td id="chargerphoto" rowspan="5" bgcolor="#CCC" align="center"><?php echo  ( isset($eleve->image) && !empty($eleve->image)) ?  "<img src=\"./photos/".$eleve->image."\" width=\"210px\" height=\"132px\" />":"<strong style=\"position:relative; top:35%;\">Aucune Image</strong>"; ?></td>
            </tr><tr>
            <td class="lib"><label>E-mail : </label></td>
                <td><input value="<?php echo isset($eleve->email)? $eleve->email:""; ?>" type="text" name="email" maxlength="50"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse Eleve </label></td>
                <td><input type="text" name="adresse" value="<?php echo isset($eleve->adresse) ? $eleve->adresse :""; ?>" maxlength="100"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse Tuteur.</label></td>
                <td><input type="text" name="addresstuteur" maxlength="50" value="<?php echo isset($eleve->addresstuteur) ? $eleve->addresstuteur :""; ?>"/></td>
            </tr><tr>
            	<td class="lib"><label>Nom Tuteur : </label></td>
                <td><input type="text" name="parent" maxlength="150" value="<?php echo isset($eleve->tuteur) ? $eleve->tuteur : ""; ?>"/></td>
            </tr><tr>
                <td class="lib"><label>Religion : <span class = 'asterisque'>*</span> </label></td>
                <td><?php $q = "SELECT * FROM religion ORDER BY LIBELLE";
							$combo = new Combo($q, "religion", 0, 1, isset($eleve->idreligion) ? true: false);
							$combo->first = "-Choisir une religion-";
							$combo->other = true;
							$combo->selectedid = isset($eleve->idreligion)? $eleve->idreligion : "";
							$combo->onchange = "preciserReligion();";
							$combo->view('210px');
				?></td>
                 <td colspan="2"><div id="cibleRel"></div></td>
           </tr><tr>
                <td class="lib"><label>Anc. Etabl. : <span class = 'asterisque'>*</span></label></td>
                <td><?php $q = "SELECT * FROM ancien_etablissement ORDER BY LIBELLE";
						 $combo = new Combo($q, "ancetbs", 0, 1, isset($eleve->idancienEts) ? true : false);
						 	$combo->first = "-Ancien &eacute;tablissement-";
							$combo->other = true;
							$combo->selectedid = isset($eleve->idancienEts)? $eleve->idancienEts : "";
							$combo->onchange = "preciserEts();";
							$combo->view('210px');
						  ?>
                 </td>
                 <td colspan="2"><div id="cibleEts"></div></td>
           </tr><tr>
           		<td class="lib"><label>Sexe : <span class = 'asterisque'>*</span></label></td>
                <td><select name = 'sexe' style="width:210px;">
                	<option value="Masculin" <?php if(!strcmp($eleve->sexe,"Masculin")) echo 'selected = "selected"'; else echo ""; ?>>Masculin</option>
                	<option value="Feminin"  <?php if( !strcmp($eleve->sexe,"Feminin")) echo 'selected = "selected"'; else echo ""; ?>>F&eacute;minin</option>
                    </select>
                 </td>
                <td></td>
                <td style="text-align:center; font-size:10px; color:red" id="erreurImage"></td>	
           </tr><tr>
           		<td class="lib"><label>Nom de la M&egrave;re </label></td>
                <td><input type="text" name="mere" maxlength="150" value="<?php echo isset($eleve->mere) ? $eleve->mere : ""; ?>"/></td>
				<td class="lib"><label>Nom du P&egrave;re </label></td>
                <td><input type="text" name="pere" maxlength="150" value="<?php echo isset($eleve->nompere) ? $eleve->nompere : ""; ?>"/></td>
       		 </tr><tr>
             	<td class="lib"><label>Adresse de la M&egrave;re </label></td>
                <td><input type="text" name="addressmere" maxlength="150" value="<?php echo isset($eleve->addressmere) ? $eleve->addressmere : ""; ?>"/></td>
                <td class="lib"><label>Adresse du P&egrave;re </label></td>
                <td><input type="text" name="addresspere" maxlength="150" value="<?php echo isset($eleve->addresspere) ? $eleve->addresspere : ""; ?>"/></td>
           </tr><tr>
           		<td colspan="4" style="text-align:center; font-size:10px; color:red">Les champs marqu&eacute;s par * sont obligatoires</td>
           </tr></table>
            </fieldset>
        </div>
        <div class="navigation">
        	<!-- Variable de formulaire -->
            <input type="hidden" value="<?php echo $eleve->id; ?>" name="id" />
            <input type="hidden" value="<?php echo $eleve->image; ?>" name="image" />
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
function exists_eleve($nom, $prenom, $datenaiss, $lieunaiss, $tel, $rel, $ancetbs, $sexe,$id,$adresse){
	try{
		$pdo = Database::connect2db();
		//(NOMEL,PRENOM,DATENAISS,LIEUNAISS,NOMPERE,ADDRESSEPERE,NOMMERE,ADDRESSEMERE,ADDRESSETUTEUR,TUTEUR,TEL,ADRESSE,EMAIL,SEXE,RELIGION,ANCETBS,DAT
		$query = "SELECT * FROM eleve WHERE NOMEL='$nom' AND PRENOM='$prenom' AND DATENAISS='$datenaiss' AND LIEUNAISS='$lieunaiss' AND TEL = '$tel' AND RELIGION = $rel AND ANCETBS = $ancetbs AND SEXE = '$sexe' AND ADRESSE = '$adresse' AND ID != $id ";
		$res = $pdo->query($query);
		return ($res->rowCount() > 0);
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
				 
}
function valider(){
	try{
		$id = $_POST["id"];
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
			//parametre de la methode exists_eleve : nom, prenom , datenaiss,lieunaiss, telephone,religion , ancienets ,sexe et id
		if(!exists_eleve( encode($_POST['nomel']),encode($_POST['prenom']), parseDate($_POST['datenaiss']),encode($_POST['lieunaiss']),$_POST['tel'], $_POST['religion'],$_POST['ancetbs'],$_POST['sexe'],$id,encode($_POST['adresse']))){
			$dir = dir('./photos/tmp/'); 
			$file="";
			while($nom = $dir->read() ) {
				if(strlen($nom) > 2) 
					$file = $nom;
			}
			if(!empty($file)){
				$ext = getExt($file);
				if(isset($_POST["image"]) && file_exists("./photos/".$_POST["image"]))
					unlink("./photos/".$_POST["image"]);
				rename("./photos/tmp/".$file,"./photos/".$id.".".$ext);
				$picture = $id.".".$ext;
				$pdo->exec(" UPDATE eleve SET IMAGE = '".$picture."' WHERE ID = $id ");
			}
			/*
				Update de l'eleve meme
			*/
			print $_POST['tel'];
			$query = "UPDATE eleve 
			SET NOMEL = '".encode($_POST['nomel'])."',
				PRENOM = '".encode($_POST['prenom'])."',
				DATENAISS = '".parseDate($_POST['datenaiss'])."',
				LIEUNAISS = '".encode($_POST['lieunaiss'])."',
				NOMPERE = '".encode($_POST['pere'])."',
				ADDRESSEPERE = '".encode($_POST['addresspere'])."',
				NOMMERE = '".encode($_POST['mere'])."',
				ADDRESSEMERE = '".encode($_POST['addressmere'])."',
				ADDRESSETUTEUR = '".encode($_POST['addresstuteur'])."',
				TUTEUR = '".encode($_POST['parent'])."',
				TEL = '".$_POST['tel']."',
				ADRESSE = '".encode($_POST['adresse'])."',
				EMAIL = '".encode($_POST['email'])."',
				SEXE = '".$_POST['sexe']."',
				RELIGION = ".$_POST['religion'].",
				ANCETBS = ".$_POST['ancetbs']." 
			WHERE ID =".$_POST['id'];
			$pdo->exec($query);
			  /*
			  /*
			  */
				/* print "<script>rediriger('../eleves/fiche.php?id=".parse($_POST['matel'])."');</script>";*/
			@header("location:../eleves/fiche.php?matel=".$_POST['id']);
		}else
			@header("location:../eleves/fiche.php?matel=-1");
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__." ".$e->getTraceAsString());
	}
}