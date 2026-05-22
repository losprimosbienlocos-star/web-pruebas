<?php
function conectar()
{
    $server  = getenv('DB_HOST') ?: "localhost";
    $usuario = getenv('DB_USER') ?: 'root';
    $pass    = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : "";
    $bdd     = getenv('DB_NAME') ?: "cengi_cursos";
    $port    = getenv('DB_PORT') ?: "3306";
    
    $con = mysqli_connect($server, $usuario, $pass, $bdd, $port) or die("error en la conexion" . mysqli_error($con));
    
    if (!mysqli_set_charset($con, "utf8")) {
        printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($con));
        exit();
    }

    return $con;
}
?>
