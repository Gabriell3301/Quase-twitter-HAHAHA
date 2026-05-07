<?php
session_start();
include("conexao.php");
$db = LigaDB();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erro: ID do post inválido.");
}

$id_post = (int) $_GET['id'];

// --- Apagar comentário (admin) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id']) && $_SESSION['id_user'] == 1) {
    $del_stmt = $db->prepare("DELETE FROM coments WHERE id = :id");
    $del_stmt->bindValue(":id", (int)$_POST['delete_comment_id'], SQLITE3_INTEGER);
    $del_stmt->execute();
    header("Location: big_post.php?id=$id_post");
    exit;
}

// --- Guardar edição do post ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'])) {
    $edit_stmt = $db->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id AND id_user = :uid");
    $edit_stmt->bindValue(":title",   trim($_POST['edit_title']),   SQLITE3_TEXT);
    $edit_stmt->bindValue(":content", trim($_POST['edit_content']), SQLITE3_TEXT);
    $edit_stmt->bindValue(":id",      $id_post,                    SQLITE3_INTEGER);
    $edit_stmt->bindValue(":uid",     $_SESSION['id_user'],        SQLITE3_INTEGER);
    $edit_stmt->execute();
    header("Location: big_post.php?id=$id_post");
    exit;
}

// --- Buscar post ---
$stmt = $db->prepare("SELECT posts.*, user.nome_user, user.perfil_imagem 
                      FROM posts JOIN user ON posts.id_user = user.id 
                      WHERE posts.id = ?");
$stmt->bindValue(1, $id_post, SQLITE3_INTEGER);
$post = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$post) die("Erro: Post não encontrado.");

$username    = htmlspecialchars($post['nome_user']);
$firstLetter = strtoupper(substr($username, 0, 1));

// --- Likes ---
$like_stmt = $db->prepare("SELECT COUNT(*) as total FROM likes WHERE post_id = :id");
$like_stmt->bindValue(":id", $id_post, SQLITE3_INTEGER);
$likes_count = $like_stmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];

$user_liked_stmt = $db->prepare("SELECT 1 FROM likes WHERE post_id = :pid AND user_id = :uid");
$user_liked_stmt->bindValue(":pid", $id_post, SQLITE3_INTEGER);
$user_liked_stmt->bindValue(":uid", $_SESSION['id_user'], SQLITE3_INTEGER);
$user_liked = (bool) $user_liked_stmt->execute()->fetchArray();

