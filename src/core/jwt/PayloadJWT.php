<?php

namespace app\classes\jwt;

use DateTime;
use TipoToken;

class PayloadJWT {
    private TipoToken $tipoToken;
    private int $id;
    private string $nome;
    private string $papel;
    private DateTime $dataCriacao;
    private DateTime $dataExpiracao;

    public function __construct(TipoToken $tipoToken, int $id, string $nome, string $papel, DateTime $dataCriacao, DateTime $dataExpiracao) {
        $this->tipoToken = $tipoToken;
        $this->id = $id;
        $this->nome = $nome;
        $this->papel = $papel;
        $this->dataCriacao = $dataCriacao;
        $this->dataExpiracao = $dataExpiracao;
    }

    public function emArray(): array {
        return [
            'tipo' => $this->tipoToken()->value,
            'sub' => $this->id(),
            'name' => $this->nome(),
            'role' => $this->papel(),
            'iat' => $this->dataCriacao()->getTimestamp(),
            'exp' => $this->dataExpiracao()->getTimestamp()
        ];
    }

    public function tipoToken(): TipoToken {
        return $this->tipoToken;
    }


    public function id(): int {
        return $this->id;
    }

    public function nome(): string {
        return $this->nome;
    }

    public function papel(): string {
        return $this->papel;
    }

    public function dataCriacao(string $formato = null): DateTime|string {
        if ($formato !== null) {
            return $this->dataCriacao->format($formato);
        }
        return $this->dataCriacao;
    }

    public function dataExpiracao(string $formato = null): DateTime|string {
        if ($formato !== null) {
            return $this->dataExpiracao->format($formato);
        }
        return $this->dataExpiracao;
    }
}
