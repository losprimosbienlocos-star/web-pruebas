<?php
function conectar()
{
    $server  = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: "localhost";
    $usuario = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $pass    = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: "";
    $bdd     = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?: "cengi_cursos";
    $port    = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: "3306";
    
    $con = mysqli_connect($server, $usuario, $pass, $bdd, $port) or die("error en la conexion" . mysqli_error($con));
    
    if (!mysqli_set_charset($con, "utf8")) {
        printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($con));
        exit();
    }

    return $con;
}
?>
