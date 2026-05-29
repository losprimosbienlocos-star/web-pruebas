<?php
require_once "conexion.php";
$mysqli = conectar();

// Obtener todas las solicitudes registradas
$sql = "
    SELECT 
        s.*, 
        i.nombre_ingenios AS ingenio, 
        c.nombre_cursos AS curso,
        g.nombre_grado AS grado_academico
    FROM solicitudes_inscripcion s
    LEFT JOIN ingenios i ON s.ingenio_id = i.id
    LEFT JOIN cursos c ON s.curso_id = c.id
    LEFT JOIN grado_academico g ON s.grado_academico_id = g.id
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

function participacion_anterior($row)
{
    $valor = $row['ha_participado_antes'] ?? false;

    return ($valor === true || $valor === 1 || $valor === '1' || $valor === 't') ? 'Sí' : 'No';
}

function excel_column_name($index)
{
    $name = '';
    while ($index > 0) {
        $index--;
        $name = chr(65 + ($index % 26)) . $name;
        $index = intdiv($index, 26);
    }

    return $name;
}

function xlsx_cell($column, $row, $value, $style = 0)
{
    $reference = excel_column_name($column) . $row;
    $value = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', (string) $value);
    $value = htmlspecialchars($value, ENT_XML1 | ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
    $styleAttribute = $style > 0 ? ' s="' . $style . '"' : '';

    return '<c r="' . $reference . '" t="inlineStr"' . $styleAttribute . '><is><t>' . $value . '</t></is></c>';
}

function build_excel_download($solicitudes)
{
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        die('El servidor no tiene habilitada la extension ZIP necesaria para generar Excel.');
    }

    $headers = [
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
        'Pais',
        'Grado academico',
        'Participo antes',
        'Cursos anteriores',
        'Como se entero',
        'Estado',
    ];

    $rows = [];
    foreach ($solicitudes as $row) {
        $rows[] = [
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
            $row['pais'] ?? '',
            $row['grado_academico'] ?? 'No especificado',
            participacion_anterior($row),
            $row['cursos_participados'] ?? '',
            $row['como_se_entero'] ?? '',
            $row['estado'] ?? '',
        ];
    }

    $lastColumn = excel_column_name(count($headers));
    $lastRow = max(count($rows) + 2, 2);

    $sheetRows = [];
    $sheetRows[] = '<row r="1"><c r="A1" t="inlineStr" s="1"><is><t>Solicitudes CENGICURSOS</t></is></c></row>';

    $headerCells = [];
    foreach ($headers as $index => $header) {
        $headerCells[] = xlsx_cell($index + 1, 2, $header, 2);
    }
    $sheetRows[] = '<row r="2">' . implode('', $headerCells) . '</row>';

    foreach ($rows as $rowIndex => $row) {
        $excelRow = $rowIndex + 3;
        $cells = [];
        foreach ($row as $columnIndex => $value) {
            $cells[] = xlsx_cell($columnIndex + 1, $excelRow, $value, 3);
        }
        $sheetRows[] = '<row r="' . $excelRow . '">' . implode('', $cells) . '</row>';
    }

    $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>'
        . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="15"/>'
        . '<cols>'
        . '<col min="1" max="1" width="10" customWidth="1"/>'
        . '<col min="2" max="2" width="18" customWidth="1"/>'
        . '<col min="3" max="3" width="28" customWidth="1"/>'
        . '<col min="4" max="4" width="18" customWidth="1"/>'
        . '<col min="5" max="8" width="26" customWidth="1"/>'
        . '<col min="9" max="17" width="20" customWidth="1"/>'
        . '</cols>'
        . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
        . '</worksheet>';

    $tempFile = tempnam(sys_get_temp_dir(), 'cengicursos-xlsx-');
    $zip = new ZipArchive();
    if ($zip->open($tempFile, ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        die('No se pudo generar el archivo Excel.');
    }

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Solicitudes" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Arial"/></font><font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Arial"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font></fonts><fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF326B00"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF03251D"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFB7B7B7"/></left><right style="thin"><color rgb="FFB7B7B7"/></right><top style="thin"><color rgb="FFB7B7B7"/></top><bottom style="thin"><color rgb="FFB7B7B7"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
    $zip->close();

    $content = file_get_contents($tempFile);
    unlink($tempFile);

    return $content;
}

if (isset($_GET['download']) && $_GET['download'] === 'excel') {
    $filename = 'solicitudes-cengicursos-' . date('Y-m-d') . '.xlsx';
    $content = build_excel_download($solicitudes);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $content;
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
                            <th class="p-4">Datos Académicos</th>
                            <th class="p-4">Participación</th>
                            <th class="p-4">Se Enteró Por</th>
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
                                    <td class="p-4 text-xs text-gray-600">
                                        <span class="font-bold text-primary"><?= htmlspecialchars($row['grado_academico'] ?? 'No especificado') ?></span><br>
                                        <span class="text-gray-400 font-medium"><?= htmlspecialchars($row['pais'] ?? 'No especificado') ?></span>
                                    </td>
                                    <td class="p-4 text-xs text-gray-600">
                                        <span class="font-bold text-primary"><?= participacion_anterior($row) ?></span>
                                        <?php if (participacion_anterior($row) === 'Sí' && !empty($row['cursos_participados'])): ?>
                                            <br><span class="text-gray-400 font-medium"><?= nl2br(htmlspecialchars($row['cursos_participados'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-xs text-gray-600">
                                        <?= htmlspecialchars($row['como_se_entero'] ?? 'No especificado') ?>
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
                                <td colspan="12" class="p-10 text-center text-gray-400">
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
