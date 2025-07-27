<?php

namespace app\classes\utils;

abstract class Validador {
    /**
     * Método responsável por validar o tamanho do texto.
     *
     * @param string $texto
     * @param integer $tamanhoMinimo
     * @param integer $tamanhoMaximo
     * @return integer  -1 => Tamanho fora dos limites | 0 => Texto vazio | 1 => Tamanho ok
     */
    public static function validarTamanhoTexto(string $texto, int $tamanhoMinimo, int $tamanhoMaximo) {
        $tamanhoTexto = mb_strlen($texto);
        if ($tamanhoTexto == 0) {
            return 0;
        } elseif ($tamanhoTexto > $tamanhoMaximo || $tamanhoTexto < $tamanhoMinimo) {
            return -1;
        }

        return 1;
    }

    /**
     * Método responsável por validar email.
     *
     * @param string $email
     * @return boolean
     */
    public static function validarEmail(string $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        if (!preg_match($pattern, $email)) {
            return false;
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return false;
        }

        return true;
    }

    /**
     * Método responsável por validar CPF.
     *
     * @param string $cpf
     * @return boolean
     */
    public static function validarCPF(string $cpf) {
        $formatoCpf = '/^[0-9]{3}.[0-9]{3}.[0-9]{3}-[0-9]{2}$/';
        if (!preg_match($formatoCpf, $cpf)) {
            return false;
        }

        // Remover caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verificar se o CPF tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Validar CPF com base nos dígitos verificadores (algoritmo oficial)
        $soma1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma1 += (int) $cpf[$i] * (10 - $i);
        }
        $resto1 = $soma1 % 11;
        $digito1 = $resto1 < 2 ? 0 : 11 - $resto1;

        $soma2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma2 += (int) $cpf[$i] * (11 - $i);
        }
        $resto2 = $soma2 % 11;
        $digito2 = $resto2 < 2 ? 0 : 11 - $resto2;

        return $cpf[9] == $digito1 && $cpf[10] == $digito2;
    }

    // Valida Data (Formato: dd/mm/yyyy)
    public static function validarData(string $data): bool {
        $partes = explode('/', $data);
        if (count($partes) != 3) {
            return false;
        }

        list($dia, $mes, $ano) = $partes;
        return checkdate((int) $mes, (int) $dia, (int) $ano);
    }

    // Valida se é um número inteiro
    public static function validarInteiro($valor): bool {
        return is_int($valor) || (is_string($valor) && preg_match('/^\d+$/', $valor));
    }

    // Valida se é um valor numérico (float)
    public static function validarNumero($valor): bool {
        return is_numeric($valor);
    }

    // Valida se o campo está vazio
    public static function validarNaoVazio($valor): bool {
        return !empty($valor);
    }

    // Valida se o valor é um array
    public static function validarArray($valor): bool {
        return is_array($valor);
    }

    // Valida CPF ou CNPJ (exemplo simples)
    public static function validarCPFouCNPJ(string $documento): bool {
        return self::validarCPF($documento) || self::validarCNPJ($documento);
    }

    // Valida CNPJ
    public static function validarCNPJ(string $cnpj): bool {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Algoritmo de validação do CNPJ (simplificado para exemplo)
        $soma = 0;
        $multiplicadores = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $multiplicadores[$i];
        }

        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $multiplicadores[$i];
        }

        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }
}
