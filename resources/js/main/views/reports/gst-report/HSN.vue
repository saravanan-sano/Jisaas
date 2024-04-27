<template>
    <a-button type="primary" @click="handleDownload">HSN/SAC Report</a-button>
</template>
<script>
import * as XLSX from "xlsx";
import _ from "lodash";
import moment from "moment";
import { message } from "ant-design-vue";

export default {
    props: {
        selectedRange: {
            type: Array,
            required: true,
        },
    },
    methods: {
        async handleDownload() {
            try {
                const response = await axiosAdmin.get(
                    "sales?fields=id,xid,unique_id,warehouse_id,x_warehouse_id,warehouse{id,xid,name},from_warehouse_id,x_from_warehouse_id,fromWarehouse{id,xid,name},invoice_number,order_type,order_date,tax_amount,discount,shipping,subtotal,paid_amount,due_amount,order_status,payment_status,total,tax_rate,staff_user_id,x_staff_user_id,staffMember{id,xid,name,profile_image,profile_image_url,user_type},user_id,x_user_id,user{id,xid,user_type,name,profile_image,profile_image_url,phone},orderPayments{id,xid,amount,payment_id,x_payment_id},orderPayments:payment{id,xid,amount,payment_mode_id,x_payment_mode_id,date,notes},orderPayments:payment:paymentMode{id,xid,name},items{id,xid,product_id,x_product_id,single_unit_price,unit_price,quantity,tax_rate,total_tax,tax_type,total_discount,subtotal},items:product{id,xid,name,image,image_url,unit_id,x_unit_id,hsn_sac_code},items:product:unit{id,xid,name,short_name},items:product:details{id,xid,warehouse_id,x_warehouse_id,product_id,x_product_id,current_stock},cancelled,terms_condition,shippingAddress{id,xid,order_id,name,email,phone,address,shipping_address,city,state,country,zipcode}&order=id%20desc&offset=0&limit=10000"
                );
                let products = response.data.filter((item) => {
                    // Filter by date within selectedRange
                    const date = moment(item.order_date);
                    const startDate = moment(this.selectedRange[0].$d);
                    const endDate = moment(this.selectedRange[1].$d);
                    const isInSelectedRange = date.isBetween(
                        startDate,
                        endDate,
                        null,
                        "[]"
                    );

                    // Filter by payment_status equals "paid"
                    const isPaid = item.payment_status === "paid";

                    return isInSelectedRange && isPaid;
                });
                if (products.length === 0) {
                    message.warning(
                        "No Report Founded in these Dates, Try someother Dates"
                    );
                    this.$emit("update:selectedRange", null);
                } else {
                    let filteredItems = _.filter(
                        products.flatMap((item) => item.items),
                        (item) => {
                            return _.get(item, "product.hsn_sac_code") !== null;
                        }
                    );
                    const groupedItems = _.groupBy(filteredItems, (item) => {
                        return _.get(item, "product.hsn_sac_code");
                    });
                    this.exportToExcel(groupedItems);
                }
            } catch (error) {
                console.error(error);
            }
        },
        exportToExcel(products) {
            const productsWithCustomColumns = Object.keys(products).map(
                (key) => {
                    return {
                        "HSN/SAC": key,
                        // Taxable value = overall GST amount - Total amount
                        "Taxable Value": _.round(
                            _.sumBy(products[key], "subtotal") -
                                _.sumBy(products[key], "total_tax"),
                            2
                        ),
                        // For 5% GST
                        "GST 5%": "5%",
                        "GST Amount 5%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["5"],
                                "total_tax"
                            ),
                            2
                        ),
                        "CGST 2.5%": "2.5%",
                        "CGST Amount 2.5%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["5"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        "SGST 2.5%": "2.5%",
                        "SGST Amount 2.5%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["5"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        // For 12% GST
                        "GST 12%": "12%",
                        "GST Amount 12%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["12"],
                                "total_tax"
                            ),
                            2
                        ),
                        "CGST 6%": "6%",
                        "CGST Amount 6%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["12"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        "SGST 6%": "6%",
                        "SGST Amount 6%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["12"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        // For 18% GST
                        "GST 18%": "18%",
                        "GST Amount 18%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["18"],
                                "total_tax"
                            ),
                            2
                        ),
                        "CGST 9%": "9%",
                        "CGST Amount 9%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["18"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        "SGST 9%": "9%",
                        "SGST Amount 9%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["18"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        // For 28% GST
                        "GST 28%": "28%",
                        "GST Amount 28%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["28"],
                                "total_tax"
                            ),
                            2
                        ),
                        "CGST 14%": "14%",
                        "CGST Amount 14%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["28"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        "SGST 14%": "14%",
                        "SGST Amount 14%": _.round(
                            _.sumBy(
                                _.groupBy(products[key], "tax_rate")["28"],
                                "total_tax"
                            ) / 2,
                            2
                        ),
                        // IGST Will be a empty string
                        "IGST Percentage": "",
                        "IGST Amount": "",
                        // Total value
                        "Total value with tax": _.sumBy(
                            products[key],
                            "subtotal"
                        ),
                    };
                }
            );

            const worksheet = XLSX.utils.json_to_sheet(
                productsWithCustomColumns
            );
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "HSN");
            const excelBuffer = XLSX.write(workbook, {
                bookType: "xlsx",
                type: "array",
            });
            this.saveExcelFile(excelBuffer);
        },
        saveExcelFile(buffer) {
            const data = new Blob([buffer], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            });
            const fileName = `HSN_${moment().format("DD_MM_yy")}.xlsx`;
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
        },
    },
};
</script>
<style></style>
