<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.product_expiry`)" class="p-0">
                <template #extra>
                    <ExportExpiryProduct
                        :data="Products"
                        tableName="product_expiry-reports-table"
                        :title="`${$t('menu.product_expiry')} ${$t(
                            'menu.reports'
                        )}`"
                    />
                </template>
            </a-page-header>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.reports`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.product_expiry`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="[15, 15]" class="mb-20">
            <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <a-radio-group
                    v-model:value="filters.days"
                    button-style="solid"
                >
                    <a-radio-button :value="1">Today</a-radio-button>
                    <a-radio-button :value="7">1 Week</a-radio-button>
                    <a-radio-button :value="14">2 Week</a-radio-button>
                    <a-radio-button :value="28">1 month</a-radio-button>
                </a-radio-group>
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24" class="mt-10">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <expiry-table :data="Products" :loading="loading" />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </a-card>
</template>
<script>
import { onMounted, reactive, ref, watch } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import ExpiryTable from "./ExpiryTable.vue";
import ExportExpiryProduct from "./ExportExpiryProduct.vue";

export default {
    components: { AdminPageHeader, ExpiryTable, ExportExpiryProduct },
    setup() {
        const filters = reactive({
            days: 1,
        });
        const Products = ref([]);
        const loading = ref(true);

        const getData = (filterDate) => {
            loading.value = true;
            axiosAdmin.post("expiry-product", filterDate).then((response) => {
                Products.value = response.data.products;
                loading.value = false;
            });
        };

        onMounted(() => {
            getData(filters);
        });

        watch(filters, (newVal, oldVal) => {
            getData(newVal);
        });
        return {
            filters,
            Products,
            loading,
        };
    },
};
</script>
