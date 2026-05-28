<?php
require_once "conexion.php";
$mysqli = conectar();

// Obtener todas las solicitudes registradas
$sql = "
    SELECT 
        s.*, 
        i.nombre_ingenios AS ingenio, 
        c.nombre_cursos AS curso 
    FROM solicitudes_inscripcion s
    LEFT JOIN ingenios i ON s.ingenio_id = i.id
    LEFT JOIN cursos c ON s.curso_id = c.id
    ORDER BY s.fecha_solicitud DESC
";

$result = $mysqli->query($sql);
$mysqli->close();

$solicitudes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
}

function nombre_ingenio_solicitud($row)
{
    $ingenio = $row['ingenio'] ?? 'No especificado';
    if (strtolower(trim($ingenio)) === 'otros' && !empty($row['otro_ingenio'])) {
        $ingenio .= ' - ' . $row['otro_ingenio'];
    }

    return $ingenio;
}

if (isset($_GET['download']) && $_GET['download'] === 'excel') {
    $filename = 'solicitudes-cengicursos-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        'ID',
        'Fecha',
        'Participante',
        'CUI',
        'Ingenio',
        'Puesto',
        'Area',
        'Curso inscrito',
        'Tipo de pago',
        'Correo',
        'Telefono',
        'Estado',
    ]);

    foreach ($solicitudes as $row) {
        fputcsv($output, [
            $row['id_solicitud'] ?? '',
            !empty($row['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($row['fecha_solicitud'])) : '',
            $row['nombre_participante'] ?? '',
            $row['cui_participante'] ?? '',
            nombre_ingenio_solicitud($row),
            $row['puesto_participante'] ?? '',
            $row['area_participante'] ?? '',
            $row['curso'] ?? 'Desconocido',
            $row['tipo_pago'] ?? '',
            $row['correo'] ?? '',
            $row['telefono'] ?? '',
            $row['estado'] ?? '',
        ]);
    }

    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Panel de Administración - Solicitudes</title>
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
</head>
<body class="bg-background font-body text-gray-800 p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 bg-white border border-gray-200 rounded-3xl p-6 shadow-md">
            <div>
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-secondary text-3xl">dashboard</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-primary">Control de Solicitudes</h1>
                </div>
                <p class="text-gray-500 text-sm mt-1">Monitoreo en tiempo real de inscripciones registradas en Supabase.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="?download=excel" class="px-5 py-2.5 bg-secondary text-white font-bold rounded-xl hover:bg-opacity-95 transition flex items-center justify-center gap-2 shadow-md">
                    <span class="material-symbols-outlined text-sm">download</span>
                    Descargar Excel
                </a>
                <a href="index.php" class="px-5 py-2.5 bg-primary text-white font-bold rounded-xl hover:bg-opacity-95 transition flex items-center gap-2 shadow-md">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Nueva Inscripción
                </a>
            </div>
        </header>

        <!-- Tabla de Registros -->
        <div class="bg-white border border-gray-200 rounded-3xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-primary text-white text-xs font-bold uppercase tracking-wider">
                            <th class="p-4">ID</th>
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Participante</th>
                            <th class="p-4">CUI</th>
                            <th class="p-4">Ingenio</th>
                            <th class="p-4">Puesto / Área</th>
                            <th class="p-4">Curso Inscrito</th>
                            <th class="p-4">Pago</th>
                            <th class="p-4">Contacto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 text-sm">
                        <?php if (count($solicitudes) > 0): ?>
                            <?php foreach ($solicitudes as $row): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-400">#<?= $row['id_solicitud'] ?></td>
                                    <td class="p-4 text-gray-500 text-xs">
                                        <?= date("d/m/Y H:i", strtotime($row['fecha_solicitud'])) ?>
                                    </td>
                                    <td class="p-4 font-bold text-primary">
                                        <?= htmlspecialchars($row['nombre_participante']) ?>
                                    </td>
                                    <td class="p-4 font-mono text-xs text-gray-600">
                                        <?= htmlspecialchars($row['cui_participante']) ?>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 bg-green-50 text-secondary font-bold rounded-lg text-xs">
                                            <?= htmlspecialchars(nombre_ingenio_solicitud($row)) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-600 text-xs">
                                        <?= htmlspecialchars($row['puesto_participante']) ?><br>
                                        <span class="text-gray-400 font-medium"><?= htmlspecialchars($row['area_participante']) ?></span>
                                    </td>
                                    <td class="p-4 font-semibold text-primary">
                                        <?= htmlspecialchars($row['curso'] ?? 'Desconocido') ?>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 <?= $row['tipo_pago'] === 'Ingenio' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' ?> font-bold rounded-lg text-xs">
                                            <?= htmlspecialchars($row['tipo_pago']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs text-gray-600">
                                        <?= htmlspecialchars($row['correo']) ?><br>
                                        <span class="text-gray-400 font-medium"><?= htmlspecialchars($row['telefono']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="p-10 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl block mb-2">inbox</span>
                                    No hay solicitudes registradas en la base de datos de la nube.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
