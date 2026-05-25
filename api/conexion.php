<?php
function db_env($key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value === false || $value === null || $value === '') ? $default : $value;
}

function conectar()
{
    $server  = db_env('DB_HOST');
    $usuario = db_env('DB_USER');
    $pass    = db_env('DB_PASSWORD');
    $bdd     = db_env('DB_NAME');
    $port    = (int) db_env('DB_PORT', 3306);
    $useSsl  = strtolower((string) db_env('DB_SSL', 'false')) === 'true';

    if (!$server || !$usuario || !$bdd) {
        die('Faltan variables de base de datos en Vercel: DB_HOST, DB_USER, DB_PASSWORD, DB_NAME y DB_PORT.');
    }

    $con = mysqli_init();

    if ($useSsl) {
        mysqli_ssl_set($con, null, null, null, null, null);
    }

    $flags = $useSsl ? MYSQLI_CLIENT_SSL : 0;

    if (!mysqli_real_connect($con, $server, $usuario, $pass, $bdd, $port, null, $flags)) {
        die('Error en la conexion: ' . mysqli_connect_error());
    }

    if (!mysqli_set_charset($con, 'utf8mb4')) {
        die('Error cargando charset utf8mb4: ' . mysqli_error($con));
    }

    return $con;
}
?><?php
function conectar()
{
    $server  = "localhost"; // Cambiado de mysql a localhost
    $usuario = 'root';
    $pass    = "";          // Contraseña vacía para conectar sin errores
    $bdd     = "cengi_cursos";
    
    $con = mysqli_connect($server, $usuario, $pass, $bdd) or die("error en la conexion" . mysqli_error($con));
    
    if (!mysqli_set_charset($con, "utf8")) {
        printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($con));
        exit();
    }

    return $con;
}
?>
