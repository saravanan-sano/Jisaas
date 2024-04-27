<template>
    <a-card
        class="page-content-sub-header breadcrumb-left-border"
        :bodyStyle="{ padding: '0px', margin: '0px 16px 0' }"
    >
        <a-row>
            <a-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                <a-page-header
                    title="Import Sales"
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
                            :accept="'.xlsx,.csv'"
                            :before-upload="beforeUpload"
                            :maxCount="1"
                            :remove="removeFile"
                        >
                            <!-- :loading="loading" -->
                            <a-button type="primary">
                                <template #icon>
                                    <UploadOutlined />
                                </template>
                                {{ $t("common.upload") }}
                            </a-button>
                        </a-upload>
                        <a-button type="primary" @click="handleImport"
                            >Save</a-button
                        >
                    </a-space>
                </div>
            </a-col>
        </a-row>
    </a-card>
    <a-modal v-model:visible="open" :closable="false" :footer="false">
        <a-progress :percent="progress_level" status="active" />
    </a-modal>
    <a-card>
        <div class="bill-body">
            <div class="bill-table">
                <a-row class="mt-20 mb-30">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-table
                            :dataSource="salesData"
                            :columns="Columns"
                            size="small"
                            :pagination="{ defaultPageSize: 10 }"
                            :scroll="{ x: 4000 }"
                        >
                        </a-table>
                    </a-col>
                </a-row>
            </div>
        </div>
    </a-card>
</template>

<script>
import { createVNode, ref } from "vue";
import { UploadOutlined } from "@ant-design/icons-vue";
import * as XLSX from "xlsx";
import dayjs from "dayjs";
import common from "../../../../common/composable/common";
import { useRouter } from "vue-router";

export default {
    components: { UploadOutlined },
    setup() {
        const salesData = ref([]);
        const Columns = ref([]);
        const open = ref(false);
        const progress_level = ref(0);
        const { selectedWarehouse, appSetting, user } = common();
        const router = useRouter();
        const removeFile = () => {
            salesData.value = [];
        };

        const beforeUpload = (file) => {
            salesData.value = [file];
            readFile(file);
            return false;
        };

        // "company_id" = app.xid,
        // "order_type" = 'sales',
        // "warehouse_id" = selectedWarehouse.xid,
        // "staff_id" = loginUser.id,
        // "shipping" = 0,
        // "due_amount" = 0,
        // "order_status" = "delivered",
        // "shipping_type" = 0,
        // "notes" = selectedWarehouse.notes,
        // "staff_user_id" = loginUser.id,
        // "payment_status" = 'paid',
        // "terms_condition" = selectedWarehouse.terms_condition,
        // "created_at" = orderDate,
        // "updated_at" = orderDate,
        // "invoice_type" = 'sales',
        // "order_date_local" = orderDate,
        // "paid_amount" = total,
        // "total_items" = product.length,

        const defaultOptions = [
            "none",
            "invoice_number",
            "order_date",
            "customer_name",
            "tax_rate",
            "tax_amount",
            "discount",
            "subtotal",
            "total",
            "total_quantity",
            "gr_rr_no",
            "product",
            "mrp",
            "unit_price",
            "pur_price",
        ];

        const selectedOptions = ref({});
        const handleChange = (event) => {
            const { name, value } = event.target;
            selectedOptions.value[name] = value;
        };

        const back = () => {
            router.go(-1);
        };

        const handleImport = async () => {
            let newData = salesData.value
                .map((item) => {
                    let temp = {};
                    Object.keys(selectedOptions.value).forEach((key) => {
                        let value = selectedOptions.value[key];
                        if (value !== "none" && item[key] !== "") {
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
                    existingProduct.total_discount += obj.discount;
                    existingProduct.total_tax += obj.tax_amount;
                    existingProduct.quantity += obj.total_quantity;
                } else {
                    // Otherwise, add a new product
                    acc[invoiceNumber].product.push({
                        name: obj.product,
                        subtotal: obj.subtotal,
                        total_discount: obj.discount,
                        total_tax: obj.tax_amount,
                        tax_rate: obj.tax_rate,
                        single_unit_price: obj.unit_price,
                        purchase_price: obj.pur_price,
                        unit_price: obj.unit_price,
                        mrp: obj.mrp,
                        quantity: obj.total_quantity,
                        created_at: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format("YYYY-MM-DD HH:mm:ss"),
                        updated_at: dayjs("1899-12-30")
                            .add(obj.order_date, "day")
                            .format("YYYY-MM-DD HH:mm:ss"),
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

            let grouped = _.chunk(Object.values(groupedData), 100);

            open.value = true;

            let err_data = [];

            for (let i = 0; i < grouped.length; i++) {
                await axiosAdmin
                    .post("/excel-sales", grouped[i])
                    .then((res) => {
                        res.data.err_data.map((data) => err_data.push(data));
                        progress_level.value = _.round(
                            ((i + 1) / grouped.length) * 100
                        );
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }

            let data = await filterErrorData(err_data);
            exportToExcel(data);
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
                    const columnNames =
                        data.length > 0 ? Object.keys(data[0]) : [];

                    const columns = columnNames.map((columnName) => ({
                        title: createSelect(columnName),
                        dataIndex: columnName,
                    }));
                    function createSelect(defaultValue) {
                        return createVNode(
                            "div",
                            {
                                style: "display: flex; flex-direction: column; align-items: center;",
                            },
                            [
                                createVNode(
                                    "label",
                                    {
                                        style: "margin-bottom: 8px; font-size:12px",
                                    },
                                    defaultValue
                                ),
                                createVNode(
                                    "select",
                                    {
                                        name: defaultValue,
                                        style: "width: fit-content",
                                        className: "import-select-box",
                                        onchange: handleChange,
                                    },
                                    defaultOptions.map((option) =>
                                        createVNode(
                                            "option",
                                            { value: option },
                                            option
                                        )
                                    )
                                ),
                            ]
                        );
                    }

                    // Update Columns with the generated column definitions
                    Columns.value = columns;

                    // Set the salesData to the imported data
                    salesData.value = data;
                };

                fileReader.onerror = (error) => {
                    console.error("File reading error:", error);
                };

                fileReader.readAsArrayBuffer(file);
            }
        };

        const exportToExcel = (error) => {
            const worksheet = XLSX.utils.json_to_sheet(error);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(
                workbook,
                worksheet,
                "Error_sales_import"
            );
            const excelBuffer = XLSX.write(workbook, {
                bookType: "xlsx",
                type: "array",
            });
            saveExcelFile(excelBuffer);
        };

        const saveExcelFile = (buffer) => {
            const data = new Blob([buffer], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            });
            const fileName = `Error_sales_import.xlsx`;
            if (navigator.msSaveBlob) {
                // For IE 10+
                navigator.msSaveBlob(data, fileName);
            } else {
                const link = document.createElement("a");
                if (link.download !== undefined) {
                    // Modern browsers
                    const url = URL.createObjectURL(data);
                    link.setAttribute("href", url);
                    link.setAttribute("download", fileName);
                    link.style.visibility = "hidden";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        };
        return {
            removeFile,
            beforeUpload,
            Columns,
            salesData,
            defaultOptions,
            handleImport,
            back,
            progress_level,
            open,
        };
    },
};
</script>
