<?php
require_once("../includes/commun_inc.php");
if(isset($_GET['id'])){
	$query = "UPDATE professeur SET ACTIF = IF(ACTIF = 1, '0', '1') WHERE IDPROF = \"".parse($_GET['id'])."\"";
	if(mysql_query($query)){
		$q = "UPDATE users SET ACTIF = (SELECT ACTIF FROM professeur WHERE IDPROF = \"".parse($_GET['id'])."\") WHERE LOGIN = \"".parse($_GET['id'])."\"";
		mysql_query($q) or die(mysql_error());
		header("location:professeur.php");
	}else
		die(mysql_error());
}
?>