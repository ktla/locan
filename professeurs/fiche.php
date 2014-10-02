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
$codepage = "FICHE_PROFESSEUR";
/********************************************************************************************************/
/*
*/
$titre = "Fiche du Professeur";
require_once("../includes/header_inc.php");
if(isset($_GET['id'])){
	fiche($_GET['id']);
}
require_once("../includes/footer_inc.php");	
function fiche($id){
$prof = new Professeur($id);	
?>
<div id="zonetravail"><div class="titre">FICHE DU PROFESSEUR.</div>
<form>
	<div class="cadre">
	<div class = "icon-pdf">
    	<a href = 'imprimerfiche.php?image=true&id=<?php echo $id; ?>' target = '_blank'><img src = '../images/icon-pdf2.png' title = 'Imprimer avec image'></a>&nbsp;&nbsp;|
    	<a href = 'imprimerfiche.php?image=false&id=<?php echo $id; ?>' target = '_blank'><img src = '../images/icon-pdf.gif' title = 'Imprimer sans image'></a>
    </div>
    <fieldset><legend>Fiche de renseignement du professeur.</legend>
    <?php
		if(!empty($prof->image) && file_exists("./photos/".$prof->image))
			print "<img class = 'fichephoto' src=\"./photos/".$prof->image."\" title=\"Photo de ".$prof->matricule."\" alt =\"Photo de ".$prof->matricule."\"/>";
		else
			print "<img class = 'fichephoto' style=\"font-weight:bold;\" alt=\"PAS DE PHOTO\" />";

		?>
        <div class="fiche">
        	<div class="titrefiche">IDENTITE</div>
   			<div class="fichecontent">
            	<label>IDENTIFIANT | MATRICULE : </label><?php  echo $prof->matricule; ?>.<br />
                <label>NOM : </label><?php  print decode($prof->nomprof); ?>.<br />
                <?php if(!empty($prof->prenom))
					print "<label>PRENOM : </label>".(!empty($prof->prenom)? decode($prof->prenom) :"").".<br />";
                 if(!empty($prof->datenaiss)){
					$d = new dateFR($prof->datenaiss);					
                	print "<label>DATE DE NAISSANCE : </label>".$d->fullYear(0).".<br />";}
				 if(!empty($prof->lieunaiss)){					
                	print "<label>LIEU DE NAISSANCE : </label>".decode($prof->lieunaiss).".<br />";}
				?>
                 <label>SEXE : </label><?php  print !empty($prof->sexe)? $prof->sexe:"" ?>.<br />
         	</div>
            <div class="titrefiche">INFOLINES</div>
            <div class="fichecontent">
            	<?php if(!empty($prof->adresse))
					print "<label>ADRESSE : </label>". decode($prof->adresse).".<br />";
				if(!empty($prof->tel))
            		print "<label>TELEPHONE : </label>". decode($prof->tel)."<br />";
				if(!empty($prof->email))
            		print "<label>E-MAIL : </label>". decode($prof->email)."<br />";
				if(!empty($prof->sexe))
            		print "<label>SEXE : </label>". decode($prof->sexe)."<br />";
				?>
            </div>
            <div class="titrefiche">AUTRES INFORMATIONS</div>
            <div class="fichecontent">
				<?php if(!empty($prof->datedebut) && strcmp("0000-00-00", $prof->datedebut)){
					$d = new dateFR($prof->datedebut);	
            		print "<label>ANCIENNETE : </label>".$d->fullYear().".<br />";
				}
				if(!empty($prof->nomcontact))
            		print "<label>PERSONNE A CONTACTER D'URGENCE : </label>".decode($prof->nomcontact)."<br />";
				if(!empty($prof->addressecontact))
            		print "<label>ADRESSE DE LA PERSONNE A CONTACTER D'URGENCE : </label>".decode($prof->addressecontact)."<br />";
				if(!empty($prof->religion))
            		print "<label>RELIGION : </label>".$prof->libellereligion."<br />";
				if(!empty($prof->reglement))
            		print "<label>CURRICULUM VITAE: </label><a href = '../includes/download.php?url=../professeurs/curriculums/".$prof->reglement."' target = '_blank'>T&eacute;l&eacute;charger CV</a><br />";
				if($prof->actif == 1)
            		print "<label>ETAT : </label>Actif<br />";
				else
					print "<label>ETAT : </label>Bloqu&eacute;<br />";
				?>
            </div>
       	</div>
    </fieldset>
    </div>
    <div class="navigation">
    	<input type="button" value="Annuler" onclick="rediriger('professeur.php?id=<?php echo $id; ?>')" />
        <input type="button" value="Modifier" onclick="rediriger('modifier.php?id=<?php echo $id; ?>')" />
    </div>
</form>
</div>
<?php }
?>