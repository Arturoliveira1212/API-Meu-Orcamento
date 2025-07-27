<?php

namespace app\dao;

use PDO;
use PDOException;
use Throwable;

class BancoDadosRelacional implements BancoDados {
    private ?PDO $pdo = null;

    public const ID_INEXISTENTE = 0;

    public function __construct() {
        $this->pdo = PDOSingleton::get();
    }

    private function rodar(string $comando, array $parametros = []) {
        try {
            $stmt = $this->pdo->prepare($comando);
            $stmt->execute($parametros);

            return $stmt;
        } catch (PDOException $e) {
            // TO DO => Salvar erros em tabela de log
            throw $e;
        }
    }

    public function executar(string $comando, array $parametros = []) {
        $stmt = $this->rodar($comando, $parametros);
        return $stmt->rowCount();
    }

    public function consultar(string $comando, array $parametros = []) {
        $stmt = $this->rodar($comando, $parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir(string $tabela, int $id) {
        $comando = "DELETE FROM $tabela WHERE id = :id";
        $parametros = ['id' => $id];

        return $this->executar($comando, $parametros);
    }

    public function desativar(string $tabela, int $id) {
        $comando = "UPDATE $tabela SET ativo = :ativo WHERE id = :id";
        $parametros = ['ativo' => 0, 'id' => $id];

        return $this->executar($comando, $parametros);
    }

    public function existe(string $tabela, string $campo, string $valor) {
        $comando = "SELECT COUNT(*) as quantidadeRegistros FROM $tabela WHERE $campo = :valor AND ativo = :ativo";
        $parametros = ['valor' => $valor, 'ativo' => 1];

        $resultado = $this->consultar($comando, $parametros)[0];
        if (isset($resultado['quantidadeRegistros']) && $resultado['quantidadeRegistros'] > 0) {
            return true;
        }

        return false;
    }

    public function executarComTransacao(callable $operacao) {
        $transacaoAtiva = $this->emTransacao();
        $resultado = null;

        try {
            if (!$transacaoAtiva) {
                $this->iniciarTransacao();
            }

            $resultado = $operacao();

            if (!$transacaoAtiva) {
                $this->finalizarTransacao();
            }
        } catch (Throwable $e) {
            if (!$transacaoAtiva) {
                $this->desfazerTransacao();
            }

            throw $e;
        }

        return $resultado;
    }

    public function ultimoIdInserido() {
        return $this->pdo->lastInsertId();
    }

    public function iniciarTransacao() {
        $this->pdo->beginTransaction();
    }

    public function finalizarTransacao() {
        $this->pdo->commit();
    }

    public function desfazerTransacao() {
        $this->pdo->rollBack();
    }

    public function emTransacao() {
        return $this->pdo->inTransaction();
    }
}
