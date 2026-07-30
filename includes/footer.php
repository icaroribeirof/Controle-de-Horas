        </main> <!-- fechar main-content -->
    </div> <!-- fechar app-wrapper -->

    <!-- App Shell Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            const themeToggleBtn = document.getElementById('theme-toggle');

            // Mobile Menu Toggle
            if (toggleBtn && sidebar && overlay) {
                function toggleMenu() {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                }

                toggleBtn.addEventListener('click', toggleMenu);
                overlay.addEventListener('click', toggleMenu);
            }

            // Theme Toggle
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', async function() {
                    const html = document.documentElement;
                    const currentTheme = html.getAttribute('data-theme') || 'claro';
                    const newTheme = currentTheme === 'escuro' ? 'claro' : 'escuro';
                    const button = this;
                    
                    button.disabled = true;
                    button.style.opacity = '0.5';
                    button.style.cursor = 'wait';
                    
                    try {
                        const response = await fetch('api/api_dashboard.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ theme: newTheme })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            html.setAttribute('data-theme', newTheme);
                            document.cookie = `tema_preferido=${newTheme}; max-age=2592000; path=/`;
                            showThemeNotification(`Tema ${newTheme === 'escuro' ? 'escuro' : 'claro'} ativado`, 'success');
                            window.dispatchEvent(new CustomEvent('tema-alterado', { detail: { theme: newTheme } }));
                        } else {
                            console.error('Erro ao alterar tema:', data.message);
                            showThemeNotification('Erro ao alterar tema', 'error');
                        }
                    } catch (error) {
                        console.error('Erro ao alterar tema:', error);
                        showThemeNotification('Erro de conexão', 'error');
                    } finally {
                        button.disabled = false;
                        button.style.opacity = '1';
                        button.style.cursor = 'pointer';
                    }
                });
            }

            function showThemeNotification(message, type = 'success') {
                const oldNotification = document.querySelector('.theme-notification');
                if (oldNotification) {
                    oldNotification.remove();
                }
                
                const notification = document.createElement('div');
                notification.className = `theme-notification ${type}`;
                notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> <span>${message}</span>`;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.animation = 'slideOutNotification 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
