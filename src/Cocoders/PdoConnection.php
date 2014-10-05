<?php

namespace Cocoders;

class PdoConnection implements Connection
{
    private $pdo;

    public function __construct($dsl)
    {
        $this->pdo = new \PDO($dsl);
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function execute($sql, $boundedParams = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($boundedParams);

        return $stmt->fetchAll();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollBack();
    }
}