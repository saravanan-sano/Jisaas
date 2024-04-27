<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.check_in_report`)" class="p-0">
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
                            {{ $t(`menu.check_in_report`) }}
                        </a-breadcrumb-item>
                    </a-breadcrumb>
                </template>
                <template #extra>
                    <CheckInExportData
                        :data="ViewReport"
                        :dates="filters.dates"
                        :staffMembers="Staffs"
                        :staffUserId="filters.staff"
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
                    {{ $t(`menu.check_in_report`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="[15, 15]" class="mb-20">
            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
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
                                              .format('YYYY-MM-DD'),
                                          dayjs(changedDateTime[1])
                                              .endOf('day')
                                              .format('YYYY-MM-DD'),
                                      ]
                                    : [
                                          dayjs()
                                              .startOf('day')
                                              .format('YYYY-MM-DD'),
                                          dayjs()
                                              .endOf('day')
                                              .format('YYYY-MM-DD'),
                                      ];
                        }
                    "
                    v-model:value="selectedDate"
                />
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="6"
                :lg="6"
                :xl="6"
                style="gap: 10px; display: flex; align-items: center"
            >
                <a-badge color="#87d068" text="Check In Time" />
                <a-badge color="#2db7f5" text="Check Out Time" />
                <a-badge color="#108ee9" text="Hours" />
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24" class="mt-10">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <CheckInTable
                            :data="checkinData"
                            :loading="loading"
                            :Columns="CheckinColumn"
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
import CheckInTable from "./CheckInTable.vue";
import CheckInExportData from "./CheckInExportData.vue";

import dayjs from "dayjs";
import _ from "lodash-es";
import fields from "./fields";

export default {
    components: {
        ProductSearchInput,
        AdminPageHeader,
        DateRangePicker,
        CheckInTable,
        CheckInExportData,
        PrinterOutlined,
    },
    setup() {
        const { permsArray, formatAmountCurrency } = common();
        const { exportColumn } = fields();
        const router = useRouter();
        const filters = reactive({
            dates: [
                dayjs().startOf("day").format("YYYY-MM-DD"),
                dayjs().endOf("day").format("YYYY-MM-DD"),
            ],
            staff: undefined,
        });
        const Staffs = ref([]);
        const checkinData = ref([]);
        const loading = ref(true);
        const ViewReport = ref(false);
        const ReportData = ref({});
        const CheckinColumn = ref([]);

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
                    "users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&order=id desc&offset=0&limit=10000"
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
                .post("reports/checkin-staff-report", filterDate)
                .then((response) => {
                    const groupedData = [];
                    if (response.checkin_report[0].details.length > 0) {
                        response.checkin_report.forEach((report) => {
                            report.details.forEach((detail) => {
                                const staffName = detail.name;
                                const existingStaff = groupedData.find(
                                    (obj) => obj.name === staffName
                                );
                                if (!existingStaff) {
                                    const newStaff = { name: staffName };
                                    if (
                                        detail.checkin &&
                                        detail.checkin.hours
                                    ) {
                                        newStaff.total_hours = parseFloat(
                                            detail.checkin.hours
                                        );
                                    } else {
                                        newStaff.total_hours = 0;
                                    }
                                    newStaff[report.date] = detail.checkin;
                                    groupedData.push(newStaff);
                                } else {
                                    if (
                                        detail.checkin &&
                                        detail.checkin.hours
                                    ) {
                                        existingStaff.total_hours += parseFloat(
                                            detail.checkin.hours
                                        );
                                    }
                                    existingStaff[report.date] = detail.checkin;
                                }
                            });
                        });

                        CheckinColumn.value = Object.keys(groupedData[0]).map(
                            (key) => {
                                if (key === "name") {
                                    return {
                                        title: "Name",
                                        dataIndex: key,
                                        width: 150,
                                        fixed: "left",
                                    };
                                } else if (key === "total_hours") {
                                    return {
                                        title: "Total Hours",
                                        dataIndex: key,
                                        width: 80,
                                    };
                                } else {
                                    return {
                                        title: key,
                                        dataIndex: key,
                                        width: 100,
                                    };
                                }
                            }
                        );
                    }
                    checkinData.value = groupedData;
                    ViewReport.value = groupedData;
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
            checkinData,
            loading,
            selectedDate,
            ViewReport,
            visible,
            ReportData,
            Staffs,
            exportColumn,
            CheckinColumn,
        };
    },
};
</script>
