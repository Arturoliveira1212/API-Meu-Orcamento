<?php

namespace app\traits;

trait Criptografavel {
    /**
     * Método responsável por gerar um hash seguro para uma senha usando password_hash.
     *
     * @param string $senha Senha em texto puro
     * @return string Hash da senha
     */
    public function gerarHash(string $senha) {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    /**
     * Método responsável por verificar se a senha corresponde ao hash armazenado.
     *
     * @param string $senha Senha digitada pelo usuário
     * @param string $hash Hash armazenado no banco de dados
     * @return bool True se a senha for válida, False caso contrário
     */
    public function verificarSenha(string $senha, string $hash) {
        return password_verify($senha, $hash);
    }
}
