<?php

namespace app\classes\factory;

use InvalidArgumentException;
use app\dao\BancoDadosRelacional;
use app\dao\DAO;
use app\services\Service;
use app\controllers\Controller;

abstract class ClassFactory {
    public const CAMINHO_CONTROLLER = 'app\\controllers\\';
    public const CAMINHO_SERVICE = 'app\\services\\';
    public const CAMINHO_DAO = 'app\\dao\\';

    /**
     * Método responsável por fabricar intâncias de controllers.
     *
     * @param string $nome
     * @throws InvalidArgumentException
     * @return Controller
     */
    public static function makeController(string $classe) {
        $nomeController = substr(strrchr($classe, '\\'), 1);
        $controller = self::CAMINHO_CONTROLLER . $nomeController . 'Controller';
        if (!class_exists($controller)) {
            throw new InvalidArgumentException("Controller $controller não encontrado.");
        }

        return new $controller(self::makeService($classe));
    }

    /**
     * Método responsável por fabricar intâncias de services.
     *
     * @param string $service
     * @throws InvalidArgumentException
     * @return Service
     */
    public static function makeService(string $classe) {
        $nomeService = substr(strrchr($classe, '\\'), 1);
        $service = self::CAMINHO_SERVICE . $nomeService . 'Service';
        if (!class_exists($service)) {
            throw new InvalidArgumentException("Service $service não encontrado.");
        }

        return new $service(self::makeDAO($classe));
    }

    /**
     * Método responsável por fabricar intâncias de DAOs.
     *
     * @param string $nomeDAO
     * @throws InvalidArgumentException
     * @return DAO
     */
    public static function makeDAO(string $classe) {
        $nomeDAO = substr(strrchr($classe, '\\'), 1);
        $DAO = self::CAMINHO_DAO . $nomeDAO . 'DAO';
        if (!class_exists($DAO)) {
            throw new InvalidArgumentException("DAO $classe não encontrado.");
        }

        return new $DAO(new BancoDadosRelacional());
    }
}
