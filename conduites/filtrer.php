<?php
	require_once("../includes/commun_inc.php");
	if(!isset($_SESSION['user']))
		header("location:../utilisateurs/connexion.php");
	$titre = "Filtrage des &eacute;l&egrave;ves";
	require_once("../includes/header_inc.php");
	if(isset($_POST['filtrer']))
		filtrer();
	else
		proposer();
	require_once("../includes/footer_inc.php");
function filtrer(){?>

<?php }
function proposer(){?>
<div id="zonetravail"><div class="titre">OPTIONS DE FILTRAGE SUR LES ELEVES.</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" enctype="multipart/form-data" onsubmit="proposer(); return false;">
	<div class="cadre">
    	<fieldset><legend>Renseignements sur le filtrage.</legend>
        	<table cellspacing="10"><caption>Filtrer par : <hr/></caption>
             <tr><td>Date Naiss.</td><td><input type="text" name="datenaiss" id="datenaiss"/></td>
            	<td>Date d'ajout.</td><td><input type="text" name="dateajout" id="dateajout"/></td>
            </tr>
            <tr><td>Classes : </td>
            	<td><?php $combo = new combo("SELECT * FROM classe", "classe", 0, 1, false);
					$combo->first = "-Choisir la classe-";
					$combo->view();?></td>
                <td>P&eacute;riode : </td>
               <td><?php $combo = new combo("SELECT * FROM periode ORDER BY PERIODE", "periode", 0, 0, false);
			   $combo->first = "-Choisir une periode-";
			   $combo->view(); ?></td>
            </tr>
            <tr><td>Compte :</td><td><input type="radio" value="1" name="compte">D&eacute;biteur.&nbsp;<input type="radio" name="compte" value="2">Cr&eacute;diteur.</td>
            	<td>Inscrit : </td><td><input type="radio" name="inscrit" value="1"/>NON.<input type="radio" value="2" name="inscrit"/>OUI.</td>
           	</tr>
           	<tr>
            	<td>Alpha.</td>
                <td><select name="alpha"><option value="">-Groupe alphabetique.</option><?php 
				for($i = 65; $i < 91; $i++)
					print "<option value=\"".chr($i)."\">".chr($i)."</option>";?>
               </select></td>
            </tr>
            </table>
        </fieldset>
    </div>
    <div class="navigation"><input type="button" value="Annuler" onclick="document.location = '../accueil/index.php'"/>
    	<input type="submit" value="Valider"/>
    </div>
</form>
</div>
<?php }
?>