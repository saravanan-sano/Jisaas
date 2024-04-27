<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header
                :title="$t(`menu.customer_product_report`)"
                class="p-0"
            >
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
                            {{ $t(`menu.customer_product_report`) }}
                        </a-breadcrumb-item>
                    </a-breadcrumb>
                </template>
                <template #extra>
                    <CustomerProductExport
                        :data="ReportData"
                        :dates="filters.dates"
                        :customer="filters.customer"
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
                    {{ $t(`menu.customer_product_report`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="[15, 15]" class="mb-20">
            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <a-select
                    v-model:value="filters.customer"
                    placeholder="Select the customer ..."
                    :allowClear="true"
                    style="width: 100%"
                    optionFilterProp="title"
                    show-search
                >
                    <a-select-option
                        v-for="cus in customer"
                        :key="cus.name"
                        :title="cus.name"
                        :value="cus.name"
                    >
                        {{ cus.name }}
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
                        <CustomerProductTable
                            :data="customerProductData"
                            :loading="loading"
                        />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </a-card>
</template>
<script>
import { onMounted, ref, reactive, watch } from "vue";
import { PrinterOutlined } from "@ant-design/icons-vue";
import ProductSearchInput from "../../../../common/components/product/ProductSearchInput.vue";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import CustomerProductExport from "./CustomerProductExport.vue";

import dayjs from "dayjs";
import _ from "lodash-es";
import CustomerProductTable from "./CustomerProductTable.vue";

export default {
    components: {
        ProductSearchInput,
        AdminPageHeader,
        DateRangePicker,
        CustomerProductExport,
        PrinterOutlined,
        CustomerProductTable,
    },
    setup() {
        const { permsArray, formatAmountCurrency } = common();
        const filters = reactive({
            dates: [
                dayjs().startOf("day").format(),
                dayjs().endOf("day").format(),
            ],
            customer: undefined,
        });
        const customer = ref([]);
        const customerProductData = ref([]);
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
                .post("reports/customer-product-report", filterDate)
                .then((response) => {
                    setData(response.data);
                    loading.value = false;
                });
        };

        const setData = (data) => {
            customerProductData.value = [];
            ReportData.value = [];
            customer.value = [];
            Object.keys(data).forEach((key) => {
                customer.value.push({ name: key });
                data[key].map((product) => {
                    ReportData.value.push({ ...product, customer: key });
                });
                customerProductData.value.push({
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
            customerProductData,
            loading,
            selectedDate,
            ReportData,
            customer,
        };
    },
};
</script>
