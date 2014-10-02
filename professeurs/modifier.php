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
$codepage = "EDIT_PROFESSEUR";
/*************************************************************************************************/
	$titre = "Modification des professeurs.";
	require_once("../includes/header_inc.php");
	if(isset($_POST['step']) && $_POST['step'] == 2)
		step2();
	else
		step1();
	require_once("../includes/footer_inc.php");
function step1(){
	$id = $_GET['id'];
	require_once("./professeur_inc.php");
	$prof = new Professeur($id);
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


function clearImage(file){
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
function clearReglement(file){
	$.ajax({
		type:'POST',
		url:'./clearReglement.php',
		data:"file="+file,
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

<div id="zonetravail"><div class="titre">MODIFICATION DU PROFESSEUR : <?php echo $prof->matricule; ?></div>
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="step2(); return false;" enctype="multipart/form-data" method="POST">
    <div class="cadre">
            <fieldset><legend style="position:relative;margin-left:38%;font-weight:bold;text-transform:capitalize;">Renseignement sur le Professeur.</legend>
            <table>
            <tr>
            	<td class="lib"><label>Nom : <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" value="<?php echo isset($prof->nomprof) ?decode($prof->nomprof):""; ?>" name="nomprof" maxlength="250"/></td>
                <td  class="lib"><label>Date de Naiss. : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($prof->datenaiss) ? $prof->datenaiss:""; ?>" type="text" name="datenaiss" id="datenaiss"/></td>
            </tr><tr>
            	<td class="lib"><label>Pr&eacute;nom : </label></td>
                <td><input type="text" value="<?php echo isset($prof->prenom) ? decode($prof->prenom):""; ?>" name="prenom" maxlength="250"/></td>
                <td class="lib"><label>Lieu de Naiss. <span class = 'asterisque'>*</span></label></td>
                <td><input type="text" name="lieunaiss" maxlength="50" value="<?php echo isset($prof->lieunaiss) ? decode($prof->lieunaiss) :""; ?>"/></td>
            </tr><tr>
                <td class="lib"><label>T&eacute;l&eacute;phone : <span class = 'asterisque'>*</span></label></td>
                <td><input value="<?php echo isset($prof->tel)? $prof->tel:""; ?>" type="text" name="tel" maxlength="15"/></td>
                <td>
                	<div class="reglement" style="float:left;">
						<a class="reglement-button" >Image</a>
						<a id="button-jointure">
                        	<input  type="file" id="photos" name="photos" class="attachment-button-file" onchange="selectionImgClick();" />
                        </a>			         
					</div>
                   	<img title="Supprimer image" style="margin-left:5px; margin-top:3px; cursor:pointer;" src="../images/icons/drop.png" onclick="clearImage('<?php echo ( isset($prof->image) && !empty($prof->image))? $prof->image : "";  ?>');"/>
                	<p style="font-size:10px;color:#818181;margin-top:2px;position:absolute;" >Max 1280x800 pixel</p>
                </td>
                <td id="chargerphoto" rowspan="5" bgcolor="#CCC" align="center"><?php echo ( isset($prof->image) && !empty($prof->image))?  "<img src=\"./photos/".$prof->image."\" width=\"210px\" height=\"132px\" />":"<strong style=\"position:relative; top:35%;\">Aucune Image</strong>"; ?></td>
            </tr><tr>
            <td class="lib"><label>E-mail : </label></td>
                <td><input value="<?php echo isset($prof->email)? decode($prof->email):""; ?>" type="text" name="email" maxlength="50"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse Professeur :<span class = 'asterisque'>*</span> </label></td>
                <td><input type="text" name="adresse" value="<?php echo isset($prof->adresse) ? decode($prof->adresse) :""; ?>" maxlength="100"/></td>
            </tr><tr>
            	<td class="lib"><label>Contact en cas d'urgence.</label></td>
                <td><input type="text" name="nomcontact" maxlength="50" value="<?php echo isset($prof->nomcontact) ? decode($prof->nomcontact) :""; ?>"/></td>
            </tr><tr>
            	<td class="lib"><label>Adresse du contact : </label></td>
                <td><input type="text" name="addressecontact" maxlength="150" value="<?php echo isset($prof->addressecontact) ? decode($prof->addressecontact) : ""; ?>"/></td>
            </tr><tr>
                <td class="lib"><label>Religion : <span class = 'asterisque'>*</span> </label></td>
                <td><?php $q = "SELECT * FROM religion ORDER BY LIBELLE";
							$combo = new Combo($q, "religion", 0, 1, isset($prof->religion) ? true: false);
							$combo->first = "-Choisir une religion-";
							$combo->other = true;
							$combo->selectedid = isset($prof->religion)? $prof->religion : "";
							$combo->onchange = "preciserReligion();";
							$combo->view('210px');
				?></td>
                 <td colspan="2"><div id="cibleRel"></div></td>
           </tr><tr>
           </tr><tr>
           		<td class="lib"><label>Sexe : <span class = 'asterisque'>*</span></label></td>
                <td><select name = 'sexe' style="width:210px;">
                	<option value="Masculin" <?php if(!strcmp($prof->sexe,"Masculin")) echo 'selected = "selected"'; else echo ""; ?>>Masculin</option>
                	<option value="Feminin"  <?php if( !strcmp($prof->sexe,"Feminin")) echo 'selected = "selected"'; else echo ""; ?>>F&eacute;minin</option>
                    </select>
                 </td>
                <td></td>
                <td style="text-align:center; font-size:10px; color:red" id="erreurImage"></td>	
           </tr> 
            <tr>
           		<td class="lib"><label>Profile : <span class = 'asterisque'>*</span> </label></td>
                <td><?php $q = "SELECT * FROM profile ORDER BY LIBELLE";
							$combo = new Combo($q, "profile", 0, 0, isset($prof->profile) ? true: false);
							$combo->first = "-Choisir un Profile-";
							$combo->selectedid = isset($prof->profile)? $prof->profile : "";
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
                    <td id="reglement_print" colspan="3" align="left" style="font-size:11px;font-style:italic;"><?php echo (isset($prof->reglement) && !empty($prof->reglement))? "<p style=\"float:left; color:red;\" >".$prof->reglement."</p><img title=\"Supprimer reglement\" height=\"10px\" width=\"10px\" style=\"margin-left:5px; margin-top:13px; cursor:pointer;\" src=\"../images/icons/cancel.png\" onclick=\"clearReglement('".$prof->reglement."');\"/>":""?></td>
                </tr><tr>
           		<td colspan="4" style="text-align:center; font-size:10px; color:red">Les champs marqu&eacute;s par * sont obligatoires</td>
           </tr></table>
            </fieldset>
            
            
           <!-- Variables de formulaire -->
         	<div>
                <input type="hidden" name="step" value="2"/>
                <input type="hidden" value="<?php echo $prof->id; ?>" name="id" />
                <input type="hidden" value="<?php echo $prof->image; ?>" name="image" />
                <input type="hidden" value="<?php echo $prof->reglement; ?>" name="curriculum" />
              	<input type="hidden" value="<?php echo $prof->matricule; ?>" name="matricule" />
           </div>
    </div>
    <div class="navigation"><input type="button" onClick="rediriger('professeur.php');" value = 'Annuler'/>
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
function exists_prof($nomprof, $prenom, $datenaiss, $lieunaiss, $tel, $rel, $adresse, $sexe ,$id){
	try{
		$pdo = Database::connect2db();
		//(NOMEL,PRENOM,DATENAISS,LIEUNAISS,NOMPERE,ADDRESSEPERE,NOMMERE,ADDRESSEMERE,ADDRESSETUTEUR,TUTEUR,TEL,ADRESSE,EMAIL,SEXE,RELIGION,ANCETBS,DAT
		$query = "SELECT * FROM professeur WHERE NOMPROF='$nomprof' AND PRENOM='$prenom' AND DATENAISS='$datenaiss' AND LIEUNAISS='$lieunaiss' AND TEL = '$tel' AND RELIGION = $rel AND ADRESSE = '$adresse' AND SEXE = '$sexe' AND ID != $id";
		$res = $pdo->query($query);
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
		
			//parametre de la methode exists_eleve : nom, prenom , datenaiss,lieunaiss, telephone,religion , ancienets et sexe
		if(!exists_prof( encode($_POST['nomprof']),encode($_POST['prenom']), parseDate($_POST['datenaiss']),encode($_POST['lieunaiss']),$_POST['tel'], $_POST['religion'],$_POST['adresse'],$_POST['sexe'],$id)){
			
			// lecture de la photo
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
				$pdo->exec(" UPDATE professeur SET PHOTO = '".$picture."' WHERE ID = $id ");
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
				if(isset($_POST["curriculum"]) && file_exists("./curriculums/".$_POST["curriculum"]))
					unlink("./curriculums/".$_POST["curriculum"]);
				rename("./curriculums/tmp/".$file,"./curriculums/".$id.".".$ext);
				$cv = $id.".".$ext;
				$pdo->exec(" UPDATE professeur SET CURRICULUM = '".$cv."' WHERE ID = $id ");
			}
			
			/*
				Insertion de l'eleve meme
			*/
			
			
			$query = "UPDATE professeur 
					  SET NOMPROF = '".encode($_POST['nomprof'])."',
						  PRENOM = '".encode($_POST['prenom'])."',
				          DATENAISS = '".parseDate($_POST['datenaiss'])."',
				          LIEUNAISS = '".encode($_POST['lieunaiss'])."',
				          ADRESSE = '".encode($_POST['adresse'])."',
				          NOMCONTACT = '".encode($_POST['nomcontact'])."',
				          ADDRESSECONTACT = '".encode($_POST['addressecontact'])."',
				          TEL = '".$_POST['tel']."',
				          EMAIL = '".encode($_POST['email'])."',
				          SEXE = '".$_POST['sexe']."',
				          RELIGION = ".$_POST['religion'].",
				          DATE = '".parseDate(date("Y-m-d"))."'
						  WHERE ID =".$_POST['id'];
						  print encode($_POST['profile']);
			$pdo->exec($query);
			$pdo->exec("UPDATE users SET NOM = '".encode($_POST['nomprof'])."',PRENOM = '".encode($_POST['prenom'])."',PROFILE = '".encode($_POST['profile'])."' WHERE LOGIN = '".$_POST['matricule']."'");
			
			//$pdo->exec(" INSERT INTO users (LOGIN,PASSWORD,NOM,PRENOM,PROFILE,ACTIF) VALUES(,,'".encode($_POST['nomprof'])."','".encode($_POST['prenom'])."','Professeur',1) ");
			  /*
			  /*
			  */
				/* print "<script>rediriger('../eleves/fiche.php?id=".parse($_POST['matel'])."');</script>";*/
				
				
			@header("location:../professeurs/fiche.php?id=".$id);
			
		}else
			@header("location:../professeurs/fiche.php?id=-1");
			
	}catch(PDOException $e){
		die($e->getMessage()." ".$e->getLine()." ".__LINE__." ".__FILE__);
	}
}
?>