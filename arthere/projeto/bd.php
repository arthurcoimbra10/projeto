<?php

$host = 'localhost';
$banco = 'arthere';
$usuario = 'root';
$senha = 'admin';

$dsn = "mysql:host=$host;dbname=$banco;charset=utf8mb4";

try {
	$pdo = new PDO($dsn, $usuario, $senha);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

?>