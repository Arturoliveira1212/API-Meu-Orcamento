<?php

namespace app\traits;

use app\classes\jwt\AutenticacaoJWT;
use app\classes\jwt\PayloadJWT;
use app\classes\jwt\TokenJWT;
use TipoToken;

trait Autenticavel {

    public function gerarToken(TipoToken $tipoToken, string $id, string $nome, string $papel, int $duracaoEmSegundos): string {
        $autenticacaoJWT = new AutenticacaoJWT();
        $token = $autenticacaoJWT->gerarToken(
            $tipoToken,
            $id,
            $nome,
            $papel,
            $duracaoEmSegundos
        );

        return $token;
    }

    public function decodificarToken(string $token): PayloadJWT|null {
        $autenticacaoJWT = new AutenticacaoJWT();
        $payloadJWT = $autenticacaoJWT->decodificarToken($token);

        return $payloadJWT;
    }
}
