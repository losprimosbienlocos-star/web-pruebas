<?php

require_once "conexion.php";

$mysqli = conectar();


// =====================================
// RECIBIR DATOS
// =====================================

$nombre_participante = $_POST['nombre_participante'];

$cui_participante = $_POST['cui_participante'];

$puesto_participante = $_POST['puesto_participante'];

$area_participante = $_POST['area_participante'];

$correo = $_POST['correo'];

$telefono = $_POST['telefono'];

$ingenio_id = !empty($_POST['ingenio_id'])
    ? $_POST['ingenio_id']
    : NULL;


// =====================================
// ARRAY DE CURSOS
// =====================================

$cursos = $_POST['curso_id'];

$tipo_pago = $_POST['tipo_pago'];


// =====================================
// SQL
// =====================================

$sql = "

INSERT INTO solicitudes_inscripcion
(

    nombre_participante,
    cui_participante,
    puesto_participante,
    area_participante,
    correo,
    telefono,
    ingenio_id,
    curso_id,
    tipo_pago

)

VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)

";


// =====================================
// RECORRER CURSOS
// =====================================

foreach($cursos as $curso_id){

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(

        "sssssssis",

        $nombre_participante,
        $cui_participante,
        $puesto_participante,
        $area_participante,
        $correo,
        $telefono,
        $ingenio_id,
        $curso_id,
        $tipo_pago

    );


// =====================================
// EJECUTAR
// =====================================

    if(!$stmt->execute()){

        die(
            "Error: "
            . $stmt->error
        );

    }

}


// =====================================
// CERRAR
// =====================================

$stmt->close();

$mysqli->close();


// =====================================
// REDIRECCIONAR
// =====================================

header("Location: index.php?ok=1");

exit();

?>
