document.addEventListener('DOMContentLoaded', function () {
    let currentSlide = 0;
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const sliderContainer = document.querySelector('.slider-container');
    const totalSlides = slides.length;

    let isDragging = false;
    let startX = 0;
    let currentX = 0;
    // Eliminamos translateX global porque usaremos porcentajes en updateSlider
    const threshold = 50;

    // Función principal para mover el slider
    function updateSlider(animate = true) {
        if (!animate) {
            slider.style.transition = 'none';
        } else {
            slider.style.transition = 'transform 0.3s ease-out';
        }

        // Usamos porcentajes para que sea responsive
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;

        // Actualizar los puntos
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    // Función inteligente que maneja el "Loop" (Bucle)
    function goToSlide(index) {
        // Lógica de Bucle Infinito:
        if (index >= totalSlides) {
            currentSlide = 0; // Si pasa del último, vuelve al primero
        } else if (index < 0) {
            currentSlide = totalSlides - 1; // Si baja del primero, va al último
        } else {
            currentSlide = index; // Si no, navegación normal
        }

        updateSlider();
    }

    // Hacemos la función accesible globalmente por si usas onclick en los puntos
    window.goToSlide = goToSlide;

    // --- EVENTOS DE MOUSE (PC) ---
    sliderContainer.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.clientX;
        slider.style.transition = 'none'; // Quitamos animación mientras arrastras
    });

    sliderContainer.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        currentX = e.clientX;
        const diff = currentX - startX;
        // Calculamos movimiento en píxeles solo para el efecto visual de arrastre
        const currentTranslate = -currentSlide * sliderContainer.offsetWidth;
        slider.style.transform = `translateX(${currentTranslate + diff}px)`;
    });

    sliderContainer.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const diff = e.clientX - startX;

        // AQUÍ ESTÁ EL CAMBIO PARA EL BUCLE:
        if (diff < -threshold) {
            // Deslizar a Izquierda -> Siguiente (con bucle)
            goToSlide(currentSlide + 1);
        } else if (diff > threshold) {
            // Deslizar a Derecha -> Anterior (con bucle)
            goToSlide(currentSlide - 1);
        } else {
            // Si movió muy poco, vuelve a centrar la imagen actual
            updateSlider();
        }
    });

    sliderContainer.addEventListener('mouseleave', () => {
        if (isDragging) {
            isDragging = false;
            updateSlider(); // Restaurar posición si el mouse sale
        }
    });

    // --- EVENTOS TÁCTILES (MÓVIL) ---
    sliderContainer.addEventListener('touchstart', (e) => {
        isDragging = true;
        startX = e.touches[0].clientX;
        slider.style.transition = 'none';
    });

    sliderContainer.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        currentX = e.touches[0].clientX;
        const diff = currentX - startX;
        const currentTranslate = -currentSlide * sliderContainer.offsetWidth;
        slider.style.transform = `translateX(${currentTranslate + diff}px)`;
    });

    sliderContainer.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const diff = e.changedTouches[0].clientX - startX;

        // MISMA LÓGICA DE BUCLE PARA MÓVIL:
        if (diff < -threshold) {
            goToSlide(currentSlide + 1);
        } else if (diff > threshold) {
            goToSlide(currentSlide - 1);
        } else {
            updateSlider();
        }
    });

    // Auto-play (Opcional: detiene el bucle si quisieras)
    setInterval(() => {
        goToSlide(currentSlide + 1);
    }, 5000);

    // Posición inicial
    updateSlider();
});
