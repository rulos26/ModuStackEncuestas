/**
 * Dashboard Enhancements JavaScript
 * Mejoras para el Dashboard de Seguimiento
 */

class DashboardEnhancements {
    constructor() {
        this.updateInterval = null;
        this.lastUpdate = new Date();
        this.isUpdating = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupAutoRefresh();
        this.setupAnimations();
        this.setupTooltips();
        this.setupKeyboardShortcuts();
    }

    setupEventListeners() {
        // Event listeners para mejor UX
        $(document).on('click', '.btn[onclick*="actualizarDatos"]', (e) => {
            this.showLoadingState(e.target);
        });

        // Mejorar feedback visual en botones
        $('.btn').on('click', function() {
            $(this).addClass('btn-clicked');
            setTimeout(() => {
                $(this).removeClass('btn-clicked');
            }, 200);
        });

        // Mejorar hover en tablas
        $('.table-hover tbody tr').on('mouseenter', function() {
            $(this).addClass('table-row-hover');
        }).on('mouseleave', function() {
            $(this).removeClass('table-row-hover');
        });
    }

    setupAutoRefresh() {
        // Actualización automática cada 30 segundos
        this.updateInterval = setInterval(() => {
            if (!this.isUpdating) {
                this.autoUpdate();
            }
        }, 30000);

        // Detener cuando la página no está visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoRefresh();
            } else {
                this.resumeAutoRefresh();
            }
        });
    }

    setupAnimations() {
        // Animaciones de entrada para elementos
        $('.card').each((index, element) => {
            $(element).addClass('dashboard-card');
            $(element).css('animation-delay', `${index * 0.1}s`);
        });

        // Animaciones para contadores
        this.animateCounters();
    }

    setupTooltips() {
        // Inicializar tooltips de Bootstrap
        $('[data-toggle="tooltip"]').tooltip();

        // Tooltips personalizados para elementos sin data-toggle
        $('.btn, .badge, .progress').each(function() {
            if (!$(this).attr('data-toggle')) {
                $(this).attr('data-toggle', 'tooltip');
                $(this).attr('title', $(this).attr('title') || this.getAttribute('aria-label'));
            }
        });
    }

    setupKeyboardShortcuts() {
        $(document).on('keydown', (e) => {
            // Ctrl/Cmd + R para actualizar
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                this.manualUpdate();
            }

            // Escape para cerrar modales
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }
        });
    }

    showLoadingState(button) {
        const $button = $(button);
        const $icon = $button.find('i');
        const originalIcon = $icon.attr('class');

        $button.addClass('loading');
        $icon.removeClass().addClass('fas fa-spinner fa-spin');

        // Restaurar después de un tiempo
        setTimeout(() => {
            $button.removeClass('loading');
            $icon.removeClass().addClass(originalIcon);
        }, 2000);
    }

    autoUpdate() {
        if (this.isUpdating) return;

        this.isUpdating = true;
        this.updateData().finally(() => {
            this.isUpdating = false;
        });
    }

    manualUpdate() {
        this.showNotification('Actualizando datos...', 'info');
        this.updateData();
    }

    async updateData() {
        try {
            const response = await $.ajax({
                url: window.dashboardUpdateUrl || '/api/dashboard/update',
                method: 'GET',
                timeout: 10000
            });

            this.updateStatistics(response.statistics);
            this.updateProgress(response.progress);
            this.updateTables(response.tables);

            this.lastUpdate = new Date();
            this.showNotification('Datos actualizados correctamente', 'success');

        } catch (error) {
            console.error('Error actualizando datos:', error);
            this.showNotification('Error al actualizar datos', 'error');
        }
    }

    updateStatistics(statistics) {
        // Actualizar contadores con animación
        Object.keys(statistics).forEach(key => {
            const $element = $(`.stat-value[data-stat="${key}"]`);
            if ($element.length) {
                this.animateCounter($element, statistics[key]);
            }
        });
    }

    updateProgress(progress) {
        // Actualizar barras de progreso
        $('.progress-bar').each(function() {
            const $bar = $(this);
            const newWidth = progress[$bar.data('progress')] || 0;
            $bar.css('width', `${newWidth}%`);
            $bar.find('strong').text(`${newWidth}% Completado`);
        });
    }

    updateTables(tables) {
        // Actualizar tablas dinámicamente
        if (tables.correosPendientes) {
            $('#tablaCorreosPendientes tbody').html(tables.correosPendientes);
        }

        if (tables.bloques) {
            $('#tablaBloques tbody').html(tables.bloques);
        }
    }

    animateCounter($element, newValue) {
        const currentValue = parseInt($element.text()) || 0;
        const increment = (newValue - currentValue) / 20;
        let current = currentValue;

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= newValue) || (increment < 0 && current <= newValue)) {
                $element.text(newValue);
                clearInterval(timer);
            } else {
                $element.text(Math.floor(current));
            }
        }, 50);
    }

    animateCounters() {
        $('.stat-value').each(function() {
            const $this = $(this);
            const finalValue = parseInt($this.text());
            $this.text('0');

            setTimeout(() => {
                this.animateCounter($this, finalValue);
            }, 500);
        });
    }

    showNotification(message, type = 'info') {
        const config = {
            success: { icon: 'fas fa-check-circle', bg: 'bg-success' },
            error: { icon: 'fas fa-exclamation-triangle', bg: 'bg-danger' },
            warning: { icon: 'fas fa-exclamation-circle', bg: 'bg-warning' },
            info: { icon: 'fas fa-info-circle', bg: 'bg-info' }
        };

        const configType = config[type] || config.info;

        const notification = $(`
            <div class="toast-notification ${configType.bg} text-white alert-dismissible fade show"
                 style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <div class="d-flex align-items-center">
                    <i class="${configType.icon} fa-lg mr-3"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);

        $('body').append(notification);

        // Auto-ocultar después de 3 segundos
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 3000);
    }

    pauseAutoRefresh() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    }

    resumeAutoRefresh() {
        this.setupAutoRefresh();
    }

    // Métodos de utilidad
    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    formatDate(date) {
        return new Date(date).toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Métodos para exportar datos
    exportToCSV(data, filename) {
        const csvContent = this.convertToCSV(data);
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');

        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    convertToCSV(data) {
        const headers = Object.keys(data[0]);
        const csvRows = [headers.join(',')];

        for (const row of data) {
            const values = headers.map(header => {
                const value = row[header];
                return `"${value}"`;
            });
            csvRows.push(values.join(','));
        }

        return csvRows.join('\n');
    }
}

// Inicializar mejoras cuando el DOM esté listo
$(document).ready(() => {
    window.dashboardEnhancements = new DashboardEnhancements();
});

// Exponer métodos globalmente para compatibilidad
window.showNotification = (message, type) => {
    if (window.dashboardEnhancements) {
        window.dashboardEnhancements.showNotification(message, type);
    }
};

window.animateCounter = (element, newValue) => {
    if (window.dashboardEnhancements) {
        window.dashboardEnhancements.animateCounter($(element), newValue);
    }
};
