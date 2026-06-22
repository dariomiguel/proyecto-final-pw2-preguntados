(function () {
    const tiempoTotalEnSegundos = 15;
    let remaining = tiempoTotalEnSegundos;
    const ring     = document.getElementById('timer-ring');
    const display  = document.getElementById('timer-display');
    const CIRCUM   = 238.76;

    function tick() {
        remaining--;
        if (remaining < 0) { remaining = 0; }

        ring.style.strokeDashoffset = CIRCUM * (1 - remaining / tiempoTotalEnSegundos);
        display.textContent = remaining;

        const ratio = remaining / tiempoTotalEnSegundos;
        if (ratio > 0.5) {
            ring.style.stroke = 'rgba(0,0,0,0.7)';
            display.style.color = '';
        } else if (ratio > 0.25) {
            ring.style.stroke = '#e6a817';
            display.style.color = '#b07d00';
        } else {
            ring.style.stroke = '#d63031';
            display.style.color = '#d63031';
        }

        if (remaining > 0) {
            setTimeout(tick, 1000);
        } else {
            const form = document.querySelector('#contenedor-trivia form');

            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "respuesta_id";
            input.value = -1; // timeout

            form.appendChild(input);
            form.submit();
        }
    }

    setTimeout(tick, 1000);
})();