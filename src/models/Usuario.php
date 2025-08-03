<?php

namespace app\classes\model;

class Usuario extends Model {
    private int $id = 0;
    private string $nome = '';
    private string $email = '';
    private string $senha = '';

    public function __construct(
        int $id = 0,
        string $nome = '',
        string $email = '',
        string $senha = ''
    ) {
        $this->setId($id);
        $this->setNome($nome);
        $this->setEmail($email);
        $this->setSenha($senha);
    }

    public function getId() {
        return $this->id;
    }

    public function setId(int $id) {
        $this->id = $id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome(string $nome) {
        $this->nome = $nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail(string $email) {
        $this->email = $email;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setSenha(string $senha) {
        $this->senha = $senha;
    }

    public function emArray(): array {
        return [
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'email' => $this->getEmail()
        ];
    }
}
