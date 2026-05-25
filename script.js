const form = document.getElementById("formInscripcion");
const btn = document.getElementById("btnEnviar");

form.addEventListener("submit", function(){

    btn.innerHTML = `
        <span class="material-symbols-outlined animate-spin">
            sync
        </span>
        Enviando...
    `;

    btn.disabled = true;

});


document.addEventListener("DOMContentLoaded", function () {

    const radios = document.querySelectorAll('input[name="ingenio_id"]');

    const container = document.getElementById('otroIngenioContainer');

    const inputOtro = document.getElementById('otro_ingenio');

    radios.forEach(radio => {

        radio.addEventListener('change', function () {

            if (this.value == "8") {

                container.classList.remove('hidden');

                inputOtro.required = true;

            } else {

                container.classList.add('hidden');

                inputOtro.required = false;

                inputOtro.value = '';

            }

        });

    });

});

