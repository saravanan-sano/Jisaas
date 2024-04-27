<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.${userType}`)" class="p-0">
                <template
                    v-if="
                        permsArray.includes(`${userType}_create`) ||
                        permsArray.includes('admin')
                    "
                    #extra
                >
                    <a-space>
                        <ImportUsers
                            v-if="
                                userType == 'customers' &&
                                permsArray.includes('admin')
                            "
                            :pageTitle="$t('customer.update_customer')"
                            :export="true"
                            :sampleFileUrl="sampleFileUrl"
                            importUrl="customers/update-import"
                            :exportUrl="exportUrl"
                            @onUploadSuccess="setUrlData"
                        />
                        <ImportUsers
                            :pageTitle="importPageTitle"
                            :sampleFileUrl="sampleFileUrl"
                            :importUrl="importUrl"
                            @onUploadSuccess="setUrlData"
                        />
                        <a-button type="primary" @click="addItem">
                            <PlusOutlined />
                            {{ $t(`${langKey}.add`) }}
                        </a-button>
                    </a-space>
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
                    {{ $t(`menu.parties`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.${userType}`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :addEditData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />
        <a-row
            v-if="
                (userType && userType == 'users') ||
                userType == 'delivery_partner'
            "
        >
            <a-col :span="24">
                <a-tabs v-model:activeKey="searchStatus" @change="setUrlData">
                    <a-tab-pane
                        key="all"
                        :tab="`${$t('common.all')} ${$t('menu.users')}`"
                    />
                    <a-tab-pane
                        v-for="usrStatus in userStatus"
                        :key="usrStatus.key"
                        :tab="`${usrStatus.value} ${$t('menu.users')}`"
                    />
                </a-tabs>
            </a-col>
        </a-row>

        <a-row
            v-if="
                userType &&
                userType != 'users' &&
                userType != 'delivery_partner'
            "
        >
            <a-col :span="24">
                <a-tabs v-model:activeKey="searchDueType" @change="setUrlData">
                    <a-tab-pane key="all" :tab="`${$t('common.all')}`" />
                    <a-tab-pane
                        key="receive"
                        :tab="`${$t('user.to_receive')}`"
                    />
                    <a-tab-pane key="pay" :tab="`${$t('user.to_pay')}`" />
                </a-tabs>
            </a-col>
        </a-row>

        <a-row :gutter="[15, 15]" style="mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <a-input-group compact>
                    <a-select
                        style="width: 25%"
                        v-model:value="table.searchColumn"
                        :placeholder="$t('common.select_default_text', [''])"
                    >
                        <a-select-option
                            v-for="filterableColumn in filterableColumns"
                            :key="filterableColumn.key"
                        >
                            {{ filterableColumn.value }}
                        </a-select-option>
                    </a-select>
                    <a-input-search
                        style="width: 75%"
                        v-model:value="table.searchString"
                        show-search
                        @change="onTableSearch"
                        @search="onTableSearch"
                        :loading="table.filterLoading"
                    />
                </a-input-group>
            </a-col>
            <a-col
                v-if="userType && userType != 'users'"
                :xs="24"
                :sm="24"
                :md="12"
                :lg="4"
                :xl="4"
            >
                <a-select
                    v-model:value="searchStatus"
                    @change="setUrlData"
                    show-search
                    style="width: 100%"
                    :placeholder="
                        $t('common.select_default_text', [$t('user.status')])
                    "
                    :allowClear="true"
                    optionFilterProp="label"
                >
                    <a-select-option
                        v-for="usrStatus in userStatus"
                        :key="usrStatus.key"
                        :value="usrStatus.key"
                        :label="usrStatus.value"
                    >
                        {{ usrStatus.value }}
                    </a-select-option>
                </a-select>
            </a-col>
            <a-col
                v-if="userType && userType == 'customers'"
                :xs="24"
                :sm="24"
                :md="12"
                :lg="4"
                :xl="4"
            >
                <a-select
                    v-model:value="searchAssignedTo"
                    @change="setUrlData"
                    show-search
                    style="width: 100%"
                    :placeholder="
                        $t('common.select_default_text', [
                            $t(`staff_member.staff`),
                        ])
                    "
                    :allowClear="true"
                    optionFilterProp="title"
                >
                    <a-select-option
                        v-for="staff in staffs"
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

        <a-row :gutter="[15, 15]" class="mt-20">
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="tableColumns"
                        :row-key="(record) => record.xid"
                        :data-source="TableData"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                    >
                        <template #bodyCell="{ column, text, record }">
                            <template v-if="column.dataIndex === 'name'">
                                <user-info :user="record" />
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="statusColors[text]">
                                    {{ $t(`common.${text}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'created_at'">
                                {{ formatDateTime(record.created_at) }}
                            </template>
                            <template v-if="column.dataIndex === 'balance'">
                                <UserBalance
                                    :amount="record.details.due_amount"
                                />
                            </template>
                            <template v-if="column.dataIndex === 'role'">
                                {{ record.role.display_name }}
                            </template>
                            <template v-if="column.dataIndex === 'assign_to'">
                                <assigned-staff-info :assign_to="record.assign_to"  :staffMember="staffs"/>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    type="primary"
                                    @click="viewItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EyeOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="
                                        permsArray.includes(
                                            `${userType}_edit`
                                        ) || permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="
                                        (permsArray.includes(
                                            `${userType}_delete`
                                        ) ||
                                            permsArray.includes('admin')) &&
                                        user.xid != record.xid &&
                                        !record.is_walkin_customer
                                    "
                                    type="primary"
                                    @click="showDeactivateConfirm(record.xid)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon
                                        ><DeleteOutlined
                                    /></template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
        {{ setData }}
        <UserView
            :user="viewData"
            :visible="detailsVisible"
            @closed="onCloseDetails"
        />
    </a-card>
</template>

<script>
import { computed, createVNode, onMounted, ref, watch } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    UserOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined,
    ExclamationCircleOutlined,
} from "@ant-design/icons-vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import UserInfo from "../../../common/components/user/UserInfo.vue";
import StateWidget from "../../../common/components/common/card/StateWidget.vue";
import AddEdit from "./AddEdit.vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import UserBalance from "./UserBalance.vue";
import UserView from "./View.vue";
import ImportUsers from "../../../common/core/ui/Import.vue";
import { Modal, notification } from "ant-design-vue";
import AssignedStaffInfo from '../../../common/components/user/AssignedStaffInfo.vue';

export default {
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        UserOutlined,
        ArrowUpOutlined,
        ArrowDownOutlined,
        AddEdit,
        UserInfo,

        StateWidget,
        AdminPageHeader,
        UserBalance,
        UserView,
        ImportUsers,
        AssignedStaffInfo,
    },
    setup() {
        const { t } = useI18n();
        const {
            statusColors,
            userStatus,
            permsArray,
            formatDateTime,
            user,
            selectedWarehouse,
            filterCustomers,
        } = common();
        const {
            supplierInitData,
            customerInitData,
            referralInitData,
            staffMemberInitData,
            DeliveryPartnerInitData,
            columns,
            UserInitData,
            supplierCustomerColumns,
            StaffColumns,
            filterableColumns,
        } = fields();
        const staffs = ref([]);
        const crudVariables = crud();
        const route = useRoute();
        const userType = ref(route.meta.menuKey);
        const crudKey = ref(route.meta.crudKey);
        const staffUrl = `users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,pincode,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}`;

        const urlParams = `?${
            route.meta.menuKey == "customers"
                ? ""
                : `user_type=${route.meta.menuKey}&&`
        }fields=id,xid,assign_to,user_type,addional_documents,accounttype,name,email,profile_image,profile_image_url,tax_number,pincode,location,business_type,is_walkin_customer,is_wholesale_customer,phone,address,shipping_address,status,login_access,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}${
            route.meta.menuKey == "customers" ? filterCustomers : ""
        }`;

        const searchStatus = ref(undefined);
        const searchAssignedTo = ref(undefined);
        const activeTabKey = ref("all");
        const searchDueType = ref("all");
        const tableColumns = ref([]);
        const sampleFileUrl = ref("");
        const importPageTitle = ref("");
        const importUrl = ref("");
        const TableData = ref([]);
        const exportUrl = ref("");

        onMounted(() => {
            exportUrl.value = `${window.location.origin}/api/export_customer`;
            axiosAdmin.get(staffUrl).then((res) => {
                staffs.value = res.data;
            });
            setUrlData();
            crudVariables.langKey.value = "staff_member";
            crudVariables.table.filterableColumns = filterableColumns;
            setFormData();
        });

        const setData = computed(() => {
            if (
                crudVariables.table.data.length > 0 ||
                searchStatus.value ||
                searchAssignedTo.value ||
                activeTabKey.value ||
                searchDueType.value
            ) {
                if (
                    selectedWarehouse.value.is_staff_base == 1 &&
                    !permsArray.value.includes("admin") &&
                    !permsArray.value.includes("view_all_customer") &&
                    userType.value == "customers"
                ) {
                    TableData.value = crudVariables.table.data.filter(
                        (customer) => customer.assign_to == user.value.xid
                    );
                    crudVariables.table.pagination.total =
                        TableData.value.length;
                } else {
                    TableData.value = crudVariables.table.data;
                }
            }
        });

        const setFormData = () => {
            // Added for Dynamic use case by saravanan

            crudVariables.initData.value = {
                ...UserInitData,
                user_type: userType.value,
            };
            crudVariables.formData.value = {
                ...UserInitData,
                user_type: userType.value,
            };
            let langKey = route.meta.langKey;
            crudVariables.langKey.value = langKey;
            if (userType.value == "delivery_partner") {
                tableColumns.value = columns;
            } else if (userType.value == "staff_members") {
                tableColumns.value = StaffColumns;
            } else {
                tableColumns.value = supplierCustomerColumns;
            }
            crudVariables.crudUrl.value = crudKey.value;
            sampleFileUrl.value =
                window.config[`${userType.value}_sample_file`];
            importPageTitle.value = t(`${langKey}.import_${userType.value}`);
            importUrl.value = `${crudKey.value}/import`;
            crudVariables.restFormData();
        };

        const setUrlData = () => {
            crudVariables.crudUrl.value = userType.value;
            var filterString = "";
            var extraFilters = {};
            if (
                searchStatus.value != undefined &&
                searchStatus.value != "all"
            ) {
                filterString += `status eq "${searchStatus.value}"`;
            }

            if (
                searchDueType.value != undefined &&
                searchDueType.value != "all" &&
                userType.value != "staff_members"
            ) {
                extraFilters.search_due_type = searchDueType.value;
            }
            if (searchAssignedTo.value != undefined) {
                extraFilters.assign_to = searchAssignedTo.value;
            }

            crudVariables.tableUrl.value = {
                url: `${
                    route.meta.menuKey == "customers" ? "customers" : "users"
                }${urlParams}`,
                filterString,
                extraFilters,
            };
            crudVariables.fetch({
                page: 1,
            });
        };

        watch(route, (newVal, oldVal) => {
            if (newVal.meta.menuParent == "users") {
                userType.value = newVal.meta.menuKey;

                searchStatus.value = undefined;
                searchAssignedTo.value = undefined;
                crudVariables.table.searchColumn = undefined;
                crudVariables.table.searchString = "";
                searchStatus.value = "all";
                searchDueType.value = "all";

                setUrlData();
                setFormData();
            }
        });

        watch(selectedWarehouse, (newVal, oldVal) => {
            setUrlData();
        });

        const showDeactivateConfirm = (id) => {
            Modal.confirm({
                title: t("common.delete") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`${route.meta.langKey}.delete_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    return axiosAdmin
                        .post("deactivate-user", { xid: id })
                        .then((successResponse) => {
                            crudVariables.fetch({
                                page: 1,
                            });
                            notification.success({
                                message: t("common.success"),
                                description: t(`${route.meta.langKey}.deleted`),
                                placement: "bottomRight",
                            });
                        })
                        .catch(() => {});
                },
                onCancel() {},
            });
        };

        return {
            statusColors,
            userStatus,
            filterableColumns,
            userType,
            tableColumns,

            searchStatus,
            searchAssignedTo,
            setUrlData,
            searchDueType,

            activeTabKey,

            ...crudVariables,
            permsArray,

            formatDateTime,
            user,

            sampleFileUrl,
            importPageTitle,
            importUrl,

            TableData,
            setData,
            showDeactivateConfirm,
            staffs,
            exportUrl,
        };
    },
};
</script>
