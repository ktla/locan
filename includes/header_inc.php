<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!-- meta http-equiv="Content-Type" content="text/html; charset=utf-8" / -->
<link rel="icon" href="../images/favicon.ico" type="image/x-icon" />
<title>Gestion des activit&eacute;s acad&eacute;miques</title>
<link rel="stylesheet" media="screen" type="text/css" href="../css/connexion.css" />
<link rel="stylesheet" media="screen" type="text/css" href="../css/design.css"/>
<link rel="stylesheet" media="screen" type="text/css" href="../css/variation.css"/>
<link rel="stylesheet" href="../css/grid.css" type="text/css" media="screen" />
<link rel="stylesheet" media="screen" type="text/css" href="../css/jquery-ui-1.10.2.custom.min.css"/>

<link href="../css/juizDropDownMenu.css" rel="stylesheet" type="text/css" />


	<script src="../js/jquery-1.7.1.js"></script>
    <script src="../js/jquery-ui-1.10.2.custom.min.js"></script>
<!--
	 JAVASCRIPT PERSONNEL 
-->
<script language="javascript" src="../js/scriptjquery.js"></script>
<script language="javascript" src="../js/script.js"></script>
<script language="javascript" src="../js/scriptajax.js"></script>
<!-- 
	INCLUSION DE JQUERY POUR LE PLUGIN DU MENU 
-->
<script type="text/javascript" src="../js/juizDropDownMenu-2.0.0.min.js"></script>
<script type="text/javascript">
$(function(){
	$("#menu").juizDropDownMenu({
		'showEffect' : 'slide',
		'hideEffect' : 'slide'
	});
});

</script>
<script src="../SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div style="padding:0; width:100%; min-width:1000px;"><img height="1" width="100%" src="../images/rainbow.png" /></div>
<?php
function display_menu($profile){
	/*
	*/
	$query = "SELECT m.*, h.LIBELLE AS ENTETE 
	FROM menu m LEFT JOIN header_menu h ON (m.HEADER = h.IDHEADER) 
	WHERE m.IDMENU IN (SELECT d.IDMENU FROM droit d WHERE d.IDDROIT IN (SELECT l.IDDROIT FROM listedroit l WHERE l.PROFILE = '".parse($profile)."')) 
	AND m.ACTIF = 1 
	ORDER BY m.HEADER";
	$res = mysql_query($query) or die(mysql_error());
	$header = array();
	print "<ul id = 'menu'>";
	$i = 0;
	while($row = mysql_fetch_array($res)){
		if(!in_array($row['HEADER'], $header)){
			if($i != 0)
				print "</ul></li>";
			print "<li><a href = \"#\">".$row['ENTETE']."</a><ul>";
			$header[] = $row['HEADER'];
		}
		$i += 1;
		/* Verifier s'il ya des sous menu ou non */
		if($row['SOUSMENU'] == 1){
			print "<li><a href=\"#\">".$row['LIBELLE']."</a>";
			$db2 = new Database("SELECT * FROM sous_menu WHERE IDMENU = :idmenu",0, array("idmenu"=>$row['IDMENU']));
			if($db2->query()){
				print "<ul>";
				while($row = $db2->fetch_assoc()){
					print "<li><a href=\"".$row['HREF']."\" onclick=\"".$row['ONCLICK']."\">".$row['LIBELLE']."</a></li>";
				}
				print "</ul>";
			}else
				die($db2->getLog('error'));
			unset($db2);
			print "</li>";
		/* Il n a pas de sous menu */
		}else
			print "<li><a href=\"".$row['HREF']."\" onclick=\"".$row['ONCLICK']."\">".$row['LIBELLE']."</a></li>";
	}
	print "</ul>";
} ?>

	<div id="container">
  		<div id="header">
        	<?php
            if(file_exists("../configurations/config.xml")){
				$res = mysql_query("SELECT LOGO, IDENTIFIANT, HAUTEURLOGO, LARGEURLOGO FROM etablissement") or die(mysql_error());
				$row = mysql_fetch_array($res);
				print "<img src=\"".$row['LOGO']."\" height=\"".$row['HAUTEURLOGO']."\" width=\"".$row['LARGEURLOGO']."'\" title=\"LOGO ETABLISSEMENT 
				".$row['IDENTIFIANT']."\" alt=\"LOGO ETABLISSEMENT ".$row['IDENTIFIANT']."\"/>";
			}else
				print "<img src=\"../images/logo_default.png\" height=\"100\" width=\"250\" title=\"LOGO DU LOGICIEL PAR DEFAUT\" alt=\"LOGO DU SITE\"/>";
			?>
       		<!-- div style="background-image:url(../images/barre.png); background-repeat:repeat-x; height:20px;"></div -->
            <span><img src="../images/titre.png" width="50%"/></span>
        </div>
        <div style="padding:0; width:100%; min-width:1000px;"><img height="2" width="100%" src="../images/rainbow.png" /></div>
        <div style="padding:0"><?php display_menu($_SESSION['profile']); ?></div>
        <div id="submenu" style="width:100%; text-align:right; border-top-width:1px; padding-bottom:10px;">
        	<?php
				if(isset($_SESSION['user'])){
        		 	echo '<strong>Bienvenue &raquo;&nbsp;&nbsp;</strong><a href="../utilisateurs/profile.php" ><span style="text-transform:uppercase;">'.$_SESSION['nom'].'</span>&nbsp;<span style="text-transform:capitalize;">'.$_SESSION['prenom'].'</span></a> &nbsp;|&nbsp;';
					echo '<a href="../accueil/index.php" title="accueil">Accueil</a>&nbsp;|&nbsp;<a href="../utilisateurs/connexion.php">D&eacute;connexion</a>';
					echo "&nbsp;&nbsp;&nbsp;&nbsp;<strong>P&eacute;riode &raquo;&nbsp;&nbsp;</strong><a href='javascript:return false;'>".$_SESSION['periode']."</a>&nbsp;&nbsp;";
			}
            else  echo 'Connexion au syst&#232;me logesta';
			?>
        </div>
        <div id="content">
        	<table><tr>
            	<td style="vertical-align:top; width:5%; height:400px; border:1px solid #CCC;">
                	<!-- div><h3 style="text-align:center;">Emploi du Temps</h3 -->
                    	<!-- div style="font-size:10px;" id="timetable"></div -->
                    	<!-- div style="font-size:10px;height:80px"></div -->
                    <!-- /div -->
                	<div><h3 style="text-align:center;z-index:-1">Calendrier</h3>
                    	<div style="font-size:10px;" id = 'calendrierdujour'></div>
                    </div>
                    <!-- div><h3 style="text-align:center;">Discussion Intantan&eacute;e</h3 -->
                    	<!-- div style="font-size:10px;" id="chat"></div -->
                    	<!-- div style="font-size:10px;height:80px;"></div>
                    </div -->
				</td>
                <td style="vertical-align:top;width:95%" id="contenu">
                	<!-- div class="title"><?php //echo $titre; ?></div -->
            			<?php //Verifie si on est autorise a acceder a cette page
							if(!is_autorized($codepage)){
								print "<p class = 'infos'>Vous n'&ecirc;tes pas autoris&eacute;s &agrave; acc&eacute;der &agrave; cette page!!!!</p>";
								die();
							}
							/*  Gerer les infos transmises par une autre page */
							if(isset($_SESSION['infos'])){
								print "<p class = 'infos'>".$_SESSION['infos']."</p>";
								unset($_SESSION['infos']);
							}
						 ?>