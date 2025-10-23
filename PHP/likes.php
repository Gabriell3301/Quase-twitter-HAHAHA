<?php
session_start();
include("conexao.php");
$db = LigaDB();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_user'])) {
    die("Erro: Usuário não autenticado.");
}

$user_id = $_SESSION['id_user'];  // ID do usuário logado
$post_id = $_POST['post_id'];     // ID do post que o usuário quer curtir/descurtir

// Consulta para verificar se já existe um like
$query = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
$stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();  // Execute retorna um SQLite3Result

$like = $result->fetchArray(SQLITE3_ASSOC);  // Agora chama fetchArray() no objeto result

if ($like) {
    // Se o usuário já curtiu, ele vai descurtir (deletar o like)
    $delete_query = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $stmt = $db->prepare($delete_query);
    $stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
    $stmt->execute();
} else {
    // Caso contrário, o usuário vai curtir (inserir o like)
    $insert_query = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
    $stmt = $db->prepare($insert_query);
    $stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
    $stmt->execute();
}

// Exibe o número de likes após a atualização
$query = "SELECT COUNT(*) as likes_count FROM likes WHERE post_id = :post_id";
$stmt = $db->prepare($query);
$stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
$result = $stmt->execute();  // Guarda o resultado da execução
$likes_count = $result->fetchArray(SQLITE3_ASSOC)['likes_count'];  // Chama fetchArray() no objeto result

echo $likes_count; // Retorna o número de likes
?>