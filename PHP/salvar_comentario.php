<?php
session_start();
require("conexao.php");
require 'filter.php';

$db = LigaDB();

// Ajustar timezone para Portugal
date_default_timezone_set('Europe/Lisbon');

// Pegar a data com horário local (automático com horário de verão)
$data_post = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id_user'])) {
        die("Erro: Usuário não autenticado.");
    }

    $id_user = $_SESSION['id_user'];
    $id_post = $_POST['id_post'];
    $comentario = trim($_POST['content']);
    // APLICA O FILTRO ANTES DE SALVAR
    $comentario = censurarTexto($comentario);  // Censura palavras proibidas
    if (empty($comentario)) {
        die("Erro: Comentário vazio.");
    }
    // 1. Salva o comentário
    $stmt = $db->prepare("INSERT INTO coments (id_post, id_user, content, data_coment) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $id_post, SQLITE3_INTEGER);
    $stmt->bindValue(2, $id_user, SQLITE3_INTEGER);
    $stmt->bindValue(3, $comentario, SQLITE3_TEXT);
    $stmt->bindValue(4, $data_post, SQLITE3_TEXT);
    $stmt->execute();

    // 2. Buscar o dono do post
    $post_stmt = $db->prepare("SELECT id_user FROM posts WHERE id = ?");
    $post_stmt->bindValue(1, $id_post, SQLITE3_INTEGER);
    $post_result = $post_stmt->execute();
    $post_info = $post_result->fetchArray(SQLITE3_ASSOC);
    $dono_post = $post_info['id_user'];

    // 3. Criar a notificação se quem comentou não for o dono
    if ($dono_post != $id_user) {
        $mensagem = "Comentaram no seu post.";
        $not_stmt = $db->prepare("INSERT INTO notificacoes (user_id, mensagem, lida, data_criada, id_post) VALUES (?, ?, 0, ?, ?)");
        $not_stmt->bindValue(1, $dono_post, SQLITE3_INTEGER);
        $not_stmt->bindValue(2, $mensagem, SQLITE3_TEXT);
        $not_stmt->bindValue(3, $data_post, SQLITE3_TEXT);
        $not_stmt->bindValue(4, $id_post, SQLITE3_INTEGER);
        $not_stmt->execute();
    }
    //Leva de volta para a página do post
    header("Location: big_post.php?id=".$_POST['id_post']);
    exit();
}
?>