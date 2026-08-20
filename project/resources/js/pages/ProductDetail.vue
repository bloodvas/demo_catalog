<template>
    <AppLayout>
        <template #sidebar>
            <h6 class="fw-bold mb-3 text-uppercase text-muted small">
                <i class="bi bi-folder2-open me-2"></i>Категории
            </h6>
            <GroupTree
                :groups="groups"
                :selected-id="selectedGroupId"
                @select="selectGroup"
            />
        </template>

        <div class="product-detail">
            <div v-if="loading" class="product-detail__loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-3 text-muted">Загрузка товара...</p>
            </div>

            <template v-else-if="product">
                <!-- Хлебные крошки -->
                <div class="product-detail__breadcrumbs">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="/" class="text-decoration-none">
                                    <i class="bi bi-house-door me-1"></i>Главная
                                </a>
                            </li>
                            <li
                                v-for="crumb in breadcrumbs"
                                :key="crumb.id"
                                class="breadcrumb-item"
                            >
                                <a
                                    v-if="crumb.type === 'group'"
                                    href="#"
                                    @click.prevent="handleBreadcrumbClick(crumb)"
                                    class="text-decoration-none"
                                >{{ crumb.name }}</a>
                                <span
                                    v-else
                                    class="text-primary fw-semibold"
                                >{{ crumb.name }}</span>
                            </li>
                        </ol>
                    </nav>
                </div>

                <!-- Карточка товара -->
                <div class="product-detail__card">
                    <div class="product-detail__header">
                        <div class="product-detail__icon-wrapper">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <h1 class="product-detail__title">{{ product.name }}</h1>
                    </div>

                    <div class="product-detail__divider"></div>

                    <div class="product-detail__price-block">
                        <span class="product-detail__price-label">Цена</span>
                        <div class="product-detail__price-value">
                            {{ formatPrice(product.price) }}
                            <span class="product-detail__price-currency">₽</span>
                        </div>
                    </div>

                    <div class="product-detail__actions">
                        <button class="btn btn-primary btn-lg" @click="goBack">
                            <i class="bi bi-arrow-left me-2"></i>Назад к каталогу
                        </button>
                    </div>
                </div>
            </template>

            <div v-else class="product-detail__notfound">
                <i class="bi bi-exclamation-triangle-fill text-warning mb-3 d-block" style="font-size: 3rem;"></i>
                <h5 class="text-muted mb-1">Товар не найден</h5>
                <p class="text-muted small mb-4">Возможно, он был удалён или jamais существовал</p>
                <button class="btn btn-primary" @click="goBack">
                    <i class="bi bi-arrow-left me-1"></i>Вернуться в каталог
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import GroupTree from '@/components/GroupTree.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

const { id } = usePage().props;
const product = ref(null);
const breadcrumbs = ref([]);
const groups = ref([]);
const selectedGroupId = ref(null);
const selectedGroup = ref(null);
const loading = ref(true);

// --- API запросы ---
async function fetchProduct(id) {
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/products/${id}`);
        product.value = data;
        breadcrumbs.value = data.breadcrumbs || [];
    } catch (error) {
        console.error('Ошибка загрузки товара:', error);
    } finally {
        loading.value = false;
    }
}

async function fetchGroups() {
    try {
        const { data } = await axios.get('/api/groups');
        groups.value = data;
    } catch (error) {
        console.error('Ошибка загрузки групп:', error);
    }
}

// --- Действия ---
function selectGroup(group) {
    selectedGroupId.value = group.id;
    selectedGroup.value = group;
    router.push('/');
}

function handleBreadcrumbClick(item) {
    selectGroup(item);
}

function goBack() {
    router.push('/');
}

// --- Форматирование ---
function formatPrice(price) {
    if (!price) return '0,00';
    return Number(price).toLocaleString('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

// --- Lifecycle ---
onMounted(async () => {
    await Promise.all([
        fetchGroups(),
        fetchProduct(id)
    ]);
});
</script>
