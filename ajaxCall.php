<?php

require_once 'config.php';
require_once 'db.class.php';

$db = new DB(DB_HOST,DB_LOGIN,DB_PASS,DB_NAME); 

// Si des données POST ont été envoyées
if (!empty($_POST))
{
	// Réception et sécurisation des données POST 
	$texte = htmlspecialchars(strip_tags(trim($_POST['texte'])),ENT_QUOTES);
	$username = htmlspecialchars(strip_tags(trim($_POST['username'])),ENT_QUOTES);
	$user_id = (int) $_POST['user_id'];

	// Sécurité: si le login et l'id de l'utilisateur ne concordent pas, le message sera impossible à envoyer
	$lulu = $db->db->prepare('SELECT * FROM util WHERE login = :username AND idutil = :idutil');
	$lulu->execute(array(':username' => $username, 'idutil' => $user_id));

	// Si le nombre de colonne vaut 1, c'est qu'il ya concordance entre le login et l'id de l'utilisateur, le message sera envoyé
	if ($lulu->rowCount())
	{
		// Préparation de la requête d'envoie
		$lulu = $db->db->prepare('INSERT INTO message(texte, util_idutil) VALUES(:texte, :util_idutil)');
		$req = $lulu->execute(array(':texte' => $texte, 'util_idutil' => $user_id));

		// Si la requête a bien été stockée on renvoit 'ok'
		if($req)
			echo 'ok';
	}
	
}

// Si des données GET ont été envoyées
if (!empty($_GET) && isset($_GET['getLastsMessage']))
{
	// Réception des X derniers messages dans la base de donnée
	$lulu = $db->db->query('SELECT m.texte, m.ladate, m.util_idutil, u.idutil, u.login FROM message m
							INNER JOIN util u
							ON u.idutil = m.util_idutil
							ORDER BY id DESC LIMIT 0, 50');
	// Affichage des X derniers messages en JSON
	echo json_encode($lulu->fetchAll(PDO::FETCH_ASSOC), JSON_FORCE_OBJECT);
}


