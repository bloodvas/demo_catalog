<template>
    <div class="catalog-layout">
        <!-- Sidebar -->
        <aside class="catalog-sidebar">
            <div class="sidebar-header">
                <i class="bi bi-shop me-2"></i>
                <span>Каталог</span>
            </div>
            <slot name="sidebar" />
        </aside>

        <!-- Main Content -->
        <main class="catalog-main">
            <slot />
        </main>
    </div>
</template>

<script setup>
</script>

<style scoped>
.catalog-layout {
    display: flex;
    min-height: 100vh;
    background-color: #f5f5f5;
}

/* ===== Sidebar ===== */
.catalog-sidebar {
    width: 280px;
    background: #fff;
    border-right: 1px solid #e9ecef;
    padding: 1.5rem 1rem;
    overflow-y: auto;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 100;
}

.sidebar-header {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #0d6efd;
}

.sidebar-header i {
    color: #0d6efd;
}

/* ===== Main Content ===== */
.catalog-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    max-width: calc(100vw - 280px);
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .catalog-sidebar {
        width: 240px;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .catalog-main {
        margin-left: 0;
        padding: 1rem;
        max-width: 100vw;
    }
}
</style>
