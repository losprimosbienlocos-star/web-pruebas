<?php

require_once "conexion.php";

$mysqli = conectar();

// =====================================
// RECIBIR DATOS
// =====================================

$nombre_participante = trim($_POST['nombre_participante'] ?? '');

$cui_participante = trim($_POST['cui_participante'] ?? '');

$puesto_participante = trim($_POST['puesto_participante'] ?? '');

$area_participante = trim($_POST['area_participante'] ?? '');

$correo = trim($_POST['correo'] ?? '');

$telefono = trim($_POST['telefono'] ?? '');
$pais = trim($_POST['pais'] ?? '');

$grado_academico_id = !empty($_POST['grado_academico_id'])
    ? (int) $_POST['grado_academico_id']
    : NULL;

$ha_participado_antes = isset($_POST['ha_participado_antes'])
    ? (int) $_POST['ha_participado_antes']
    : 0;

$cursos_participados = trim($_POST['cursos_participados'] ?? '');

$como_se_entero = trim($_POST['como_se_entero'] ?? '');
$otro_ingenio = trim($_POST['otro_ingenio'] ?? '');

$ingenio_id = !empty($_POST['ingenio_id'])
    ? (int) $_POST['ingenio_id']
    : NULL;


// =====================================
// ARRAY DE CURSOS
// =====================================

$cursos = array_map('intval', $_POST['curso_id'] ?? []);

$tipo_pago = trim($_POST['tipo_pago'] ?? '');

if ($nombre_participante === '' || $cui_participante === '' || empty($cursos) || $tipo_pago === '') {
    http_response_code(400);
    die('Faltan datos obligatorios para registrar la inscripción.');
}


$ingenioName = "No especificado";
if (!empty($ingenio_id)) {
    $stmtIng = $mysqli->prepare("SELECT nombre_ingenios FROM ingenios WHERE id = ?");
    $stmtIng->bind_param('i', $ingenio_id);
    $stmtIng->execute();
    $resIng = $stmtIng->get_result();
    if ($row = $resIng->fetch_assoc()) {
        $ingenioName = $row['nombre_ingenios'];
    }
    $stmtIng->close();
}

if (strtolower(trim($ingenioName)) === 'otros' && $otro_ingenio === '') {
    http_response_code(400);
    die('Debe especificar el nombre del ingenio.');
}

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
    pais,
    grado_academico_id,
    ha_participado_antes,
    cursos_participados,
    como_se_entero,
    ingenio_id,
    otro_ingenio,
    curso_id,
    tipo_pago

)

VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

";

// =====================================
// RECORRER CURSOS
// =====================================

foreach($cursos as $curso_id){

    $stmt = $mysqli->prepare($sql);

$stmt->bind_param(

    "sssssssisssisis",

    $nombre_participante,
    $cui_participante,
    $puesto_participante,
    $area_participante,
    $correo,
    $telefono,
    $pais,
    $grado_academico_id,
    $ha_participado_antes,
    $cursos_participados,
    $como_se_entero,
    $ingenio_id,
    $otro_ingenio,
    $curso_id,
    $tipo_pago

);

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
// CERRAR INSERCIÓN
// =====================================

$stmt->close();

// =====================================
// OBTENER NOMBRES REALES DE LOS DATOS ENVIADOS
// =====================================

$cursoNames = [];
if (!empty($cursos)) {
    $placeholders = implode(',', array_fill(0, count($cursos), '?'));
    $stmtCursos = $mysqli->prepare("SELECT nombre_cursos FROM cursos WHERE id IN ($placeholders)");
    $types = str_repeat('i', count($cursos));
    $stmtCursos->bind_param($types, ...$cursos);
    $stmtCursos->execute();
    $resCursos = $stmtCursos->get_result();
    while ($row = $resCursos->fetch_assoc()) {
        $cursoNames[] = $row['nombre_cursos'];
    }
    $stmtCursos->close();
}

$ingenioResumen = $ingenioName;
if (strtolower(trim($ingenioName)) === 'otros' && $otro_ingenio !== '') {
    $ingenioResumen .= ' - ' . $otro_ingenio;
}

$gradoAcademico = 'No especificado';

if (!empty($grado_academico_id)) {

    $stmtGrado = $mysqli->prepare(
        "SELECT nombre_grado 
         FROM grado_academico 
         WHERE id = ?"
    );

    $stmtGrado->bind_param(
        'i',
        $grado_academico_id
    );

    $stmtGrado->execute();

    $resGrado = $stmtGrado->get_result();

    if ($row = $resGrado->fetch_assoc()) {

        $gradoAcademico =
            $row['nombre_grado'];
    }

    $stmtGrado->close();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Inscripción Exitosa - Cengicaña Digital</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#03251d",
                        secondary: "#326b00",
                        background: "#f4f7f6",
                        surface: "#ffffff"
                    },
                    fontFamily: {
                        body: ["Montserrat"]
                    }
                }
            }
        }
    </script>
    <style>
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }
        .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid #4CAF50;
        }
        .check-icon::after {
            content: '';
            position: absolute;
            left: 28px;
            top: 14px;
            width: 16px;
            height: 32px;
            border: solid #4CAF50;
            border-width: 0 4px 4px 0;
            transform: rotate(45deg);
        }
    </style>
