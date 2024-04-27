<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header
                :title="`${$t('menu.staff')} ${$t('menu.reports')}`"
                class="p-0"
            >
                <template #extra>
                    <ExportTable
                        exportType="staff_report"
                        tableName="staff_report"
                        :title="`${$t('menu.staff')} ${$t('menu.reports')}`"
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
                    {{ $t(`menu.staff`) }}
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
                        $t('common.select_default_text', [$t(`staff`)])
                    "
                    :allowClear="true"
                    optionFilterProp="title"
                    show-search
                    @change="handelSearch"
                    style="width: 100%"
                >
                    <a-select-option
                        v-for="staff in staffMembers"
                        :key="staff.xid"
                        :value="staff.xid"
                        :title="staff.name"
                    >
                        {{ staff.name }}
                        <span v-if="staff.phone && staff.phone != ''">
                            <br />
                            {{ staff.phone }}
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
                        id="staff_report"
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
        const staffSalesReport = ref([]);
        const filteredValue = ref([]);
        const staffMembers = ref([]);
        const searchValue = ref(undefined);
        const loading = ref(true);

        const handelSearch = (e) => {
            searchValue.value = e;
        };

        const watchFilterData = computed(() => {
            if (searchValue.value) {
                let filteredData = staffSalesReport.value.filter((item) => {
                    return item.xid == searchValue.value;
                });

                filteredValue.value = filteredData;
            } else {
                filteredValue.value = staffSalesReport.value;
            }
        });

        const getData = async () => {
            loading.value = true;
            await axiosAdmin
                .get("staff_members")
                .then((res) => {
                    filteredValue.value = res.data;
                    staffSalesReport.value = res.data;
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
                    "users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,pincode,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&limit=10000"
                )
                .then((res) => {
                    staffMembers.value = res.data;
                    loading.value = false;
                });
        };

        onMounted(() => {
            getData();
            getReferralUsers();
        });

        return {
            Columns,
            staffSalesReport,
            staffMembers,
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
