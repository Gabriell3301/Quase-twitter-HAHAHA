<?php
/**
 * _post_card.php
 * Partial reutilizável para renderizar um card de post.
 * 
 * Requer:
 *   - $db        : instância SQLite3 ligada
 *   - $post      : array com os dados do post (id, nome_user, perfil_imagem, title, content, data_post)
 *   - $_SESSION['id_user'] : id do utilizador da sessão
 */

$username    = htmlspecialchars($post['nome_user']);
$firstLetter = strtoupper(substr($username, 0, 1));
$post_id     = $post['id'];

// --- Likes ---
$like_stmt = $db->prepare("SELECT COUNT(*) as likes FROM likes WHERE post_id = :post_id");
$like_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
$likes_count = $like_stmt->execute()->fetchArray(SQLITE3_ASSOC)['likes'];

// --- Comentários ---
$coment_stmt = $db->prepare("SELECT COUNT(*) as total FROM coments WHERE id_post = :post_id");
$coment_stmt->bindValue(":post_id", $post_id, SQLITE3_INTEGER);
$coments_count = $coment_stmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];
?>

<div class="post" data-post-id="<?php echo $post_id; ?>">
    <a href="big_post.php?id=<?php echo $post_id; ?>">

        <div class="post-header">
            <div class="post-avatar">
                <?php if (empty($post['perfil_imagem'])): ?>
                    <?php echo $firstLetter; ?>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($post['perfil_imagem']); ?>" alt="Foto de perfil">
                <?php endif; ?>
            </div>

            <div class="post-user">
                <div class="post-username"><?php echo $username; ?></div>
                <div class="post-time"><?php echo $post['data_post']; ?></div>
            </div>

            <?php if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == 1): ?>
                <form method="POST" class="delete-form" style="margin-left:auto;" onsubmit="return confirmarDelete();">
                    <input type="hidden" name="delete_post_id" value="<?php echo $post_id; ?>">
                    <button type="submit" class="delete-button" title="Excluir Post">🗑</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
        <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>

        <div class="post-meta">
            <div class="post-actions">
                <a href="big_post.php?id=<?php echo $post_id; ?>">
                    <button class="post-action">
                        <span class="icon">💬</span>
                        <?php echo $coments_count > 0 ? $coments_count . " Comentários" : "Comentar"; ?>
                    </button>
                </a>
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