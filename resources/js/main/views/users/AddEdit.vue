<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :visible="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <a-row>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        :label="$t('user.profile_image')"
                        name="profile_image"
                        :help="
                            rules.profile_image
                                ? rules.profile_image.message
                                : null
                        "
                        :validateStatus="rules.profile_image ? 'error' : null"
                    >
                        <Upload
                            :formData="formData"
                            folder="user"
                            imageField="profile_image"
                            @onFileUploaded="
                                (file) => {
                                    formData.profile_image = file.file;
                                    formData.profile_image_url = file.file_url;
                                }
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="18" :lg="18">
                    <a-row :gutter="16">
                        {{ handleWarehouseChange(formData.warehouse_id) }}
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                name="warehouse_id"
                                :help="
                                    rules.warehouse_id
                                        ? rules.warehouse_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.warehouse_id ? 'error' : null
                                "
                                class="required"
                            >
                                <template #label>
                                    {{ $t("warehouse.warehouse") }}
                                </template>
                                <span
                                    v-if="permsArray.includes('admin')"
                                    style="display: flex"
                                >
                                    <a-select
                                        v-model:value="formData.warehouse_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('warehouse.warehouse'),
                                            ])
                                        "
                                    >
                                        <a-select-option
                                            v-for="warehouse in warehouses"
                                            :key="warehouse.xid"
                                            :value="warehouse.xid"
                                        >
                                            {{ warehouse.name }}
                                        </a-select-option>
                                    </a-select>
                                    <WarehouseAddButton
                                        @onAddSuccess="warehouseAdded"
                                    />
                                </span>
                                <span v-else>
                                    <a-select
                                        :value="selectedWarehouse.xid"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('warehouse.warehouse'),
                                            ])
                                        "
                                        disabled
                                    >
                                        <a-select-option
                                            :value="selectedWarehouse.xid"
                                        >
                                            {{ selectedWarehouse.name }}
                                        </a-select-option>
                                    </a-select>
                                </span>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row
                        :gutter="16"
                        v-if="formData.user_type == 'customers'"
                    >
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                name="assign_to"
                                :help="
                                    rules.assign_to
                                        ? rules.assign_to.message
                                        : null
                                "
                                :validateStatus="
                                    rules.assign_to ? 'error' : null
                                "
                            >
                                <template #label> Assign To </template>
                                <a-select
                                    v-model:value="formData.assign_to"
                                    :placeholder="`${$t(
                                        'common.select_default_text'
                                    )} Staff members`"
                                    :allowClear="true"
                                    optionFilterProp="title"
                                    show-search
                                    :disabled="UpdateToAllWarehouse"
                                >
                                    <a-select-option
                                        v-for="staff in staffs"
                                        :key="staff.xid"
                                        :value="staff.xid"
                                        :title="staff.name"
                                    >
                                        {{ staff.name }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row
                        :gutter="16"
                        v-if="
                            (formData.user_type == 'staff_members' ||
                                formData.user_type == 'delivery_partner') &&
                            permsArray.includes('admin')
                        "
                    >
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('user.role')"
                                name="role_id"
                                :help="
                                    rules.role_id ? rules.role_id.message : null
                                "
                                :validateStatus="rules.role_id ? 'error' : null"
                                class="required"
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.role_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('user.role'),
                                            ])
                                        "
                                        :allowClear="true"
                                    >
                                        <a-select-option
                                            v-for="role in roles"
                                            :key="role.xid"
                                            :value="role.xid"
                                        >
                                            {{ role.display_name }}
                                        </a-select-option>
                                    </a-select>
                                    <RoleAddButton @onAddSuccess="roleAdded" />
                                </span>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.name'),
                                        ])
                                    "
                                    maxLength="49"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.phone')"
                                name="phone"
                                :help="rules.phone ? rules.phone.message : null"
                                :validateStatus="rules.phone ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.phone"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.phone'),
                                        ])
                                    "
                                    @input="validateNumber"
                                    :maxlength="appSetting.max_mobile_digit"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                                :class="{
                                    required:
                                        formData.user_type == 'staff_members' ||
                                        formData.user_type ==
                                            'delivery_partner',
                                }"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.email'),
                                        ])
                                    "
                                    maxLength="99"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.status')"
                                name="status"
                                :help="
                                    rules.status ? rules.status.message : null
                                "
                                :validateStatus="rules.status ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.status"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('user.status'),
                                        ])
                                    "
                                >
                                    <a-select-option value="enabled"
                                        >Enabled</a-select-option
                                    >
                                    <a-select-option value="disabled"
                                        >Disabled</a-select-option
                                    >
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row>
                        <a-col
                            :xs="24"
                            :sm="24"
                            :md="24"
                            :lg="24"
                            v-if="
                                formData.user_type == 'staff_members' ||
                                formData.user_type == 'delivery_partner'
                            "
                        >
                            <a-form-item
                                :label="$t('user.type')"
                                name="accounttype"
                                :help="
                                    rules.accounttype
                                        ? rules.accounttype.message
                                        : null
                                "
                                :validateStatus="
                                    rules.accounttype ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.accounttype"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="0">
                                        Normal
                                    </a-radio-button>
                                    <a-radio-button :value="1">
                                        Private
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col
                            :xs="24"
                            :sm="24"
                            :md="24"
                            :lg="24"
                            v-if="formData.user_type == 'staff_members'"
                        >
                            <a-form-item
                                :label="$t('user.login_access')"
                                name="login_access"
                                :help="
                                    rules.login_access
                                        ? rules.login_access.message
                                        : null
                                "
                                :validateStatus="
                                    rules.login_access ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.login_access"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="0">
                                        Both
                                    </a-radio-button>
                                    <a-radio-button :value="1">
                                        Desktop
                                    </a-radio-button>
                                    <a-radio-button :value="2">
                                        Mobile
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col
                            :span="24"
                            v-if="
                                formData.user_type == 'staff_members' ||
                                formData.user_type == 'delivery_partner' ||
                                formData.user_type == 'customers'
                            "
                        >
                            <a-form-item
                                :label="$t('user.password')"
                                name="password"
                                :help="
                                    rules.password
                                        ? rules.password.message
                                        : null
                                "
                                :validateStatus="
                                    rules.password ? 'error' : null
                                "
                                :class="
                                    formData.user_type == 'customers'
                                        ? ''
                                        : 'required'
                                "
                            >
                                <a-input-password
                                    v-model:value="formData.password"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.password'),
                                        ])
                                    "
                                    :disabled="UpdateToAllWarehouse"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row
                        :gutter="16"
                        v-if="
                            formData.user_type != 'staff_members' &&
                            formData.user_type != 'delivery_partner'
                        "
                    >
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('tax.tax_no')"
                                name="tax_number"
                                :help="
                                    rules.tax_number
                                        ? rules.tax_number.message
                                        : null
                                "
                                :validateStatus="
                                    rules.tax_number ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.tax_number"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('tax.tax_no'),
                                        ])
                                    "
                                    maxLength="49"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col
                            :xs="24"
                            :sm="24"
                            :md="12"
                            :lg="12"
                            v-if="formData.user_type === 'customers'"
                        >
                            <a-form-item
                                :label="$t('user.is_wholesale_customer')"
                                name="is_wholesale_customer"
                                :help="
                                    rules.is_wholesale_customer
                                        ? rules.is_wholesale_customer.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_wholesale_customer ? 'error' : null
                                "
                            >
                                <a-switch
                                    v-model:checked="
                                        formData.is_wholesale_customer
                                    "
                                    :checkedValue="1"
                                    :unCheckedValue="0"
                                    :disabled="UpdateToAllWarehouse"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row
                        :gutter="16"
                        v-if="
                            formData.user_type != 'staff_members' &&
                            formData.user_type != 'referral' &&
                            formData.user_type != 'delivery_partner'
                        "
                    >
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('user.opening_balance')"
                                name="opening_balance"
                                :help="
                                    rules.opening_balance
                                        ? rules.opening_balance.message
                                        : null
                                "
                                :validateStatus="
                                    rules.opening_balance ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.opening_balance"
                                    placeholder="0"
                                    :disabled="UpdateToAllWarehouse"
                                >
                                    <template #prefix>
                                        {{ appSetting.currency.symbol }}
                                    </template>
                                    <template #addonAfter>
                                        <a-select
                                            v-model:value="
                                                formData.opening_balance_type
                                            "
                                            style="width: 100px"
                                            :disabled="UpdateToAllWarehouse"
                                        >
                                            <a-select-option value="receive">
                                                {{ $t("user.receive") }}
                                            </a-select-option>
                                            <a-select-option value="pay">
                                                {{ $t("user.pay") }}
                                            </a-select-option>
                                        </a-select>
                                    </template>
                                </a-input>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-col>
            </a-row>

            <a-row
                :gutter="16"
                v-if="
                    formData.user_type != 'staff_members' &&
                    formData.user_type != 'referral' &&
                    formData.user_type != 'delivery_partner'
                "
            >
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('user.credit_period')"
                        name="credit_period"
                        :help="
                            rules.credit_period
                                ? rules.credit_period.message
                                : null
                        "
                        :validateStatus="rules.credit_period ? 'error' : null"
                    >
                        <a-input-number
                            v-model:value="formData.credit_period"
                            placeholder="0"
                            :addon-after="$t('user.days')"
                            type="number"
                            :precision="0"
                            :style="{ width: '100%' }"
                            :disabled="UpdateToAllWarehouse"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('user.credit_limit')"
                        name="credit_limit"
                        :help="
                            rules.credit_limit
                                ? rules.credit_limit.message
                                : null
                        "
                        :validateStatus="rules.credit_limit ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.credit_limit"
                            placeholder="0"
                            :addon-before="appSetting.currency.symbol"
                            :disabled="UpdateToAllWarehouse"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="
                            formData.user_type != 'staff_members' ||
                            formData.user_type != 'referral'
                                ? $t('user.address')
                                : $t('user.billing_address')
                        "
                        name="address"
                        :help="rules.address ? rules.address.message : null"
                        :validateStatus="rules.address ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.address"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    formData.user_type != 'staff_members' ||
                                    formData.user_type != 'referral' ||
                                    formData.user_type != 'delivery_partner'
                                        ? $t('user.address')
                                        : $t('user.billing_address'),
                                ])
                            "
                            :auto-size="{ minRows: 2, maxRows: 3 }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16" v-if="
                    formData.user_type != 'staff_members' &&
                    formData.user_type != 'referral' &&
                    formData.user_type != 'delivery_partner'
                ">
                <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item
                        :label="$t('warehouse.pincode')"
                        name="pincode"
                        :help="rules.pincode ? rules.pincode.message : null"
                        :validateStatus="rules.pincode ? 'error' : null"
                    >
                        <a-select
                            v-model:value="formData.pincode"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('warehouse.pincode'),
                                ])
                            "
                            allowClear
                            :disabled="UpdateToAllWarehouse"
                        >
                            <a-select-option
                                v-for="pincode in pincodes"
                                :key="pincode"
                                :value="pincode"
                            >
                                {{ pincode }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item
                        :label="$t('warehouse.location')"
                        name="location"
                        :help="rules.location ? rules.location.message : null"
                        :validateStatus="rules.location ? 'error' : null"
                    >
                        <a-select
                            v-model:value="formData.location"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('warehouse.location'),
                                ])
                            "
                            allowClear
                            :disabled="UpdateToAllWarehouse"
                        >
                            <a-select-option
                                v-for="location in locations"
                                :key="location"
                                :value="location"
                            >
                                {{ location }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="8" :md="8" :lg="8">
                    <a-form-item
                        :label="$t('user.business_type')"
                        name="business_type"
                        :help="
                            rules.business_type
                                ? rules.business_type.message
                                : null
                        "
                        :validateStatus="rules.business_type ? 'error' : null"
                    >
                        <a-select
                            v-model:value="formData.business_type"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('warehouse.business_type'),
                                ])
                            "
                            allowClear
                            :disabled="UpdateToAllWarehouse"
                        >
                            <a-select-option
                                v-for="businessType in businessTypes"
                                :key="businessType"
                                :value="businessType"
                            >
                                {{ businessType }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row
                :gutter="16"
                v-if="
                    formData.user_type != 'delivery_partner' &&
                    formData.user_type != 'referral' &&
                    formData.user_type != 'staff_members'
                "
            >
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('user.shipping_address')"
                        name="shipping_address"
                        :help="
                            rules.shipping_address
                                ? rules.shipping_address.message
                                : null
                        "
                        :validateStatus="
                            rules.shipping_address ? 'error' : null
                        "
                    >
                        <a-textarea
                            v-model:value="formData.shipping_address"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('user.shipping_address'),
                                ])
                            "
                            :auto-size="{ minRows: 2, maxRows: 3 }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>

            <a-button
                type="primary"
                @click="onSubmit"
                style="margin-left: 8px"
                :loading="loading"
                :disabled="error"
            >
                <template #icon> <SaveOutlined /> </template>
                {{
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import { useStore } from "vuex";
import apiAdmin from "../../../common/composable/apiAdmin";
import Upload from "../../../common/core/ui/file/Upload.vue";
import WarehouseAddButton from "../settings/warehouses/AddButton.vue";
import RoleAddButton from "../settings/roles/AddButton.vue";
import common from "../../../common/composable/common";
import TooltipWarehouse from "./TooltipWarehouse.vue";
import { rule } from "postcss";

export default defineComponent({
    props: [
        "addEditData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        Upload,
        RoleAddButton,
        WarehouseAddButton,
        TooltipWarehouse,
    },
    setup(props, { emit }) {
        const { permsArray, user, appSetting, selectedWarehouse } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const roles = ref([]);
        const warehouses = ref([]);
        const staffs = ref([]);
        const formData = ref({});
        const roleUrl = "roles?limit=10000";
        const warehouseUrl = "warehouses?limit=10000";
        const staffUrl =
            "users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,pincode,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}";
        const store = useStore();

        const pincodes = ref([]);
        const locations = ref([]);
        const businessTypes = ref([]);
        const UpdateToAllWarehouse = ref(false);
        const error = ref(false);

        onMounted(() => {
            pincodes.value = selectedWarehouse.value.pincode
                ? selectedWarehouse.value.pincode.split(",")
                : [];
            locations.value = selectedWarehouse.value.location
                ? selectedWarehouse.value.location.split(",")
                : [];
            businessTypes.value = selectedWarehouse.value.business_type
                ? selectedWarehouse.value.business_type.split(",")
                : [];

            const rolesPromise = axiosAdmin.get(roleUrl);
            const warehousesPromise = axiosAdmin.get(warehouseUrl);
            const staffsPromise = axiosAdmin.get(staffUrl);

            Promise.all([rolesPromise, warehousesPromise, staffsPromise]).then(
                ([rolesResponse, warehousesResponse, staffsResponse]) => {
                    roles.value = rolesResponse.data;
                    warehouses.value = warehousesResponse.data;
                    staffs.value = staffsResponse.data;
                }
            );

            formData.value = { ...props.addEditData };
        });

        const onSubmit = () => {
            if (UpdateToAllWarehouse.value) {
                addEditRequestAdmin({
                    url: "customer-update",
                    data: {
                        ...formData.value,
                        _method: undefined,
                        id: props.url.split("/")[1],
                    },
                    successMessage: props.successMessage,
                    success: (res) => {
                        emit("addEditSuccess");
                    },
                });
            } else {
                addEditRequestAdmin({
                    url: props.url,
                    data: formData.value,
                    successMessage: props.successMessage,
                    success: (res) => {
                        emit("addEditSuccess", res.xid);

                        if (user.value.xid == res.xid) {
                            store.dispatch("auth/updateUser");
                        }
                    },
                });
            }
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const roleAdded = () => {
            axiosAdmin.get(roleUrl).then((response) => {
                roles.value = response.data;
            });
        };

        const warehouseAdded = () => {
            axiosAdmin.get(warehouseUrl).then((response) => {
                warehouses.value = response.data;
            });
        };

        watch(props, (newVal, oldVal) => {
            formData.value =
                newVal.addEditType == "add"
                    ? {
                          ...newVal.addEditData,
                          warehouse_id: selectedWarehouse.value.xid,
                      }
                    : {
                          ...newVal.addEditData,
                          role_id:
                              newVal.data.role && newVal.data.role.xid
                                  ? newVal.data.role.xid
                                  : undefined,
                          warehouse_id:
                              newVal.data.warehouse && newVal.data.warehouse.xid
                                  ? newVal.data.warehouse.xid
                                  : selectedWarehouse.value.xid,
                          opening_balance:
                              newVal.data.details &&
                              newVal.data.details.opening_balance
                                  ? newVal.data.details.opening_balance
                                  : undefined,
                          opening_balance_type:
                              newVal.data.details &&
                              newVal.data.details.opening_balance_type
                                  ? newVal.data.details.opening_balance_type
                                  : undefined,
                          credit_period:
                              newVal.data.details &&
                              newVal.data.details.credit_period
                                  ? newVal.data.details.credit_period
                                  : undefined,
                          credit_limit:
                              newVal.data.details &&
                              newVal.data.details.credit_limit
                                  ? newVal.data.details.credit_limit
                                  : undefined,
                          _method: "PUT",
                      };

            if (
                props.addEditData.user_type === "customers" &&
                props.addEditType == "edit"
            ) {
                warehouses.value.push({ name: "All", xid: "all" });
            }

            if (
                props.addEditData.user_type === "customers" &&
                props.addEditType == "edit" &&
                !props.visible
            ) {
                warehouses.value = warehouses.value.filter(
                    (w) => w.xid != "all"
                );
            }
        });

        const handleWarehouseChange = (value) => {
            if (value == "all") UpdateToAllWarehouse.value = true;
            else UpdateToAllWarehouse.value = false;
        };

        const validateNumber = (event) => {
            formData.value.phone = event.target.value.replace(/[^0-9]/g, "");
            if (
                formData.value.phone.length < appSetting.value.max_mobile_digit
            ) {
                error.value = true;
                rules.value = {
                    phone: {
                        message: `The phone must be ${appSetting.value.max_mobile_digit} digits`,
                        required: true,
                    },
                };
            } else {
                error.value = false;
                rules.value = {};
            }
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,
            warehouses,
            formData,

            roleAdded,
            warehouseAdded,
            permsArray,
            appSetting,
            selectedWarehouse,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
            staffs,
            pincodes,
            locations,
            businessTypes,
            UpdateToAllWarehouse,
            handleWarehouseChange,
            validateNumber,
            error,
        };
    },
});
</script>
