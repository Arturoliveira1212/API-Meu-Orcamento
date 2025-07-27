<?php

namespace app\classes\http;

use Slim\Psr7\Response;

abstract class RespostaHttp {
    public const HEADERS_PADRAO = [
        'Content-Type' => 'application/json'
    ];

    public static function enviarResposta(Response $response, HttpStatusCode $status = HttpStatusCode::OK, array $data = [], array $headers = []) {
        $headers = array_merge(self::HEADERS_PADRAO, $headers);

        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        if (!empty($data)) {
            $response->getBody()->write(json_encode([
                'sucess' => HttpStatusCode::statusEhSucesso($status->value),
                ...$data
            ]));
        }

        return $response->withStatus($status->value);
    }
}
