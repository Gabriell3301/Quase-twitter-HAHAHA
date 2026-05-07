document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', () => {

        // alterna classe
        button.classList.toggle('liked');

        // pega o id do post
        const postId = button.dataset.postId;

        // pega o contador
        const countSpan = document.getElementById(`like-count-${postId}`);

        // pega número atual
        let currentText = countSpan.textContent;
        let number = parseInt(currentText) || 0;

        if (button.classList.contains('liked')) {
            number++;
        } else {
            number--;
        }

        countSpan.textContent = number > 0 ? `${number} Likes` : "Likes";

        // 👉 aqui depois tu liga com backend (fetch/ajax)
    });
});