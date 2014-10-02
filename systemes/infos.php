<?php	
include_once("../includes/commun_inc.php");
if(!isset($_SESSION['user']))
	header("location:../utilisateurs/connexion.php");
$titre = "Infos. sur le syst&egrave;me | le webmaster";
$codepage = "INFO_CONCEPTEUR";
require_once("../includes/header_inc.php");
infos();
require_once("../includes/footer_inc.php");

function infos(){?>
<div id = 'zonetravail'><div class="titre">INFOS SYSTEMES ET WEBMASTER</div>
    <form>
    	<div class = 'cadre'>
    	<fieldset><legend>Informations syst&egrave;mes.</legend>
    		<table>
            	<tr><td><label>Architecture du Logiciel.</label></td>
                	<td>3-Tiers.</td>
                </tr>
                <tr><td><label>Nom du Logiciel.</label></td>
                	<td>LOGESTA : Logiciel de Gestion des Travaux Acad&eacute;miques.</td>
                </tr>
                <tr><td><label>Nom de la Base de donn&eacute;es.</label></td>
                	<td>locan</td>
                </tr>
                <tr><td><label>Date de cr&eacute;ation.</label></td>
                	<td>Juin 2012</td>
                </tr>
                <tr><td><label>Copyrigth 2012.</label></td>
                	<td> &copy;&nbsp;Universit&eacute; Adventiste Cosendai.</td>
                </tr>
                <tr><td><label>Proc&eacute;dure d'installation : </label></td>
                	<td><a href="../Procedure_installation.txt" title="Procedure d'installtion" target="_blank">Proc&eacute;dure d'installation.</a></td>
                </tr>
                <tr><td><label>T&eacute;l&eacute;charger le manuel d'utilisation: </label></td>
                	<td><a href="../systemes/manuel.pdf" target="_blank">Manuel d'utilisation(PDF).</a>&nbsp;&nbsp;<a target="_blank" href="../systemes/manuel.docx">Manuel d'utilisation(DOC).</a></td>
                </tr>
            </table>
        </fieldset></div>
       	<div style="margin:15px;font-family:Georgia, 'Times New Roman', Times, serif; font-weight:bold;">
    	<fieldset><legend>Informations sur le webmaster.</legend>
    		<table cellspacing="10" class="lbl">
            	<tr><td><label>Nom et Pr&eacute;nom : </label></td>
                	<td>Ainam Jean Paul.</td>
                </tr>
                <tr><td><label>Niveau scolaire</label></td>
                	<td>Master In Computer Science. (2014 Babcock University, Ilishan - Remo; Ogun State - Nig&eacute;ria).
                    <br/>Licence en Maintenance Informatique. <br/>Et Genie Logiciel. (2012 UAC).
                    <br/>CCNA : Cisco Certified Network Associate (Nov. 2012 Lagos, Nig&eacute;ria)
                    </td>
                </tr>
                 <tr><td>Curriculum vitae : </td>
                	<td><a href="../systemes/cv.pdf">Telecharger le cv</a></td>
                </tr>
                <tr><td style="color:red"><label>Contacts Temporaires au Nig&eacute;ria.</label></td>
                	<td><a href="mailto:jpainam@gmail.com">jpainam@gmail.com.</a></td>
    			</tr>
                <tr><td></td>
                	<td>(+234) 0706-390-46-92 (Nig&eacute;ria).</td>
                </tr>
                <tr><td><label>Contacts Permanents au Tchad : </label></td>
                	<td><a href="mailto:jpainam@gmail.com">jpainam@gmail.com.</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="mailto:tapmonwangkel@gmail.com">tapmonwangkel@yahoo.fr.</a></td>
                </tr>
                <tr><td></td>
                	<td>(+235) 66-11-20-03</td>
                </tr>
                <tr><td></td>
                	<td>(+235) 63-76-78-80</td>
                </tr>
                <tr><td><label>R&eacute;alisateur et Concepteur : </label></td>
                	<td>Ainam Jean Paul.</td>
                </tr>
                <tr><td><label>Siteweb</label></td>
                	<td><a target = '_blank' href="http://www.logesta.fr.nf">www.logesta.fr.nf</a>
					&nbsp;&nbsp;|&nbsp;&nbsp;<a target = '_blank' href="http://www.cv-facile.fr.nf">www.cv-facile.fr.nf</a>
					&nbsp;&nbsp;|&nbsp;&nbsp;<a target = '_blank' href="http://www.jpainam.skyrock.com">Mon Blog</a>
					</td>
                </tr>
            </table>
        </fieldset></div>
</form></div>
<?php }
?>