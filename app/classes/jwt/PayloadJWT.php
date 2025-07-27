<?php

namespace app\classes\jwt;

class PayloadJWT {
    /** Id do usuário */
    private int $sub;
    /** Nome do usuário */
    private ?string $name;
    /** Papel do usuário */
    private ?string $role;
    /** Quando foi emitido */
    private int $iat;
    /** Expiração */
    private int $exp;

    public function __construct(int $sub, ?string $name, ?string $role, int $iat, int $exp) {
        $this->sub = $sub;
        $this->name = $name;
        $this->role = $role;
        $this->iat = $iat;
        $this->exp = $exp;
    }

    public function toArray() {
        return [
            'sub' => $this->sub,
            'name' => $this->name,
            'role' => $this->role,
            'iat' => $this->iat,
            'exp' => $this->exp
        ];
    }

    public function sub() {
        return $this->sub;
    }

    public function name() {
        return $this->name;
    }

    public function role() {
        return $this->role;
    }

    public function iat() {
        return $this->iat;
    }
    public function exp() {
        return $this->exp;
    }
}
