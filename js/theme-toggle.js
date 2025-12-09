document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const root = document.documentElement;
    
    // Verificar si hay un tema guardado en localStorage
    const savedTheme = localStorage.getItem('hypeTheme');
    if (savedTheme === 'orange') {
        applyOrangeTheme();
    }
    
    // Event listener para el botón
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = localStorage.getItem('hypeTheme') || 'green';
            
            if (currentTheme === 'green') {
                applyOrangeTheme();
                localStorage.setItem('hypeTheme', 'orange');
            } else {
                applyGreenTheme();
                localStorage.setItem('hypeTheme', 'green');
            }
            
            // Animación del botón
            this.style.transform = 'rotate(180deg) scale(1.1)';
            setTimeout(() => {
                this.style.transform = '';
            }, 300);
        });
    }
    
    function applyOrangeTheme() {
        // Colores principales
        root.style.setProperty('--hype-green', '#ff8c00');
        root.style.setProperty('--hype-black', '#1a0a00');
        root.style.setProperty('--hype-dark-gray', '#2a1505');
        root.style.setProperty('--hype-gray', '#3a2010');
        root.style.setProperty('--hype-light-gray', '#4a3020');
        
        // Actualizar gradientes y sombras del fondo
        updateBackgroundEffects('#ff8c00', 'rgba(255, 140, 0, 0.08)', 'rgba(255, 140, 0, 0.1)', 'rgba(255, 140, 0, 0.3)');
    }
    
    function applyGreenTheme() {
        // Colores principales
        root.style.setProperty('--hype-green', '#39ff14');
        root.style.setProperty('--hype-black', '#0a0a0a');
        root.style.setProperty('--hype-dark-gray', '#1a1a1a');
        root.style.setProperty('--hype-gray', '#2a2a2a');
        root.style.setProperty('--hype-light-gray', '#404040');
        
        // Restaurar gradientes y sombras originales
        updateBackgroundEffects('#39ff14', 'rgba(57, 255, 20, 0.08)', 'rgba(57, 255, 20, 0.1)', 'rgba(57, 255, 20, 0.3)');
    }
    
    function updateBackgroundEffects(color, radialColor, boxShadowLight, glowColor) {
        // Actualizar el pseudo-elemento ::before del login-container
        const loginContainer = document.querySelector('.login-container');
        if (loginContainer) {
            const style = document.createElement('style');
            style.id = 'dynamic-theme-styles';
            
            // Remover estilo anterior si existe
            const oldStyle = document.getElementById('dynamic-theme-styles');
            if (oldStyle) {
                oldStyle.remove();
            }
            
            style.innerHTML = `
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
                
                .stat-card:hover {
                    box-shadow: 0 10px 30px ${glowColor} !important;
                }
                
                .theme-toggle:hover {
                    box-shadow: 0 0 20px ${color} !important;
                }
                
                .form-group input:focus {
                    box-shadow: 0 0 0 3px ${boxShadowLight} !important;
                }
                
                .users-table tbody tr:hover {
                    background-color: ${radialColor} !important;
                }
            `;
            
            document.head.appendChild(style);
        }
    }
});