<?php
session_start();
include("conexao.php");
$db = LigaDB();

$postsPorCarregar = 5;

$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPorCarregar;

$query = "SELECT posts.*, user.nome_user, user.perfil_imagem 
          FROM posts 
          JOIN user ON posts.id_user = user.id 
          WHERE posts.visib = 1
          ORDER BY data_post DESC 
          LIMIT $postsPorCarregar OFFSET $offset";

$resultado = $db->query($query);

while ($post = $resultado->fetchArray(SQLITE3_ASSOC)) {
    include("_post_card.php");
}
?>
<script src="../JS/Likes.js"></script>