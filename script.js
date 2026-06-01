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


// ======================================
// SELECTOR DE PAISES
// ======================================

const countryCodes = `
    AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ
    BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR BS BT BV BW BY BZ
    CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ
    DE DJ DK DM DO DZ
    EC EE EG EH ER ES ET
    FI FJ FK FM FO FR
    GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY
    HK HM HN HR HT HU
    ID IE IL IM IN IO IQ IR IS IT
    JE JM JO JP
    KE KG KH KI KM KN KP KR KW KY KZ
    LA LB LC LI LK LR LS LT LU LV LY
    MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ
    NA NC NE NF NG NI NL NO NP NR NU NZ
    OM
    PA PE PF PG PH PK PL PM PN PR PS PT PW PY
    QA
    RE RO RS RU RW
    SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX SY SZ
    TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ
    UA UG UM US UY UZ
    VA VC VE VG VI VN VU
    WF WS
    YE YT
    ZA ZM ZW
`
    .trim()
    .split(/\s+/);

const countryNameOverrides = {
    BO: "Bolivia",
    BQ: "Caribe Neerlandés",
    CD: "República Democrática del Congo",
    CI: "Costa de Marfil",
    FK: "Islas Malvinas",
    GB: "Reino Unido",
    IR: "Irán",
    KP: "Corea del Norte",
    KR: "Corea del Sur",
    LA: "Laos",
    MD: "Moldavia",
    MK: "Macedonia del Norte",
    PS: "Palestina",
    RU: "Rusia",
    SY: "Siria",
    TZ: "Tanzania",
    US: "Estados Unidos",
    VA: "Ciudad del Vaticano",
    VE: "Venezuela",
    VN: "Vietnam"
};

const regionNames = typeof Intl !== "undefined" && Intl.DisplayNames
    ? new Intl.DisplayNames(["es"], { type: "region" })
    : null;

const countries = countryCodes
    .map((code) => ({
        code,
        name: countryNameOverrides[code] || regionNames?.of(code) || code
    }))
    .sort((a, b) => a.name.localeCompare(b.name, "es"));

function initCountryCombobox() {
    const input = document.getElementById("pais");
    const dropdown = document.getElementById("countryDropdown");
    const toggle = document.getElementById("countryDropdownToggle");
    const selectedFlag = document.getElementById("countrySelectedFlag");

    if (!input || !dropdown || !toggle || !selectedFlag) {
        return;
    }

    const setExpanded = (expanded) => {
        dropdown.classList.toggle("hidden", !expanded);
        input.setAttribute("aria-expanded", expanded ? "true" : "false");
    };

    const setSelectedFlag = (country) => {
        selectedFlag.parentElement?.classList.toggle(
            "has-country-flag",
            Boolean(country)
        );

        selectedFlag.className = country
            ? `fi fi-${country.code.toLowerCase()} country-selected-flag`
            : "country-selected-flag";
    };

    const updateSelectedFlag = () => {
        const country = countries.find(
            (item) => item.name === input.value
        );

        setSelectedFlag(country);
    };

    const selectCountry = (country) => {
        input.value = country.name;
        setSelectedFlag(country);
        setExpanded(false);
        saveFormDraft();
        input.focus();
    };

    const renderOptions = () => {
        const query = normalizarTextoBusqueda(input.value);
        const matches = countries
            .filter((country) =>
                normalizarTextoBusqueda(country.name).includes(query) ||
                normalizarTextoBusqueda(country.code).includes(query)
            )
            .slice(0, 80);

        dropdown.innerHTML = "";

        if (matches.length === 0) {
            const empty = document.createElement("div");
            empty.className = "country-empty";
            empty.textContent = "No se encontró ese país.";
            dropdown.appendChild(empty);
            return;
        }

        matches.forEach((country) => {
            const option = document.createElement("button");
            option.type = "button";
            option.className = "country-option";
            option.setAttribute("role", "option");
            option.dataset.countryName = country.name;
            option.innerHTML = `
                <span class="fi fi-${country.code.toLowerCase()} country-option-flag" aria-hidden="true"></span>
                <span class="country-option-name">${country.name}</span>
                <span class="country-option-code">${country.code}</span>
            `;

            option.addEventListener("click", () => {
                selectCountry(country);
            });

            dropdown.appendChild(option);
        });
    };

    const openDropdown = () => {
        renderOptions();
        setExpanded(true);
    };

    input.addEventListener("focus", openDropdown);

    input.addEventListener("input", () => {
        updateSelectedFlag();
        renderOptions();
        setExpanded(true);
    });

    input.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            setExpanded(false);
            return;
        }

        if (event.key !== "Enter") {
            return;
        }

        const firstOption = dropdown.querySelector(".country-option");

        if (!dropdown.classList.contains("hidden") && firstOption) {
            event.preventDefault();
            firstOption.click();
        }
    });

    toggle.addEventListener("click", () => {
        if (dropdown.classList.contains("hidden")) {
            input.focus();
            openDropdown();
        } else {
            setExpanded(false);
        }
    });

    document.addEventListener("click", (event) => {
        if (!event.target.closest(".country-combobox")) {
            setExpanded(false);
        }
    });

    updateSelectedFlag();
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

        initCountryCombobox();

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
