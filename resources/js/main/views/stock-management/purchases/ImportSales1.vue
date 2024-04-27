<template>
    <a-card
        class="page-content-sub-header breadcrumb-left-border"
        :bodyStyle="{ padding: '0px', margin: '0px 16px 0' }"
    >
        <a-row>
            <a-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                <a-page-header
                    :title="`Import ${$t(`menu.sales`)}`"
                    @back="() => back()"
                    class="p-0"
                />
            </a-col>

            <a-col :xs="20" :sm="20" :md="20" :lg="20" :xl="20">
                <div
                    style="
                        display: flex;
                        justify-content: flex-end;
                        align-items: center;
                    "
                >
                    <a-space>
                        <a-upload
                            v-model:file-list="fileList"
                            :accept="'.xlsx,.csv'"
                            :before-upload="beforeUpload"
                            :maxCount="1"
                            :remove="() => removeFile()"
                        >
                            <!-- :loading="loading" -->
                            <a-button type="primary">
                                <template #icon>
                                    <UploadOutlined />
                                </template>
                                {{ $t("common.upload") }}
                            </a-button>
                        </a-upload>
                        <a-button
                            type="primary"
                            @click="handleSave"
                            :loading="loading"
                            ><SaveOutlined />Save</a-button
                        >
                    </a-space>
                </div>
            </a-col>
        </a-row>
        <a-row v-if="loading">
            <a-progress :percent="progressLevel" status="active" />
        </a-row>
    </a-card>

    <a-card>
        <div class="bill-body">
            <div class="bill-table">
                <a-row class="mt-20 mb-30">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-table
                            :dataSource="finalData"
                            :columns="salesDateColumns"
                            size="small"
                            :pagination="{ defaultPageSize: 10 }"
                            :row-key="(record) => record.invoice_number"
                            :scroll="{ x: 1200 }"
                        >
                            <template #bodyCell="{ column, record }">
                                <template
                                    v-if="column.dataIndex === 'order_date'"
                                >
                                    {{ formatDateTime(record.order_date) }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'gr_rr_no'"
                                >
                                    {{
                                        record.gr_rr_no ? record.gr_rr_no : "-"
                                    }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'tax_rate'"
                                >
                                    {{ record.tax_rate }}%
                                </template>
                                <template
                                    v-if="column.dataIndex === 'tax_amount'"
                                >
                                    {{
                                        formatAmountCurrency(record.tax_amount)
                                    }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'discount'"
                                >
                                    {{ formatAmountCurrency(record.discount) }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'subtotal'"
                                >
                                    {{ formatAmountCurrency(record.subtotal) }}
                                </template>
                                <template v-if="column.dataIndex === 'total'">
                                    {{ formatAmountCurrency(record.total) }}
                                </template>
                            </template>
                            <template #expandedRowRender="orderProduct">
                                <a-table
                                    :columns="saleDataProduct"
                                    :data-source="orderProduct.record.product"
                                    :pagination="false"
                                    :row-key="(record) => record.name"
                                >
                                    <template #bodyCell="{ column, record }">
                                        <template
                                            v-if="
                                                column.dataIndex ===
                                                'purchase_price'
                                            "
                                        >
                                            {{
                                                formatAmountCurrency(
                                                    record.purchase_price
                                                )
                                            }}
                                        </template>
                                        <template
                                            v-if="
                                                column.dataIndex ===
                                                'unit_price'
                                            "
                                        >
                                            {{
                                                formatAmountCurrency(
                                                    record.unit_price
                                                )
                                            }}
                                        </template>
                                        <template
                                            v-if="column.dataIndex === 'mrp'"
                                        >
                                            {{
                                                formatAmountCurrency(record.mrp)
                                            }}
                                        </template>
                                        <template
                                            v-if="
                                                column.dataIndex === 'subtotal'
                                            "
                                        >
                                            {{
                                                formatAmountCurrency(
                                                    record.subtotal
                                                )
                                            }}
                                        </template>
                                    </template>
                                </a-table>
                            </template>
                        </a-table>
                    </a-col>
                </a-row>
            </div>
        </div>
    </a-card>
    <a-modal
        v-model:visible="isModalOpen"
        title="Map Import Excel Data"
        :footer="false"
        :width="'70%'"
    >
        <a-form layout="vertical">
            <a-space direction="vertical" style="width: 100%">
                <a-row :gutter="[16, 16]">
                    <a-col
                        :span="8"
                        v-for="(defaultOption, key) in defaultOptions"
                        :key="key"
                    >
                        <a-row :gutter="16">
                            <a-form-item
                                :label="defaultOption.label"
                                style="width: 95%"
                            >
                                <!-- :class="
                                    defaultOption.is_required ? 'required' : ''
                                "
                                 :required="defaultOption.is_required"   -->
                                <a-col :span="24">
                                    <a-select
                                        style="width: 100%"
                                        @change="
                                            (name) =>
                                                handleMappingSelect(
                                                    name,
                                                    defaultOption.value
                                                )
                                        "
                                        placeholder="Please select a Value"
                                        allowClear
                                        optionFilterProp="title"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="(
                                                mapping, index
                                            ) in MappingColumn"
                                            :key="index"
                                            :value="mapping"
                                            :title="mapping"
                                        >
                                            {{ mapping }}
                                        </a-select-option>
                                    </a-select>
                                </a-col>
                            </a-form-item>
                        </a-row>
                    </a-col>
                </a-row>
                <a-row style="justify-content: flex-end">
                    <a-button type="primary" @click="handleImport"
                        >Preview</a-button
                    >
                </a-row>
            </a-space>
        </a-form>
    </a-modal>
</template>

<script>
import { ref } from "vue";
import { UploadOutlined, SaveOutlined } from "@ant-design/icons-vue";
import * as XLSX from "xlsx";
import fields from "./fields";
import dayjs from "dayjs";
import common from "../../../../common/composable/common";
import { useRouter } from "vue-router";
export default {
    components: { UploadOutlined, SaveOutlined },
    setup() {
        const { salesDateColumns, saleDataProduct, defaultOptions } = fields();
        const router = useRouter();
        const {
            selectedWarehouse,
            appSetting,
            user,
            formatAmountCurrency,
            formatDateTime,
            orderPageObject
        } = common();

        const salesData = ref([]);
        const finalData = ref([]);
        const fileList = ref([]);

        const isModalOpen = ref(false);
        const MappingColumn = ref([]);
        const selectedOptions = ref({
            "  BILL DISC": "discount",
            "  GST AMOUNT": "tax_amount",
            "  ITEM GROSS": "subtotal",
            "  ITEM NET AMT": "total",
            "  ITEM RATE": "item_unit_price",
            "  LANDING COST (BILLING)": "item_unit_price",
            "  MRP (BILLING)": "item_mrp",
            "  PUR PRICE (BILLING)": "item_purchase_price",
            "  SOLD QTY": "total_quantity",
            "  TAX%": "tax_rate",
            "BILL DT.  ": "order_date",
            "BILL NO  ": "invoice_number",
            "CUST GST NO  ": "gr_rr_no",
            "CUSTOMER NAME  ": "customer_name",
            "ITEM NAME  ": "product",
        });

        const progressLevel = ref(0);
        const loading = ref(false);

        const removeFile = () => {
            fileList.value = [];
            salesData.value = [];
            finalData.value = [];
        };

        const back = () => {
            router.go(-1);
        };

        const beforeUpload = (file) => {
            fileList.value = [file];
            isModalOpen.value = true;
            readFile(file);
            return false;
        };

        const handleMappingSelect = (name, value) => {
            if (name !== undefined) {
                selectedOptions.value[name] = value;
            }
        };

        const readFile = (file) => {
            if (file instanceof Blob) {
                const fileReader = new FileReader();

                fileReader.onload = (e) => {
                    const bufferArray = e.target.result;
                    const wb = XLSX.read(bufferArray, { type: "array" });
                    const wsname = wb.SheetNames[0];
                    const ws = wb.Sheets[wsname];
                    const data = XLSX.utils.sheet_to_json(ws);

                    // Extract column names from the keys of the first row
                    MappingColumn.value =
                        data.length > 0 ? Object.keys(data[0]) : [];

                    salesData.value = data;
                };

                fileReader.onerror = (error) => {
                    console.error("File reading error:", error);
                };

                fileReader.readAsArrayBuffer(file);
            }
        };
        const handleImport = () => {
            let newData = salesData.value
                .map((item) => {
                    let temp = {};
                    Object.keys(selectedOptions.value).forEach((key) => {
                        let value = selectedOptions.value[key];
                        if (value != undefined && item[key] !== "") {
                            temp[value] = item[key];
                        }
                    });
                    return temp;
                })
                .filter((item) => {
                    // Filter out data with undefined, null, or empty invoiceNumber
                    return (
                        item.invoice_number && item.invoice_number.trim() !== ""
                    );
                });

            const groupedData = newData.reduce((acc, obj) => {
                const invoiceNumber = obj.invoice_number;
                if (!acc[invoiceNumber]) {
                    acc[invoiceNumber] = {
                        invoice_number: invoiceNumber,
                        order_date: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format("YYYY-MM-DD HH:mm:ss"),
                        customer_name: obj.customer_name,
                        tax_rate: obj.tax_rate,
                        tax_amount: 0,
                        discount: 0,
                        subtotal: 0,
                        total: 0,
                        total_quantity: 0,
                        gr_rr_no: obj.gr_rr_no,
                        product: [],
                        company_id: appSetting.value.xid,
                        order_type: "sales",
                        warehouse_id: selectedWarehouse.value.xid,
                        staff_id: user.value.xid,
                        shipping: 0,
                        due_amount: 0,
                        order_status: "delivered",
                        shipping_type: 1,
                        notes: "Imported Data",
                        staff_user_id: user.value.xid,
                        payment_status: "paid",
                        terms_condition:
                            selectedWarehouse.value.terms_condition,
                        created_at: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format("YYYY-MM-DD HH:mm:ss"),
                        updated_at: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format("YYYY-MM-DD HH:mm:ss"),
                        invoice_type: "sales",
                        order_date_local: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format("YYYY-MM-DD HH:mm:ss"),
                        paid_amount: 0,
                        total_items: 0,
                    };
                }

                // Check if product with the same name already exists, if yes, then sum up their values
                const existingProductIndex = acc[
                    invoiceNumber
                ].product.findIndex((p) => p.name === obj.product);
                if (existingProductIndex !== -1) {
                    const existingProduct =
                        acc[invoiceNumber].product[existingProductIndex];
                    existingProduct.subtotal += obj.subtotal;
                    existingProduct.total_discount += obj.item_total_discount;
                    existingProduct.tax_amount += obj.tax_amount;
                    existingProduct.quantity += obj.total_quantity;
                } else {
                    // Otherwise, add a new product
                    acc[invoiceNumber].product.push({
                        name: obj.product,
                        subtotal: obj.subtotal,
                        total_discount: obj.discount,
                        tax_amount: obj.tax_amount,
                        tax_rate: obj.tax_rate,
                        single_unit_price: obj.item_unit_price,
                        purchase_price: obj.item_purchase_price,
                        unit_price: obj.item_unit_price,
                        mrp: obj.item_mrp,
                        quantity: obj.total_quantity,
                        created_at: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format(),
                        updated_at: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format(),
                    });
                }
                acc[invoiceNumber].tax_amount += obj.tax_amount;
                acc[invoiceNumber].discount += obj.discount;
                acc[invoiceNumber].subtotal += obj.subtotal;
                acc[invoiceNumber].total += obj.total;
                acc[invoiceNumber].total_quantity += obj.total_quantity;
                acc[invoiceNumber].paid_amount += obj.total;
                acc[invoiceNumber].total_items = 0;
                return acc;
            }, {});

            // Convert Set to array
            Object.values(groupedData).forEach((invoice) => {
                invoice.product = Array.from(invoice.product);
                invoice.total_items = invoice.product.length;
            });
            finalData.value = Object.values(groupedData);
            isModalOpen.value = false;
        };

        const handleSave = async () => {
            let grouped = _.chunk(finalData.value, 100);

            loading.value = true;

            let err_data = [];

            for (let i = 0; i < grouped.length; i++) {
                await axiosAdmin
                    .post("/excel-sales", grouped[i])
                    .then((res) => {
                        res.data.err_data.map((data) => err_data.push(data));
                        progressLevel.value = _.round(
                            ((i + 1) / grouped.length) * 100
                        );
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }

            let data = await filterErrorData(err_data);
            if (data.length > 0) exportToExcel(data);
        };

        const filterErrorData = async (err_data) => {
            let invoiceKey = "";

            Object.keys(selectedOptions.value).forEach((key) => {
                if (selectedOptions.value[key] == "invoice_number") {
                    invoiceKey = key;
                }
            });

            const comparator = (salesItem, errItem) => {
                if (salesItem[invoiceKey] === errItem.invoice_number) {
                    salesItem.Error_Message = errItem.status;
                }
                return salesItem[invoiceKey] === errItem.invoice_number;
            };

            let errorData = _.intersectionWith(
                salesData.value,
                err_data,
                comparator
            );
            return errorData;
        };

        const exportToExcel = (errorData) => {
            progressLevel.value = 0;
            loading.value = false;
            removeFile();

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(errorData);
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(wb, `Sales_Import_Error_Data.xlsx`);
        };
        return {
            removeFile,
            beforeUpload,
            orderPageObject,
            salesDateColumns,
            salesData,
            defaultOptions,
            handleImport,
            isModalOpen,
            MappingColumn,
            handleMappingSelect,
            saleDataProduct,
            finalData,
            formatAmountCurrency,
            formatDateTime,
            handleSave,
            progressLevel,
            loading,
            fileList,
            back,
        };
    },
};
</script>
