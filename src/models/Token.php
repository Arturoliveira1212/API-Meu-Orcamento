<?php

namespace app\classes\model;

use DateTime;

// use app\classes\model\Model;
// use app\classes\model\Usuario;

// CREATE TABLE refresh_tokens (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT NOT NULL,
//     token TEXT NOT NULL,
//     expires_at DATETIME NOT NULL,
//     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
//     revoked BOOLEAN DEFAULT FALSE
// );

class Token extends Model {
    private Usuario $usuario;
    private string $token;
    private DateTime $dataCriacao;
    private DateTime $dataExpiracao;

    public function __construct(Usuario $usuario = null, string $token = '', DateTime $dataCriacao = null, DateTime $dataExpiracao = null) {
        $this->setId($id);
        $this->setUsuario($usuario);
        $this->setToken($token);
        $this->setDataCriacao($dataCriacao ?? new DateTime());
        $this->setDataExpiracao($dataExpiracao ?? (new DateTime())->modify('+1 hour'));
    }

    public function emArray(): array {
        return [
            'token' => $this->getToken(),
            'dataCriacao' => $this->getDataCriacao()->format('Y-m-d H:i:s'),
            'dataExpiracao' => $this->getDataExpiracao()->format('Y-m-d H:i:s')
        ];
    }
}