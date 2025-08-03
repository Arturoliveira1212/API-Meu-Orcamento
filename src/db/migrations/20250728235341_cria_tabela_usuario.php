<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CriaTabelaUsuario extends AbstractMigration {

    public function up(): void {
        $sql = <<<'SQL'
            CREATE TABLE usuario (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                ativo BOOLEAN NOT NULL DEFAULT TRUE
            ) ENGINE=INNODB;
        SQL;
        $this->execute( $sql );
    }

    public function down(): void {
        $this->execute( 'DROP TABLE usuario' );
    }
}
