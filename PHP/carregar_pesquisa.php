<?php
session_start();
include("conexao.php");
$db = LigaDB();

if (!isset($_SESSION['id_user'])) {
    exit("Usuário não autenticado");
}

// Número de posts por carregamento
$postsPorCarregar = 5; 

// Verificar a página atual
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPorCarregar;

// Pegar o termo de pesquisa
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construir a consulta SQL com base no termo de pesquisa
$params = [];
$sql = "SELECT posts.*, user.nome_user, user.perfil_imagem FROM posts 
        JOIN user ON posts.id_user = user.id 
        WHERE posts.visib = 1";

if (!empty($search)) {
    $sql .= " AND user.nome_user LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY data_post DESC LIMIT $postsPorCarregar OFFSET $offset";

// Preparar e executar a consulta
$stmt = $db->prepare($sql);

// Bind dos parâmetros se houver pesquisa
if (!empty($search)) {
    $stmt->bindValue(1, "%$search%", SQLITE3_TEXT);
}

$resultado = $stmt->execute();

// Verificar se encontrou algum resultado
$resultCount = 0;

// Exibir os posts para o AJAX
while ($post = $resultado->fetchArray(SQLITE3_ASSOC)) {
    $resultCount++;
    $username = htmlspecialchars($post['nome_user']);
    $firstLetter = strtoupper(substr($username, 0, 1));
    $post_id = $post['id'];

    // Consulta o número de likes do post
    $like_query = "SELECT COUNT(*) as likes FROM likes WHERE post_id = :post_id";
    $like_stmt = $db->prepare($like_query);
    $like_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $like_result = $like_stmt->execute();
    $likes_count_result = $like_result->fetchArray(SQLITE3_ASSOC);
    $likes_count = $likes_count_result['likes'];

    // Verifica se o usuário curtiu este post
    $user_like_query = "SELECT * FROM likes WHERE post_id = :post_id AND user_id = :user_id";
    $user_like_stmt = $db->prepare($user_like_query);
    $user_like_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $user_like_stmt->bindValue(":user_id", $_SESSION['id_user'], SQLITE3_INTEGER);
    $user_like_result = $user_like_stmt->execute();
    $user_liked = $user_like_result->fetchArray(SQLITE3_ASSOC);
    
    $likedClass = $user_liked ? 'liked' : '';

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
                    <button class='post-action like-button $likedClass' data-post-id='{$post_id}'>
                        <span class='icon'>❤️</span>
                        <span id='like-count-{$post_id}'>" . ($likes_count > 0 ? "{$likes_count} Likes" : "Likes") . "</span>
                    </button>
                </div>
            </div>
        </a>
    </div>";
}

// Se não encontrou resultados, não retorna nada
if ($resultCount == 0) {
    echo "";
}
?>