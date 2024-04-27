<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.sales_margin`)" class="p-0">
                <template #extra>
                    <ExprotTable
                        exportType="sales_margin_reports"
                        tableName="sales-margin-reports-table"
                        :title="`${$t('menu.sales_margin')} ${$t(
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
                    {{ $t(`menu.sales_margin`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-row :gutter="15" class="mb-20" style="align-items: center">
            <a-col :xs="24" :sm="24" :md="10" :lg="6" :xl="6">
                <DateRangePicker
                    @dateTimeChanged="
                        (changedDateTime) => {
                            filters.dates = changedDateTime;
                        }
                    "
                />
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="14"
                :lg="18"
                :xl="18"
                v-if="overAllSummary != ''"
            >
                <div class="profit-margin-card-wrapper">
                    <div class="card">
                        <span class="title">{{
                            $t("product.purchase_price")
                        }}</span>
                        <p class="amount">
                            {{
                                formatAmountCurrency(
                                    overAllSummary.purchase_price
                                )
                            }}
                        </p>
                    </div>
                    <div class="card">
                        <span class="title">{{ $t("product.margin") }}</span>
                        <p class="amount">
                            {{ formatAmountCurrency(overAllSummary.margin) }}
                        </p>
                    </div>
                    <div class="card">
                        <span class="title">{{
                            $t("product.sales_price")
                        }}</span>
                        <p class="amount">
                            {{
                                formatAmountCurrency(overAllSummary.sales_price)
                            }}
                        </p>
                    </div>
                </div>
            </a-col>
        </a-row>

        <SalesMargin :dates="filters.dates" @overallSummary="handleSummary" />
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
import SalesMargin from "./SalesMargin.vue";
import ExprotTable from "../../../components/report-exports/ExportTable.vue";

export default {
    components: {
        UserInfo,
        DateRangePicker,
        AdminPageHeader,
        SalesMargin,
        ExprotTable,
    },
    setup() {
        const datatable = table();
        const { permsArray, formatAmountCurrency, formatDate } = common();
        const filters = reactive({
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

        const overAllSummary = ref("");

        const handleSummary = (data) => {
            overAllSummary.value = data;
            // filters.dates = [data.start_date, data.end_date];
        };

        onMounted(() => {
            const usersPromise = axiosAdmin.get("users?limit=10000");

            Promise.all([usersPromise]).then(([usersResponse]) => {
                users.value = usersResponse.data;
            });
        });

        return {
            ...datatable,
            filters,
            users,
            permsArray,
            handleSummary,
            overAllSummary,
            formatAmountCurrency,
            formatDate,
        };
    },
};
</script>
<style>
.profit-margin-card-wrapper {
    width: fit-content;
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.profit-margin-card-wrapper .amount {
    margin: 0;
    font-weight: bolder;
}

.profit-margin-card-wrapper .title {
    color: gray;
}

.profit-margin-card-wrapper .card {
    padding: 1px 12px;
    background: #fff;
    border: 1px solid #d9d9d9;
    border-radius: 2px;
}
</style>
