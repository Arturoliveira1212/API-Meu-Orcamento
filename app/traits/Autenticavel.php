<?php

namespace app\traits;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use app\classes\jwt\TokenJWT;
use app\classes\jwt\PayloadJWT;
use Throwable;

trait Autenticavel {
    /**
     * Método responsável por gerar um token JWT.
     *
     * @param string $id
     * @param string $nome
     * @param string $papel
     * @param integer $duracaoEmSegundos Por padrão é 1 hora
     * @param string $algoritimoCriptografia Por padrão é HS256
     * @return TokenJWT Token JWT gerado
     */
    public function gerarToken(string $id, string $nome, string $papel, int $duracaoEmSegundos = 3600, string $algoritimoCriptografia = 'HS256') {
        $criadoEm = time();
        $expiraEm = $criadoEm + $duracaoEmSegundos;

        $payloadJWT = new PayloadJWT($id, $nome, $papel, $criadoEm, $expiraEm);
        $token = JWT::encode($payloadJWT->toArray(), $_ENV['SECRET_KEY_JWT'], $algoritimoCriptografia);
        $tokenJWT = new TokenJWT($token, $duracaoEmSegundos);

        return $tokenJWT;
    }

    /**
     * Undocumented function
     *
     * @param string $token Token JWT recebido
     * @param string $algoritimoCriptografia Por padrão é HS256
     * @return PayloadJWT|null Retorna o payload do token ou null em caso de erro
     */
    public function decodificarToken(string $token, string $algoritimoCriptografia = 'HS256') {
        try {
            $payload = JWT::decode($token, new Key($_ENV['SECRET_KEY_JWT'], $algoritimoCriptografia));
            $payloadJWT = new PayloadJWT($payload->sub, $payload->name, $payload->role, $payload->iat, $payload->exp);

            return $payloadJWT;
        } catch (Throwable $e) {
            return null;
        }
    }
}
