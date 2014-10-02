<?php
require_once(__DIR__."/database_inc.php");
$db = new Database("SELECT * FROM users");
print "<h2>Select</h2>";
if($db->select()){
	//print_r($db);
	//print($db->length);
	for($i = 0; $i < $db->length; $i++){
		/**
			Recupere une ligne de l'enregistrement
		*/
		$row = $db->getRow($i);
		/* Recupere une cellule de l'enregistrement, remplace le foreach */
		print $row->item('PROFILE')." ";
		print $row->item('LOGIN')."<br/>";

	}
}else{
	/**
		Pour afficher l'erreur $db->getLog('error')
		Pour afficher la requete $db->getLog('query')
		Pour afficher les parametre de passer a l'objet pdo $db->getLog('pdo_param') = tableau, donc utiliser print_r ou $db->getLog('pdo_param');
	*/
	die($db->getLog('error'));
}
print "<h2>Update</h2>";
/** pour un update tu fais simplemt 
	En utilisant le meme object defini precedement ou definir un new oject et passer la requete dedans
	SetQuery  prend en argument la nouvelle requete et le array des argument
*/
$param = array(
	'new' => "adminessai",
	'old' => 'admin2'
);
$db->setQuery("UPDATE users SET LOGIN = :new WHERE LOGIN = :old", $param);
if($db->update()){
	print "Succees update<br/>";
}else{
	die($db->getLog('error'));
}
print "<br/><br/><br/><h2>Insert</h2><br/>";
/**
	Pour Effectur un insert utilise la methode insert
*/
$param = array(
	"login" => "newlogin",
	"pwd" => "newpassword",
	"pr" => "Administration"
);
$db->setQuery("INSERT INTO users(LOGIN, PASSWORD, PROFILE) VALUES(:login, :pwd, :pr)", $param);
if($db->insert()){
	print "Insert avec success<br/>";
	/* Afficher le dernier insert id */
	print "Last insert id est ".$db->lastInsertId();
}else{
	die($db->getLog('error'));
}
print "<br/><br/><br/><h2>Delete</h2><br/>";
$db->setQuery("DELETE FROM users WHERE LOGIN = :login", array("login"=>"all2"));
if($db->delete()){
	print "<br/>delete success<br/>";
}else
	die($db->getLog('error'));
	
	
/** Close la connexion */
//$db->close();
?>