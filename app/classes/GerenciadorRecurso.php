<?php

namespace app\classes;

use app\classes\http\HttpMethod;
use app\middlewares\AutenticacaoMiddleware;
use Slim\App;
use Throwable;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use app\classes\http\RespostaHttp;
use app\classes\http\HttpStatusCode;
use app\exceptions\ServiceException;
use app\classes\factory\ClassFactory;
use app\exceptions\NaoAutorizadoException;
use app\exceptions\NaoEncontradoException;

class GerenciadorRecurso {
    private App $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    public function post(string $caminho, string $controller, string $metodo, array $middlewares = []) {
        $this->adicionar(HttpMethod::POST, $caminho, $controller, $metodo, $middlewares);
    }

    public function put(string $caminho, string $controller, string $metodo, array $middlewares = []) {
        $this->adicionar(HttpMethod::PUT, $caminho, $controller, $metodo, $middlewares);
    }

    public function delete(string $caminho, string $controller, string $metodo, array $middlewares = []) {
        $this->adicionar(HttpMethod::DELETE, $caminho, $controller, $metodo, $middlewares);
    }

    public function get(string $caminho, string $controller, string $metodo, array $middlewares = []) {
        $this->adicionar(HttpMethod::GET, $caminho, $controller, $metodo, $middlewares);
    }

    private function adicionar(HttpMethod $metodoHttp, string $caminho, string $controller, string $metodo, array $middlewares = []) {
        $rota = $this->app->map(
            [$metodoHttp->value],
            $caminho,
            function (Request $request, Response $response, $args) use ($controller, $metodo) {
                return $this->executar($controller, $metodo, $request, $response, $args);
            }
        );

        if (!empty($middlewares)) {
            $middlewares = array_reverse($middlewares);
            foreach ($middlewares as $middleware) {
                $rota->add($middleware);
            }
        }
    }

    private function executar(string $controller, string $metodo, Request $request, Response $response, $args) {
        try {
            $resposta = new Response();
            $corpoRequisicao = (array) $request->getParsedBody();
            $parametros = (array) $request->getQueryParams();
            $payloadJWT = $request->getAttribute('payloadJWT');

            $controller = ClassFactory::makeController($controller);
            $retorno = $controller->$metodo($corpoRequisicao, $args, $parametros, $payloadJWT);

            $resposta = RespostaHttp::enviarResposta($response, $retorno['status'] ?? HttpStatusCode::OK, $retorno['data'] ?? []);
        } catch (NaoEncontradoException $e) {
            $resposta = RespostaHttp::enviarResposta($resposta, HttpStatusCode::NOT_FOUND, [
                'message' => $e->getMessage()
            ]);
        } catch (ServiceException $e) {
            $resposta = RespostaHttp::enviarResposta($resposta, HttpStatusCode::BAD_REQUEST, [
                'message' => 'Os dados enviados são inválidos.',
                'data' => [
                    'erros' => json_decode($e->getMessage(), true)
                ]
            ]);
        } catch (NaoAutorizadoException $e) {
            $resposta = RespostaHttp::enviarResposta($resposta, HttpStatusCode::UNAUTHORIZED, [
                'message' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            $resposta = RespostaHttp::enviarResposta($resposta, HttpStatusCode::INTERNAL_SERVER_ERROR, [
                'message' => 'Houve um erro interno.' . $e
            ]);
        } finally {
            return $resposta;
        }
    }
}
