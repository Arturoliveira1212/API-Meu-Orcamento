<?php

namespace app\middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;
use app\classes\http\RespostaHttp;
use app\classes\http\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorpoRequisicaoMiddleware implements MiddlewareInterface {
    private string $formato;
    private array $camposObrigatorios;
    private array $camposOpcionais;

    public function __construct(string $formato, array $camposObrigatorios = [], array $camposOpcionais = []) {
        $this->formato = $formato;
        $this->camposObrigatorios = $camposObrigatorios;
        $this->camposOpcionais = $camposOpcionais;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $contentType = $request->getHeaderLine('Content-Type');
        $corpoRequisicao = $request->getParsedBody();

        if (empty($corpoRequisicao) || !$this->validarFormato($contentType)) {
            return $this->corpoRequisicaoInvalido([
                'message' => 'O corpo da requisição deve ser em JSON válido.'
            ]);
        }

        $erros = [];
        $corpoRequisicaoValidado = $this->validarCorpoRequisicao($corpoRequisicao, $erros);
        if (!empty($erros)) {
            return $this->corpoRequisicaoInvalido([
                'message' => 'O corpo da requisição é inválido.',
                'data' => [
                    'erros' => $erros
                ]
            ]);
        }

        $request = $request->withParsedBody($corpoRequisicaoValidado);

        return $handler->handle($request);
    }

    private function corpoRequisicaoInvalido(array $data) {
        return RespostaHttp::enviarResposta(new Response(), HttpStatusCode::BAD_REQUEST, $data);
    }

    private function validarFormato(string $contentType) {
        return strpos($contentType, $this->formato) !== false;
    }

    private function validarCorpoRequisicao(array $corpoRequisicao, array &$erros = []) {
        $corpoRequisicaoValidado = [];
        $erros = [];

        if (!empty($this->camposObrigatorios)) {
            $this->validarCampos(
                $this->camposObrigatorios,
                $corpoRequisicao,
                $corpoRequisicaoValidado,
                $erros,
                true
            );
        }

        if (!empty($this->camposOpcionais)) {
            $this->validarCampos(
                $this->camposOpcionais,
                $corpoRequisicao,
                $corpoRequisicaoValidado,
                $erros,
                false
            );
        }

        return $corpoRequisicaoValidado;
    }

    private function validarCampos(array $campos, array $corpoRequisicao, array &$corpoRequisicaoValidado, array &$erros, bool $campoObrigatorio) {
        foreach ($campos as $campo => $tipo) {
            if (!isset($corpoRequisicao[$campo])) {
                if ($campoObrigatorio) {
                    $erros[$campo] = "Campo {$campo} não foi enviado.";
                }
                continue;
            }

            if (is_array($tipo)) {
                if (is_array($corpoRequisicao[$campo])) {
                    $errosFilho = [];
                    $corpoRequisicaoValidadoFilho = [];

                    $this->validarCampos(
                        $tipo,
                        $corpoRequisicao[$campo],
                        $corpoRequisicaoValidadoFilho,
                        $errosFilho,
                        $campoObrigatorio
                    );

                    if (!empty($errosFilho)) {
                        $erros[$campo] = $errosFilho;
                    }

                    if (!empty($corpoRequisicaoValidadoFilho)) {
                        $corpoRequisicaoValidado[$campo] = $corpoRequisicaoValidadoFilho;
                    }
                } else {
                    $erros[$campo] = "Campo {$campo} deve ser do tipo array.";
                }
            } elseif (!$this->tipoValido($corpoRequisicao[$campo], $tipo)) {
                $erros[$campo] = "Campo {$campo} deve ser do tipo {$tipo}.";
            } else {
                $corpoRequisicaoValidado[$campo] = $corpoRequisicao[$campo];
            }
        }
    }

    private function tipoValido($valor, $tipo) {
        switch ($tipo) {
            case 'string':
                return is_string($valor);
            case 'numeric':
                return is_numeric($valor);
            case 'int':
                return is_int($valor);
            case 'float':
                return is_float($valor);
            case 'bool':
                return is_bool($valor);
            default:
                return false;
        }
    }
}
