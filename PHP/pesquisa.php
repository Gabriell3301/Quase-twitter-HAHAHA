<?php
session_start();
include("conexao.php");
$db = LigaDB();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Usuários</title>
    <link rel="stylesheet" href="../CSS/feed.css">
    <link rel="stylesheet" href="../CSS/pesquisa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1>Pesquisar</h1>
    </div>

    <div class="search-container">
        <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-input" placeholder="Buscar por nome de usuário..." autocomplete="off">
            <button id="clear-search" class="clear-btn" style="display: none;"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div id="posts-container">
        <!-- Os posts serão carregados aqui dinamicamente -->
    </div>
    
    <div id="loading" style="display: none; text-align: center; padding: 20px;">
        <div class="spinner"></div>
        <p>Carregando posts...</p>
    </div>

    <div id="no-results" style="display: none; text-align: center; padding: 20px;">
        <p>Nenhum resultado encontrado.</p>
    </div>

    <?php include("Nav.php"); ?>

    <script>
        let page = 1;
        let loading = false;
        let noMorePosts = false;
        let searchText = '';
        let debounceTimer;

        document.addEventListener('DOMContentLoaded', function() {
            // Iniciar com pesquisa vazia (carrega todos os posts)
            loadPosts();

            // Detectar scroll para carregar mais posts
            window.addEventListener('scroll', function() {
                if (loading || noMorePosts) return;
                
                if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
                    page++;
                    loadPosts();
                }
            });

            // Campo de pesquisa com debounce
            const searchInput = document.getElementById('search-input');
            const clearSearch = document.getElementById('clear-search');

            searchInput.addEventListener('input', function() {
                clearSearch.style.display = this.value ? 'block' : 'none';
                
                // Implementar debounce para não sobrecarregar com muitas requisições
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    searchText = this.value.trim();
                    page = 1;
                    noMorePosts = false;
                    document.getElementById('posts-container').innerHTML = '';
                    loadPosts();
                }, 500);
            });

            // Botão para limpar pesquisa
            clearSearch.addEventListener('click', function() {
                searchInput.value = '';
                clearSearch.style.display = 'none';
                searchText = '';
                page = 1;
                noMorePosts = false;
                document.getElementById('posts-container').innerHTML = '';
                loadPosts();
            });
        });

        function loadPosts() {
            if (loading || noMorePosts) return;
            
            loading = true;
            document.getElementById('loading').style.display = 'block';
            document.getElementById('no-results').style.display = 'none';

            fetch(`carregar_pesquisa.php?page=${page}&search=${encodeURIComponent(searchText)}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    
                    if (data.trim() === '') {
                        noMorePosts = true;
                        if (page === 1) {
                            document.getElementById('no-results').style.display = 'block';
                        }
                    } else {
                        document.getElementById('posts-container').insertAdjacentHTML('beforeend', data);
                    }
                    
                    loading = false;
                })
                .catch(error => {
                    console.error('Erro ao carregar posts:', error);
                    document.getElementById('loading').style.display = 'none';
                    loading = false;
                });
        }

        // Função para dar like no post
        document.addEventListener('click', function(event) {
            if (event.target.closest('.like-button')) {
                const button = event.target.closest('.like-button');
                const postId = button.getAttribute('data-post-id');
                
                fetch('like_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    const likeCountElement = document.getElementById(`like-count-${postId}`);
                    likeCountElement.textContent = data.likes > 0 ? `${data.likes} Likes` : 'Likes';
                    
                    if (data.liked) {
                        button.classList.add('liked');
                    } else {
                        button.classList.remove('liked');
                    }
                });
            }
        });
    </script>
</body>
</html>