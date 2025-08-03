<?php

namespace app\classes\jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;
use DateTime;
use TipoToken;

class AutenticacaoJWT {
    private string $secretKey;
    private string $algoritimoDeCriptografia;

    public function __construct(string $algoritimoDeCriptografia = 'HS256') {
        $this->secretKey = $_ENV['SECRET_KEY_JWT'];
        $this->algoritimoDeCriptografia = $algoritimoDeCriptografia;
    }

    /**
     * Método responsável por gerar um token JWT.
     * @param TipoToken $tipoToken
     * @param int $id
     * @param string $nome
     * @param string $papel
     * @param int $duracaoEmSegundos
     * @return string
     */
    public function gerarToken(TipoToken $tipoToken, int $id, string $nome, string $papel, int $duracaoEmSegundos): array {
        $dataCriacao = new DateTime();
        $dataExpiracao = new DateTime()->modify("+{$duracaoEmSegundos} seconds");

        $payloadJWT = new PayloadJWT(
            $tipoToken,
            $id,
            $nome,
            $papel,
            $dataCriacao,
            $dataExpiracao
        );
        $token = JWT::encode(
            $payloadJWT->emArray(),
            $this->secretKey,
            $this->algoritimoDeCriptografia
        );

        return [
            'token' => $token,
            'dataCriacao' => $dataCriacao,
            'dataExpiracao' => $dataExpiracao
        ];
    }

    /**
     * Método responsável por decodificar token JWT.
     *
     * @param string $token Token JWT recebido
     * @return PayloadJWT|null Retorna o payload do token ou null em caso de erro
     */
    public function decodificarToken(string $token): PayloadJWT|null {
        try {
            $payload = JWT::decode(
                $token,
                new Key($this->secretKey, $this->algoritimoDeCriptografia)
            );
            $payloadJWT = new PayloadJWT(
                TipoToken::from($payload->tipo),
                $payload->sub,
                $payload->name,
                $payload->role,
                new DateTime()->setTimestamp($payload->iat),
                new DateTime()->setTimestamp($payload->exp)
            );

            return $payloadJWT;
        } catch (Throwable $e) {
            return null;
        }
    }
}
