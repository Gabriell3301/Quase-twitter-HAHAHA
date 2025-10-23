<?php
session_start();
require 'conexao.php'; // Arquivo que faz a conexão com o banco
require 'filter.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id_user'])) {
    echo "Erro: Usuário não autenticado. Levando para Login...";
    header("refresh:3; url=../HTML/login_page.html"); // Redireciona após 2 segundos
    die();
}

$id_user = $_SESSION['id_user']; // ID do usuário logado
$title = $_POST['title'];
$content = $_POST['content'];

// APLICA O FILTRO ANTES DE SALVAR
$content = censurarTexto($content);  // Censura palavras proibidas
$title = censurarTexto($title);      // (opcional) Se quiser censurar o título também

// Conectar ao SQLite
$db = LigaDB();

// Ajustar timezone para Portugal
date_default_timezone_set('Europe/Lisbon');

// Pegar a data com horário local (automático com horário de verão)
$data_post = date('Y-m-d H:i:s');
// Preparar a inserção
$stmt = $db->prepare("INSERT INTO posts (id_user, title, content, data_post) VALUES (?, ?, ?, ?)");
$stmt->bindValue(1, $id_user, SQLITE3_INTEGER);
$stmt->bindValue(2, $title, SQLITE3_TEXT);
$stmt->bindValue(3, $content, SQLITE3_TEXT);
$stmt->bindValue(4, $data_post, SQLITE3_TEXT); // Aqui entra a data certa

if ($stmt->execute()) {
    header("Location: feed.php");
} else {
    echo "Erro ao publicar post.";
}
?>