<template>
    <a-modal
        :visible="visible"
        :centered="true"
        :maskClosable="false"
        title="Press(p) To Print Invoice"
        width="22cm"
        @cancel="onClose"
        bodyStyle="height: 79vh;overflow: scroll;"
    >
        <div id="sales_a5_invoice" class="A5_invoice">
            <div
                v-for="(items, key) in chunkOrderItems(order.items)"
                :key="key"
            >
                <header class="a5_invoie_header_wraper">
                    <div class="a5_invoie_header">
                        <div
                            class="a5_invoie_header_title"
                            :style="`font-size: ${DFontSize + 2}px`"
                        >
                            <u>{{
                                order.order.order_type == "quotations"
                                    ? "Quotation"
                                    : "Invoice"
                            }}</u>
                        </div>
                        <div
                            class="a5_invoie_header_logo"
                            v-if="
                                invoiceTemplate.header.logo &&
                                selectedWarehouse.logo_url
                            "
                        >
                            <img
                                class="invoice-logo"
                                :src="selectedWarehouse.logo_url"
                            />
                        </div>
                        <div
                            class="a5_invoie_header_title"
                            :style="`font-size: ${DFontSize + 2}px`"
                        >
                            <p :style="`font-size: ${DFontSize + 2}px`">
                                {{ selectedWarehouse.name }}
                            </p>
                        </div>
                        <div
                            class="a5_invoie_gstin_title"
                            :style="`font-size: ${DFontSize}px`"
                            v-if="
                                selectedWarehouse && selectedWarehouse.address
                            "
                        >
                            <p>{{ selectedWarehouse.address }}</p>
                        </div>
                        <div
                            class="a5_invoie_contact_info"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            <p>Tel: {{ selectedWarehouse.phone }}</p>
                            <p>Email: {{ selectedWarehouse.email }}</p>
                        </div>
                        <div
                            class="a5_invoie_gstin_title"
                            :style="`font-size: ${DFontSize}px`"
                            v-if="
                                selectedWarehouse && selectedWarehouse.gst_in_no
                            "
                        >
                            <p style="font-weight: bold">
                                {{ $t("tax.tax_no") }}:
                                {{ selectedWarehouse.gst_in_no }}
                            </p>
                        </div>
                    </div>
                    <div class="a5_invoice_order_details_wrapper">
                        <div class="a5_invoice_customer_details">
                            <div
                                class="a5_invoice_customer_address"
                                v-if="order && order.user && order.user.name"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <div>Party Details</div>
                            </div>
                            <div
                                class="a5_text"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                {{ order.user.name }}
                                <br />
                                {{
                                    order.order.order_type == "online-orders"
                                        ? order.orderShippingAddress &&
                                          order.orderShippingAddress
                                              .shipping_address
                                            ? order.orderShippingAddress
                                                  .shipping_address
                                            : "Self Pickup"
                                        : order && order.user
                                        ? order.user.address
                                        : ""
                                }}
                            </div>
                            <div
                                :style="`font-size: ${DFontSize}px; text-align: left`"
                                v-if="
                                    order &&
                                    order.user &&
                                    order.user.email &&
                                    invoiceTemplate.customer_details.email
                                "
                            >
                                {{ order.user.email }}
                            </div>
                            <div
                                :style="`font-size: ${DFontSize}px; text-align: left`"
                                v-if="
                                    order &&
                                    order.user &&
                                    order.user.phone &&
                                    invoiceTemplate.customer_details.phone
                                "
                            >
                                {{ order.user.phone }}
                            </div>
                            <div
                                :style="`
                                    font-size: ${DFontSize}px;
                                    font-weight: bold;
                                    text-align: left;`"
                                v-if="
                                    order && order.user && order.user.tax_number
                                "
                            >
                                {{ $t("tax.tax_no") }}/UIN :
                                {{ order.user.tax_number }}
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.delivery_to
                                "
                            >
                                <div>Delivered To</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.delivery_to }}
                                </div>
                            </div>
                        </div>
                        <div class="a5_invoice_order_details">
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <div>Invoice No</div>
                                <div>:</div>
                                <div>{{ order.order.invoice_number }}</div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <div>Dated</div>
                                <div>:</div>
                                <div>
                                    {{
                                        formatDateTime(
                                            order.order.order_date_local
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                v-if="selectedWarehouse.gst_in_no"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <div>Area Code</div>
                                <div>:</div>
                                <div>
                                    {{
                                        selectedWarehouse.gst_in_no.substring(
                                            0,
                                            2
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.place_of_supply
                                "
                            >
                                <div>Place of Supply</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.place_of_supply }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.reverse_charge
                                "
                            >
                                <div>Reverse Charge</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.reverse_charge }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order && order.order && order.order.gr_rr_no
                                "
                            >
                                <div>GR/RR No</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.gr_rr_no }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.transport
                                "
                            >
                                <div>Transport</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.transport }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.vechile_no
                                "
                            >
                                <div>Vehicle No</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.vechile_no.toUpperCase() }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order && order.order && order.order.station
                                "
                            >
                                <div>Station</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.station }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_customer"
                                :style="`font-size: ${DFontSize}px`"
                                v-if="
                                    order &&
                                    order.order &&
                                    order.order.buyer_order_no
                                "
                            >
                                <div>BUYER ORDER NO</div>
                                <div>:</div>
                                <div>
                                    {{ order.order.buyer_order_no }}
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                {{ calculationBasedOnGST(order) }}
                <div class="a5_invoice_product_wrapper">
                    <table
                        class="a5_invoice_body_wrapper"
                        style="border-collapse: collapse; width: 100%"
                    >
                        <thead style="background: #eee">
                            <tr :style="`font-size: ${DFontSize}px`">
                                <th
                                    :style="`
                                        width: 2%;
                                        border-bottom: 1px solid #000;
                                        border-left: none;
                                        font-size: ${DFontSize}px;
                                    `"
                                >
                                    S.No
                                </th>
                                <th
                                    :style="`
                                        width: 38%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    Description of Goods
                                </th>
                                <th
                                    :style="`
                                        width: 11%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    HSN/SAC
                                </th>

                                <th
                                    :style="`
                                        width: 5%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    Qty
                                </th>
                                <th
                                    :style="`
                                        width: 13%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                    v-if="
                                        invoiceTemplate.table_setting &&
                                        invoiceTemplate.table_setting.units
                                    "
                                >
                                    Unit
                                </th>
                                <th
                                    :style="`
                                        width: 10%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                    v-if="order.show_mrp"
                                >
                                    MRP
                                </th>
                                <th
                                    :style="`
                                        width: 10%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    Rate
                                </th>
                                <th
                                    :style="`
                                        width: 5%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                    v-if="selectedWarehouse.gst_in_no != null"
                                >
                                    {{ $t("tax.tax") }}
                                </th>
                                <th
                                    :style="`
                                        width: 10%;
                                        border: 1px solid #000;
                                        border-top: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    Discount
                                </th>
                                <th
                                    :style="`
                                        border: 1px solid #000;
                                        border-top: none;
                                        border-right: 0;
                                        font-size: ${DFontSize}px;`"
                                >
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.xid">
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{ item.sn }}
                                </td>
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        text-align: center;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{ item.name }} <br />
                                    <div
                                        v-if="item && item.identity_code"
                                        :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                    >
                                        <b>Additional Details:</b> <br />
                                        {{ item.identity_code }}
                                    </div>
                                </td>
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{
                                        item && item.hsn_sac_code
                                            ? item.hsn_sac_code
                                            : "-"
                                    }}
                                </td>

                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        border-right: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{
                                        item && item.quantity
                                            ? item.quantity
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                    v-if="
                                        invoiceTemplate.table_setting &&
                                        invoiceTemplate.table_setting.units
                                    "
                                >
                                    {{
                                        item && item.unit_short_name
                                            ? item.unit_short_name
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                    v-if="order.show_mrp"
                                >
                                    {{
                                        item && item.mrp
                                            ? formatAmountCurrency(item.mrp)
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{
                                        item && item.unit_price
                                            ? formatAmountCurrency(
                                                  item.unit_price
                                              )
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;
                                    `"
                                    v-if="selectedWarehouse.gst_in_no != null"
                                >
                                    {{
                                        item && item.tax_rate
                                            ? item.tax_rate
                                            : "-"
                                    }}
                                </td>
                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{
                                        item && item.total_discount
                                            ? `${formatAmountCurrency(
                                                  item.total_discount
                                              )}`
                                            : "0"
                                    }}
                                </td>

                                <td
                                    :style="`
                                        border: 1px solid #000;
                                        border-right: none;
                                        font-size: ${DFontSize}px;`"
                                >
                                    {{
                                        item && item.subtotal
                                            ? formatAmountCurrency(
                                                  item.subtotal
                                              )
                                            : "-"
                                    }}
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    chunkOrderItems(order.items).length ==
                                        key + 1 &&
                                    invoiceTemplate.footer &&
                                    invoiceTemplate.footer.total_item_count &&
                                    invoiceTemplate.table_setting &&
                                    invoiceTemplate.table_setting.units
                                "
                            >
                                <td></td>
                                <td></td>
                                <td
                                    v-if="
                                        invoiceTemplate.footer &&
                                        invoiceTemplate.footer.total_item_count
                                    "
                                >
                                    Items:
                                </td>
                                <td
                                    v-if="
                                        invoiceTemplate.footer &&
                                        invoiceTemplate.footer.total_item_count
                                    "
                                >
                                    {{ order.order.total_quantity }}
                                </td>
                                <td
                                    v-if="
                                        invoiceTemplate.table_setting &&
                                        invoiceTemplate.table_setting.units
                                    "
                                ></td>
                                <td
                                    v-if="invoiceTemplate.table_setting.mrp"
                                ></td>
                                <td></td>
                                <td
                                    v-if="selectedWarehouse.gst_in_no != null"
                                ></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p
                    class="continue_page"
                    v-if="chunkOrderItems(order.items).length != key + 1"
                    :style="`font-size: ${DFontSize}px`"
                >
                    * Continue Page {{ key + 1 }} *
                </p>
                <footer
                    class="a5_invoie_footer_wraper"
                    v-if="chunkOrderItems(order.items).length == key + 1"
                >
                    <div class="a5_invoie_gst_total_new">
                        <div
                            class="a5_invoie_footer_calculation"
                            :style="`font-size: ${DFontSize}px`"
                            v-if="selectedWarehouse.gst_in_no != null"
                        >
                            <div
                                class="footer_calculation"
                                v-if="!checkStartingDigits"
                            >
                                <div
                                    class="a5_footer_tax"
                                    v-for="(tax, key) in taxDifProducts"
                                    :key="key"
                                >
                                    <p :style="`font-size: ${DFontSize}px`">
                                        Sales@{{ key }}% ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum
                                            )
                                        }}
                                    </p>
                                    <p :style="`font-size: ${DFontSize}px`">
                                        {{ $t("tax.c_tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum / 2
                                            )
                                        }}
                                    </p>
                                    <p :style="`font-size: ${DFontSize}px`">
                                        {{ $t("tax.s_tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum / 2
                                            )
                                        }}
                                    </p>
                                    <p :style="`font-size: ${DFontSize}px`">
                                        {{ $t("tax.tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                    <p :style="`font-size: ${DFontSize}px`">
                                        Sale ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum +
                                                    tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="footer_calculation" v-else>
                                <div
                                    class="a5_footer_tax"
                                    v-for="(tax, key) in taxDifProducts"
                                    :key="key"
                                >
                                    <p :style="`font-size: ${DFontSize}px`">
                                        Sales@{{ key }}% ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum
                                            )
                                        }}
                                    </p>
                                    <p :style="`font-size: ${DFontSize}px`">
                                        {{ $t("tax.i_tax") }} ={{
                                            formatAmountCurrency(
                                                tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                    <p :style="`font-size: ${DFontSize}px`">
                                        Sale ={{
                                            formatAmountCurrency(
                                                tax.totalAmountSum +
                                                    tax.taxAmountSum
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="a5_invoice_total_wrapper"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            <div
                                class="a5_invoice_gst_footer_new"
                                v-if="selectedWarehouse.gst_in_no != null"
                            >
                                <div :style="`font-size: ${DFontSize}px`">
                                    SUB TOTAL
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    :
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ formatAmountCurrency(totalAmountSum) }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_gst_footer_new"
                                v-if="
                                    !checkStartingDigits &&
                                    selectedWarehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ $t("tax.c_tax") }}
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    :
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ formatAmountCurrency(totalTax / 2) }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_gst_footer_new"
                                v-if="
                                    !checkStartingDigits &&
                                    selectedWarehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ $t("tax.s_tax") }}
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    :
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ formatAmountCurrency(totalTax / 2) }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_gst_footer_new"
                                v-if="
                                    checkStartingDigits &&
                                    selectedWarehouse.gst_in_no != null &&
                                    totalTax != 0
                                "
                            >
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ $t("tax.i_tax") }}
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    :
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{ formatAmountCurrency(totalTax) }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_gst_footer_new"
                                v-if="order.order.shipping != 0"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <div :style="`font-size: ${DFontSize}px`">
                                    SHIPPING
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    :
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{
                                        formatAmountCurrency(
                                            order.order.shipping
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="a5_invoice_gst_footer_new"
                                v-if="order.order.discount != 0"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <div :style="`font-size: ${DFontSize}px`">
                                    DISCOUNT
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    :
                                </div>
                                <div :style="`font-size: ${DFontSize}px`">
                                    {{
                                        formatAmountCurrency(
                                            order.order.discount
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="a5_invoie_gst_total_1">
                        <p :style="`font-size: ${DFontSize}px; width: 75%`">
                            In Words : {{ numberToWords(order.order.total) }}
                        </p>
                        <div
                            class="a5_invoice_gst_footer"
                            :style="`font-size: ${DFontSize}px`"
                        >
                            <div :style="`font-size: ${DFontSize}px`">
                                Round Off
                            </div>
                            <div :style="`font-size: ${DFontSize}px`">:</div>
                            <div :style="`font-size: ${DFontSize}px`">
                                {{
                                    formatAmountCurrency(
                                        roundOff(
                                            totalAmountSum,
                                            totalTax,
                                            order.order.shipping,
                                            order.order.discount
                                        ).difference
                                    )
                                }}
                            </div>
                            <div :style="`font-size: ${DFontSize}px`">
                                Total
                            </div>
                            <div :style="`font-size: ${DFontSize}px`">:</div>
                            <div :style="`font-size: ${DFontSize}px`">
                                {{
                                    formatAmountCurrency(
                                        roundOff(
                                            totalAmountSum,
                                            totalTax,
                                            order.order.shipping,
                                            order.order.discount
                                        ).total
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="a5_invoie_bank_details"
                        v-if="
                            selectedWarehouse && selectedWarehouse.bank_details
                        "
                        :style="`font-size: ${DFontSize}px`"
                    >
                        <p>
                            Bank Details :
                            {{ selectedWarehouse.bank_details }}
                        </p>
                    </div>
                    <div
                        class="a5_invoie_footer_last"
                        :style="`font-size: ${DFontSize}px`"
                    >
                        <div class="a5_invoice_signature">
                            <div
                                class="a5_invoice_terms"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <p v-if="order.order.notes">Notes:</p>
                                <p v-if="order.order.notes">
                                    {{ order.order.notes }}<br />
                                </p>
                                <p>Terms & Conditions</p>
                                <p>E & O.E</p>
                                <template v-if="order.order.terms_condition">
                                    <p
                                        v-for="(
                                            item, index
                                        ) in order.order.terms_condition.split(
                                            '\n'
                                        )"
                                        :key="index"
                                        :style="`font-size: ${DFontSize}px`"
                                    >
                                        {{ item }}
                                    </p>
                                </template>
                            </div>
                            <div
                                class="a5_receiver_signature"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <p>Receiver's Signature:</p>
                            </div>
                        </div>
                        <div class="a5_invoice_store_signature">
                            <div
                                class="a5_auth_signature"
                                :style="`font-size: ${DFontSize}px`"
                            >
                                <p>for {{ selectedWarehouse.name }}</p>
                                <p>Authorized Signature</p>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <template #footer>
            <div class="footer-button">
                <a-button type="primary" v-print="'#sales_a5_invoice'">
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    {{ $t("common.print_invoice") }}
                </a-button>
            </div>
        </template>
    </a-modal>
</template>

<script>
import {
    ref,
    defineComponent,
    computed,
    onBeforeUnmount,
    onMounted,
} from "vue";
import { PrinterOutlined } from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
const posInvoiceCssUrl = window.config.pos_invoice_css;
import _ from "lodash-es"

export default defineComponent({
    props: ["visible", "order", "routeBack"],
    emits: ["closed", "success"],
    components: {
        PrinterOutlined,
    },
    setup(props, { emit }) {
        const {
            appSetting,
            formatAmountCurrency,
            formatAmountCurrency1,
            formatDateTime,
            selectedWarehouse,
            numberToWords,
        } = common();
        const store = useStore();
        const router = useRouter();
        const company = appSetting.value;
        const invoiceTemplate = ref(JSON.parse(company.invoice_template));
        const DFontSize =
            invoiceTemplate.value && invoiceTemplate.value.font_size
                ? invoiceTemplate.value.font_size
                : 10;

        const chunkOrderItems = (value) => {
            let chunkItems = _.chunk(value, 7);

            if (chunkItems[chunkItems.length - 1].length > 4) {
                let lastItem = chunkItems.pop();
                _.chunk(lastItem, 4).map((item) => chunkItems.push(item));
            }

            return chunkItems;
        };

        const onClose = () => {
            store.dispatch("auth/updateVisibleSubscriptionModules");
            if (props.routeBack) {
                router.push(props.routeBack);
            } else {
                router.go(-1);
            }
            emit("closed");
        };

        const handleKeyPress = (event) => {
            if (
                event.key === "p" &&
                document.activeElement.nodeName === "DIV"
            ) {
                const printButton = document.getElementById("printButton");
                if (printButton) {
                    printButton.click();
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

        const checkStartingDigits = computed(() => {
            if (
                props.order &&
                props.order.user &&
                props.order.user.tax_number  &&
                selectedWarehouse.value.gst_in_no
            ) {
                const warehouseGSTNumber = selectedWarehouse.value.gst_in_no
                    ? selectedWarehouse.value.gst_in_no
                    : 0;
                const userTaxNumber = props.order.user.tax_number;

                // Compare the first two characters of the strings and return the result
                return (
                    warehouseGSTNumber.substring(0, 2) !=
                    userTaxNumber.substring(0, 2)
                );
            } else {
                // Handle the case when props.order.order is undefined
                return false;
            }
        });

        const totalTax = ref("");
        const totalAmountSum = ref("");
        const taxDifProducts = ref({});

        const calculationBasedOnGST = () => {
            // Group products by tax_rate
            const groupedProducts = _.groupBy(props.order.items, "tax_rate");

            // Calculate the sum of tax_amount and total_amount for each tax_rate
            const result = _.mapValues(groupedProducts, (productList) => {
                const taxAmountSum = _.sumBy(productList, "total_tax");
                const totalAmountSum = _.sumBy(
                    productList,
                    (product) => product.subtotal - product.total_tax
                );
                return { taxAmountSum, totalAmountSum };
            });

            // Calculate the sum of taxAmountSum and totalAmountSum from the result object
            let sumOfTaxAmountSum = 0;
            let sumOfTotalAmountSum = 0;

            for (const key in result) {
                if (result.hasOwnProperty(key)) {
                    sumOfTaxAmountSum += parseFloat(result[key].taxAmountSum);
                    sumOfTotalAmountSum += parseFloat(
                        result[key].totalAmountSum
                    );
                }
            }
            totalTax.value = sumOfTaxAmountSum;
            totalAmountSum.value = sumOfTotalAmountSum;
            taxDifProducts.value = result;
        };

        const roundOff = (total, tax, shipping, discount) => {
            let overallSum = total + tax + shipping - discount;
            let roundOverallSum = _.round(overallSum);

            return {
                total: roundOverallSum,
                difference: Math.abs(overallSum - roundOverallSum),
            };
        };

        return {
            appSetting,
            onClose,
            formatDateTime,
            selectedWarehouse,
            formatAmountCurrency,
            formatAmountCurrency1,
            numberToWords,
            checkStartingDigits,
            calculationBasedOnGST,
            totalTax,
            totalAmountSum,
            taxDifProducts,
            invoiceTemplate,
            roundOff,
            chunkOrderItems,
            DFontSize,
        };
    },
});
</script>
<style>
#sales_a5_invoice {
    text-align: center;
    color: #000;
    font-family: "Times New Roman", Times, serif;
}

#sales_a5_invoice p {
    margin: 0;
    /* font-size: 12px; */
}

.a5_invoie_header_logo > img {
    display: block;
    max-width: 120px;
    width: 100%;
}

.a5_invoie_header_logo {
    display: flex;
    justify-content: center;
    align-items: center;
}

.a5_invoie_header_title {
    /* font-size: 16px; */
    font-weight: 900;
}

/* .a5_invoie_gstin_title {
    font-size: 12px;
} */

.a5_invoie_contact_info {
    display: flex;
    justify-content: center;
    gap: 9px;
    /* font-size: 12px; */
}

/* #sales_a5_invoice {
    border: 1px solid #000;
} */

.a5_invoie_header {
    padding: 3px;
    border: 1px solid #000;
}

.a5_invoice_order_details_wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-right: 1px solid #000;
    border-left: 1px solid #000;
}

.a5_invoice_customer_address {
    display: grid;
    grid-template-columns: 25% 2% 68%;
    text-align: left;
    /* margin-bottom: 9px; */
    /* height: 20%; */
}
.a5_text {
    text-align: left;
    /* font-size: 12px; */
}
.a5_invoice_customer {
    display: grid;
    grid-template-columns: 38% 2% 60%;
    text-align: left;
}
.a5_invoie_header_wraper {
    width: 100%;
}

.a5_invoice_customer_address {
    grid-template-columns: 38% 2% 60%;
    /* min-height: 45%;
    max-height: 38%; */
}

.a5_invoice_customer_details {
    padding: 3px;
    border-right: 1px solid #000;
}

.a5_invoice_order_details {
    padding: 3px;
}

.a5_invoice_customer_address > div:nth-child(1),
.a5_invoice_customer > div:nth-child(1),
.a5_invoice_customer_address > div:nth-child(2),
.a5_invoice_customer > div:nth-child(2) {
    font-weight: 900;
}
.a5_invoice_customer_address,
.a5_invoice_customer {
    /* font-size: 12px; */
}

.a5_invoie_gst_total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: flex-start;
    padding: 3px;
    border-bottom: 1px solid #000;
}
.a5_invoie_gst_total_1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 3px;
    border-bottom: 1px solid #000;
    width: 100%;
}

.a5_invoice_gst_footer {
    display: grid;
    grid-template-columns: 45% 10% 45%;
    /* font-size: 12px; */
    width: 27%;
}
.a5_invoie_gst_total_1 > p:nth-child(1) {
    width: 73%;
    text-align: left;
}
.a5_invoice_gst_footer > div:nth-child(1),
.a5_invoice_gst_footer > div:nth-child(2),
.a5_invoice_gst_footer > div:nth-child(4),
.a5_invoice_gst_footer > div:nth-child(5) {
    font-weight: 900;
}
.a5_invoice_gst_footer > div:nth-child(3),
.a5_invoice_gst_footer > div:nth-child(6) {
    text-align: right;
}

.a5_invoie_footer_calculation {
    width: 75%;
    display: flex;
    flex-direction: column;
    align-items: baseline;
    /* font-size: 12px; */
    /* padding: 9px; */
    /* border-bottom: 1px solid #000; */
}
.a5_invoie_footer_calculation > p {
    font-weight: 900;
}

.a5_footer_tax {
    display: flex;
    justify-content: flex-start;
    gap: 5px;
}

.a5_invoie_bank_details {
    padding: 3px;
    border-bottom: 1px solid #000;
    font-weight: 900;
    text-align: left;
}

.a5_invoie_footer_last {
    display: grid;
    grid-template-columns: 1fr 1fr;
    /* font-size: 12px; */
    text-align: left;
}

.a5_invoice_terms {
    padding: 3px;
    /* font-size: 12px; */
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.a5_invoice_terms > p:nth-child(1) {
    text-decoration: underline;
}
.a5_receiver_signature {
    padding: 3px;
    font-weight: 900;
    text-align: left;
    border-top: 1px solid #000;
}

.a5_auth_signature {
    padding: 3px;
    font-weight: 900;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.a5_auth_signature > p:nth-child(1) {
    margin-bottom: 30px !important;
}

.a5_invoie_body_wraper {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px !important;
}
.a5_invoie_body_wraper > tbody > td {
    padding: 0 2px;
}

.a5_invoice_product_wrapper {
    position: relative;
}

.a5_invoie_footer_wraper {
    border: 1px solid #000;
}

.header.a5_invoie_header_wraper {
    border: 1px solid;
}
.a5_invoice_body_wrapper {
    border: 1px solid #000;
    border-bottom: none;
}

.a5_invoie_gst_total_new {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    width: 100%;
    border-bottom: 1px solid #000;
    padding: 3px;
}
.a5_invoice_total_wrapper {
    width: 25%;
    display: flex;
    flex-direction: column;
    align-items: baseline;
    justify-content: flex-start;
}
.a5_invoice_gst_footer_new {
    display: grid;
    grid-template-columns: 45% 10% 45%;
    width: 100%;
    justify-items: flex-start;
}
.a5_invoice_gst_footer_new > div:nth-child(1),
.a5_invoice_gst_footer_new > div:nth-child(2) {
    font-weight: 900;
}
.a5_invoice_gst_footer_new > div:nth-child(3) {
    text-align: right;
    width: 100%;
}

/* .a5_invoice_product_wrapper > table > thead > tr {
    font-size: 12px;
} */

.a5_invoice_signature {
    border-right: 1px solid #000;
}

@media print {
    .A5_invoice {
        width: 100%;
    }

    .a5_invoie_header_wraper {
        width: 100%;
    }
}
</style>
