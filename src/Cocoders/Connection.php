<?php

namespace Cocoders;


interface Connection
{
    public function beginTransaction();
    public function execute($sql, $boundedParams = []);
    public function commit();
    public function rollback();
} 