<?php

namespace app\controllers;

use DateTime;
use Throwable;
use app\classes\http\HttpStatusCode;
use app\classes\Model;
use app\services\Service;

abstract class Controller {
    private Service $service;

    public function __construct(Service $service) {
        $this->setService($service);
    }

    protected function service() {
        return $this->service;
    }

    protected function setService(Service $service) {
        $this->service = $service;
    }

    abstract protected function criar(array $dados);

    public function novo(array $dados, $args, $parametros) {
        $idRecursoPai = isset($args['id']) ? intval($args['id']) : null;
        $objeto = $this->criar($dados);
        $this->service()->salvar($objeto, $idRecursoPai);

        return $this->resposta(HttpStatusCode::CREATED, [
            'message' => 'Cadastrado com sucesso.'
        ]);
    }

    public function editar(array $dados, $args, $parametros) {
        $id = intval($args['id']);

        $objeto = $this->criar($dados);
        $objeto->setId($id);
        $this->service()->salvar($objeto);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Atualizado com suceso.'
        ]);
    }

    public function obterTodos(array $dados, $args, array $parametros) {
        $objeto = $this->service()->obterComRestricoes($parametros);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Sucesso ao obter os dados.',
            'data' => [
                $objeto
            ]
        ]);
    }

    public function obterComId(array $dados, $args) {
        $id = intval($args['id']);
        $objeto = $this->service()->obterComId($id);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Sucesso ao obter os dados.',
            'data' => [
                $objeto
            ]
        ]);
    }

    public function excluirComId(array $dados, $args, $parametros) {
        $id = intval($args['id']);
        $this->service()->excluirComId($id);

        return $this->resposta(HttpStatusCode::NO_CONTENT);
    }

    protected function povoarSimples(Model $objeto, array $campos, array $dados) {
        foreach ($campos as $campo) {
            if (isset($dados[$campo])) {
                $metodo = 'set' . ucfirst($campo);
                if (method_exists($objeto, $metodo)) {
                    try {
                        $objeto->$metodo($dados[$campo]);
                    } catch (Throwable $e) {
                    }
                }
            }
        }
    }

    protected function povoarDateTime(Model $objeto, array $campos, array $dados) {
        foreach ($campos as $campo) {
            if (isset($dados[$campo])) {
                $metodo = 'set' . ucfirst($campo);
                if (method_exists($objeto, $metodo)) {
                    $data = DateTime::createFromFormat('d/m/Y', $dados[$campo]);
                    if ($data) {
                        $objeto->$metodo($data);
                    }
                }
            }
        }
    }

    protected function resposta(HttpStatusCode $status = HttpStatusCode::OK, array $data = []) {
        return [
            'status' => $status,
            'data' => $data
        ];
    }
}
