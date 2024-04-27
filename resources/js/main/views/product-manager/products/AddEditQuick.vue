<template>
    <a-modal
        :title="`Quick ${pageTitle}`"
        :width="drawerWidth"
        :visible="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="true"
    >
        <template #closeIcon
            ><div @click="onClose"><CloseOutlined /></div
        ></template>
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('product.name')"
                                name="name"
                                :help="
                                    rules.name
                                        ? rules.name.message
                                        : null || rules.slug
                                        ? rules.slug.message
                                        : null
                                "
                                :validateStatus="
                                    rules.name
                                        ? 'error'
                                        : null || rules.slug
                                        ? 'error'
                                        : null
                                "
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.name'),
                                        ])
                                    "
                                    v-on:keyup="
                                        formData.slug = slugify(
                                            $event.target.value
                                        )
                                    "
                                    @click="generateBarCode"
                                    maxLength="99"
                                    id="nameFocus"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <!-- <a-row :gutter="16">
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.unit')"
                                name="unit_id"

                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.unit_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('product.unit'),
                                            ])
                                        "
                                        :allowClear="true"
                                        @change="
                                            (value, option) =>
                                                (selectedUnit = option)
                                        "
                                    >
                                        <a-select-option
                                            v-for="unit in units"
                                            :key="unit.xid"
                                            :value="unit.xid"
                                            :short_name="unit.short_name"
                                        >
                                            {{ unit.name }} ({{
                                                unit.short_name
                                            }})
                                        </a-select-option>
                                    </a-select>
                                    <UnitAddButton @onAddSuccess="unitAdded" />
                                </span>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.category')"
                                name="category_id"

                            >
                                <span style="display: flex">
                                    <a-tree-select
                                        :key="
                                            'categories_total' +
                                            categories.length
                                        "
                                        v-model:value="formData.category_id"
                                        show-search
                                        style="width: 100%"
                                        :dropdown-style="{
                                            maxHeight: '250px',
                                            overflow: 'auto',
                                        }"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('product.category'),
                                            ])
                                        "
                                        :treeData="categories"
                                        allow-clear
                                        tree-default-expand-all
                                    />
                                    <CategoryAddButton
                                        @onAddSuccess="categoryAdded"
                                    />
                                </span>
                            </a-form-item>
                        </a-col>
                    </a-row> -->
                    <a-row :gutter="16">
                        <!-- <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.opening_stock')"
                                name="opening_stock"
                                :help="
                                    rules.opening_stock
                                        ? rules.opening_stock.message
                                        : null
                                "
                                :validateStatus="
                                    rules.opening_stock ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.opening_stock"
                                    placeholder="0"
                                >
                                    <template #addonAfter>
                                        {{
                                            selectedUnit &&
                                            selectedUnit.short_name
                                                ? selectedUnit.short_name
                                                : ""
                                        }}
                                    </template>
                                </a-input>
                            </a-form-item>
                        </a-col> -->

                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.sales_price')"
                                name="sales_price"
                                :help="
                                    rules.sales_price
                                        ? rules.sales_price.message
                                        : null
                                "
                                :validateStatus="
                                    rules.sales_price ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input-number
                                    v-model:value="formData.sales_price"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.sales_price'),
                                        ])
                                    "
                                    min="0"
                                    style="width: 100%"
                                >
                                    <template #addonBefore>
                                        {{ appSetting.currency.symbol }}
                                    </template>
                                    <template #addonAfter>
                                        <a-select
                                            v-model:value="
                                                formData.sales_tax_type
                                            "
                                            style="width: 120px"
                                            :disabled="
                                                selectedWarehouse.gst_in_no ==
                                                null
                                            "
                                        >
                                            <a-select-option
                                                value="inclusive"
                                                v-if="
                                                    [
                                                        'inclusive',
                                                        'both',
                                                    ].includes(
                                                        selectedWarehouse.product_tax_type
                                                    )
                                                "
                                            >
                                                {{ $t("common.with_tax") }}
                                            </a-select-option>
                                            <a-select-option value="exclusive" v-if="
                                                    [
                                                        'exclusive',
                                                        'both',
                                                    ].includes(
                                                        selectedWarehouse.product_tax_type
                                                    )
                                                ">
                                                {{ $t("common.without_tax") }}
                                            </a-select-option>
                                        </a-select>
                                    </template>
                                </a-input-number>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.item_code')"
                                name="item_code"
                                :help="
                                    rules.item_code
                                        ? rules.item_code.message
                                        : null
                                "
                                :validateStatus="
                                    rules.item_code ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.item_code"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.item_code'),
                                        ])
                                    "
                                    maxLength="30"
                                >
                                    <template #addonAfter>
                                        <a-button
                                            v-if="formData.item_code == ''"
                                            type="text"
                                            size="small"
                                            @click="generateBarCode"
                                        >
                                            <template #icon>
                                                <BarcodeOutlined />
                                            </template>
                                            {{ $t("product.generate_barcode") }}
                                        </a-button>
                                        <Barcode
                                            :itemCode="formData.item_code"
                                            :barcodeSymbology="
                                                formData.barcode_symbology
                                            "
                                            :options="{
                                                height: 75,
                                                format: 'CODE128A',
                                            }"
                                            v-else
                                        />
                                    </template>
                                </a-input>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <!-- <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.slug')"
                                name="slug"
                                :help="rules.slug ? rules.slug.message : null"
                                :validateStatus="rules.slug ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.slug"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.slug'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col> -->

                        <!-- <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                name="stock_quantitiy_alert"
                                :help="
                                    rules.stock_quantitiy_alert
                                        ? rules.stock_quantitiy_alert.message
                                        : null
                                "
                                :validateStatus="
                                    rules.stock_quantitiy_alert ? 'error' : null
                                "
                            >
                                <template #label>
                                    <InputLabelPopover
                                        :label="$t('product.quantitiy_alert')"
                                        :content="$t('popover.quantitiy_alert')"
                                    />
                                </template>
                                <a-input-number
                                    v-model:value="
                                        formData.stock_quantitiy_alert
                                    "
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.quantitiy_alert'),
                                        ])
                                    "
                                    min="0"
                                    style="width: 100%"
                                />
                            </a-form-item>
                        </a-col> -->

                        <!-- <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.brand')"
                                name="brand_id"
                                :help="
                                    rules.brand_id
                                        ? rules.brand_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.brand_id ? 'error' : null
                                "
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.brand_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('product.brand'),
                                            ])
                                        "
                                        :allowClear="true"
                                        style="width: 100%"
                                        optionFilterProp="title"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="brand in brands"
                                            :key="brand.xid"
                                            :title="brand.name"
                                            :value="brand.xid"
                                        >
                                            {{ brand.name }}
                                        </a-select-option>
                                    </a-select>
                                    <BrandAddButton
                                        @onAddSuccess="brandAdded"
                                    />
                                </span>
                            </a-form-item>
                        </a-col> -->

                        <!-- <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('product.barcode_symbology')"
                                name="barcode_symbology"
                                :help="
                                    rules.barcode_symbology
                                        ? rules.barcode_symbology.message
                                        : null
                                "
                                :validateStatus="
                                    rules.barcode_symbology ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.barcode_symbology"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('product.barcode_symbology'),
                                        ])
                                    "
                                >
                                    <a-select-option
                                        v-for="barcodeSym in barcodeSymbology"
                                        :key="barcodeSym.key"
                                        :value="barcodeSym.value"
                                    >
                                        {{ barcodeSym.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col> -->
                        <!--  <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.product_code')"
                                name="product_code"
                                :help="
                                    rules.product_code
                                        ? rules.product_code.message
                                        : null
                                "
                                :validateStatus="
                                    rules.product_code ? 'error' : null
                                "
                            >
                                <a-input
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.product_code'),
                                        ])
                                    "
                                    v-model:value="formData.product_code"
                                    maxLength="30"
                                >
                                </a-input>
                            </a-form-item>
                        </a-col> -->
                        <!-- <a-col :xs="12" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.opening_stock_date')"
                                name="opening_stock_date"
                                :help="
                                    rules.opening_stock_date
                                        ? rules.opening_stock_date.message
                                        : null
                                "
                                :validateStatus="
                                    rules.opening_stock_date ? 'error' : null
                                "
                            >
                                <a-date-picker
                                    v-model:value="formData.opening_stock_date"
                                    :format="appSetting.date_format"
                                    valueFormat="YYYY-MM-DD"
                                    style="width: 100%"
                                />
                            </a-form-item>
                        </a-col> -->
                    </a-row>
                </a-col>
            </a-row>

            <!-- <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('product.purchase_price')"
                        name="purchase_price"
                        :help="
                            rules.purchase_price
                                ? rules.purchase_price.message
                                : null
                        "
                        :validateStatus="rules.purchase_price ? 'error' : null"
                        class="required"
                    >
                        <a-input-number
                            v-model:value="formData.purchase_price"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('product.purchase_price'),
                                ])
                            "
                            min="0"
                            style="width: 100%"
                        >
                            <template #addonBefore>
                                {{ appSetting.currency.symbol }}
                            </template>
                            <template #addonAfter>
                                <div>
                                    {{ $t("common.without_tax") }}
                                </div>
                            </template>
                        </a-input-number>
                    </a-form-item>
                </a-col>

            </a-row> -->
        </a-form>
        <template #footer>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
            <a-button
                type="primary"
                :loading="loading"
                @click="onSubmit"
                style="margin-left: 8px"
                :disabled="displayMessageForHSN"
            >
                <template #icon>
                    <SaveOutlined />
                </template>
                {{
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
        </template>
    </a-modal>
</template>
<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    BarcodeOutlined,
    CloseOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import moment from "moment";
import { forEach, find } from "lodash-es";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import Upload from "../../../../common/core/ui/file/Upload.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import FormItemHeading from "../../../../common/components/common/typography/FormItemHeading.vue";
import InputLabelPopover from "../../../../common/components/common/typography/InputLabelPopover.vue";
import Barcode from "./Barcode.vue";
import BrandAddButton from "../brands/AddButton.vue";
import CategoryAddButton from "../categories/AddButton.vue";
import UnitAddButton from "../../settings/units/AddButton.vue";
import TaxAddButton from "../../settings/taxes/AddButton.vue";
import WarehouseAddButton from "../../settings/warehouses/AddButton.vue";

export default defineComponent({
    props: [
        "formData",
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
        UserInfo,
        FormItemHeading,
        InputLabelPopover,
        BarcodeOutlined,
        Barcode,
        BrandAddButton,
        CategoryAddButton,
        UnitAddButton,
        TaxAddButton,
        WarehouseAddButton,
        CloseOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const {
            permsArray,
            taxTypes,
            barcodeSymbology,
            appSetting,
            slugify,
            selectedWarehouse,
        } = common();
        const { t } = useI18n();
        const brands = ref([]);
        const categories = ref([]);
        const units = ref([]);
        const taxes = ref([]);
        const customFields = ref([]);
        const warehouses = ref([]);
        const customFieldsData = ref({});
        const selectedUnit = ref({});
        const store = useStore();
        const brandsUrl = "brands?limit=10000";
        const categoriesUrl = "categories?limit=10000";
        const unitsUrl = "units?limit=10000";
        const taxesUrl = "taxes?limit=10000";
        const customFieldsUrl = "custom-fields?limit=10000";
        const warehouseUrl = "warehouses?limit=10000";
        const waehouseId = ref(undefined);
        const displayMessageForHSN = ref(false);

        onMounted(() => {
            moment.suppressDeprecationWarnings = true;
            const brandsPromise = axiosAdmin.get(brandsUrl);
            const categoriesPromise = axiosAdmin.get(categoriesUrl);
            const unitsPromise = axiosAdmin.get(unitsUrl);
            const taxesPromise = axiosAdmin.get(taxesUrl);
            const customFieldsPromise = axiosAdmin.get(customFieldsUrl);
            const warehousesPromise = axiosAdmin.get(warehouseUrl);

            Promise.all([
                brandsPromise,
                categoriesPromise,
                unitsPromise,
                taxesPromise,
                customFieldsPromise,
                warehousesPromise,
            ]).then(
                ([
                    brandsResponse,
                    categoriesResponse,
                    unitsResponse,
                    taxesResponse,
                    customFieldsResponse,
                    warehousesResponse,
                ]) => {
                    brands.value = brandsResponse.data;
                    units.value = unitsResponse.data;
                    taxes.value = taxesResponse.data;
                    customFields.value = customFieldsResponse.data;
                    warehouses.value = warehousesResponse.data;
                    selectedUnit.value = find(units.value, [
                        "xid",
                        props.formData.unit_id,
                    ]);

                    setCategories(categoriesResponse.data);
                }
            );

            //  formData.name.focus();
        });

        const validateHSNSACCode = (value) => {
            if (value.length == 8) {
                displayMessageForHSN.value = false;
            } else {
                displayMessageForHSN.value = true;
            }
        };

        const setCategories = (categoryResponseData) => {
            // Category Tree
            const allCategoriesArray = [];
            const listArray = [];
            categoryResponseData.map((responseArray) => {
                listArray.push({
                    xid: responseArray.xid,
                    x_parent_id: responseArray.x_parent_id,
                    title: responseArray.name,
                    value: responseArray.xid,
                    // disabled: responseArray.x_parent_id == null ? true : false,
                    disabled: false,
                });
            });

            listArray.forEach((node) => {
                // No parentId means top level
                if (!node.x_parent_id) return allCategoriesArray.push(node);

                // Insert node as child of parent in listArray array
                const parentIndex = listArray.findIndex(
                    (el) => el.xid === node.x_parent_id
                );
                if (!listArray[parentIndex].children) {
                    return (listArray[parentIndex].children = [node]);
                }

                listArray[parentIndex].children.push(node);
            });

            categories.value = allCategoriesArray;
        };

        const disabledDate = (current) => {
            // Can not select days before today and today
            return current && current > moment().endOf("day");
        };

        const generateBarCode = () => {
            props.formData.item_code = parseInt(Math.random() * 10000000000);
        };

        const onSubmit = () => {
            const newData = {
                ...props.formData,
                tax_id:
                    props.formData.tax_id == null ? "" : props.formData.tax_id,
                custom_fields: customFieldsData.value,
                warehouse_id: waehouseId.value,
                category_id: categories.value[0].xid,
                unit_id: units.value[0].xid,
                purchase_price: props.formData.sales_price,
            };

            addEditRequestAdmin({
                url: props.url,
                data: newData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", newData.name);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const brandAdded = () => {
            axiosAdmin.get(brandsUrl).then((response) => {
                brands.value = response.data;
            });
        };

        const categoryAdded = () => {
            axiosAdmin.get(categoriesUrl).then((response) => {
                setCategories(response.data);
            });
        };

        const unitAdded = () => {
            axiosAdmin.get(unitsUrl).then((response) => {
                units.value = response.data;
            });
        };

        const taxAdded = () => {
            axiosAdmin.get(taxesUrl).then((response) => {
                taxes.value = response.data;
            });
        };

        const warehouseAdded = () => {
            axiosAdmin.get(warehouseUrl).then((response) => {
                warehouses.value = response.data;
            });
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (newVal == true) {
                    var newFields = {};
                    forEach(customFields.value, (customField) => {
                        if (
                            props.addEditType == "add" ||
                            props.formData.custom_fields.length == 0
                        ) {
                            newFields[customField.name] = "";
                        } else {
                            var searchedField = find(
                                props.formData.custom_fields,
                                ["field_name", customField.name]
                            );
                            newFields[customField.name] =
                                searchedField === undefined
                                    ? ""
                                    : searchedField.field_value;
                        }
                    });
                    customFieldsData.value = { ...newFields };

                    selectedUnit.value = find(units.value, [
                        "xid",
                        props.formData.unit_id,
                    ]);

                    waehouseId.value =
                        props.addEditType == "add"
                            ? selectedWarehouse.value.xid
                            : props.data.warehouse && props.data.warehouse.xid
                            ? props.data.warehouse.xid
                            : undefined;
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            disabledDate,

            brands,
            categories,
            taxes,
            units,
            warehouses,

            selectedWarehouse,
            slugify,
            generateBarCode,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
            appSetting,

            customFields,
            customFieldsData,
            taxTypes,
            barcodeSymbology,
            selectedUnit,

            brandAdded,
            categoryAdded,
            unitAdded,
            taxAdded,
            warehouseAdded,

            permsArray,
            waehouseId,
            displayMessageForHSN,
            validateHSNSACCode,
        };
    },
});
</script>

<style>
.ant-calendar-picker {
    width: 100%;
}
</style>
