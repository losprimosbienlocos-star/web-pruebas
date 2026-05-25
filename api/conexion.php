<?php
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
