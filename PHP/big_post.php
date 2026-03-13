<?php
session_start();
include("conexao.php");
$db = LigaDB();

// Verifica se o ID do post foi passado na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erro: ID do post inválido.");
}

$id_post = $_GET['id'];

// Busca o post no banco de dados
$stmt = $db->prepare("SELECT posts.*, user.nome_user, user.perfil_imagem FROM posts 
                      JOIN user ON posts.id_user = user.id 
                      WHERE posts.id = ?");
$stmt->bindValue(1, $id_post, SQLITE3_INTEGER);
$post = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Se o post não existir, exibe erro
if (!$post) {
    die("Erro: Post não encontrado.");
}
    $username = htmlspecialchars($post['nome_user']);
    $firstLetter = strtoupper(substr($username, 0, 1));
// Busca os comentários do post
$comentarios_query = $db->prepare("SELECT coments.*, user.nome_user, user.perfil_imagem FROM coments 
                                   JOIN user ON coments.id_user = user.id 
                                   WHERE coments.id_post = ? 
                                   ORDER BY coments.data_coment ASC");
$comentarios_query->bindValue(1, $id_post, SQLITE3_INTEGER);
$comentarios_result = $comentarios_query->execute();
    
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/big_post.css">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="feed.php" class="header-back">← Voltar</a>
        </div>
    </header>

    <div class="post-container">
        <div class="post">
            <div class="post-header">
                <div class='post-avatar'>
                    <?php if (empty($post['perfil_imagem'])): ?>
                        <?php echo $firstLetter; ?>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($post['perfil_imagem']); ?>" alt="Foto de perfil">
                    <?php endif; ?>
                </div>
                <div class="post-user">
                    <div class="post-username"><?php echo htmlspecialchars($post['nome_user']); ?></div>
                    <div class="post-time"><?php echo $post['data_post']; ?></div>
                </div>
            </div>
            <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
            <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        </div>

        <div class="comments-section">
            <h2>Comentários</h2>
            <div class="comments-list">
                <?php while ($comentario = $comentarios_result->fetchArray(SQLITE3_ASSOC)) { 
                    $username_comment = htmlspecialchars($comentario['nome_user']);
                    $firstLetter_commet = strtoupper(substr($username_comment, 0, 1));
                ?>
                    <div class="comment">
                        <div class="comentario-linha">
                            <div class='post-avatar'>
                                <?php if (empty($comentario['perfil_imagem'])): ?>
                                    <?php echo $firstLetter_commet; ?>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($comentario['perfil_imagem']); ?>" alt="Foto de perfil">
                                <?php endif; ?>
                            </div>
                            <strong><?php echo htmlspecialchars($comentario['nome_user']); ?>:</strong>
                        </div>
                        <?php echo nl2br(htmlspecialchars($comentario['content'])); ?>
                        <div class="comment-time"><?php echo $comentario['data_coment']; ?></div>
                    </div>
                <?php } ?>
            </div>

            <!-- Formulário para Comentar -->
            <?php if (isset($_SESSION['id_user'])) { ?>
                <form action="salvar_comentario.php" method="POST" class="comment-form">
                    <input type="hidden" name="id_post" value="<?php echo $id_post; ?>">
                    <textarea name="content" placeholder="Escreva um comentário..." required></textarea>
                    <button type="submit">Enviar</button>
                </form>
            <?php } else { ?>
                <p><a href="login.php">Faça login para comentar</a></p>
            <?php } ?>
        </div>
    </div>
</body>
</html>