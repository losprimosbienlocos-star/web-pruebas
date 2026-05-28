<?php

require_once "conexion.php";

// =====================================
// OBTENER INGENIOS
// =====================================

$ingenios = supabase_request(
    supabase_table('ingenios', 'select=*&order=nombre_ingenios.asc')
);

usort($ingenios, function ($a, $b) {
    $aEsOtros = strtolower(trim($a['nombre_ingenios'] ?? '')) === 'otros';
    $bEsOtros = strtolower(trim($b['nombre_ingenios'] ?? '')) === 'otros';

    if ($aEsOtros === $bEsOtros) {
        return strcasecmp($a['nombre_ingenios'] ?? '', $b['nombre_ingenios'] ?? '');
    }

    return $aEsOtros ? 1 : -1;
});


// =====================================
// FILTRO TIPO
// =====================================

$tiposPermitidos = ['Curso', 'Diplomado', 'Seminario'];
$tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], $tiposPermitidos, true)
    ? $_GET['tipo']
    : 'Curso';


// =====================================
// OBTENER CURSOS SEGUN TIPO
// =====================================

$cursos = supabase_request(
    supabase_table(
        'cursos',
        'select=*&order=nombre_cursos.asc'
    )
);

?>

<!DOCTYPE html>
<html class="light" lang="es">

<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>Solicitud de Inscripción</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet"/>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <link rel="stylesheet" href="styles.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#03251d",
                        secondary: "#326b00",
                        background: "#f8f9ff",
                        surface: "#ffffff",
                        outline: "#c1c8c4",
                        container: "#eff4ff"
                    },
                    fontFamily: {
                        body: ["Montserrat"]
                    }
                }
            }
        }
    </script>

</head>

<body class="bg-background font-body text-gray-800 overflow-x-hidden">

<!-- HERO -->

<section class="hero-section relative">

    <img
        src="css/images/formulario.jpeg"
        class="absolute inset-0 w-full h-full object-cover"
    >

    <div class="hero-overlay"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 md:px-10 h-full flex items-center">

        <div class="text-white max-w-2xl">

            <h2 class="text-4xl md:text-6xl font-extrabold mb-4">
                Solicitud de Inscripción
            </h2>

            <p class="text-lg md:text-xl text-white/90">
                Gestión moderna de capacitaciones industriales y agrícolas.
            </p>

        </div>

    </div>

</section>


<!-- CONTENIDO -->

<main class="max-w-7xl mx-auto px-4 md:px-10 -mt-16 relative z-20 pb-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- FORMULARIO -->

        <div class="lg:col-span-8 bg-white border border-outline rounded-2xl shadow-lg p-6 md:p-8">

            <div class="flex items-center gap-3 mb-8">

                <span class="material-symbols-outlined text-primary text-3xl">
                    assignment_ind
                </span>

                <h2 class="text-3xl font-bold text-primary">
                    Datos del Participante
                </h2>

            </div>

            <form action="guardar_inscripcion.php" method="POST" id="formInscripcion">

                <!-- NOMBRE Y CUI -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="label-form">
                            Nombre Participante
                        </label>

                        <input
                            type="text"
                            name="nombre_participante"
                            class="input-form"
                            required
                        >
                    </div>

                    <div>
                        <label class="label-form">
                            CUI Participante
                        </label>

                        <input
                            type="text"
                            name="cui_participante"
                            class="input-form"
                            required
                        >
                    </div>

                </div>

                <!-- PUESTO Y AREA -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

                    <div>
                        <label class="label-form">
                            Puesto Participante
                        </label>

                        <input
                            type="text"
                            name="puesto_participante"
                            class="input-form"
                            required
                        >
                    </div>

                    <div>
                        <label class="label-form">
                            Área Participante
                        </label>

                        <input
                            type="text"
                            name="area_participante"
                            class="input-form"
                            required
                        >
                    </div>

                </div>

                <!-- CORREO Y TELEFONO -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

                    <div>
                        <label class="label-form">
                            Correo
                        </label>

                        <input
                            type="email"
                            name="correo"
                            class="input-form"
                            required
                        >
                    </div>

                    <div>
                        <label class="label-form">
                            Teléfono
                        </label>

                        <input
                            type="text"
                            name="telefono"
                            class="input-form"
                            required
                        >
                    </div>

                </div>


                <!-- INGENIOS -->

                <div class="mt-6">

                    <label class="label-form mb-3 block">
                        Ingenio
                    </label>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                        <?php foreach($ingenios as $ingenio): ?>

                            <label class="card-option">

                                <?php $esOtros = strtolower(trim($ingenio['nombre_ingenios'] ?? '')) === 'otros'; ?>

                                <input
                                    type="radio"
                                    name="ingenio_id"
                                    value="<?= $ingenio['id'] ?>"
                                    data-ingenio-otros="<?= $esOtros ? '1' : '0' ?>"
                                    required
                                >

                                <span>
                                    <?= htmlspecialchars($ingenio['nombre_ingenios']) ?>
                                </span>

                            </label>

                        <?php endforeach; ?>

                    </div>

                    <div id="otroIngenioContainer" class="mt-4 hidden">
                        <label class="label-form">
                            Especifique el ingenio
                        </label>

                        <input
                            type="text"
                            name="otro_ingenio"
                            id="otro_ingenio"
                            class="input-form"
                            placeholder="Ingrese el nombre del ingenio"
                        >
                    </div>

                </div>


                <!-- FILTROS -->

                <div class="mt-8">

                    <label class="label-form mb-4 block">
                        Tipo de Capacitación
                    </label>

                    <div class="flex flex-wrap gap-3">

                        <button
                            type="button"
                            data-tipo-capacitacion="Curso"
                            class="tipo-capacitacion-btn px-5 py-2 rounded-xl bg-green-600 text-white font-semibold shadow hover:scale-105 transition"
                        >
                            Cursos
                        </button>

                        <button
                            type="button"
                            data-tipo-capacitacion="Diplomado"
                            class="tipo-capacitacion-btn px-5 py-2 rounded-xl bg-blue-600 text-white font-semibold shadow hover:scale-105 transition"
                        >
                            Diplomados
                        </button>

                        <button
                            type="button"
                            data-tipo-capacitacion="Seminario"
                            class="tipo-capacitacion-btn px-5 py-2 rounded-xl bg-yellow-500 text-white font-semibold shadow hover:scale-105 transition"
                        >
                            Seminarios
                        </button>

                    </div>

                </div>


                <!-- CURSOS -->

                <div class="mt-8">

                    <label id="tipoCursoLabel" class="label-form mb-4 block">
                        <?= $tipo ?>
                    </label>

