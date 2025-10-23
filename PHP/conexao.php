<?php
function LigaDB()
{
    try{
        return new SQLite3("BaseDeDados.db");
    }catch(Exception $e)
    {
        echo "Erro ao tentar entrar na base de dados: " . $e->getMessage();
        return false;
    }
}
?>