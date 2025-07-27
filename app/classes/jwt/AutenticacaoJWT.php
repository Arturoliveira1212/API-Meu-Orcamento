<?php

namespace app\classes\jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class AutenticacaoJWT {
    private string $secretKey;
    private string $algoritimoDeCriptografia;

    public function __construct(string $algoritimoDeCriptografia = 'HS256') {
        $this->secretKey = $_ENV['SECRET_KEY_JWT'];
        $this->algoritimoDeCriptografia = $algoritimoDeCriptografia;
    }

    /**
     * Método responsável por gerar um token JWT.
     *
     * @param array $payload Dados que serão incluídos no token
     * @return TokenJWT Token JWT gerado
     */
    public function gerarToken(string $id, string $nome, string $papel, int $duracaoEmSegundos = 3600) {
        $criadoEm = time();
        $expiraEm = $criadoEm + $duracaoEmSegundos;

        $payloadJWT = new PayloadJWT($id, $nome, $papel, $criadoEm, $expiraEm);
        $token = JWT::encode($payloadJWT->toArray(), $this->secretKey, $this->algoritimoDeCriptografia);
        $tokenJWT = new TokenJWT($token, $duracaoEmSegundos);

        return $tokenJWT;
    }

    /**
     * Método responsável por decodificar token JWT.
     *
     * @param string $token Token JWT recebido
     * @return PayloadJWT|null Retorna o payload do token ou null em caso de erro
     */
    public function decodificarToken(string $token) {
        try {
            $payload = JWT::decode($token, new Key($this->secretKey, $this->algoritimoDeCriptografia));
            $payloadJWT = new PayloadJWT($payload->sub, $payload->name, $payload->role, $payload->iat, $payload->exp);

            return $payloadJWT;
        } catch (Throwable $e) {
            return null;
        }
    }
}
