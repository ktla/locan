<?php
/******************************************
*
*	Classe matiere
*
*******************************************/
class Matiere{
	private $codemat;
	public $libelle;
	private $pdo;
	private $result;
	function __construct($codemat){
		try{
			$this->codemat = $codemat;
			$this->pdo = Database::connect2db();
			$this->result = $this->pdo->prepare("SELECT * FROM matiere WHERE CODEMAT = :codemat");
			$this->result->bindValue('codemat', $codemat, PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				die('Aucune matiere ayant id '.$codemat);
				return;
			}
			$row = $this->result->fetch(PDO::FETCH_ASSOC);
			$this->libelle = $row['LIBELLE'];
			$this->result->closeCursor();
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
	}
}
?>