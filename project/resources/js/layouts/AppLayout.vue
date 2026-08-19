<template>
    <div class="catalog-app">
        <header class="catalog-header">
            <div class="container-fluid px-3 px-md-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill text-white fs-4"></i>
                        <h1 class="h5 mb-0 text-white fw-bold">Каталог товаров</h1>
                    </div>
                    <a href="/" class="text-white text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i>На главную
                    </a>
                </div>
            </div>
        </header>

        <div class="catalog-body">
            <div class="container-fluid">
                <div class="row g-0">
                    <aside class="catalog-sidebar">
                        <div class="sidebar-inner">
                            <slot name="sidebar"></slot>
                        </div>
                    </aside>

                    <main class="catalog-content">
                        <slot></slot>
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Layout-компонент без логики
</script>

<style scoped>
.catalog-app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #f1f5f9;
    font-family: 'Inter', sans-serif;
}

.catalog-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.catalog-body {
    flex: 1;
    padding: 1.5rem 0;
}

.catalog-sidebar {
    width: 280px;
    min-width: 280px;
    background: #fff;
    border-right: 1px solid #e2e8f0;
}

.sidebar-inner {
    padding: 1rem;
    max-height: calc(100vh - 70px - 3rem);
    overflow-y: auto;
}

.sidebar-inner::-webkit-scrollbar {
    width: 4px;
}

.sidebar-inner::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.catalog-content {
    flex: 1;
    padding: 0 1.5rem;
    overflow-y: auto;
}

@media (max-width: 991.98px) {
    .catalog-sidebar {
        width: 100%;
        min-width: 100%;
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
    }

    .sidebar-inner {
        max-height: none;
        overflow-y: visible;
    }

    .catalog-content {
        padding: 1rem;
    }
}
</style>
