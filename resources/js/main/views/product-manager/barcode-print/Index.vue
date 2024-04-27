<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.barcode`)" class="p-0" />
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
                    {{ $t(`menu.barcode`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <a-card class="page-content-container">
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <span style="display: flex">
                    <a-select
                        v-if="!searchBarcodeInput"
                        v-model:value="orderSearchTerm"
                        show-search
                        :filter-option="false"
                        :placeholder="
                            $t('common.select_default_text', [
                                $t('product.product'),
                            ])
                        "
                        style="width: 100%"
                        :not-found-content="productFetching ? undefined : null"
                        @search="fetchProducts"
                        size="large"
                        option-label-prop="label"
                        @focus="products = []"
                        @select="searchValueSelected"
                    >
                        <template #suffixIcon><SearchOutlined /></template>
                        <template v-if="productFetching" #notFoundContent>
                            <a-spin size="small" />
                        </template>
                        <a-select-option
                            v-for="product in products"
                            :key="product.xid"
                            :value="product.xid"
                            :label="product.name"
                            :product="product"
                        >
                            => {{ product.name }}
                        </a-select-option>
                    </a-select>
                </span>
            </a-col>
        </a-row>
        <br />
        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-table
                    :row-key="(record) => record.xid"
                    :dataSource="selectedProducts"
                    :columns="BarcodeColumns"
                    :pagination="{ defaultPageSize: 5 }"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'name'">
                            {{ record.name }}
                        </template>
                        <template v-if="column.dataIndex === 'barcode'">
                            <vue-barcode
                                :value="record.item_code"
                                :options="{
                                    height: 75,
                                    format: barcodeSymbology,
                                }"
                                tag="svg"
                            >
                                <template #body>
                                    {{ record.name }}
                                </template>
                            </vue-barcode>
                        </template>
                        <template v-if="column.dataIndex === 'count'">
                            <a-input-number
                                id="inputNumber"
                                v-model:value="record.count"
                                :min="1"
                            />
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-button
                                type="danger"
                                @click="showDeleteConfirm(record)"
                                style="margin-left: 4px"
                            >
                                <template #icon><DeleteOutlined /></template>
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </a-col>
        </a-row>
        <br />
        <a-row :gutter="16">
            <a-col
                :xs="24"
                :sm="24"
                :md="24"
                :lg="24"
                style="
                    display: flex;
                    justify-content: flex-end;
                    align-items: center;
                "
            >
                <a-button
                    v-print="'#barcode_print'"
                    type="primary"
                    @click="handleSave"
                    >Save</a-button
                >
            </a-col>
        </a-row>

        <div id="barcode_print">
            <div class="barcode_grid_wrapper">
                <div
                    class="barcode"
                    v-for="(value, index) in printData"
                    :key="index"
                >
                    <div class="barcode_label_wrapper">
                        <p class="barcode_product_name">{{ value.name }}</p>
                    </div>
                    <vue-barcode
                        :value="value.item_code"
                        :options="{
                            text: `${value.item_code} ${formatAmountCurrency(
                                value.unit_price
                            )}`,
                            fontSize: 12,
                            marginTop: 0,
                        }"
                        tag="svg"
                    />
                </div>
            </div>
        </div>
    </a-card>
</template>
<script>
import { computed, onMounted, ref, toRefs } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import fields from "./fields";
import { DeleteOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";

export default {
    components: { AdminPageHeader, DeleteOutlined },

    setup() {
        const {
            fetchProducts,
            selectedProducts,
            state,
            searchValueSelected,
            BarcodeColumns,
            showDeleteConfirm,
        } = fields();

        const printData = ref([]);

        const { formatAmountCurrency } = common();

        const handleSave = () => {
            const data = selectedProducts.value.flatMap((item) => {
                return Array.from({ length: item.count }, () => ({ ...item }));
            });
            printData.value = data;
        };
        return {
            fetchProducts,
            selectedProducts,
            printData,
            ...toRefs(state),
            searchValueSelected,
            BarcodeColumns,
            showDeleteConfirm,
            handleSave,
            formatAmountCurrency,
        };
    },
};
</script>
<style>
.barcode_grid_wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
}
.barcode_grid_wrapper .barcode {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: white;
    padding: 5px;
}
.barcode_grid_wrapper .barcode p {
    margin: 0;
}
.barcode_label_wrapper {
    text-align: center;
    width: 279px;
}
.barcode_product_name {
    text-align: center;
    font: 12px monospace;
}
/* .barcode_grid_wrapper .barcode > p,
.barcode_grid_wrapper .barcode > svg {
    scale: 0.6;
} */

#barcode_print {
    display: none;
}

@media print {
    #barcode_print {
        display: unset;
    }
}
</style>
