<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header
                :title="`${$t('menu.referral')} ${$t('menu.reports')}`"
                class="p-0"
            >
                <template #extra>
                    <ExportTable
                        exportType="referral_report"
                        tableName="referral_report"
                        :title="`${$t('menu.referral')} ${$t('menu.reports')}`"
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
                    {{ $t(`menu.referral`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <a-card class="page-content-container">
        {{ watchFilterData }}
        <a-row :gutter="15" class="mb-20">
            <a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="4">
                <a-select
                    v-model:value="searchValue"
                    :placeholder="
                        $t('common.select_default_text', [$t(`referral`)])
                    "
                    :allowClear="true"
                    optionFilterProp="title"
                    show-search
                    @change="handelSearch"
                    style="width: 100%"
                >
                    <a-select-option
                        v-for="referral in referralUsers"
                        :key="referral.xid"
                        :value="referral.xid"
                        :title="referral.name"
                    >
                        {{ referral.name }}
                        <span v-if="referral.phone && referral.phone != ''">
                            <br />
                            {{ referral.phone }}
                        </span>
                    </a-select-option>
                </a-select>
            </a-col>
        </a-row>
        <a-row>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="Columns"
                        :row-key="(record) => record.xid"
                        :data-source="filteredValue"
                        :pagination="{ pageSize: 50 }"
                        :loading="loading"
                        id="referral_report"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'tax_no'">
                                {{ record.tax_number }}
                            </template>
                            <template
                                v-if="column.dataIndex === 'total_orders'"
                            >
                                {{ record.orders.length }}
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </a-card>
</template>

<script>
import { computed, onMounted, ref } from "vue";
import fields from "./fields";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import ExportTable from "../../../components/report-exports/ExportTable.vue";
export default {
    components: { AdminPageHeader, ExportTable },
    setup() {
        const { Columns } = fields();
        const referralData = ref([]);
        const filteredValue = ref([]);
        const referralUsers = ref([]);
        const searchValue = ref(undefined);
        const loading = ref(true);

        const handelSearch = (e) => {
            searchValue.value = e;
        };

        const watchFilterData = computed(() => {
            if (searchValue.value) {
                let filteredData = referralData.value.filter((item) => {
                    return item.xid == searchValue.value;
                });

                filteredValue.value = filteredData;
            } else {
                filteredValue.value = referralData.value;
            }
        });

        const getData = async () => {
            loading.value = true;
            await axiosAdmin
                .get("referral")
                .then((res) => {
                    filteredValue.value = res.data;
                    referralData.value = res.data;
                    loading.value = false;
                })
                .catch((err) => {
                    console.log(err);
                });
        };

        const getReferralUsers = async () => {
            loading.value = true;
            await axiosAdmin
                .get(
                    "referral?fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&order=id desc&offset=0&limit=10"
                )
                .then((res) => {
                    referralUsers.value = res.data;
                    loading.value = false;
                });
        };

        onMounted(() => {
            getData();
            getReferralUsers();
        });

        return {
            Columns,
            referralData,
            referralUsers,
            handelSearch,
            searchValue,
            filteredValue,
            loading,
            watchFilterData,
        };
    },
};
</script>

<style></style>
