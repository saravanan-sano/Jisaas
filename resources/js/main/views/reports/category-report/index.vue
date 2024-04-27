<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.category_report`)" class="p-0">
                <template #breadcrumb>
                    <a-breadcrumb separator="-" style="font-size: 12px">
                        <a-breadcrumb-item>
                            <router-link
                                :to="{ name: 'admin.dashboard.index' }"
                            >
                                {{ $t(`menu.dashboard`) }}
                            </router-link>
                        </a-breadcrumb-item>
                        <a-breadcrumb-item>
                            {{ $t(`menu.category_report`) }}
                        </a-breadcrumb-item>
                    </a-breadcrumb>
                </template>
                <template #extra>
                    <CategoryExportData
                        :data="ReportData"
                        :dates="filters.dates"
                        :categories="filters.categories"
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
                    {{ $t(`menu.category_report`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="[15, 15]" class="mb-20">
            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <a-select
                    v-model:value="filters.categories"
                    placeholder="Select the category ..."
                    :allowClear="true"
                    style="width: 100%"
                    optionFilterProp="title"
                    show-search
                >
                    <a-select-option
                        v-for="category in categories"
                        :key="category.name"
                        :title="category.name"
                        :value="category.name"
                    >
                        {{ category.name }}
                    </a-select-option>
                </a-select>
            </a-col>
            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <DateRangePicker
                    @dateTimeChanged="
                        (changedDateTime) => {
                            filters.dates =
                                changedDateTime.length > 0
                                    ? [
                                          dayjs(changedDateTime[0])
                                              .startOf('day')
                                              .add(1, 'day')
                                              .format(),
                                          dayjs(changedDateTime[1])
                                              .endOf('day')
                                              .format(),
                                      ]
                                    : [
                                          dayjs().startOf('day').format(),
                                          dayjs().endOf('day').format(),
                                      ];
                        }
                    "
                    v-model:value="selectedDate"
                />
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24" class="mt-10">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <CategoryTable
                            :data="categoryData"
                            :loading="loading"
                        />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </a-card>
</template>
<script>
import { onMounted, ref, onBeforeMount, reactive, watch } from "vue";
import { useRouter } from "vue-router";
import { PrinterOutlined } from "@ant-design/icons-vue";
import ProductSearchInput from "../../../../common/components/product/ProductSearchInput.vue";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import CategoryExportData from "./CategoryExportData.vue";

import dayjs from "dayjs";
import _ from "lodash-es";
import CategoryTable from "./CategoryTable.vue";

export default {
    components: {
        ProductSearchInput,
        AdminPageHeader,
        DateRangePicker,
        CategoryExportData,
        PrinterOutlined,
        CategoryTable,
    },
    setup() {
        const { permsArray, formatAmountCurrency } = common();
        const filters = reactive({
            dates: [
                dayjs().startOf("day").format(),
                dayjs().endOf("day").format(),
            ],
            categories: undefined,
        });
        const categories = ref([]);
        const categoryData = ref([]);
        const loading = ref(true);
        const ReportData = ref([]);

        const selectedDate = ref([
            dayjs().startOf("day"),
            dayjs().endOf("day"),
        ]);

        onMounted(() => {
            getData(filters);
        });

        const getData = (filterDate) => {
            loading.value = true;
            axiosAdmin
                .post("reports/category-report", filterDate)
                .then((response) => {
                    setData(response.data);
                    loading.value = false;
                });
        };

        const setData = (data) => {
            categoryData.value = [];
            ReportData.value = [];
            categories.value = [];
            Object.keys(data).forEach((key) => {
                categories.value.push({ name: key });
                data[key].map((product) => {
                    ReportData.value.push({ ...product, category: key });
                });
                categoryData.value.push({
                    name: key,
                    total_products: data[key].length,
                    quantity: _.sumBy(data[key], "quantity"),
                    tax_amount: _.sumBy(data[key], "tax_amount"),
                    subtotal: _.sumBy(data[key], "subtotal"),
                    discount: _.sumBy(data[key], "discount"),
                    total: _.sumBy(data[key], "total"),
                    products: data[key],
                });
            });
        };

        watch(filters, (newVal, oldVal) => {
            getData(newVal);
        });

        return {
            permsArray,
            dayjs,
            formatAmountCurrency,
            filters,
            categoryData,
            loading,
            selectedDate,
            ReportData,
            categories,
        };
    },
};
</script>
