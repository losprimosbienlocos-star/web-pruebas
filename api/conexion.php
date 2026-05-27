<?php

function conectar()
{
    $server  = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST');
    $usuario = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER');
    $pass    = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
    $bdd     = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME');
    $port    = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: "5432";

    $conexion = "
        host=$server
        port=$port
        dbname=$bdd
        user=$usuario
        password=$pass
        sslmode=require
    ";

    $con = pg_connect($conexion);

    if (!$con) {
        die("Error en la conexión a PostgreSQL");
    }

    return $con;
}
?>
