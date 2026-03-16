<?php
session_start();
include ("conexao.php");
include ("Nav.php");
$db = LigaDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $postIdToDelete = (int) $_POST['delete_post_id'];
    
    // Atualiza visib para 0
    $updateQuery = "UPDATE posts SET visib = 0 WHERE id = :post_id";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindValue(':post_id', $postIdToDelete, SQLITE3_INTEGER);
    $stmt->execute();
}

// Número de posts por carregamento
$postsPorCarregar = 5;

// Página inicial sempre 1
$page = 1;
$offset = ($page - 1) * $postsPorCarregar;

// Buscar posts com nome de usuário
$query = "SELECT posts.*, user.nome_user, user.perfil_imagem FROM posts 
          JOIN user ON posts.id_user = user.id 
          WHERE posts.visib = 1
          ORDER BY data_post DESC 
          LIMIT $postsPorCarregar OFFSET $offset";

$resultado = $db->query($query);

// Notificações
    
    // Exemplo: buscar notificações não lidas do usuário logado
    $id_user = $_SESSION['id_user'];
    $query = $db->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE user_id = :id AND lida = 0");
    $query->bindValue(":id", $id_user, SQLITE3_INTEGER);
    $result = $query->execute(); 
    $notificacao = $result->fetchArray(SQLITE3_ASSOC);
    $nao_lidas = $notificacao['total'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/feed.css">
    <title>Feed</title>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="header-title">Feed</div>
            <details>
          <summary>⚙️</summary>
          <button class="header-icon" onclick="location.href='configuracao.php';">Configuração</button><br>
          <button class="header-icon" onclick="location.href='log_out.php';" style="color: red; text-decoration: underline;">Log Out</button>
        </details>
        </div>
    </header>
    <div class="container">
    <div class="posts" id="posts"> 
    <?php
while ($post = $resultado->fetchArray(SQLITE3_ASSOC)) { 
    // Correto: usa fetchArray() para os posts
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

    $coments_sql = "SELECT COUNT(*) as total FROM coments WHERE id_post = :post_id";
    $coments_stmt = $db->prepare($coments_sql);
    $coments_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
    $coments_result = $coments_stmt->execute();
    $coments_count = $coments_result->fetchArray(SQLITE3_ASSOC);
    $coments_count_result = $coments_count['total'];
?>
    <div class='post' data-post-id="<?php echo $post['id']; ?>">
        <a href='big_post.php?id=<?php echo $post['id']; ?>'>

        <div class='post-header'>
            <div class='post-avatar'>
                <?php if (empty($post['perfil_imagem'])): ?>
                    <?php echo $firstLetter; ?>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($post['perfil_imagem']); ?>" alt="Foto de perfil">
                <?php endif; ?>
            </div>            
            
            <div class='post-user'>
                <div class='post-username'><?php echo $username; ?></div>
                <div class='post-time'><?php echo $post['data_post']; ?></div>
            </div>
            <?php if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == 1): ?>
    <form method="POST" class="delete-form" style="margin-left:auto;" onsubmit="return confirmarDelete();">
        <input type="hidden" name="delete_post_id" value="<?php echo $post['id']; ?>">
        <button type="submit" class="delete-button" title="Excluir Post">🗑</button>
    </form>
<?php endif; ?>
        </div>
        <div class='post-title'><?php echo htmlspecialchars($post['title']); ?></div>
        <div class='post-content'><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        <div class='post-meta'>
            <div class='post-actions'>
            <a href='big_post.php?id=<?php echo $post['id']; ?>'>
                <button class='post-action'>
                    <span class='icon'>💬</span>
                    <?php echo $coments_count_result > 0 ? $coments_count_result . " Comentários" : "Comentar"; ?>
                </button></a>    
                <button class="post-action like-button" data-post-id="<?php echo $post_id; ?>">
                        <span class="icon">❤️</span>
                        <span id="like-count-<?php echo $post_id; ?>">
                            <?php echo $likes_count > 0 ? $likes_count . " Likes" : "Likes"; ?>
                        </span>
                    </button>
            </div>
        </div>
    </a>
    </div>

    <?php } ?>
</div>
        <div id='loading' style='display: none'>Carregando mais posts...</div>
        <div id="sentinela"></div> <!-- Elemento invisível para ativar o carregamento -->
    </div>

    <button class='new-post-btn' onclick="location.href='../HTML/criar_post.html'">+</button>

   <!-- 
    <nav class='nav-bottom'>
        <button class='nav-item active'><span class='icon'>🏠</span></button>
        <button class='nav-item'><span class='icon'>🔍</span></button>
        <button class='nav-item'><span class='icon'>🔔</span></button>
        <button class="nav-item notificacao-btn">
            <span class="icon">✉️</span>
                <?php if ($nao_lidas > 0): ?>
                    <span class="notificacao-badge">
                        <?php echo $nao_lidas > 9 ? '9+' : $nao_lidas; ?>
                    </span>
                <?php endif; ?>
        </button>
    </nav>
    !-->

<script>
        let pagina = 1;
    let carregando = false;
    const sentinela = document.getElementById('sentinela');
    const loading = document.getElementById('loading');

    async function carregarMaisPosts() {
        if (carregando) return;
        carregando = true;
        loading.style.display = 'block';
        pagina++;

        try {
            let response = await fetch(`carregar_posts.php?page=${pagina}`);
            let data = await response.text();

            if (data.trim()) {
                document.getElementById("posts").insertAdjacentHTML('beforeend', data);
            } else {
                loading.innerHTML = 'Não há mais posts.';
                observer.disconnect(); // Para de observar quando não há mais posts
            }
        } catch (error) {
            console.error("Erro ao carregar posts:", error);
            loading.innerHTML = "Erro ao carregar posts. Tente novamente.";
        } finally {
            carregando = false;
            loading.style.display = 'none';
        }
    }

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            carregarMaisPosts();
        }
    });

    observer.observe(sentinela);
</script>
<script>
    document.addEventListener('click', function(event) 
    {
        if (event.target.closest('.like-button')) { 
            const button = event.target.closest('.like-button');
            const postId = button.getAttribute('data-post-id');

            // Envia a requisição AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'likes.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Atualiza o número de likes na interface
                    const likesCount = xhr.responseText;
                    document.getElementById('like-count-' + postId).textContent = likesCount > 0 ? likesCount + " Likes" : "Likes";

                    // Alterna o estado do botão de curtir/descurtir
                    button.classList.toggle('liked');
                }
            };
            xhr.send('post_id=' + postId);
        }
    });
</script>
</body>
</html>