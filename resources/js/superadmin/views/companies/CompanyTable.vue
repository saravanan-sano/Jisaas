<template>
    <AddEdit :addEditType="addEditType" :visible="addEditVisible" :url="addEditUrl" @addEditSuccess="addEditSuccess"
        @closed="onCloseAddEdit" :formData="formData" :data="viewData" :pageTitle="pageTitle"
        :successMessage="successMessage" />


    <a-row class="mb-20" v-if="showFilterInput">
        <a-input-group>
            <a-row :gutter="[15, 15]">
                <a-col :xs="24" :sm="24" :md="12" :lg="12" :xl="6">
                    <a-input-group compact>
                        <a-select style="width: 25%" v-model:value="table.searchColumn"
                            :placeholder="$t('common.select_default_text', [''])">
                            <a-select-option v-for="filterableColumn in filterableColumns" :key="filterableColumn.key">
                                {{ filterableColumn.value }}
                            </a-select-option>
                        </a-select>
                        <a-input-search style="width: 75%" v-model:value="table.searchString" show-search
                            @change="onTableSearch" @search="onTableSearch" :loading="table.filterLoading" />
                    </a-input-group>
                </a-col>
            </a-row>
        </a-input-group>
    </a-row>

    <a-row>
        <a-col :span="24">
            <div class="table-responsive">
                <a-table :columns="columns" :row-key="(record) => record.xid" :data-source="table.data"
                    :pagination="table.pagination" :loading="table.loading" @change="handleTableChange" bordered>
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'logo'">
                            <a-image :width="150" :src="record.light_logo_url" />
                        </template>
                        <template v-if="column.dataIndex === 'name'">
                            <a-typography-text strong>
                                {{ record.name }}
                            </a-typography-text>
                        </template>
                        <template v-if="column.dataIndex === 'details'">
                            <p style="text-align: left;">
                                {{ $t("company.name") }}:
                                <b> {{ record.name }}</b><br>
                                {{ $t("company.email") }}:
                                <b> {{ record.email }}</b><br>
                                {{ $t("company.phone") }}:
                                <b> {{ record?.users?.mobile }}</b>
                            <ul style="padding-left: 17px;">

                                <li>
                                    {{ $t("common.verified") }}:
                                    <CloseOutlined v-if="record?.users?.is_verified == 0" :style="{ color: 'red' }" />
                                    <CheckOutlined v-else :style="{ color: 'green' }" />
                                </li>
                                <li>
                                    {{ $t("Google Login") }}:
                                    <CloseOutlined v-if="record?.is_googlelogin == 0" :style="{ color: 'red' }" />
                                    <CheckOutlined v-else :style="{ color: 'green' }" />
                                </li>
                                <li>
                                    {{ $t("company.register_date") }}:
                                    {{ formatDate(record.created_at) }}
                                </li>
                                <li>
                                    {{ $t("company.total_users") }}:
                                    {{ record.total_users }}
                                </li>
                                <!-- <li  v-if="record.product != null">
                                    {{ $t("company.total_users") }}:
                                    {{ record.product.length}}
                                </li> -->
                            </ul>
                            </p>
                        </template>
                        <template v-if="column.dataIndex === 'subscription_plan'">
                            <span v-if="record.subscription_plan &&
                                record.subscription_plan.name
                                ">
                                {{
                                    record.subscription_plan
                                    ? record.subscription_plan.name
                                    : "-"
                                }}
                                ({{ record.package_type }})
                                <br />
                            </span>
                            <ChangeSubscriptionPlan :company="record" @success="setUrlData" />
                        </template>
                        <template v-if="column.dataIndex === 'status'">
                            <CompanyStatus :status="record.status" />
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-button type="primary" @click="viewItem(record)" style="margin-left: 4px">
                                <template #icon>
                                    <InfoCircleOutlined />
                                </template>
                            </a-button>
                            <a-button type="primary" @click="editItem(record)" style="margin-left: 4px">
                                <template #icon>
                                    <EditOutlined />
                                </template>
                            </a-button>
                            <a-button type="primary" @click="showDeleteConfirm(record.xid)" style="margin-left: 4px">
                                <template #icon>
                                    <DeleteOutlined />
                                </template>
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>
</template>
<script>
import { onMounted } from "vue";
import {
    EditOutlined,
    DeleteOutlined,
    CheckOutlined,
    CloseOutlined,
    InfoCircleOutlined,
} from "@ant-design/icons-vue";
import fields from "./fields";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import CompanyStatus from "../../../main/components/company/CompanyStatus.vue";
import ChangeSubscriptionPlan from "./ChangeSubscriptionPlan.vue";

export default {
    props: {
        showFilterInput: {
            default: true,
        },
        showPagination: {
            default: true,
        },
        perPageItems: {
            default: 10,
        },
    },
    components: {
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        CompanyStatus,
        CheckOutlined,
        CloseOutlined,
        InfoCircleOutlined,

        ChangeSubscriptionPlan,
    },
    setup(props) {
        const {
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            multiDimensalObjectColumns,
        } = fields();
        const crudVariables = crud();
        const { permsArray, formatDate } = common();

        onMounted(() => {
            crudVariables.table.pagination = {
                pageSize: props.perPageItems,
                showSizeChanger: true,
            };

            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url:
                    "superadmin/companies?fields=id,xid,name,short_name,email,phone,website,light_logo,light_logo_url,is_googlelogin,dark_logo,app_layout,dark_logo_url,small_dark_logo,small_dark_logo_url,small_light_logo,small_light_logo_url,address,timezone,status,package_type,admin_id,x_admin_id,admin{id,xid,name,email},total_users,created_at,verified,subscription_plan_id,x_subscription_plan_id,subscriptionPlan{id,xid,name},'payment_transcation_id',x_payment_transcation_id',paymentTranscation{id,xid,paid_on,next_payment_date},users{name,email,contact_name,is_verified,country_code,mobile},product{name}",
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "company";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.multiDimensalObjectColumns.value = {
                ...multiDimensalObjectColumns,
            };
        };

        return {
            columns,
            filterableColumns,
            permsArray,
            ...crudVariables,
            setUrlData,
            formatDate,
        };
    },
};
</script>
