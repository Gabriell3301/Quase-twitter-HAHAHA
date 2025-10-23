<?php
session_start();
include("conexao.php");
$db = LigaDB();
// Número de posts por carregamento
$postsPorCarregar = 5; 

// Verificar a página atual
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPorCarregar;

// Buscar posts do banco de dados com limite e offset
$query = "SELECT posts.*, user.nome_user, user.perfil_imagem FROM posts 
          JOIN user ON posts.id_user = user.id 
          WHERE posts.visib = 1
          ORDER BY data_post DESC 
          LIMIT $postsPorCarregar OFFSET $offset";
$resultado = $db->query($query);

// Exibir os posts para o AJAX
while ($post = $resultado->fetchArray(SQLITE3_ASSOC)) { // Correto: usa fetchArray() para os posts
    $username = htmlspecialchars($post['nome_user']);
    $firstLetter = strtoupper(substr($username, 0, 1));
    $post_id = $post['id'];

    $imagemPerfil = $post['perfil_imagem'] ?? null; // Garante que não dá erro se não tiver o campo

    // Consulta o número de likes do post
    $like_query = "SELECT COUNT(*) as likes FROM likes WHERE post_id = :post_id";
    $like_stmt = $db->prepare($like_query);
    $like_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $like_result = $like_stmt->execute(); // Executa a consulta para os likes
    $likes_count_result = $like_result->fetchArray(SQLITE3_ASSOC); // Use fetchArray() no resultado
    $likes_count = $likes_count_result['likes']; // Aqui pegamos a contagem de likes

    // Verifica se o usuário curtiu este post
    $user_like_query = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $user_like_stmt = $db->prepare($user_like_query);
    $user_like_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $user_like_stmt->bindValue(":user_id", $_SESSION['id_user'], SQLITE3_INTEGER);
    $user_like_result = $user_like_stmt->execute(); // Executa a consulta
    $user_liked = $user_like_result->fetchArray(SQLITE3_ASSOC); // Use fetchArray() no resultado

        echo "<div class='post' data-post-id='{$post['id']}'>
        <a href='big_post.php?id={$post['id']}'>
                <div class='post-header'>
                <div class='post-avatar'>" .
                    (empty($post['perfil_imagem']) 
                        ? htmlspecialchars($firstLetter) 
                        : "<img src='" . htmlspecialchars($post['perfil_imagem']) . "' alt='Foto de perfil'>"
                    ) .
                "</div>                
                <div class='post-user'>
                    <div class='post-username'>{$username}</div>
                    <div class='post-time'>{$post['data_post']}</div>
                </div>
                " . 
                    ($_SESSION['id_user'] == 1 ? "
                    <form method='POST' action='ocultar_post.php' class='delete-form' style='margin-left:auto;' onsubmit='return confirmarDelete();'>
                        <input type='hidden' name='delete_post_id' value='{$post['id']}'>
                        <button type='submit' class='delete-button' title='Excluir Post'>🗑</button>
                    </form>" : "") ."
            </div>
            <div class='post-title'>" . htmlspecialchars($post['title']) . "</div>
            <div class='post-content'>" . nl2br(htmlspecialchars($post['content'])) . "</div>
            <div class='post-meta'>
                <div class='post-actions'>
                    <a href='big_post.php?id={$post['id']}'>
                        <button class='post-action'><span class='icon'>💬</span> Comentar</button>
                    </a>
                    <button class='post-action like-button' data-post-id='{$post_id}'>
                        <span class='icon'>❤️</span>
                        <span id='like-count-{$post_id}'>" . ($likes_count > 0 ? "{$likes_count} Likes" : "Likes") . "</span>
                    </button>
                </div>
            </div>
        </a>
      </div>";
      
}
?>