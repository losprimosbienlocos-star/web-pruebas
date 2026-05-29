const form = document.getElementById("formInscripcion");
const btn = document.getElementById("btnEnviar");
const storageKey = "cengicursosFormDraft";

function collectFormDraft() {

    if (!form) {
        return {};
    }

    const data = {};
    const formData = new FormData(form);

    for (const [key, value] of formData.entries()) {
        if (key === "curso_id[]") {
            if (!Array.isArray(data[key])) {
                data[key] = [];
            }

            data[key].push(value);
            continue;
        }

        data[key] = value;
    }

    return data;
}

function saveFormDraft() {
    sessionStorage.setItem(
        storageKey,
        JSON.stringify(collectFormDraft())
    );
}

function restoreFormDraft() {

    if (!form) {
        return;
    }

    const raw = sessionStorage.getItem(storageKey);

    if (!raw) {
        return;
    }

    let data = {};

    try {

        data = JSON.parse(raw);

    } catch (error) {

        sessionStorage.removeItem(storageKey);

        return;
    }

    Object.entries(data).forEach(([name, value]) => {

        const fields = Array.from(form.elements).filter(
            (field) => field.name === name
        );

        fields.forEach((field) => {

            if (field.type === "radio") {

                field.checked = field.value === value;

            } else if (field.type === "checkbox") {

                field.checked = Array.isArray(value)
                    ? value.includes(field.value)
                    : field.value === value;

            } else {

                field.value = value;
            }
        });
    });
}

function toggleOtroIngenio() {

    const container = document.getElementById(
        "otroIngenioContainer"
    );

    const inputOtro = document.getElementById(
        "otro_ingenio"
    );

    const selected = document.querySelector(
        'input[name="ingenio_id"]:checked'
    );

    if (!container || !inputOtro) {
        return;
    }

    const esOtros =
        selected?.dataset.ingenioOtros === "1";

    container.classList.toggle(
        "hidden",
        !esOtros
    );

    inputOtro.required = esOtros;

    if (!esOtros) {
        inputOtro.value = "";
    }
}


// ======================================
// MOSTRAR CURSOS PARTICIPADOS
// ======================================

function toggleCursosParticipados() {

    const cursosParticipadosContainer =
        document.getElementById(
            "cursosParticipadosContainer"
        );

    const cursosParticipados =
        document.getElementById(
            "cursos_participados"
        );

    const seleccionado = document.querySelector(
        'input[name="ha_participado_antes"]:checked'
    );

    if (
        !cursosParticipadosContainer ||
        !cursosParticipados
    ) {
        return;
    }

    const mostrar =
        seleccionado?.value === "1";

    cursosParticipadosContainer.classList.toggle(
        "hidden",
        !mostrar
    );

    cursosParticipados.required = mostrar;

    if (!mostrar) {
        cursosParticipados.value = "";
    }
}


// ======================================
// TIPO CAPACITACION
// ======================================

let tipoCapacitacionActual = "";

function normalizarTextoBusqueda(texto) {
    return (texto || "")
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
}

function filtrarCursos() {
    const busqueda = normalizarTextoBusqueda(
        document.getElementById("buscadorCursos")?.value || ""
    );

    document.querySelectorAll(".curso-card")
        .forEach((card) => {
            const nombre = normalizarTextoBusqueda(
                card.dataset.cursoNombre || card.textContent
            );

            const coincideTipo =
                card.dataset.cursoTipo === tipoCapacitacionActual;

            const coincideBusqueda =
                busqueda === "" || nombre.includes(busqueda);

            card.classList.toggle(
                "hidden",
                !(coincideTipo && coincideBusqueda)
            );
        });
}

function setTipoCapacitacion(tipo) {
    tipoCapacitacionActual = tipo;

    const label = document.getElementById(
        "tipoCursoLabel"
    );

    if (label) {
        label.textContent = tipo;
    }

    filtrarCursos();

    document.querySelectorAll(".tipo-capacitacion-btn")
        .forEach((button) => {

            const active =
                button.dataset.tipoCapacitacion === tipo;

            button.classList.toggle(
                "ring-4",
                active
            );

            button.classList.toggle(
                "ring-primary/20",
                active
            );
        });
}


// ======================================
// DOM READY
// ======================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        restoreFormDraft();

        toggleOtroIngenio();

        toggleCursosParticipados();

        setTipoCapacitacion(
            document.getElementById(
                "tipoCursoLabel"
            )?.textContent.trim() || "Curso"
        );


        // ======================================
        // INGENIOS
        // ======================================

        document.querySelectorAll(
            'input[name="ingenio_id"]'
        ).forEach((radio) => {

            radio.addEventListener(
                "change",
                function () {

                    toggleOtroIngenio();

                    saveFormDraft();
                }
            );
        });


        // ======================================
        // PARTICIPO ANTES
        // ======================================

        document.querySelectorAll(
            'input[name="ha_participado_antes"]'
        ).forEach((radio) => {

            radio.addEventListener(
                "change",
                function () {

                    toggleCursosParticipados();

                    saveFormDraft();
                }
            );
        });


        // ======================================
        // BOTONES TIPO
        // ======================================

        document.querySelectorAll(
            ".tipo-capacitacion-btn"
        ).forEach((button) => {

            button.addEventListener(
                "click",
                function () {

                    setTipoCapacitacion(
                        this.dataset.tipoCapacitacion
                    );

                    saveFormDraft();
                }
            );
        });

        document.getElementById("buscadorCursos")?.addEventListener(
            "input",
            filtrarCursos
        );


        // ======================================
        // AUTOGUARDADO
        // ======================================

        form?.addEventListener(
            "input",
            saveFormDraft
        );

        form?.addEventListener(
            "change",
            saveFormDraft
        );
    }
);


// ======================================
// SUBMIT
// ======================================

form?.addEventListener(
    "submit",
    function () {

        sessionStorage.removeItem(
            storageKey
        );

        if (!btn) {
            return;
        }

        btn.innerHTML = `
            <span class="material-symbols-outlined animate-spin">
                sync
            </span>
            Enviando...
        `;

        btn.disabled = true;
    }
);
