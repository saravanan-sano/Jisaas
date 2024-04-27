<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ public_path('css/pos_invoice_css.css') }}">
    <title>{{ $order->invoice_number }}</title>

    <style>
        @page {
            size: A4;
            margin: 20px;
        }



            .A4_invoice {
                width: 100%;
            }

            body {
                margin: 20px;
                position: relative;
                width: 100%;
                height: auto;
                margin: 0 auto;
                color: #555555;
                background: #FFFFFF;
                font-size: 12px;
                font-family: DejaVu Sans;
            }

            .A4_invoice p {
                margin: 0;
            }

            .A4_invoice {
                text-align: center;
                color: #000;
                font-family: Arial, Helvetica, sans-serif;
            }


            .a4_invoie_header_logo>img {
                display: block;
                max-width: 160px;
                width: 100%;
            }

            .a4_invoie_header_logo {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .a4_invoie_header_title {
                font-size: 14px;
                font-weight: 900;
            }

            .a4_invoie_gstin_title {
                font-size: 14px;
                font-weight: 900;
            }

            .a4_invoie_contact_info {
                display: flex;
                justify-content: center;
                gap: 10px;
                font-size: 14px;
                font-weight: 900;
            }

            .A4_invoice {
                border: 1px solid #000;
            }

            .a4_invoie_header {
                padding: 10px;
                border-bottom: 1px solid #000;
            }

            .a4_invoice_order_details_wrapper {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .a4_invoice_customer_address {
                display: grid;
                grid-template-columns: 25% 2% 68%;
                text-align: left;
                margin-bottom: 10px;
                /* height: 20%; */
            }

            .a4_invoice_customer {
                display: grid;
                grid-template-columns: 38% 2% 60%;
                text-align: left;
            }

            .a4_invoice_customer_address {
                grid-template-columns: 38% 2% 60%;
                /* min-height: 45%;
                max-height: 38%; */
            }

            .a4_invoice_customer_details {
                padding: 10px;
                border-right: 1px solid #000;
                border-bottom: 1px solid #000;
            }

            .a4_invoice_order_details {
                padding: 10px;
                border-bottom: 1px solid #000;
            }

            .a4_invoice_customer_address>div:nth-child(1),
            .a4_invoice_customer>div:nth-child(1),
            .a4_invoice_customer_address>div:nth-child(2),
            .a4_invoice_customer>div:nth-child(2) {
                font-weight: 900;
            }

            .a4_invoice_customer_address,
            .a4_invoice_customer {
                font-size: 12px;
            }

            .a4_invoie_gst_total {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                justify-content: flex-start;
                padding: 10px;
                border-bottom: 1px solid #000;
            }

            .a4_invoie_gst_total_1 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
                border-bottom: 1px solid #000;
            }

            .a4_invoice_gst_footer {
                display: grid;
                grid-template-columns: 60% 10% 30%;
                font-size: 12px;
                width: 30%;
            }

            .a4_invoice_gst_footer>div:nth-child(1),
            .a4_invoice_gst_footer>div:nth-child(2) {
                font-weight: 900;
            }

            .a4_invoice_gst_footer>div:nth-child(3) {
                text-align: right;
            }

            .a4_invoie_footer_calculation {
                display: flex;
                flex-direction: column;
                align-items: baseline;
                font-size: 12px;
                padding: 10px;
                border-bottom: 1px solid #000;
            }

            .a4_invoie_footer_calculation>p {
                font-weight: 900;
            }

            .footer_tax {
                display: flex;
                justify-content: flex-start;
                gap: 10px;
            }

            .a4_invoie_bank_details {
                padding: 10px;
                border-bottom: 1px solid #000;
                font-weight: 900;
                text-align: left;
            }

            .a4_invoie_footer_last {
                display: grid;
                grid-template-columns: 1fr 1fr;
                font-size: 12px;
                text-align: left;
            }

            .a4_invoice_terms {
                padding: 10px;
                font-size: 8px;
                border-right: 1px solid #000;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
            }

            .a4_invoice_terms>p:nth-child(1) {
                text-decoration: underline;
            }

            .a4_receiver_signature {
                padding: 10px;
                font-weight: 900;
                text-align: left;
                border-bottom: 1px solid #000;
            }

            .a4_auth_signature {
                padding: 10px;
                font-weight: 900;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
            }

            .a4_auth_signature>p:nth-child(1) {
                margin-bottom: 40px !important;
            }

            .a4_invoie_body_wraper {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px !important;
            }

            .a4_invoie_body_wraper>tbody>td:nth-child(2) {
                text-align: left;
            }

            .a4_invoie_body_wraper>tbody>td:nth-child(6),
            .a4_invoie_body_wraper>tbody>td:nth-child(7),
            .a4_invoie_body_wraper>tbody>td:nth-child(8) {
                text-align: right;
            }

            .invoice_product_wrapper {
                position: relative;
                min-height: 60mm;
            }

            .a4_invoie_footer_wraper {
                border-top: 1px solid #000;
            }

            .seprator {
                width: 100%;
                padding: 10px;
                border-top: 1px solid #000;
            }
    </style>
</head>

<body>
    <div id="sales_a4_invoice" class="A4_invoice">
        <header class="a4_invoie_header_wraper">
            <div class="a4_invoie_header">
                <div class="a4_invoie_header_logo" >
                    <img class="invoice-logo" src="{{ $order->warehouse->logo_url }}" />
                </div>
                <div class="a4_invoie_header_title">
                    <p>{{ $order->warehouse->name }}</p>
                </div>
                <div class="a4_invoie_gstin_title" v-if="selectedWarehouse.gst_in_no != null">
                    <p>
                        <b>GSTIN:</b>{{ $order->warehouse->gst_in_no }}
                    </p>
                </div>
                <div class="a4_invoie_contact_info">
                    <p><b>Tel: </b>{{ $order->warehouse->phone }}</p>
                    <p><b>Email: </b>{{ $order->warehouse->email }}</p>
                </div>
                <div class="a4_invoie_contact_info">
                    <p><b>Address: </b>{{ $order->warehouse->address }}</p>
                </div>
            </div>
            <div class="a4_invoice_order_details_wrapper">
                <div class="a4_invoice_customer_details">
                    <div class="a4_invoice_customer_address">
                        <div>Party Details</div>
                        <div>:</div>

                    </div>
                    <div class="text">
                            {{ $order->user->name }}
                            <br />
                            {{ $order->user->shipping_address }}
                        </div>
                    <div class="a4_invoice_customer">
                        <div>Party E-mail ID</div>
                        <div>:</div>
                        <div>
                            {{ $order->user->email }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Party Mobile No</div>
                        <div>:</div>
                        <div>
                            {{ $order->user->phone }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>GSTIN/UIN</div>
                        <div>:</div>
                        <div>
                            {{ $order->user->tax_number }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Delivered To</div>
                        <div>:</div>
                        <div>
                            {{ $order->user->delivery_to }}
                        </div>
                    </div>
                </div>
                <div class="a4_invoice_order_details">
                    <div class="a4_invoice_customer">
                        <div>{{ $traslations['invoice'] }}</div>
                        <div>:</div>
                        <div>{{ $order->invoice_number }}</div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Dated</div>
                        <div>:</div>
                        <div>
                            {{ $order->order_date->format($dateTimeFormat) }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Place of Supply</div>
                        <div>:</div>
                        <div>
                            {{ $order->place_of_supply }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Reverse Charge</div>
                        <div>:</div>
                        <div>
                            {{ $order->reverse_charge }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>GR/RR No</div>
                        <div>:</div>
                        <div>
                            {{ $order->gr_rr_no }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Transport</div>
                        <div>:</div>
                        <div>
                            {{ $order->transport }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Vehicle No</div>
                        <div>:</div>
                        <div>
                            {{ $order->vechile_no }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>Station</div>
                        <div>:</div>
                        <div>
                            {{ $order->station }}
                        </div>
                    </div>
                    <div class="a4_invoice_customer">
                        <div>BUYER ORDER NO</div>
                        <div>:</div>
                        <div>
                            {{ $order->buyer_order_no }}
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="invoice_product_wrapper">
            <table class="a4_invoice_body_wrapper" style="border-collapse: collapse; width: 100%">
                <thead style="background: #eee; border-top: 1px solid #000">
                    <tr>
                        <th
                            style="
                                    width: 2%;
                                    border-bottom: 1px solid #000;
                                    border-left: none;
                                ">
                            S.No
                        </th>
                        <th
                            style="
                                    width: 38%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            Description of Goods
                        </th>
                        <th
                            style="
                                    width: 11%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            HSN/SAC
                        </th>
                        <th
                            style="
                                    width: 5%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            GST%
                        </th>
                        <th
                            style="
                                    width: 5%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            Qty
                        </th>
                        <th
                            style="
                                    width: 13%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            Unit
                        </th>
                        <th
                            style="
                                    width: 13%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            Rate
                        </th>
                        <th
                            style="
                                    width: 13%;
                                    border: 1px solid #000;
                                    border-top: none;
                                ">
                            Discount
                        </th>
                        <th
                            style="
                                    width: 13%;
                                    border: 1px solid #000;
                                    border-right: none;
                                    border-top: none;
                                ">
                            Amount
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td style="border: 1px solid #000">
                                {{ $loop->iteration }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->product->name }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->hsn_sac_code }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->tax_rate }}
                            </td>
                            <td
                                style="
                                    border: 1px solid #000;
                                    border-right: none;
                                ">
                                {{ $item->quantity . ' ' . $item->unit->short_name }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->unit_short_name }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->unit_price }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->total_discount . '(' . $item->discount_rate . ')' }}
                            </td>
                            <td style="border: 1px solid #000">
                                {{ $item->subtotal }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="seprator"></div>
        {{-- <footer class="a4_invoie_footer_wraper">
            <div class="a4_invoie_gst_total">
                <div class="a4_invoice_gst_footer">
                    <div>SUB TOTAL</div>
                    <div>:</div>
                    <div>
                        {{ formatAmountCurrency(totalAmountSum) }}
                    </div>
                </div>
                <div class="a4_invoice_gst_footer" v-if="checkStartingDigits">
                    <div v-if="totalTax != 0">ADD : CGST</div>
                    <div v-if="totalTax != 0">:</div>
                    <div v-if="totalTax != 0">
                        {{ formatAmountCurrency(totalTax / 2) }}
                    </div>
                </div>
                <div class="a4_invoice_gst_footer" v-if="checkStartingDigits">
                    <div v-if="totalTax != 0">ADD : SGST</div>
                    <div v-if="totalTax != 0">:</div>
                    <div v-if="totalTax != 0">
                        {{ formatAmountCurrency(totalTax / 2) }}
                    </div>
                </div>
                <div class="a4_invoice_gst_footer" v-if="!checkStartingDigits">
                    <div v-if="totalTax != 0">ADD : IGST</div>
                    <div v-if="totalTax != 0">:</div>
                    <div v-if="totalTax != 0">
                        {{ formatAmountCurrency(totalTax) }}
                    </div>
                </div>
                <div class="a4_invoice_gst_footer">
                    <div>SHIPPING</div>
                    <div>:</div>
                    <div>
                        {{ formatAmountCurrency(order . order . shipping) }}
                    </div>
                </div>
                <div class="a4_invoice_gst_footer">
                    <div>DISCOUNT</div>
                    <div>:</div>
                    <div>
                        {{ formatAmountCurrency(order . order . discount) }}
                    </div>
                </div>
            </div>
            <div class="a4_invoie_gst_total_1">
                <p>In Words : {{ numberToWords(order . order . total) }}</p>
                <div class="a4_invoice_gst_footer">
                    <div>Total</div>
                    <div>:</div>
                    <div>
                        {{ formatAmountCurrency(totalAmountSum + totalTax + order . order . shipping - order . order . discount) }}
                    </div>
                </div>
            </div>
            <div class="a4_invoie_footer_calculation">
                <div class="footer_calculation" v-if="checkStartingDigits">
                    <div class="footer_tax" v-for="(tax, key) in taxDifProducts" :key="key">
                        <p>
                            Sales @{{ key }}% =
                            {{ formatAmountCurrency(tax . totalAmountSum) }}
                        </p>
                        <p v-if="tax.taxAmountSum != 0">
                            CGST =
                            {{ formatAmountCurrency(tax . taxAmountSum / 2) }}
                        </p>
                        <p v-if="tax.taxAmountSum != 0">
                            SGST =
                            {{ formatAmountCurrency(tax . taxAmountSum / 2) }}
                        </p>
                        <p v-if="tax.taxAmountSum != 0">
                            Total GST =
                            {{ formatAmountCurrency(tax . taxAmountSum) }}
                        </p>
                        <p>
                            Total Sale =
                            {{ formatAmountCurrency(tax . totalAmountSum + tax . taxAmountSum) }}
                        </p>
                    </div>
                </div>
                <div class="footer_calculation" v-else>
                    <div class="footer_tax" v-for="(tax, key) in taxDifProducts" :key="key">
                        <p>
                            Sales @{{ key }}% =
                            {{ formatAmountCurrency(tax . totalAmountSum) }}
                        </p>
                        <p v-if="tax.taxAmountSum != 0">
                            IGST =
                            {{ formatAmountCurrency(tax . taxAmountSum) }}
                        </p>
                        <p>
                            Total Sale =
                            {{ formatAmountCurrency(tax . totalAmountSum + tax . taxAmountSum) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="a4_invoie_bank_details">
                <p>
                    Bank Details :
                    {{ selectedWarehouse && selectedWarehouse . bank_details ? selectedWarehouse . bank_details : '-' }}
                </p>
            </div>
            <div class="a4_invoie_footer_last">
                <div class="a4_invoice_terms">
                    <p>Terms & Conditions</p>
                    <p>E & O.E</p>
                    <p v-for="(
                                item, index
                            ) in order.order.terms_condition.split('\n')"
                        :key="index">
                        {{ item }}
                    </p>
                </div>
                <div class="a4_invoice_signature">
                    <div class="a4_receiver_signature">
                        <p>Receiver's Signature:</p>
                    </div>
                    <div class="a4_auth_signature">
                        <p>for {{ selectedWarehouse . name }}</p>
                        <p>Authorized Signature</p>
                    </div>
                </div>
            </div>
        </footer> --}}
    </div>
</body>

</html>
