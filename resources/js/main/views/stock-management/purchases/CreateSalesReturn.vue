<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header
                :title="$t(`menu.${orderPageObject.menuKey}`)"
                @back="() => $router.go(-1)"
                class="p-0"
            >
                <template #extra>
                    <a-button
                        type="primary"
                        :loading="loading"
                        @click="onSubmit"
                        block
                        :disabled="
                            formData.subtotal <= 0 ||
                            formData.user_id == undefined ||
                            formData.user_id == '' ||
                            !formData.user_id
                        "
                    >
                        <template #icon> <SaveOutlined /> </template>
                        {{ $t("common.save") }}
                    </a-button>
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
                    <router-link
                        :to="{
                            name: `admin.stock.${orderPageObject.type}.index`,
                        }"
                    >
                        {{ $t(`menu.${orderPageObject.menuKey}`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`common.create`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                    <a-form-item
                        :label="$t(`stock.invoice_number`)"
                        name="invoice_number"
                        :help="
                            rules.invoice_number
                                ? rules.invoice_number.message
                                : null
                        "
                        :validateStatus="rules.invoice_number ? 'error' : null"
                        class="required"
                    >
                        <a-input-search
                            v-model:value="invoiceNumber"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('stock.invoice_number'),
                                ])
                            "
                            @search="SearchInvoice"
                        />
                    </a-form-item>
                </a-col>
                <a-col
                    v-if="orderPageObject.type == 'stock-transfers'"
                    :xs="24"
                    :sm="24"
                    :md="8"
                    :lg="8"
                >
                    <a-form-item
                        :label="$t(`${orderPageObject.langKey}.warehouse`)"
                        name="warehouse_id"
                        :help="
                            rules.warehouse_id
                                ? rules.warehouse_id.message
                                : null
                        "
                        :validateStatus="rules.warehouse_id ? 'error' : null"
                        class="required"
                    >
                        <span style="display: flex">
                            <a-select
                                v-model:value="formData.warehouse_id"
                                :placeholder="
                                    $t('common.select_default_text', [
                                        $t(
                                            `${orderPageObject.langKey}.warehouse`
                                        ),
                                    ])
                                "
                                :allowClear="true"
                                optionFilterProp="title"
                                show-search
                            >
                                <a-select-option
                                    v-for="warehouse in warehouses"
                                    :key="warehouse.xid"
                                    :value="warehouse.xid"
                                    :title="warehouse.name"
                                >
                                    {{ warehouse.name }}
                                </a-select-option>
                            </a-select>
                            <WarehouseAddButton
                                @onAddSuccess="warehouseAdded"
                            />
                        </span>
                    </a-form-item>
                </a-col>
                <a-col v-else :xs="24" :sm="24" :md="8" :lg="8">
                    <a-form-item
                        :label="$t(`${orderPageObject.langKey}.user`)"
                        name="user_id"
                        :help="rules.user_id ? rules.user_id.message : null"
                        :validateStatus="rules.user_id ? 'error' : null"
                        class="required"
                    >
                        <span style="display: flex">
                            <a-select
                                v-model:value="formData.user_id"
                                :placeholder="
                                    $t('common.select_default_text', [
                                        $t(`${orderPageObject.langKey}.user`),
                                    ])
                                "
                                :allowClear="true"
                                optionFilterProp="title"
                                show-search
                                @change="customerChanged"
                            >
                                <a-select-option
                                    v-for="user in users"
                                    :key="user.xid"
                                    :value="user.xid"
                                    :title="user.name"
                                >
                                    {{ user.name }}
                                    <span v-if="user.phone && user.phone != ''">
                                        <br />
                                        {{ user.phone }}
                                    </span>
                                </a-select-option>
                            </a-select>
                            <SupplierAddButton
                                v-if="orderPageObject.userType == 'suppliers'"
                                @onAddSuccess="userAdded"
                            />
                            <CustomerAddButton
                                v-else
                                @onAddSuccess="userAdded"
                            />
                        </span>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                    <a-form-item
                        :label="
                            $t(
                                `${orderPageObject.langKey}.${orderPageObject.langKey}_date`
                            )
                        "
                        name="order_date"
                        :help="
                            rules.order_date ? rules.order_date.message : null
                        "
                        :validateStatus="rules.order_date ? 'error' : null"
                        class="required"
                    >
                        <a-date-picker
                            v-model:value="formData.order_date"
                            :format="formatOrderDate"
                            :disabled-date="disabledDate"
                            show-time
                            :placeholder="$t('common.date_time')"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-table
                        :row-key="(record) => record.xid"
                        :dataSource="selectedProducts"
                        :columns="orderReturnItemColumns"
                        :pagination="false"
                        :loading="pageloader"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.dataIndex === 'sn'">
                                {{ index++ + 1 }}
                            </template>
                            <template v-if="column.dataIndex === 'name'">
                                {{ record.name }} <br />
                                <!-- <small>
                                    <a-typography-text code>
                                        {{ $t("product.avl_qty") }}
                                        {{
                                            `${record.stock_quantity}${record.unit_short_name}`
                                        }}
                                    </a-typography-text>
                                </small> -->
                            </template>
                            <template
                                v-if="column.dataIndex === 'unit_quantity'"
                            >
                                <a-input-number
                                    id="inputNumber"
                                    v-model:value="record.quantity"
                                    @change="quantityChanged(record)"
                                    :min="0"
                                    :max="record.return_quantity"
                                />
                            </template>
                            <template
                                v-if="column.dataIndex === 'single_unit_price'"
                            >
                                {{
                                    formatAmountCurrency(
                                        record.single_unit_price
                                    )
                                }}
                            </template>
                            <template
                                v-if="column.dataIndex === 'total_discount'"
                            >
                                {{ formatAmountCurrency(record.discount_rate) }}
                            </template>
                            <template v-if="column.dataIndex === 'total_tax'">
                                {{ formatAmountCurrency(record.tax_rate) }}
                            </template>
                            <template v-if="column.dataIndex === 'subtotal'">
                                {{ formatAmountCurrency(record.subtotal) }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <!-- <a-button
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button> -->
                                <a-button
                                    type="primary"
                                    @click="showDeleteConfirm(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon
                                        ><DeleteOutlined
                                    /></template>
                                </a-button>
                            </template>
                        </template>
                        <template #summary>
                            <a-table-summary-row>
                                <a-table-summary-cell
                                    :col-span="4"
                                ></a-table-summary-cell>
                                <a-table-summary-cell>
                                    {{ $t("product.subtotal") }}
                                </a-table-summary-cell>
                                <a-table-summary-cell>
                                    {{
                                        formatAmountCurrency(productsAmount.tax)
                                    }}
                                </a-table-summary-cell>
                                <a-table-summary-cell :col-span="2">
                                    {{
                                        formatAmountCurrency(
                                            productsAmount.subtotal
                                        )
                                    }}
                                </a-table-summary-cell>
                            </a-table-summary-row>
                        </template>
                    </a-table>
                </a-col>
            </a-row>

            <a-row :gutter="16" class="mt-30">
                <a-col :xs="24" :sm="24" :md="16" :lg="16">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.terms_condition')"
                                name="terms_condition"
                                :help="
                                    rules.terms_condition
                                        ? rules.terms_condition.message
                                        : null
                                "
                                :validateStatus="
                                    rules.terms_condition ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.terms_condition"
                                    :placeholder="
                                        $t('warehouse.terms_condition')
                                    "
                                    :auto-size="{ minRows: 2, maxRows: 3 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('stock.notes')"
                                name="notes"
                                :help="rules.notes ? rules.notes.message : null"
                                :validateStatus="rules.notes ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.notes"
                                    :placeholder="$t('stock.notes')"
                                    :auto-size="{ minRows: 2, maxRows: 3 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-col>
                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                    <a-row
                        :gutter="16"
                        v-if="orderPageObject.type != 'quotations'"
                    >
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('stock.status')"
                                name="order_status"
                                :help="
                                    rules.order_status
                                        ? rules.order_status.message
                                        : null
                                "
                                :validateStatus="
                                    rules.order_status ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.order_status"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('stock.status'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option
                                        v-for="status in allOrderStatus"
                                        :key="status.key"
                                        :value="status.key"
                                    >
                                        {{ status.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('stock.order_tax')"
                                name="tax_id"
                                :help="
                                    rules.tax_id ? rules.tax_id.message : null
                                "
                                :validateStatus="rules.tax_id ? 'error' : null"
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.tax_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('stock.order_tax'),
                                            ])
                                        "
                                        :allowClear="true"
                                        @change="taxChanged"
                                        optionFilterProp="title"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="tax in taxes"
                                            :key="tax.xid"
                                            :value="tax.xid"
                                            :title="tax.name"
                                            :tax="tax"
                                        >
                                            {{ tax.name }} ({{ tax.rate }}%)
                                        </a-select-option>
                                    </a-select>
                                    <TaxAddButton @onAddSuccess="taxAdded" />
                                </span>
                            </a-form-item>
                        </a-col>
                    </a-row> -->
                    <!-- <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('stock.discount')"
                                name="discount"
                                :help="
                                    rules.discount
                                        ? rules.discount.message
                                        : null
                                "
                                :validateStatus="
                                    rules.discount ? 'error' : null
                                "
                            >
                                <a-input-number
                                    v-model:value="formData.discount"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('stock.discount'),
                                        ])
                                    "
                                    @change="recalculateFinalTotal"
                                    :min="0"
                                    style="width: 100%"
                                >
                                    <template #addonBefore>
                                        {{ appSetting.currency.symbol }}
                                    </template>
                                </a-input-number>
                            </a-form-item>
                        </a-col>
                    </a-row> -->
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('stock.shipping')"
                                name="shipping"
                                :help="
                                    rules.shipping
                                        ? rules.shipping.message
                                        : null
                                "
                                :validateStatus="
                                    rules.shipping ? 'error' : null
                                "
                            >
                                <a-input-number
                                    v-model:value="formData.shipping"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('stock.shipping'),
                                        ])
                                    "
                                    @change="recalculateFinalTotal"
                                    :min="0"
                                    style="width: 100%"
                                >
                                    <template #addonBefore>
                                        {{ appSetting.currency.symbol }}
                                    </template>
                                </a-input-number>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <!-- <a-row :gutter="16" class="mt-10">
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ $t("stock.order_tax") }}
                        </a-col>
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ formatAmountCurrency(formData.tax_amount) }}
                        </a-col>
                    </a-row> -->
                    <a-row :gutter="16" class="mt-10">
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ $t("stock.discount") }}
                        </a-col>
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ formatAmountCurrency(formData.discount) }}
                        </a-col>
                    </a-row>
                    <a-row :gutter="16" class="mt-10">
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ $t("stock.shipping") }}
                        </a-col>
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ formatAmountCurrency(formData.shipping) }}
                        </a-col>
                    </a-row>
                    <a-row :gutter="16" class="mt-10">
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ $t("stock.grand_total") }}
                        </a-col>
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            {{ formatAmountCurrency(formData.subtotal) }}
                        </a-col>
                    </a-row>
                    <a-row
                        :gutter="16"
                        class="mt-20"
                        v-if="
                            orderPageObject.type == 'sales' ||
                            orderPageObject.type == 'sales-returns'
                        "
                    >
                        <a-col :lg="12">
                            <a-form-item
                                name="payment_mode_id"
                                :help="
                                    rules.payment_mode_id
                                        ? rules.payment_mode_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.payment_mode_id ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.payment_mode_id"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('payments.payment_mode'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option
                                        v-for="paymentMode in paymentModes"
                                        :key="paymentMode.xid"
                                        :value="paymentMode.xid"
                                    >
                                        {{ paymentMode.name }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :lg="12">
                            <a-button
                                id="submitButton"
                                type="primary"
                                block
                                @click="completeOrder"
                                :disabled="
                                    formData.subtotal <= 0 ||
                                    formData.user_id == undefined ||
                                    formData.user_id == '' ||
                                    !formData.user_id
                                "
                                :loading="quickPayLoader"
                            >
                                {{ $t("stock.quick_pay_now") }}
                                (F2)
                            </a-button>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16" class="mt-20">
                        <a-button
                            type="primary"
                            :loading="loading"
                            @click="onSubmit"
                            :disabled="
                                formData.subtotal <= 0 ||
                                formData.user_id == undefined ||
                                formData.user_id == '' ||
                                !formData.user_id
                            "
                            block
                        >
                            <template #icon> <SaveOutlined /> </template>
                            {{ $t("common.save") }}
                        </a-button>
                    </a-row>
                </a-col>
            </a-row>
        </a-form>
    </a-card>

    <a-modal
        :visible="addEditVisible"
        :closable="false"
        :centered="true"
        :title="addEditPageTitle"
        @ok="onAddEditSubmit"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('product.unit_price')"
                        name="unit_price"
                        :help="
                            addEditRules.unit_price
                                ? addEditRules.unit_price.message
                                : null
                        "
                        :validateStatus="
                            addEditRules.unit_price ? 'error' : null
                        "
                    >
                        <a-input-number
                            v-model:value="addEditFormData.unit_price"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('product.unit_price'),
                                ])
                            "
                            :min="0"
                            style="width: 100%"
                        >
                            <template #addonBefore>
                                {{ appSetting.currency.symbol }}
                            </template>
                        </a-input-number>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('product.discount')"
                        name="discount_rate"
                        :help="
                            addEditRules.discount_rate
                                ? addEditRules.discount_rate.message
                                : null
                        "
                        :validateStatus="
                            addEditRules.discount_rate ? 'error' : null
                        "
                    >
                        <a-input-number
                            v-model:value="addEditFormData.discount_rate"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('product.discount'),
                                ])
                            "
                            :min="0"
                            style="width: 100%"
                        >
                            <template #addonAfter>%</template>
                        </a-input-number>
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button
                key="submit"
                type="primary"
                :loading="addEditFormSubmitting"
                @click="onAddEditSubmit"
            >
                <template #icon>
                    <SaveOutlined />
                </template>
                {{ $t("common.update") }}
            </a-button>
            <a-button key="back" @click="onAddEditClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-modal>
    <InvoiceModal
        :visible="printInvoiceModalVisible"
        :order="printInvoiceOrder"
        @closed="printInvoiceModalVisible = false"
    />
