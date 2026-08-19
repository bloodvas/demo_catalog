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

        <!-- Хлебные крошки -->
        <Breadcrumbs
            v-if="breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
            @click="handleBreadcrumbClick"
        />

        <!-- Карточка товара -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-3 text-muted">Загрузка товара...</p>
        </div>

        <div v-else-if="product" class="bg-white rounded-3 shadow-sm p-4">
            <div class="row">
                <div class="col-12">
                    <h2 class="h3 fw-bold mb-3">{{ product.name }}</h2>
                    <p class="text-muted mb-4">
                        <i class="bi bi-folder me-1"></i>
                        <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id">
                            <a
                                v-if="crumb.type === 'group'"
                                href="#"
                                @click.prevent="handleBreadcrumbClick(crumb)"
                            >{{ crumb.name }}</a>
                            <span v-else-if="index > 0"> / </span>
                            <span v-else class="text-muted">{{ crumb.name }}</span>
                        </template>
                    </p>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="text-primary fs-2 fw-bold">
                            {{ formatPrice(product.price) }} ₽
                        </span>
                    </div>
                    <button class="btn btn-outline-secondary btn-lg" @click="goBack">
                        <i class="bi bi-arrow-left me-2"></i>Назад к списку
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-5">
            <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3 d-block"></i>
            <h5 class="text-muted">Товар не найден</h5>
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