<div id="cursosContainer" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach($cursos as $curso): ?>

                            <label
                                class="curso-card <?= ($curso['tipo'] ?? '') === $tipo ? '' : 'hidden' ?>"
                                data-curso-tipo="<?= htmlspecialchars($curso['tipo'] ?? '') ?>"
                            >

                                <div class="flex items-center gap-3">

                                    <span class="material-symbols-outlined text-secondary">
                                        school
                                    </span>

                                    <div>

                                        <p class="font-bold text-primary">
                                            <?= htmlspecialchars($curso['nombre_cursos']) ?>
                                        </p>

                                    </div>

                                </div>

                                <input
                                    type="checkbox"
                                    name="curso_id[]"
                                    value="<?= $curso['id'] ?>"
                                    class="checkbox-custom"
                                >

                            </label>

                        <?php endforeach; ?>

                    </div>

                </div>


                <!-- TIPO PAGO -->

                <div class="mt-8">

                    <label class="label-form mb-3 block">
                        Tipo Pago
                    </label>

                    <select
                        name="tipo_pago"
                        class="input-form"
                        required
                    >

                        <option value="">
                            Seleccione
                        </option>

                        <option value="Ingenio">
                            Ingenio
                        </option>

                        <option value="Propio">
                            Propio
                        </option>

                    </select>

                </div>


                <!-- BOTON -->

                <div class="mt-10">

                    <button
                        type="submit"
                        id="btnEnviar"
                        class="btn-submit"
                    >

                        <span class="material-symbols-outlined">
                            send
                        </span>

                        Guardar Solicitud

                    </button>

                </div>

            </form>

        </div>


        <!-- SIDEBAR -->

        <aside class="lg:col-span-4 flex flex-col gap-6">

            <div class="sidebar-card-primary">

                <div class="space-y-3 text-sm">

                    <div class="flex gap-2">
                        <span class="material-symbols-outlined">
                            check_circle
                        </span>

                        <p>Certificación avalada.</p>
                    </div>

                    <div class="flex gap-2">
                        <span class="material-symbols-outlined">
                            check_circle
                        </span>

                        <p>Capacitación especializada.</p>
                    </div>

                </div>

            </div>

            <div class="bg-white rounded-2xl shadow-md overflow-hidden">

                <img
                    src="css/images/cengi.png"
                    class="w-40 mx-auto object-contain py-4"
                >

                <div class="p-5">

                    <h3 class="font-bold text-xl text-primary mb-4">
                        CENGICAÑA DIGITAL 
                        PARA INSCRIBIRTE A LOS CURSOS QUE QUIERAS
                    </h3>

                    

                </div>

            </div>

        </aside>

    </div>

</main>

<script src="script.js"></script>

</body>
</html>