</template>

<script>
import { onBeforeUnmount, onMounted, ref, toRefs } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    SearchOutlined,
    SaveOutlined,
    BarcodeOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import apiAdmin from "../../../../common/composable/apiAdmin";
import stockManagement from "./stockManagement";
import common from "../../../../common/composable/common";
import fields from "./fields";
import SupplierAddButton from "../../users/SupplierAddButton.vue";
import CustomerAddButton from "../../users/CustomerAddButton.vue";
import TaxAddButton from "../../settings/taxes/AddButton.vue";
import WarehouseAddButton from "../../settings/warehouses/AddButton.vue";
import ProductAddButton from "../../product-manager/products/AddButton.vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { useStore } from "vuex";
import dayjs from "dayjs";
import InvoiceModal from "../invoice-template/a4/A4Invoice.vue";
import axios from "axios";
import { message } from "ant-design-vue";
export default {
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        InvoiceModal,
        ExclamationCircleOutlined,
        SearchOutlined,
        SaveOutlined,
        BarcodeOutlined,

        SupplierAddButton,
        CustomerAddButton,
        TaxAddButton,
        WarehouseAddButton,
        ProductAddButton,
        DateTimePicker,
        AdminPageHeader,
    },
    emits: ["onAddSuccess"],
    setup({ emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const store = useStore();
        const {
            appSetting,
            formatAmount,
            formatAmountCurrency,
            taxTypes,
            orderStatus,
            purchaseOrderStatus,
            salesOrderStatus,
            salesReturnStatus,
            purchaseReturnStatus,
            permsArray,
            selectedWarehouse,
            formatDateTime,
            disabledDate,
            user,
        } = common();
        const { orderReturnItemColumns } = fields();
        const {
            state,
            orderType,
            orderPageObject,
            selectedProducts,
            formData,
            productsAmount,
            taxes,

            recalculateValues,
            fetchProducts,
            searchValueSelected,
            quantityChanged,
            recalculateFinalTotal,
            showDeleteConfirm,
            taxChanged,
            editItem,

            // Add Edit
            addEditVisible,
            addEditFormData,
            addEditFormSubmitting,
            addEditRules,
            addEditPageTitle,
            onAddEditSubmit,
            onAddEditClose,

            barcodeSearchTerm,
            searchBarcodeInput,
            barcodeFetch,
            isCustomerWholesale,
        } = stockManagement();
        const { t } = useI18n();
        const users = ref([]);
        const warehouses = ref([]);
        const allUnits = ref([]);
        const router = useRouter();
        const allOrderStatus = ref([]);
        const taxUrl = "taxes?limit=10000";
        const unitUrl = "units?limit=10000";
        const warehouseUrl = `warehouses?filters=id ne "${selectedWarehouse.value.xid}"&hashable=${selectedWarehouse.value.xid}&limit=10000`;
        const usersUrl = `${orderPageObject.value.userType}?limit=10000`;
        const quickPayLoader = ref(false);
        const paymentModes = ref([]);
        const pageloader = ref(false);
        const printInvoiceModalVisible = ref(false);
        const printInvoiceOrder = ref({});

        onMounted(() => {
            const taxesPromise = axiosAdmin.get(taxUrl);
            const unitsPromise = axiosAdmin.get(unitUrl);
            const usersPromise = axiosAdmin.get(usersUrl);
            const warehousesPromise = axiosAdmin.get(warehouseUrl);

            axiosAdmin.get("payment-modes").then((response) => {
                paymentModes.value = response.data;

                setDefaultPayment();
            });

            Promise.all([
                usersPromise,
                taxesPromise,
                unitsPromise,
                warehousesPromise,
            ]).then(
                ([
                    usersResponse,
                    taxResponse,
                    unitResponse,
                    warehousesResponse,
                ]) => {
                    //! Removed By saravanan for Staff Based Customer.
                    // users.value = usersResponse.data;
                    if (
                        selectedWarehouse.value.is_staff_base == 1 &&
                        !permsArray.value.includes("admin") &&
                        !permsArray.value.includes("view_all_customer")
                    ) {
                        users.value = usersResponse.data.filter(
                            (customer) => customer.assign_to == user.value.xid
                        );
                    } else {
                        users.value = usersResponse.data;
                    }
                    // ------------                    ----------- //
                    taxes.value = taxResponse.data;
                    allUnits.value = unitResponse.data;
                    warehouses.value = warehousesResponse.data;
                }
            );

            if (orderType.value == "purchases") {
                allOrderStatus.value = purchaseOrderStatus;
            } else if (orderType.value == "sales") {
                allOrderStatus.value = salesOrderStatus;
            } else if (orderType.value == "sales-returns") {
                allOrderStatus.value = salesReturnStatus;
            } else if (orderType.value == "purchase-returns") {
                allOrderStatus.value = purchaseReturnStatus;
            } else if (orderType.value == "quotations") {
                allOrderStatus.value = [];
            } else if (orderType.value == "stock-transfers") {
                allOrderStatus.value = salesOrderStatus;
            }
        });

        const customerChanged = () => {
            for (let i = 0; i < users.value.length; i++) {
                if (users.value[i].xid == formData.value.user_id) {
                    // customerBalance.value = users.value[i].details.due_amount;
                    isCustomerWholesale.value =
                        users.value[i].is_wholesale_customer == 1
                            ? true
                            : false;
                    selectedProducts.value.map((product) => {
                        quantityChanged(product);
                    });
                }
            }
        };

        const onSubmit = () => {
            const newFormDataObject = {
                ...formData.value,
                order_type: orderPageObject.value.type,
                total: formData.value.subtotal,
                total_items: selectedProducts.value.length,
                product_items: selectedProducts.value,
            };

            addEditRequestAdmin({
                url: orderType.value,
                data: newFormDataObject,
                successMessage: t(`${orderPageObject.value.langKey}.created`),
                success: (res) => {
                    store.dispatch("auth/updateVisibleSubscriptionModules");
                    router.push({
                        name: `admin.stock.${orderType.value}.index`,
                    });
                },
            });
        };

        const quickPay = (res) => {
            let quickPayData = {
                amount: formData.value.subtotal.toString(),
                date: dayjs(formData.value.order_date).format("YYYY-MM-DD"),
                notes: "Quick Payment",
                order_id: res.xid,
                payment_mode_id: formData.value.payment_mode_id,
            };
            quickPayLoader.value = true;
            addEditRequestAdmin({
                url: "order-payments",
                data: quickPayData,
                success: (response) => {
                    axiosAdmin.get(`sales/${res.xid}`).then((order) => {
                        printInvoiceOrder.value = order.data;
                        printInvoiceModalVisible.value = true;
                    });

                    // store.dispatch("auth/updateVisibleSubscriptionModules");
                    // router.push({
                    //     name: `admin.stock.${orderType.value}.index`,
                    // });
                    // quickPayLoader.value = false;
                },
            });
        };

        const completeOrder = () => {
            const newFormDataObject = {
                ...formData.value,
                order_type: orderPageObject.value.type,
                total: formData.value.subtotal,
                total_items: selectedProducts.value.length,
                product_items: selectedProducts.value,
            };

            addEditRequestAdmin({
                url: orderType.value,
                data: newFormDataObject,
                successMessage: t(`${orderPageObject.value.langKey}.created`),
                success: (res) => {
                    // Quick Payment
                    quickPay(res);
                    // End
                },
            });
        };

        const setDefaultPayment = () => {
            const defaultPayment = paymentModes.value.filter((item) => {
                return item.is_default == 1;
            });
            formData.value = {
                ...formData.value,
                payment_mode_id:
                    defaultPayment.length > 0
                        ? defaultPayment[0].xid
                        : paymentModes.value[0].xid,
            };
        };

        const handleKeyPress = (event) => {
            if (event.key === "F2" && !quickPayLoader.value) {
                const submitButton = document.getElementById("submitButton");
                if (submitButton) {
                    submitButton.click();
                    return;
                }
            }
        };

        // Listen for the key press event on component mount
        onMounted(() => {
            window.addEventListener("keydown", handleKeyPress);
        });

        onBeforeUnmount(() => {
            window.removeEventListener("keydown", handleKeyPress);
        });

        const userAdded = () => {
            axiosAdmin.get(usersUrl).then((response) => {
                users.value = response.data;
            });
        };

        const unitAdded = () => {
            axiosAdmin.get(unitUrl).then((response) => {
                allUnits.value = response.data;
            });
        };

        const taxAdded = () => {
            axiosAdmin.get(taxUrl).then((response) => {
                taxes.value = response.data;
            });
        };

        const warehouseAdded = () => {
            axiosAdmin.get(warehouseUrl).then((response) => {
                warehouses.value = response.data;
            });
        };
        const SearchInvoice = (e) => {
            pageloader.value = true;
            selectedProducts.value = [];
            if (e.trim() === "") {
                pageloader.value = false;
                return;
            }
            if (selectedProducts.value.length == 0) {
                const data = {
                    order_type: "sales",
                    invoice: e,
                };
                axios
                    .post(`/api/orderinvoiceget`, data)
                    .then((res) => {
                        let data = res.data.data[0];
                        const checkValidItems = data.items.filter((item) => {
                            return item.return_quantity != 0;
                        });
                        if (checkValidItems.length > 0) {
                            formData.value.user_id = data.user.xid;
                            formData.value.staff_user_id = data.x_staff_user_id;
                            formData.value.subtotal = data.subtotal;
                            formData.value.discount = data.discount;
                            formData.value.shipping = data.shipping;
                            formData.value.tax_amount = data.tax_amount;
                            formData.value.tax_rate = data.tax_rate;
                            formData.value.total = data.total;
                            formData.value = {
                                ...formData.value,
                                sales_invoice_no: data.invoice_number,
                                discount: data.discount,
                                discount_type: data.discount_type
                                    ? data.discount_type
                                    : "fixed",
                                discount_value: data.discount,
                            };
                            checkValidItems.map((item) => {
                                searchValueSelected("", {
                                    product: {
                                        ...item.product,
                                        details: item.details,
                                        item_id: "",
                                        quantity: item.return_quantity,
                                        return_quantity: item.return_quantity,
                                        discount_rate: item.discount_rate,
                                        tax_rate: item.tax_rate,
                                        single_unit_price:
                                            item.single_unit_price,
                                        unit_price: item.unit_price,
                                        subtotal: item.subtotal,
                                        tax_type: item.tax_type,
                                        x_tax_id: item.x_tax_id,
                                    },
                                });
                            });
                        } else {
                            message.warning(
                                "There are no products available for return"
                            );
                        }
                        pageloader.value = false;
                    })
                    .catch((err) => {
                        if (err.status == 400) {
                            message.error("Invalid Invoice Number");
                            pageloader.value = false;
                        }
                    });
            }
        };
        const formatOrderDate = (newValue) => {
            return newValue ? formatDateTime(newValue.format()) : undefined;
        };
        return {
            formatOrderDate,
            SearchInvoice,
            pageloader,
            ...toRefs(state),
            formData,
            productsAmount,
            rules,
            loading,
            users,
            warehouses,
            taxes,
            onSubmit,
            fetchProducts,
            searchValueSelected,
            selectedProducts,
            showDeleteConfirm,
            quantityChanged,
            formatAmountCurrency,
            taxChanged,
            recalculateFinalTotal,
            appSetting,
            editItem,
            orderPageObject,

            orderReturnItemColumns,

            // Add Edit
            addEditVisible,
            addEditFormData,
            addEditFormSubmitting,
            addEditRules,
            addEditPageTitle,
            onAddEditSubmit,
            onAddEditClose,
            allOrderStatus,
            taxTypes,
            permsArray,

            userAdded,
            unitAdded,
            taxAdded,
            warehouseAdded,

            barcodeSearchTerm,
            searchBarcodeInput,
            barcodeFetch,
            quickPayLoader,
            completeOrder,
            paymentModes,
            printInvoiceModalVisible,
            printInvoiceOrder,
            disabledDate,
            customerChanged,
            selectedWarehouse,
        };
    },
};
</script>
<style>
.ant-select-item-option-active {
    background: #b8b8b8 !important;
}
</style>
