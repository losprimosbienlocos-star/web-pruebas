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
// OBTENER GRADOS ACADEMICOS
// =====================================

$responseGrados = supabase_request(
    supabase_table(
        'grado_academico',
        'select=id,nombre_grado&order=id.asc'
    )
);

$gradosAcademicos = $responseGrados;

if (!is_array($gradosAcademicos)) {
    $gradosAcademicos = [];
}
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

    <title>Solicitud de Inscripción Digital</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet"/>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css">

    <link rel="stylesheet" href="styles.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#003b2f",
                        secondary: "#73bc25",
                        background: "#f7fbf2",
                        surface: "#ffffff",
                        outline: "#cfe6bf",
                        container: "#eef8e4"
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
                Solicitud de <span class="text-secondary">Inscripción Digital</span>
            </h2>

            <p class="text-lg md:text-xl text-white/90">
                Innovación y Capacitación Agrícola e Industrial | Cengicaña Digital
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

                <div class="identity-grid grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="label-form">
                            Nombre completo
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
                            No. de Identificación Personal
                            <span class="block text-xs text-gray-400 font-normal mt-1">
                                Número de Identificación Personal del País
                            </span>
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
                            Puesto de trabajo
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
                            Área de Trabajo
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
                            Correo electrónico
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
                            No. de teléfono
                        </label>

                        <input
                            type="text"
                            name="telefono"
                            class="input-form"
                            required
                        >
                    </div>

                </div>


                <!-- PAIS Y GRADO ACADEMICO -->

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

    <div class="country-combobox">
        <label class="label-form" for="pais">
            País
        </label>

        <div class="country-input-shell">
            <span id="countrySelectedFlag" class="country-selected-flag" aria-hidden="true"></span>

            <input
                type="text"
                name="pais"
                id="pais"
                class="input-form country-input"
                placeholder="Seleccione o busque un país"
                autocomplete="off"
                role="combobox"
                aria-expanded="false"
                aria-controls="countryDropdown"
                required
            >

            <button
                type="button"
                id="countryDropdownToggle"
                class="country-dropdown-toggle"
                aria-label="Mostrar países"
            >
                <span class="material-symbols-outlined">
                    expand_more
                </span>
            </button>
        </div>

        <div
            id="countryDropdown"
            class="country-dropdown hidden"
            role="listbox"
        ></div>
    </div>

    <div>
        <label class="label-form">
            Grado Académico
        </label>

<select
    name="grado_academico_id"
    class="input-form"
    required
>

    <option value="">
        Seleccione
    </option>

    <?php if (!empty($gradosAcademicos)): ?>

        <?php foreach ($gradosAcademicos as $grado): ?>

            <option value="<?= (int)($grado['id'] ?? 0) ?>">

                <?= htmlspecialchars(
                    $grado['nombre_grado'] ?? 'Sin nombre'
                ) ?>

            </option>

        <?php endforeach; ?>

    <?php else: ?>

        <option value="">
            No hay grados académicos
        </option>

    <?php endif; ?>

</select>
    </div>

</div>


<!-- PARTICIPO ANTES -->

<div class="mt-6">

    <label class="label-form mb-3 block">
        ¿Ha participado anteriormente en cursos?
    </label>

    <div class="flex gap-6">

        <label class="flex items-center gap-2">

            <input
                type="radio"
                name="ha_participado_antes"
                value="1"
                required
            >

            <span>Sí</span>

        </label>

        <label class="flex items-center gap-2">

            <input
                type="radio"
                name="ha_participado_antes"
                value="0"
                required
                checked
            >

            <span>No</span>

        </label>

    </div>

</div>


<!-- CURSOS PARTICIPADOS -->

<div
    id="cursosParticipadosContainer"
    class="mt-5 hidden"
>

    <label class="label-form">
        ¿En qué cursos ha participado?
    </label>

    <textarea
        name="cursos_participados"
        id="cursos_participados"
        rows="4"
        class="input-form"
        placeholder="Ejemplo: Seguridad Industrial, Liderazgo, Calderas..."
    ></textarea>

</div>


<!-- COMO SE ENTERO -->

<div class="mt-6">

    <label class="label-form mb-3 block">
        ¿Cómo se enteró del curso?
    </label>

    <select
        name="como_se_entero"
        class="input-form"
        required
    >

        <option value="">
            Seleccione
        </option>

        <option value="Correo">
            Correo
        </option>

        <option value="Redes Sociales">
            Redes Sociales
        </option>

        <option value="Página Web">
            Página Web
        </option>

        <option value="Recomendación">
            Recomendación
        </option>

        <option value="Comunidad">
            Comunidad WhatsApp
        </option>

    </select>

</div>

                
                <!-- INGENIOS -->

                <div class="mt-6">

                    <label class="label-form mb-3 block">
                        Ingenio/Institucion/Empresa
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
                            class="tipo-capacitacion-btn px-5 py-2 rounded-xl bg-secondary text-white font-semibold shadow hover:scale-105 transition"
                        >
                            Cursos
                        </button>

                        <button
                            type="button"
                            data-tipo-capacitacion="Diplomado"
                            class="tipo-capacitacion-btn px-5 py-2 rounded-xl bg-[#5fa61f] text-white font-semibold shadow hover:scale-105 transition"
                        >
                            Diplomados
                        </button>

                        <button
                            type="button"
                            data-tipo-capacitacion="Seminario"
                            class="tipo-capacitacion-btn px-5 py-2 rounded-xl bg-[#4d8f1b] text-white font-semibold shadow hover:scale-105 transition"
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

                    <div class="relative mb-4">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            search
                        </span>
                        <input
                            type="search"
                            id="buscadorCursos"
                            class="input-form search-input"
                            placeholder="Buscar curso, diplomado o seminario"
                            autocomplete="off"
                        >
                    </div>

<div id="cursosContainer" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach($cursos as $curso): ?>

                            <label
                                class="curso-card <?= ($curso['tipo'] ?? '') === $tipo ? '' : 'hidden' ?>"
                                data-curso-tipo="<?= htmlspecialchars($curso['tipo'] ?? '') ?>"
                                data-curso-nombre="<?= htmlspecialchars(mb_strtolower($curso['nombre_cursos'] ?? '', 'UTF-8')) ?>"
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
                        Responsable de pago
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

                        <p>Tu Futuro Profesional Comienza Aquí</p>
                    </div>

                </div>

            </div>

            <div class="bg-white rounded-2xl shadow-md overflow-hidden">

                <img
                    src="css/images/cengi.png"
                    class="w-40 mx-auto object-contain py-4"
                >

                <div class="p-5">

                    <h3 class="font-bold text-xl text-secondary mb-4">
                        Completa el siguiente formulario para formalizar tu registro en nuestros programas de formación especializada.
                    </h3>

                    

                </div>

            </div>

        </aside>

    </div>

</main>

<script src="script.js"></script>

</body>
</html>
