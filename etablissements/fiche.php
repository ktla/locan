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
$codepage = "FICHE_ETABLISSEMENT";
/***************************************************************************************************/
	$titre = "Fiche de l'&eacute;tablissement.";
	require_once("../includes/header_inc.php");
	fiche();
	require_once("../includes/footer_inc.php");
/*
	Fonction specifique a la page
*/
function fiche(){
	$res = mysql_query("SELECT * FROM etablissement") or die(mysql_error());
	$row = mysql_fetch_array($res);
?>
<div id="zonetravail"><div class="titre">FICHE DE L'ETABLISSEMENT.</div>
<form>
	<div class="cadre">
    <?php if(is_autorized("PRINT_ETABLISSEMENT")){?>
	<div class = "icon-pdf">
    	<a href = 'imprimer.php?image=true' target = '_blank'><img src = '../images/icon-pdf2.png' title = 'Imprimer avec image'></a>&nbsp;&nbsp;|
    	<a href = 'imprimer.php?image=false' target = '_blank'><img src = '../images/icon-pdf.gif' title = 'Imprimer sans image'></a>
    </div><?php }?>
    <fieldset><legend>Fiche de renseignement sur l'&eacute;tablissement.</legend>
    	<img class = 'fichephoto' src="<?php echo $row['LOGO']; ?>" title="Logo de l'&eacute;tablissement" alt="LOGO ETABLISSEMENT"/>
        <div class="fiche">
        	<div class="titrefiche">IDENTITE</div>
   			<div class="fichecontent">
            	<label>IDENTIFIANT : </label><?php  echo $row['IDENTIFIANT']; ?>.<br />
                <label>APPELLATION | NOM : </label><?php  echo $row['LIBELLE']; ?>.<br />
                <label>ADRESSE : </label><?php  echo $row['ADRESSE']; ?>.<br />
                <?php if(!empty($row['DATECREATION'])){
					$d = new dateFR($row['DATECREATION']);
                	print "<label>DATE DE CREATION : </label>".$d->fullYear(0)."<br />";} ?>
         	</div>
            <div class="titrefiche">INFOLINES</div>
            <div class="fichecontent">
            	<?php if(!empty($row['TEL']))
            		print "<label>TELEPHONE : </label>".$row['TEL']."<br />";
				if(!empty($row['MOBILE']))
            		print "<label>MOBILE : </label>".$row['MOBILE']."<br />";
				if(!empty($row['SITEWEB']))
            		print "<label>SITEWEB : </label>".$row['SITEWEB']."<br />";
				if(!empty($row['EMAIL']))
            		print "<label>E-MAIL : </label>".$row['EMAIL']."<br />";
				?>
            </div>
            <div class="titrefiche">AUTRES INFORMATIONS</div>
            <div class="fichecontent">
            	<label>PRINCIPAL : </label><?php  echo $row['PRINCIPAL']; ?>.<br />
				<?php if(!empty($row['AUTORISATION']))
            		print "<label>NUMERO D'AUTORISATION : </label>".$row['AUTORISATION']."<br />";
				if(!empty($row['CPTEBANCAIRE']))
            		print "<label>COMPTE BANCAIRE : </label>".$row['CPTEBANCAIRE']."<br />";
				if(!empty($row['REGLEMENT']))
            		print "<label>REGLEMENT : </label><a href = '../includes/download.php?url=".$row['REGLEMENT']."' target = '_blank'>T&eacute;l&eacute;charger</a><br />";
				?>
            </div>
       	</div>
    </fieldset>
    </div><div class="navigation">
    <?php if(is_autorized("EDIT_ETABLISSEMENT"))
    		print "<input type=\"button\" value=\"Modifier\" onclick=\"rediriger('modifier.php')\" />";
		?>
    </div>
</form>
</div>
<?php } ?>