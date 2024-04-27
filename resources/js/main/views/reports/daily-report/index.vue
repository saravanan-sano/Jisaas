<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.daily_report`)" class="p-0">
                <template #extra>
                    <a-button @click="visible" type="primary"
                        ><template #icon>
                            <PrinterOutlined />
                        </template>
                        {{ $t("common.print") }}</a-button
                    >
                    <DailyReportRecept
                        :visible="ViewReport"
                        :report="ReportData"
                        @closed="ViewReport = false"
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
                    {{ $t(`menu.daily_report`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="[15, 15]" class="mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="6">
                <a-select
                    v-model:value="filters.staff"
                    placeholder="Select the staff user"
                    :allowClear="true"
                    style="width: 100%"
                    optionFilterProp="title"
                    show-search
                >
                    <a-select-option
                        v-for="staff in Staffs"
                        :key="staff.xid"
                        :title="staff.name"
                        :value="staff.xid"
                    >
                        {{ staff.name }}
                    </a-select-option>
                </a-select>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="6">
                <DateRangePicker
                    @dateTimeChanged="
                        (changedDateTime) => {
                            filters.dates = [
                                dayjs(changedDateTime[0])
                                    .startOf('day')
                                    .add(1, 'day')
                                    .format('YYYY-MM-DD HH:mm:ss'),
                                dayjs(changedDateTime[1])
                                    .endOf('day')
                                    .format('YYYY-MM-DD HH:mm:ss'),
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
                    <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                        <HourlyTable :data="hourlyData" :loading="loading" />
                    </a-col>
                    <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                        <CategoryTable
                            :data="categoryData"
                            :loading="loading"
                        />
                    </a-col>
                    <a-divider />
                    <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                        <PaymentTable :data="paymentData" :loading="loading" />
                    </a-col>
                    <a-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                        <ProductTable :data="productData" :loading="loading" />
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
import HourlyTable from "./Hourly.vue";
import CategoryTable from "./Category.vue";
import ProductTable from "./Product.vue";
import PaymentTable from "./Payment.vue";
import DailyReportRecept from "./DailyReportRecept.vue";
import dayjs from "dayjs";
import _ from "lodash-es";

export default {
    components: {
        ProductSearchInput,
        AdminPageHeader,
        DateRangePicker,
        HourlyTable,
        CategoryTable,
        ProductTable,
        PaymentTable,
        DailyReportRecept,
        PrinterOutlined,
    },
    setup() {
        const { permsArray, formatAmountCurrency } = common();
        const router = useRouter();
        const filters = reactive({
            dates: [
                dayjs().startOf("day").format("YYYY-MM-DD HH:mm:ss"),
                dayjs().endOf("day").format("YYYY-MM-DD HH:mm:ss"),
            ],
            staff: undefined,
        });
        const Staffs = ref([]);

        const hourlyData = ref([]);
        const categoryData = ref([]);
        const productData = ref([]);
        const paymentData = ref([]);
        const loading = ref(true);
        const ViewReport = ref(false);
        const ReportData = ref({});

        const visible = () => {
            ViewReport.value = true;
        };

        const selectedDate = ref([
            dayjs().startOf("day"),
            dayjs().endOf("day"),
        ]);

        onBeforeMount(() => {
            if (
                !(
                    (permsArray.value.includes("sales_view") &&
                        permsArray.value.includes("sales_returns_view")) ||
                    permsArray.value.includes("admin")
                )
            ) {
                router.push("admin.dashboard.index");
            }
        });

        onMounted(() => {
            getStaffMembers();
            getData(filters);
        });

        const getStaffMembers = () => {
            axiosAdmin
                .get(
                    "users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&order=id desc&offset=0&limit=10"
                )
                .then((res) => {
                    Staffs.value = res.data;
                });
        };

        const getData = (filterDate) => {
            loading.value = true;
            axiosAdmin.post("dailyReport", filterDate).then((response) => {
                hourlyData.value = response.totalSalesData;
                categoryData.value = response.category_sale;
                productData.value = response.product_sales;

                const paymentSum = _.map(
                    _.groupBy(response.payments, "payment_mode"),
                    (transactions, payment_mode) => {
                        const { total_amount, total_orders } =
                            transactions.reduce(
                                (accumulator, transaction) => {
                                    accumulator.total_amount +=
                                        transaction.total_amount;
                                    accumulator.total_orders +=
                                        transaction.total_orders;
                                    return accumulator;
                                },
                                { total_amount: 0, total_orders: 0 }
                            );

                        return { payment_mode, total_amount, total_orders };
                    }
                );
                paymentData.value = paymentSum;

                const AverageOrder = _.sumBy(
                    response.totalSalesData,
                    "order_count"
                );
                const AverageCustomer = _.sumBy(
                    response.totalSalesData,
                    "order_count"
                );
                const TotalSales = _.sumBy(
                    response.totalSalesData,
                    "total_sales"
                );
                const TotalItems = _.sumBy(
                    response.category_sale,
                    "total_quantity"
                );
                const staffMember = Staffs.value.filter((value) => {
                    return value.xid == filters.staff;
                });

                ReportData.value = {
                    payments: paymentSum,
                    category: response.category_sale,
                    hourly: response.totalSalesData,
                    product:response.product_sales,
                    avg_order: AverageOrder,
                    avg_customer: AverageCustomer,
                    total_sales: TotalSales,
                    avg_order_value: TotalSales / AverageOrder,
                    total_items: TotalItems,
                    total_discount: 0,
                    other_charges: 0,
                    staff_member: staffMember,
                    start_date: dayjs(filters.dates[0]).format("DD/MM/YYYY"),
                    end_date: dayjs(filters.dates[1]).format("DD/MM/YYYY"),
                };
                loading.value = false;
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
            hourlyData,
            categoryData,
            productData,
            paymentData,
            loading,
            selectedDate,
            ViewReport,
            visible,
            ReportData,
            Staffs,
        };
    },
};
</script>
