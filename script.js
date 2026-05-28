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
            continue;
        }

        data[key] = value;
    }

    return data;
}

function saveFormDraft() {
    sessionStorage.setItem(storageKey, JSON.stringify(collectFormDraft()));
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
        const fields = Array.from(form.elements).filter((field) => field.name === name);

        fields.forEach((field) => {
            if (field.type === "radio") {
                field.checked = field.value === value;
            } else {
                field.value = value;
            }
        });
    });
}

function toggleOtroIngenio() {
    const container = document.getElementById("otroIngenioContainer");
    const inputOtro = document.getElementById("otro_ingenio");
    const selected = document.querySelector('input[name="ingenio_id"]:checked');

    if (!container || !inputOtro) {
        return;
    }

    const esOtros = selected?.dataset.ingenioOtros === "1";

    container.classList.toggle("hidden", !esOtros);
    inputOtro.required = esOtros;

    if (!esOtros) {
        inputOtro.value = "";
    }
}

document.addEventListener("DOMContentLoaded", function () {
    restoreFormDraft();
    toggleOtroIngenio();

    document.querySelectorAll('input[name="ingenio_id"]').forEach((radio) => {
        radio.addEventListener("change", function () {
            toggleOtroIngenio();
            saveFormDraft();
        });
    });

    document.querySelectorAll('a[href^="?tipo="]').forEach((link) => {
        link.addEventListener("click", saveFormDraft);
    });

    form?.addEventListener("input", saveFormDraft);
    form?.addEventListener("change", saveFormDraft);
});

form?.addEventListener("submit", function () {
    sessionStorage.removeItem(storageKey);

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
});
