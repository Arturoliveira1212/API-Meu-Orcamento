<?php

namespace app\classes\jwt;

class TokenJWT {
    private string $codigo;
    private int $duracaoEmSegundos;

    public function __construct(string $codigo, int $duracaoEmSegundos) {
        $this->codigo = $codigo;
        $this->duracaoEmSegundos = $duracaoEmSegundos;
    }

    public function codigo() {
        return $this->codigo;
    }

    public function duracaoEmSegundos() {
        return $this->duracaoEmSegundos;
    }

    public function validadeTokenFormatada() {
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
