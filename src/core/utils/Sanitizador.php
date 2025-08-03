<?php

namespace app\classes\utils;

abstract class Sanitizador {
    public static function limparArray(array $array) {
        $novoArray = [];

        foreach ($array as $chave => $valor) {
            self::limparValor($chave);
            if ($chave === '') {
                continue;
            }

            if (is_array($valor)) {
                self::limparArray($valor);
            } elseif (!is_numeric($valor)) {
                self::limparValor($valor);
            }

            $novoArray[$chave] = $valor;
        }

        return $novoArray;
    }

    public static function limparValor(&$valor) {
        $valor = htmlspecialchars(strip_tags(trim($valor)));
    }
}
