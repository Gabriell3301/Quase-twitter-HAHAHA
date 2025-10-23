<?php
session_start();
include("conexao.php");
$db = LigaDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $postId = $_POST['delete_post_id'];

    $query = "UPDATE posts SET visib = 0 WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $postId, SQLITE3_INTEGER);
    $stmt->execute();
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
