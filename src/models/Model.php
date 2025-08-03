<?php

namespace src\models;

use JsonSerializable;

abstract class Model implements JsonSerializable {
    abstract public function emArray(): array;

    public function jsonSerialize(): mixed {
        return $this->emArray();
    }
}
