/**
 * Sistema centralizado de gerenciamento de tema (claro/escuro)
 * Sincroniza o tema entre todas as páginas do projeto
 */

class ThemeManager {
    constructor() {
        this.THEME_KEY = 'tema_preferido';
        this.COOKIE_DURATION = 2592000; // 30 dias em segundos
        this.THEME_LIGHT = 'claro';
        this.THEME_DARK = 'escuro';
        this.init();
    }

    /**
     * Inicializa o gerenciador de tema
     */
    init() {
        this.loadTheme();
        this.setupStorageListener();
    }

    /**
     * Carrega o tema salvo
     */
    loadTheme() {
        const savedTheme = this.getSavedTheme();
        this.applyTheme(savedTheme);
    }

    /**
     * Obtém o tema salvo do cookie ou localStorage
     */
    getSavedTheme() {
        // Tentar obter do localStorage primeiro (mais confiável em navegadores modernos)
        const localStorageTheme = localStorage.getItem(this.THEME_KEY);
        if (localStorageTheme) {
            return localStorageTheme;
        }

        // Fallback para cookie
        const cookieTheme = this.getCookie(this.THEME_KEY);
        if (cookieTheme) {
            return cookieTheme;
        }

        // Padrão
        return this.THEME_LIGHT;
    }

    /**
     * Aplica o tema ao documento
     */
    applyTheme(theme) {
			console.log('applyTheme chamada com tema:', theme);
        if (theme !== this.THEME_LIGHT && theme !== this.THEME_DARK) {
            theme = this.THEME_LIGHT;
        }
        document.documentElement.setAttribute('data-theme', theme);
    }

    /**
     * Alterna entre temas claro e escuro
     */
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || this.THEME_LIGHT;
        const newTheme = currentTheme === this.THEME_DARK ? this.THEME_LIGHT : this.THEME_DARK;
        this.setTheme(newTheme);
        return newTheme;
    }

    /**
     * Define o tema atual
     */
    setTheme(theme) {
        if (theme !== this.THEME_LIGHT && theme !== this.THEME_DARK) {
            return false;
        }

        // Aplicar ao DOM
        this.applyTheme(theme);

        // Salvar em localStorage
        localStorage.setItem(this.THEME_KEY, theme);

        // Salvar em cookie também (para compatibilidade com PHP)
        this.setCookie(this.THEME_KEY, theme, this.COOKIE_DURATION);

        // Disparar evento customizado para sincronização entre abas/janelas
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: theme }
        }));

        return true;
    }

    /**
     * Obtém o tema atual
     */
    getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || this.THEME_LIGHT;
    }

    /**
     * Configura listener para mudanças de tema em outras abas/janelas
     */
    setupStorageListener() {
        window.addEventListener('storage', (event) => {
            if (event.key === this.THEME_KEY && event.newValue) {
                this.applyTheme(event.newValue);
            }
        });

        // Listener para evento customizado de tema
        window.addEventListener('themeChanged', (event) => {
            // Sincronizar com servidor se necessário
            this.syncWithServer(event.detail.theme);
        });
    }

    /**
     * Sincroniza o tema com o servidor
     */
    async syncWithServer(theme) {
			console.log('syncWithServer chamada com tema:', theme);
			try {
				const response = await fetch('api/api_theme.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({ theme: theme }),
				});
				if (!response.ok) {
					console.warn('Erro ao sincronizar tema com servidor');
				}
				console.log('Resposta do servidor:', response);
			} catch (error) {
				// Falha silenciosa - o tema já foi aplicado localmente
				console.warn('Erro ao sincronizar tema:', error);
			}
    }

    /**
     * Obtém valor de cookie
     */
    getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return null;
    }

    /**
     * Define um cookie
     */
    setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
}

// Inicializar o gerenciador de tema quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
    window.themeManager.loadTheme(); // Carregar o tema ao inicializar
});

console.log('themeManager inicializado:', window.themeManager);
