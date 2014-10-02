<?php
require_once("../includes/commun_inc.php");
if(isset($_GET['id'])){
	$query = "UPDATE menu SET ACTIF = IF(ACTIF = 1, '0', '1') WHERE IDMENU = \"".parse($_GET['id'])."\"";
	mysql_query($query) or die(mysql_error());
	header("location:menu.php");
}
?>