<?php

namespace app\classes\jwt;

use TipoToken;

class TokenJWT {
    private string $token;
    private TipoToken $tipoToken;
    private int $duracaoEmSegundos;

    const DURACAO_ACCESS_TOKEN = 3600; // 1 hora
    const DURACAO_REFRESH_TOKEN = 604800; // 7 dias

    public function __construct(string $token, TipoToken $tipoToken, int $duracaoEmSegundos) {
        $this->token = $token;
        $this->tipoToken = $tipoToken;
        $this->duracaoEmSegundos = $duracaoEmSegundos;
    }

    public function token(): string {
        return $this->token;
    }

    public function duracaoEmSegundos(): int {
        return $this->duracaoEmSegundos;
    }

    public function validadeTokenFormatada(): string {
        $horas = floor($this->duracaoEmSegundos / 3600);
        $minutos = floor(($this->duracaoEmSegundos % 3600) / 60);
        $segundosRestantes = $this->duracaoEmSegundos % 60;

        $tempo = [];
        if ($horas > 0) {
            $tempo[] = "{$horas}h";
        }
        if ($minutos > 0) {
            $tempo[] = "{$minutos}m";
        }
        if ($segundosRestantes > 0) {
            $tempo[] = "{$segundosRestantes}s";
        }

        return implode(' ', $tempo);
    }
}
