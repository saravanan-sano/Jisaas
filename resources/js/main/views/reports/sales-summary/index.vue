<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.sales_summary`)" class="p-0">
                <template #extra>
                    <!-- <ExprotTable
						exportType="sales_summary_reports"
						tableName="sales-summary-reports-table"
						:title="`${$t('menu.sales_summary')} ${$t('menu.reports')}`"
					/> -->
                    <SalesSummaryExcelExport
                    :date="filters.dates"
                    :staffUserId="filters.user_id"
                    :staffMembers="users"
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
                    {{ $t(`menu.sales_summary`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="15" class="mb-20">
            <a-col :xs="24" :sm="24" :md="7" :lg="4" :xl="4">
                <a-select
                    v-model:value="filters.user_id"
                    :placeholder="
                        $t('common.select_default_text', [
                            $t(`staff_member.staff_member`),
                        ])
                    "
                    :allowClear="true"
                    style="width: 100%"
                    optionFilterProp="title"
                    show-search
                >
                    <a-select-option
                        v-for="user in users"
                        :key="user.xid"
                        :title="user.name"
                        :value="user.xid"
                    >
                        {{ user.name }}
                    </a-select-option>
                </a-select>
            </a-col>

            <a-col :xs="24" :sm="24" :md="10" :lg="6" :xl="6">
                <DateRangePicker
                    @dateTimeChanged="
                        (changedDateTime) => {
                            filters.dates = changedDateTime;
                        }
                    "
                />
            </a-col>
        </a-row>

        <SalesSummary :user_id="filters.user_id" :dates="filters.dates" />
    </a-card>
</template>
<script>
import { onMounted, onBeforeMount, ref, reactive } from "vue";
import { useRouter } from "vue-router";
import table from "../../../../common/composable/datatable";
import common from "../../../../common/composable/common";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import SalesSummary from "./SalesSummary.vue";
import ExprotTable from "../../../components/report-exports/ExportTable.vue";
import SalesSummaryExcelExport from "./SalesSummaryExcelExport.vue";

export default {
    components: {
        UserInfo,
        DateRangePicker,
        AdminPageHeader,
        SalesSummary,
        ExprotTable,
        SalesSummaryExcelExport,
    },
    setup() {
        const datatable = table();
        const { permsArray } = common();
        const filters = reactive({
            user_id: undefined,
            payment_mode_id: undefined,
            dates: [],
        });
        const users = ref({});
        const router = useRouter();

        onBeforeMount(() => {
            if (
                !(
                    permsArray.value.includes("users_view") ||
                    permsArray.value.includes("admin")
                )
            ) {
                router.push("admin.dashboard.index");
            }
        });

        onMounted(() => {
            const usersPromise = axiosAdmin.get(
                "users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,pincode,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&limit=10000"
            );

            Promise.all([usersPromise]).then(([usersResponse]) => {
                users.value = usersResponse.data;
            });
        });

        return {
            ...datatable,
            filters,
            users,
            permsArray,
        };
    },
};
</script>
