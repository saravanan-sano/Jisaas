<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header
                :title="$t(`menu.assigned_staff_report`)"
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
                            {{ $t(`menu.assigned_staff_report`) }}
                        </a-breadcrumb-item>
                    </a-breadcrumb>
                </template>
                <template #extra>
                    <ExportData
                        :data="ViewReport"
                        :filter="filters.dates"
                        :columns="exportColumn"
                        title="assigned_staff_report"
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
                    {{ $t(`menu.assigned_staff_report`) }}
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
                    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <AssignedTable
                            :data="assignedData"
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
import AssignedTable from "./AssignedTable.vue";
import ExportData from "./ExportData.vue";
// import DailyReportRecept from "./DailyReportRecept.vue";

import dayjs from "dayjs";
import _ from "lodash-es";
import fields from "./fields";

export default {
    components: {
        ProductSearchInput,
        AdminPageHeader,
        DateRangePicker,
        AssignedTable,
        // DailyReportRecept,
        ExportData,
        PrinterOutlined,
    },
    setup() {
        const { permsArray, formatAmountCurrency } = common();
        const { exportColumn } = fields();
        const router = useRouter();
        const filters = reactive({
            dates: [
                dayjs().startOf("day").format("YYYY-MM-DD HH:mm:ss"),
                dayjs().endOf("day").format("YYYY-MM-DD HH:mm:ss"),
            ],
            staff: undefined,
        });
        const Staffs = ref([]);
        const assignedData = ref([]);
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
                    Staffs.value = res.data.filter((sta) => {
                        return sta.role.name != "admin";
                    });
                });
        };

        const getData = (filterDate) => {
            loading.value = true;
            axiosAdmin
                .post("assignedStaffReport", filterDate)
                .then((response) => {
                    assignedData.value = response;
                    ViewReport.value = response;
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
            assignedData,
            loading,
            selectedDate,
            ViewReport,
            visible,
            ReportData,
            Staffs,
            exportColumn,
        };
    },
};
</script>