// --- Comentários ---
$coments_stmt = $db->prepare("SELECT coments.*, user.nome_user, user.perfil_imagem 
                               FROM coments JOIN user ON coments.id_user = user.id 
                               WHERE coments.id_post = ? ORDER BY coments.data_coment ASC");
$coments_stmt->bindValue(1, $id_post, SQLITE3_INTEGER);
$coments_result = $coments_stmt->execute();

$is_owner = isset($_SESSION['id_user']) && $_SESSION['id_user'] == $post['id_user'];
$is_admin = isset($_SESSION['id_user']) && $_SESSION['id_user'] == 1;

$post_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="pt" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,600;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/big_post.css">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
</head>
<body>

<header class="header">
    <a href="feed.php" class="btn-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Voltar
    </a>
    <button class="btn-theme" onclick="toggleTheme()" title="Alternar tema">
        <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>
</header>

<main class="container">

    <!-- POST -->
    <article class="post-card">
        <div class="post-header">
            <div class="avatar">
                <?php if (empty($post['perfil_imagem'])): ?>
                    <?php echo $firstLetter; ?>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($post['perfil_imagem']); ?>" alt="">
                <?php endif; ?>
            </div>
            <div class="post-meta">
                <span class="post-author"><?php echo $username; ?></span>
                <span class="post-time"><?php echo $post['data_post']; ?></span>
            </div>
            <?php if ($is_owner || $is_admin): ?>
                <button class="btn-edit" onclick="toggleEdit()" title="Editar post">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
            <?php endif; ?>
        </div>

        <!-- Modo leitura -->
        <div id="post-view">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        </div>

        <!-- Modo edição -->
        <?php if ($is_owner || $is_admin): ?>
        <form id="post-edit" class="edit-form" method="POST" style="display:none;">
            <input type="text" name="edit_title" value="<?php echo htmlspecialchars($post['title']); ?>" class="edit-input" required>
            <textarea name="edit_content" class="edit-textarea" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            <div class="edit-actions">
                <button type="button" class="btn-cancel" onclick="toggleEdit()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
        <?php endif; ?>

        <!-- Ações -->
        <div class="post-actions">
            <button class="btn-like <?php echo $user_liked ? 'liked' : ''; ?>" id="like-btn" data-post-id="<?php echo $id_post; ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $user_liked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span id="like-label"><?php echo $likes_count > 0 ? $likes_count . " Likes" : "Gostar"; ?></span>
            </button>

            <button class="btn-share" onclick="partilharPost()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Partilhar
            </button>

            <span id="share-feedback" class="share-feedback"></span>
        </div>
    </article>

    <!-- COMENTÁRIOS -->
    <section class="comments-section">
        <h2 class="comments-title">
            Comentários
            <span class="comments-count" id="coments-total">
                <?php
                $cnt_stmt = $db->prepare("SELECT COUNT(*) as t FROM coments WHERE id_post = ?");
                $cnt_stmt->bindValue(1, $id_post, SQLITE3_INTEGER);
                echo $cnt_stmt->execute()->fetchArray(SQLITE3_ASSOC)['t'];
                ?>
            </span>
        </h2>

        <div class="comments-list" id="comments-list">
            <?php while ($c = $coments_result->fetchArray(SQLITE3_ASSOC)):
                $c_user    = htmlspecialchars($c['nome_user']);
                $c_letter  = strtoupper(substr($c_user, 0, 1));
            ?>
            <div class="comment" id="comment-<?php echo $c['id']; ?>">
                <div class="comment-header">
                    <div class="avatar avatar-sm">
                        <?php if (empty($c['perfil_imagem'])): ?>
                            <?php echo $c_letter; ?>
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($c['perfil_imagem']); ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="comment-author"><?php echo $c_user; ?></span>
                        <span class="comment-time"><?php echo $c['data_coment']; ?></span>
                    </div>
                    <?php if ($is_admin): ?>
                    <form method="POST" class="delete-comment-form" onsubmit="return confirm('Apagar comentário?')">
                        <input type="hidden" name="delete_comment_id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn-delete-comment" title="Apagar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <p class="comment-body"><?php echo nl2br(htmlspecialchars($c['content'])); ?></p>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Formulário novo comentário -->
        <?php if (isset($_SESSION['id_user'])): ?>
        <form action="salvar_comentario.php" method="POST" class="comment-form">
            <input type="hidden" name="id_post" value="<?php echo $id_post; ?>">
            <textarea name="content" placeholder="Escreve um comentário..." required></textarea>
            <button type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Enviar
            </button>
        </form>
        <?php else: ?>
        <p class="login-prompt"><a href="login.php">Faz login para comentar</a></p>
        <?php endif; ?>
    </section>

</main>

<script>
// --- Tema ---
function toggleTheme() {
    const html = document.documentElement;
    html.dataset.theme = html.dataset.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', html.dataset.theme);
}
(function() {
    const saved = localStorage.getItem('theme');
    if (saved) document.documentElement.dataset.theme = saved;
})();

// --- Editar post ---
function toggleEdit() {
    const view = document.getElementById('post-view');
    const edit = document.getElementById('post-edit');
    const isEditing = edit.style.display !== 'none';
    view.style.display = isEditing ? 'block' : 'none';
    edit.style.display = isEditing ? 'none'  : 'block';
}

// --- Like ---
document.getElementById('like-btn')?.addEventListener('click', function() {
    const btn    = this;
    const postId = btn.dataset.postId;
    const xhr    = new XMLHttpRequest();
    xhr.open('POST', 'likes.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const count = parseInt(xhr.responseText, 10);
            document.getElementById('like-label').textContent = count > 0 ? count + ' Likes' : 'Gostar';
            btn.classList.toggle('liked');
            const svg = btn.querySelector('svg');
            svg.setAttribute('fill', btn.classList.contains('liked') ? 'currentColor' : 'none');
        }
    };
    xhr.send('post_id=' + postId);
});

// --- Partilhar ---
function partilharPost() {
    const url = window.location.href;
    const feedback = document.getElementById('share-feedback');

    if (navigator.share) {
        navigator.share({ title: document.title, url });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            feedback.textContent = '✓ Link copiado!';
            feedback.classList.add('visible');
            setTimeout(() => feedback.classList.remove('visible'), 2500);
        });
    }
}
</script>
</body>
</html>