<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.products`)" class="p-0">
                <template
                    v-if="
                        permsArray.includes('products_create') ||
                        permsArray.includes('admin')
                    "
                    #extra
                >
                    <a-space>
                        <ImportProducts
                            :pageTitle="$t('product.update_products')"
                            :export="true"
                            :sampleFileUrl="sampleFileUrl"
                            importUrl="products/updateimport"
                            :exportUrl="exportUrl"
                            @onUploadSuccess="setUrlData"
                            class="mob"
                        />
                        <HelpVideo
                            :pageTitle="$t('common.help_video')"
                            :FileUrl="FileUrl"
                            importUrl="brands/import"
                            @onUploadSuccess="setUrlData"
                            class="mob"
                        />
                        <ImportProducts
                            :pageTitle="$t('product.import_products')"
                            :export="false"
                            :sampleFileUrl="sampleFileUrl"
                            importUrl="products/import"
                            @onUploadSuccess="setUrlData"
                            class="mob"
                        />
                        <SubscriptionModuleVisibility moduleName="product">
                            <a-button type="primary" @click="addItem">
                                <PlusOutlined />
                                {{ $t("common.add") }}
                            </a-button>
                        </SubscriptionModuleVisibility>
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
                    {{ $t(`menu.product_manager`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.products`) }}
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
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />

        <ProductView
            :product="viewData"
            :visible="detailsVisible"
            @closed="onCloseDetails"
        />

        <SubscriptionModuleVisibilityMessage moduleName="product" />

        <a-row :gutter="[15, 15]" class="mb-20">
            <a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="4">
                <a-input-search
                    v-model:value="table.searchString"
                    show-search
                    @change="onTableSearch"
                    @search="onTableSearch"
                    style="width: 100%"
                    :loading="table.filterLoading"
                    :placeholder="$t('common.search')"
                />
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="4">
                <a-select
                    v-model:value="filters.brand_id"
                    :placeholder="
                        $t('common.select_default_text', [$t('product.brand')])
                    "
                    :allowClear="true"
                    style="width: 100%"
                    optionFilterProp="title"
                    show-search
                    @change="setUrlData"
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
            </a-col>
            <a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="4">
                <a-tree-select
                    v-model:value="filters.category_id"
                    show-search
                    style="width: 100%"
                    :dropdown-style="{ maxHeight: '250px', overflow: 'auto' }"
                    :placeholder="
                        $t('common.select_default_text', [
                            $t('category.category'),
                        ])
                    "
                    :tree-data="categories"
                    allow-clear
                    tree-default-expand-all
                    :filterTreeNode="filterTreeNode"
                    @change="setUrlData"
                />
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        bordered
                    >
                        <template #bodyCell="{ column, record }">
                            <template
                                v-if="
                                    column.dataIndex === 'status' &&
                                    record &&
                                    record.details
                                "
                            >
                                <a-popover
                                    v-if="
                                        record.details.status == 'out_of_stock'
                                    "
                                    placement="top"
                                >
                                    <template #content>
                                        {{ $t("common.out_of_stock") }}
                                    </template>
                                    <a-badge status="error" />
                                </a-popover>
                                <a-badge v-else status="success" />
                            </template>
                            <template v-if="column.dataIndex === 'name'">
                                <a-badge>
                                    <a-avatar
                                        shape="square"
                                        :src="record.image_url"
                                    />
                                    {{ record.name }}
                                </a-badge>
                            </template>
                            <template
                                v-if="column.dataIndex === 'warehouse_id'"
                            >
                                {{
                                    record.details &&
                                    record.details.warehouse &&
                                    record.details.warehouse.name
                                        ? record.details.warehouse.name
                                        : "-"
                                }}
                            </template>
                            <template v-if="column.dataIndex === 'category_id'">
                                {{
                                    record.category ? record.category.name : "-"
                                }}
                            </template>
                            <template v-if="column.dataIndex === 'brand_id'">
                                {{ record.brand ? record.brand.name : "-" }}
                            </template>
                            <template
                                v-if="
                                    column.dataIndex === 'sales_price' &&
                                    record &&
                                    record.details
                                "
                            >
                                {{
                                    formatAmountCurrency(
                                        record.details.sales_price
                                    )
                                }}
                            </template>
                            <template
                                v-if="
                                    column.dataIndex === 'purchase_price' &&
                                    record &&
                                    record.details
                                "
                            >
                                {{
                                    formatAmountCurrency(
                                        record.details.purchase_price
                                    )
                                }}
                            </template>
                            <template
                                v-if="
                                    column.dataIndex === 'current_stock' &&
                                    record &&
                                    record.details
                                "
                            >
                                {{
                                    `${record.details.current_stock} ${
                                        record.unit && record.unit.short_name
                                            ? record.unit.short_name
                                            : ""
                                    }`
                                }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-space>
                                    <a-button
                                        @click="viewItem(record)"
                                        type="primary"
                                    >
                                        <template #icon>
                                            <EyeOutlined />
                                        </template>
                                    </a-button>
                                    <a-button
                                        v-if="
                                            permsArray.includes(
                                                'products_edit'
                                            ) || permsArray.includes('admin')
                                        "
                                        type="primary"
                                        @click="editItem(record)"
                                    >
                                        <template #icon>
                                            <EditOutlined />
                                        </template>
                                    </a-button>
                                    <a-tooltip title="Clone Product">
                                        <a-button
                                            type="primary"
                                            @click="() => handleClone(record)"
                                        >
                                            <CopyOutlined />
                                        </a-button>
                                    </a-tooltip>
                                    <a-tooltip
                                        :title="
                                            record.items.length > 0
                                                ? 'This product is associated with orders.'
                                                : ''
                                        "
                                    >
                                        <a-button
                                            v-if="
                                                permsArray.includes(
                                                    'products_delete'
                                                ) ||
                                                permsArray.includes('admin')
                                            "
                                            type="primary"
                                            :disabled="record.items.length > 0"
                                            @click="
                                                showDeleteConfirm(record.xid)
                                            "
                                        >
                                            <template #icon>
                                                <DeleteOutlined />
                                            </template>
                                        </a-button>
                                    </a-tooltip>
                                </a-space>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </a-card>
</template>
<script>
import { onMounted, ref, watch } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExportOutlined,
    CopyOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import ProductInfo from "../../../../common/components/product/ProductInfo.vue";
import AddEdit from "./AddEdit.vue";
import ProductView from "./View.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import ImportProducts from "../../../../common/core/ui/Import.vue";
import SubscriptionModuleVisibility from "../../../../common/components/common/visibility/SubscriptionModuleVisibility.vue";
import SubscriptionModuleVisibilityMessage from "../../../../common/components/common/visibility/SubscriptionModuleVisibilityMessage.vue";
import HelpVideo from "../../../../common/core/ui/help.vue";
import { includes, has, get } from "lodash-es";

export default {
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        ExportOutlined,
        CopyOutlined,
        AddEdit,
        ProductView,
        ProductInfo,
        AdminPageHeader,
        ImportProducts,
        SubscriptionModuleVisibility,
        SubscriptionModuleVisibilityMessage,
        HelpVideo,
    },

    setup() {
        const {
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
            multiDimensalObjectColumns,
        } = fields();
        const crudVariables = crud();
        const {
            appSetting,
            permsArray,
            formatAmountCurrency,
            getRecursiveCategories,
            filterTreeNode,
            selectedWarehouse,
        } = common();
        const filters = ref({
            category_id: undefined,
            brand_id: undefined,
        });
        const sampleFileUrl = window.config.product_sample_file;

        const categories = ref([]);
        const brands = ref([]);
        const helpvideos = JSON.parse(localStorage.getItem("helpvideos"));
        for (let index = 0; index < helpvideos.length; index++) {
            if (helpvideos[index].pagename == "product")
                var FileUrl = helpvideos[index].video_url;
        }

        const exportUrl = ref("");
        onMounted(() => {
            getInitialData();
            setUrlData();
            exportUrl.value = `${window.location.origin}/api/export`;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "product";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];
            crudVariables.multiDimensalObjectColumns.value = {
                ...multiDimensalObjectColumns,
            };
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url: "products?fields=id,xid,name,slug,hsn_sac_code,product_code,barcode_symbology,item_code,image,image_url,category_id,x_category_id,category{id,xid,name},brand_id,x_brand_id,brand{id,xid,name},unit_id,x_unit_id,unit{id,xid,name,short_name},description,details{stock_quantitiy_alert,price_history,expiry,opening_stock,opening_stock_date,wholesale_price,wholesale_quantity,is_wholesale_only,is_shipping,shipping_price,mrp,purchase_price,sales_price,tax_id,x_tax_id,purchase_tax_type,sales_tax_type,current_stock,warehouse_id,x_warehouse_id,status},details:tax{id,xid,name,rate},details:warehouse{id,xid,name},customFields{id,xid,field_name,field_value},details:wholesale{id,warehouse_id,start_quantity,end_quantity,wholesale_price},warehouse_id,x_warehouse_id,warehouse{id,xid}",
                filters,
            };

            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });
        };

        const getInitialData = () => {
            const categoriesPromise = axiosAdmin.get("categories?limit=10000");
            const brandsPromise = axiosAdmin.get("brands?limit=10000");

            Promise.all([categoriesPromise, brandsPromise]).then(
                ([categoriesResponse, brandsResponse]) => {
                    categories.value =
                        getRecursiveCategories(categoriesResponse);
                    brands.value = brandsResponse.data;
                }
            );
        };

        const handleClone = (item) => {
            const itemDetails = {};
            var multiDimension = crudVariables.multiDimensalObjectColumns.value;

            Object.keys(crudVariables.initData.value).forEach((key) => {
                if (has(multiDimension, key)) {
                    const multiDimensalObjectColumnValue = multiDimension[key];
                    itemDetails[key] = get(
                        item,
                        multiDimensalObjectColumnValue
                    );
                    if (
                        key == "wholesale" &&
                        itemDetails.wholesale.length == 0
                    ) {
                        itemDetails[key] = [
                            {
                                id: "",
                                start_quantity: 0,
                                end_quantity: 0,
                                wholesale_price: 0,
                            },
                        ];
                    }
                    if (
                        key == "wholesale" &&
                        itemDetails.wholesale.length > 0
                    ) {
                        itemDetails[key] = itemDetails[key].map((item) => {
                            return {
                                ...item,
                                end_quantity: parseFloat(item.end_quantity),
                                start_quantity: parseFloat(item.start_quantity),
                            };
                        });
                    }
                } else if (includes(crudVariables.hashableColumns.value, key)) {
                    itemDetails[key] = item[`x_${key}`];
                } else {
                    itemDetails[key] = item[key];
                    if (crudVariables.langKey.value == "warehouse") {
                        if (
                            key == "pincode" ||
                            key == "location" ||
                            key == "business_type"
                        ) {
                            itemDetails[key] = item[key]
                                ? item[key].split(",")
                                : [];
                        }
                    }
                    itemDetails.deleted_wholesale = [];
                }
            });
            crudVariables.formData.value = itemDetails;
            crudVariables.addItem();
        };

        watch(selectedWarehouse, (newVal, oldVal) => {
            setUrlData();
        });

        return {
            columns,
            appSetting,
            ...crudVariables,
            permsArray,
            formatAmountCurrency,
            exportUrl,
            categories,
            brands,
            filters,
            filterTreeNode,
            setUrlData,
            sampleFileUrl,
            FileUrl,
            handleClone,
        };
    },
};
</script>

<style lang="less">
.ant-badge-status-dot {
    width: 8px;
    height: 8px;
}
@media only screen and (max-width: 600px) {
    .mob {
        display: none;
    }
}
</style>
