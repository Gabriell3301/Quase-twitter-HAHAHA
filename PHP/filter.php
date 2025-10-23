<?php
function censurarTexto($texto, $idiomas = ['pt', 'en', 'es']) {
    $basePath = __DIR__ . '/../WordsToFilter/';
    
    foreach ($idiomas as $lang) {
        $filePath = $basePath . "badwords_{$lang}.txt";
        if (!file_exists($filePath)) continue;

        $palavras = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($palavras as $palavra) {
            $padrao = '/\b' . preg_quote($palavra, '/') . '\b/i';
            $texto = preg_replace_callback($padrao, function ($matches) {
                return str_repeat('*', strlen($matches[0]));
            }, $texto);
        }
    }

    return $texto;
}