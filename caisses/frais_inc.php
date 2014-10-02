<?php
/*******************************
*
*	Classe frais
*
********************************/
class Frais{
	public $montant;
	public $code;
	public $id;
	public $type;
	public $libelle;
	public $datedebut;
	public $datefin;
	private $pdo;
	private $result;
	function __construct($id){
		try{
			$this->id = $id;
			$query = "SELECT * FROM classe_frais WHERE ID = :id";
			$this->pdo = Database::connect2db();
			$this->result = $this->pdo->prepare($query);
			$this->result->bindValue('id', $this->id, PDO::PARAM_STR);
			$this->result->execute();
			if(!$this->result->rowCount()){
				die('Aucun frais contenant cet ID '.$this->id);
				return;
			}
			$row = $this->result->fetch(PDO::FETCH_ASSOC);
			$this->montant = $row['MONTANT'];
			$this->code = $row['CODE'];
			$this->libelle = $row['LIBELLE'];
			$this->type = $row['TYPE'];
			$this->datedebut = $row['DATEDEBUT'];
			$this->datefin = $row['DATEFIN'];
			$this->result->closeCursor();
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
		}
	}
	/**
		Function permettant d'effectuer une interperiode
		Il s'agit de copier les parametre d'une periode
		a la periode actuelle
	*/
	public static function interperiode($ancienneperiode){
		try{
			$newperiode = $_SESSION['periode'];
			$db = Database::connect2db();
			$res = $db->prepare("SELECT * FROM classe_frais WHERE PERIODE = :periode");
			$res->bindValue('periode', $ancienneperiode, PDO::PARAM_STR);
			$res->execute();
			$addf = $db->prepare("INSERT INTO classe_frais (CODE, IDCLASSE, LIBELLE, DATEDEBUT, DATEFIN, MONTANT, TYPE, PERIODE) 
			VALUES(:code, :idclasse, :libelle, :datedebut, :datefin, :montant, :type, :periode)");
			
			$addr = $db->prepare("INSERT INTO classe_reduction (CODE, IDFRAIS, LIBELLE, MONTANT, TYPE) 
			VALUES(:code, :idfrais, :libelle, :montant, :type)");
			$addf->bindValue('periode', $newperiode, PDO::PARAM_STR);
			while($row = $res->fetch(PDO::FETCH_ASSOC)){
				$addf->bindValue('code', $row['CODE'], PDO::PARAM_STR);
				$addf->bindValue('idclasse', $row['IDCLASSE'], PDO::PARAM_STR);
				$addf->bindValue('libelle', $row['LIBELLE'], PDO::PARAM_STR);
				$addf->bindValue('datedebut', $row['DATEDEBUT'] , PDO::PARAM_STR);
				$addf->bindValue('datefin', $row['DATEFIN'] , PDO::PARAM_STR);
				$addf->bindValue('montant', $row['MONTANT'] , PDO::PARAM_STR);
				$addf->bindValue('type', $row['TYPE'] , PDO::PARAM_STR);
				$addf->execute();
				$newid = $db->lastInsertId();
				$previd = $row['ID'];
				/* Selectionner la reduction appliquer a ce frais */
				$adds = $db->query("SELECT * FROM classe_reduction WHERE IDFRAIS = '".$previd."'");
				if($adds->rowCount()){
					$addsrow = $adds->fetch(PDO::FETCH_ASSOC);
					$addr->bindValue('code', $addsrow['CODE'], PDO::PARAM_STR);
					$addr->bindValue('idfrais', $newid, PDO::PARAM_STR);
					$addr->bindValue('libelle', $addsrow['LIBELLE'], PDO::PARAM_STR);
					$addr->bindValue('montant', $addsrow['MONTANT'], PDO::PARAM_STR);
					$addr->bindValue('type', $addsrow['TYPE'], PDO::PARAM_STR);
					$addr->execute();
				}
				$adds->closeCursor();
			}
			$res->closeCursor();
			$addr->closeCursor();
			return true;
		}catch(PDOException $e){
			var_dump($e->getTrace());
			die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
			return false;
		}
	}
}
?>