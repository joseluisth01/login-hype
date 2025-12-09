document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const root = document.documentElement;
    
    // Verificar si hay un tema guardado en localStorage
    const savedTheme = localStorage.getItem('hypeTheme') || 'green';
    applyTheme(savedTheme);
    
    // Event listener para el botón
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = localStorage.getItem('hypeTheme') || 'green';
            let nextTheme;
            
            // Ciclo: verde → naranja → azul → verde
            if (currentTheme === 'green') {
                nextTheme = 'orange';
            } else if (currentTheme === 'orange') {
                nextTheme = 'blue';
            } else {
                nextTheme = 'green';
            }
            
            applyTheme(nextTheme);
            localStorage.setItem('hypeTheme', nextTheme);
            
            // Animación del botón
            this.style.transform = 'rotate(180deg) scale(1.1)';
            setTimeout(() => {
                this.style.transform = '';
            }, 300);
        });
    }
    
    function applyTheme(theme) {
        switch(theme) {
            case 'orange':
                applyOrangeTheme();
                break;
            case 'blue':
                applyBlueTheme();
                break;
            default:
                applyGreenTheme();
                break;
        }
    }
    
    function applyGreenTheme() {
        // Colores principales
        root.style.setProperty('--hype-green', '#39ff14');
        root.style.setProperty('--hype-black', '#0a0a0a');
        root.style.setProperty('--hype-dark-gray', '#1a1a1a');
        root.style.setProperty('--hype-gray', '#2a2a2a');
        root.style.setProperty('--hype-light-gray', '#404040');
        
        // Restaurar imagen original de la lata
        const canImage = document.querySelector('.can-image');
        if (canImage) {
            canImage.src = 'images/lata.webp';
        }
        
        // Restaurar gradientes y sombras originales
        updateBackgroundEffects('#39ff14', 'rgba(57, 255, 20, 0.08)', 'rgba(57, 255, 20, 0.1)', 'rgba(57, 255, 20, 0.3)');
    }
    
    function applyOrangeTheme() {
        // Colores principales
        root.style.setProperty('--hype-green', '#ff8c00');
        root.style.setProperty('--hype-black', '#1a0a00');
        root.style.setProperty('--hype-dark-gray', '#2a1505');
        root.style.setProperty('--hype-gray', '#3a2010');
        root.style.setProperty('--hype-light-gray', '#4a3020');
        
        // Cambiar imagen de la lata
        const canImage = document.querySelector('.can-image');
        if (canImage) {
            canImage.src = 'images/lata2.webp';
        }
        
        // Actualizar gradientes y sombras
        updateBackgroundEffects('#ff8c00', 'rgba(255, 140, 0, 0.08)', 'rgba(255, 140, 0, 0.1)', 'rgba(255, 140, 0, 0.3)');
    }
    
    function applyBlueTheme() {
        // Colores principales
        root.style.setProperty('--hype-green', '#00d4ff');
        root.style.setProperty('--hype-black', '#001a1f');
        root.style.setProperty('--hype-dark-gray', '#002a35');
        root.style.setProperty('--hype-gray', '#003a4a');
        root.style.setProperty('--hype-light-gray', '#004a5f');
        
        // Cambiar imagen de la lata
        const canImage = document.querySelector('.can-image');
        if (canImage) {
            canImage.src = 'images/lata3.webp';
        }
        
        // Actualizar gradientes y sombras
        updateBackgroundEffects('#00d4ff', 'rgba(0, 212, 255, 0.08)', 'rgba(0, 212, 255, 0.1)', 'rgba(0, 212, 255, 0.3)');
    }
    
    function updateBackgroundEffects(color, radialColor, boxShadowLight, glowColor) {
        const style = document.createElement('style');
        style.id = 'dynamic-theme-styles';
        
        // Remover estilo anterior si existe
        const oldStyle = document.getElementById('dynamic-theme-styles');
        if (oldStyle) {
            oldStyle.remove();
        }
        
        style.innerHTML = `
            /* Login/Signup effects */
            .login-container::before {
                background: radial-gradient(circle, ${radialColor} 0%, transparent 70%) !important;
            }
            
            .form-wrapper {
                box-shadow: 0 20px 60px ${boxShadowLight} !important;
            }
            
            .logo h1 {
                text-shadow: 0 0 20px ${glowColor} !important;
            }
            
            .can-image {
                filter: drop-shadow(0 0 30px ${glowColor}) !important;
            }
            
            .btn-primary:hover {
                box-shadow: 0 10px 30px ${glowColor} !important;
            }
            
            .form-group input:focus {
                box-shadow: 0 0 0 3px ${boxShadowLight} !important;
            }
            
            /* Dashboard effects */
            .stat-card:hover {
                box-shadow: 0 10px 30px ${glowColor} !important;
            }
            
            .users-table tbody tr:hover {
                background-color: ${radialColor} !important;
            }
            
            /* Theme toggle button */
            .theme-toggle:hover {
                box-shadow: 0 0 20px ${color} !important;
            }
        `;
        
        document.head.appendChild(style);
    }
});