</head>
<body class="bg-background font-body text-gray-800 flex items-center justify-center min-h-screen py-10 px-4">

    <div class="max-w-xl w-full bg-white border border-gray-200 rounded-3xl shadow-2xl p-6 md:p-10 text-center relative overflow-hidden">
        
        <!-- Decoración Superior -->
        <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-primary to-secondary"></div>

        <!-- Checkmark Animado -->
        <div class="success-checkmark mb-6">
            <div class="check-icon flex items-center justify-center bg-green-50">
                <!-- Se dibuja el checkmark por CSS -->
            </div>
        </div>

        <h2 class="text-3xl font-extrabold text-primary mb-2">
            ¡Inscripción Recibida!
        </h2>
        <p class="text-gray-500 mb-8 text-sm md:text-base">
            Tu solicitud de inscripción ha sido registrada exitosamente en nuestra base de datos.
        </p>

        <!-- Detalle de Datos Enviados -->
        <div class="text-left bg-gray-50 border border-gray-100 rounded-2xl p-5 mb-8 space-y-4">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                Resumen de Datos Enviados
            </h3>
            
            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm font-medium">Nombre:</span>
                <span class="col-span-2 text-primary font-bold text-sm"><?= htmlspecialchars($nombre_participante) ?></span>
            </div>

            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm font-medium">CUI:</span>
                <span class="col-span-2 text-gray-700 font-mono text-sm"><?= htmlspecialchars($cui_participante) ?></span>
            </div>

            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm font-medium">Puesto / Área:</span>
                <span class="col-span-2 text-gray-700 text-sm"><?= htmlspecialchars($puesto_participante) ?> (<?= htmlspecialchars($area_participante) ?>)</span>
            </div>

            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm font-medium">Ingenio:</span>
                <span class="col-span-2 text-gray-700 text-sm"><?= htmlspecialchars($ingenioResumen) ?></span>
            </div>

            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm font-medium">Contacto:</span>
                <span class="col-span-2 text-gray-700 text-sm"><?= htmlspecialchars($correo) ?> / <?= htmlspecialchars($telefono) ?></span>
            </div>

            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
    <span class="text-gray-500 text-sm font-medium">País:</span>
    <span class="col-span-2 text-gray-700 text-sm">
        <?= htmlspecialchars($pais) ?>
    </span>
</div>

<div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
    <span class="text-gray-500 text-sm font-medium">Grado:</span>
    <span class="col-span-2 text-gray-700 text-sm">
        <?= htmlspecialchars($gradoAcademico) ?>
    </span>
</div>

<div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
    <span class="text-gray-500 text-sm font-medium">
        Participó Antes:
    </span>

    <span class="col-span-2 text-gray-700 text-sm">
        <?= $ha_participado_antes ? 'Sí' : 'No' ?>
    </span>
</div>

<?php if ($ha_participado_antes && $cursos_participados !== ''): ?>

<div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">

    <span class="text-gray-500 text-sm font-medium">
        Cursos Anteriores:
    </span>

    <span class="col-span-2 text-gray-700 text-sm">
        <?= nl2br(htmlspecialchars($cursos_participados)) ?>
    </span>

</div>

<?php endif; ?>

<div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">

    <span class="text-gray-500 text-sm font-medium">
        Se Enteró Por:
    </span>

    <span class="col-span-2 text-gray-700 text-sm">
        <?= htmlspecialchars($como_se_entero) ?>
    </span>

</div>

            <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm font-medium">Tipo Pago:</span>
                <span class="col-span-2 text-gray-700 text-sm font-semibold"><?= htmlspecialchars($tipo_pago) ?></span>
            </div>

            <div class="pt-2">
                <span class="text-gray-500 text-sm font-medium block mb-2">Capacitaciones Inscritas:</span>
                <ul class="space-y-1 pl-4 list-disc text-primary font-bold text-sm">
                    <?php foreach ($cursoNames as $name): ?>
                        <li><?= htmlspecialchars($name) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="index.php" class="w-full sm:w-auto px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-opacity-90 transition flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Volver al Formulario
            </a>
        </div>

    </div>

</body>
</html>
