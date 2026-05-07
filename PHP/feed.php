<?php
session_start();
include("conexao.php");
include("Header.php");
include("Nav.php");
$db = LigaDB();

// --- Apagar post (apenas admin) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $postIdToDelete = (int) $_POST['delete_post_id'];
    $stmt = $db->prepare("UPDATE posts SET visib = 0 WHERE id = :post_id");
    $stmt->bindValue(':post_id', $postIdToDelete, SQLITE3_INTEGER);
    $stmt->execute();
}

// --- Posts iniciais ---
$postsPorCarregar = 5;
$offset  = 0;
$query   = "SELECT posts.*, user.nome_user, user.perfil_imagem 
            FROM posts 
            JOIN user ON posts.id_user = user.id 
            WHERE posts.visib = 1
            ORDER BY data_post DESC 
            LIMIT $postsPorCarregar OFFSET $offset";
$resultado = $db->query($query);

// --- Notificações não lidas ---
$id_user  = $_SESSION['id_user'];
$nao_lidas_stmt = $db->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE user_id = :id AND lida = 0");
$nao_lidas_stmt->bindValue(":id", $id_user, SQLITE3_INTEGER);
$nao_lidas = $nao_lidas_stmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="pt" data-theme="dark">
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/feed.css">
    <title>Feed</title>
</head>
<body>

<div class="container">
    <div class="posts" id="posts">
        <?php while ($post = $resultado->fetchArray(SQLITE3_ASSOC)): ?>
            <?php include("_post_card.php"); ?>
        <?php endwhile; ?>
    </div>

    <div id="loading" style="display:none">Carregando mais posts...</div>
    <div id="sentinela"></div>
</div>

<button class="new-post-btn" onclick="location.href='../HTML/criar_post.html'">+</button>

<script src="../JS/Likes.js"></script>

<script>
    let pagina    = 1;
    let carregando = false;
    const sentinela = document.getElementById('sentinela');
    const loading   = document.getElementById('loading');

    async function carregarMaisPosts() {
        if (carregando) return;
        carregando = true;
        loading.style.display = 'block';
        pagina++;

        try {
            const response = await fetch(`carregar_posts.php?page=${pagina}`);
            const data     = await response.text();

            if (data.trim()) {
                document.getElementById('posts').insertAdjacentHTML('beforeend', data);
            } else {
                loading.innerHTML = 'Não há mais posts.';
                observer.disconnect();
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
        if (entries[0].isIntersecting) carregarMaisPosts();
    });
    observer.observe(sentinela);
</script>

<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.like-button');
        if (!button) return;

        const postId = button.getAttribute('data-post-id');
        const xhr    = new XMLHttpRequest();

        xhr.open('POST', 'likes.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const count = parseInt(xhr.responseText, 10);
                document.getElementById('like-count-' + postId).textContent =
                    count > 0 ? count + " Likes" : "Likes";
                button.classList.toggle('liked');
            }
        };
        xhr.send('post_id=' + postId);
    });
</script>

</body>
</html>