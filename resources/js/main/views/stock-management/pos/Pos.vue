<template>
    <a-card
        class="page-content-sub-header breadcrumb-left-border"
        :bodyStyle="{ padding: '0px', margin: '0px 16px 0' }"
    >
        <a-row>
            <a-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2">
                <a-page-header title="POS" @back="() => back()" class="p-0" />
            </a-col>

            <a-col :xs="22" :sm="22" :md="22" :lg="22" :xl="22">
                <div class="pos-left-header">
                    {{ getCreditBalance }}
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="18" :lg="18" :xl="18">
                            <div style="display: flex">
                                <a-select
                                    v-if="!searchBarcodeInput"
                                    v-model:value="orderSearchTerm"
                                    :autofocus="true"
                                    show-search
                                    :filter-option="false"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('product.product'),
                                        ])
                                    "
                                    style="width: 100%"
                                    :not-found-content="
                                        productFetching ? undefined : null
                                    "
                                    @search="fetchProducts"
                                    option-label-prop="label"
                                    @focus="products = []"
                                    @select="searchValueSelected"
                                    id="productFocus"
                                >
                                    <!-- <template #suffixIcon>

                                            <SearchOutlined />
</template>-->
                                    <template
                                        v-if="productFetching"
                                        #notFoundContent
                                    >
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
                                <a-input
                                    v-else
                                    v-model:value="barcodeSearchTerm"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.barcode'),
                                        ])
                                    "
                                    :autofocus="barcodeSearchTerm != ''"
                                    @keyup.enter="barcodeFetch"
                                    id="bcinputFocus"
                                    ref="bcinputFocus"
                                />
                                <a-button
                                    v-if="searchBarcodeInput"
                                    @click="() => (searchBarcodeInput = false)"
                                    class="ml-5"
                                >
                                    <template #icon>
                                        <BarcodeOutlined />
                                    </template>
                                    <slot></slot>
                                </a-button>
                                <a-button
                                    v-else
                                    @click="() => (searchBarcodeInput = true)"
                                    class="ml-5"
                                    id="barcodeFocus"
                                >
                                    <template #icon>
                                        <SearchOutlined />
                                    </template>
                                    <slot></slot>
                                </a-button>
                                <a-button
                                    v-if="searchBarcodeInput"
                                    @click="showBarcodereader"
                                    class="ml-5"
                                    id="barcodeOpenButton"
                                >
                                    <CameraOutlined />
                                    <slot></slot>
                                </a-button>

                                <!-- <CustomerAddButton
                                                    @onAddSuccess="customerAdded"
                                                /> -->

                                <!-- <div v-if="postLayout != 1"> -->
                                <ProductAddButton
                                    size="medium"
                                    v-if="innerWidth >= 768 && postLayout != 1"
                                    @onAddSuccess="ProductAdded"
                                />
                                <StockAdd
                                    size="small"
                                    v-if="innerWidth >= 768 && postLayout != 1"
                                    >{{ $t("stock.stock") }}
                                </StockAdd>
                                <!-- </div> -->
                                <ProductAddButton
                                    size="medium"
                                    v-if="innerWidth <= 768"
                                    @onAddSuccess="ProductAdded"
                                />
                                <StockAdd
                                    v-if="innerWidth <= 768"
                                    size="small"
                                ></StockAdd>
                            </div>
                        </a-col>
                        <a-col
                            :xs="24"
                            :sm="24"
                            :md="6"
                            :lg="6"
                            :xl="6"
                            class="mobile"
                        >
                            <span style="display: flex">
                                <a-select
                                    v-model:value="formData.user_id"
                                    :placeholder="$t('user.walk_in_customer')"
                                    style="width: 100%"
                                    optionFilterProp="title"
                                    show-search
                                    id="customerFocus"
                                    @change="customerChanged"
                                >
                                    <a-select-option
                                        v-for="customer in customers"
                                        :key="customer.xid"
                                        :title="customer.name + customer.phone"
                                        :value="customer.xid"
                                    >
                                        {{ customer.name }}
                                        <span
                                            v-if="
                                                customer.phone &&
                                                customer.phone != ''
                                            "
                                        >
                                            <br />
                                            {{ customer.phone }}
                                        </span>
                                    </a-select-option>
                                </a-select>
                                <CustomerAddButton
                                    @onAddSuccess="customerAdded"
                                />
                            </span>
                        </a-col>
                    </a-row>
                </div>
            </a-col>
        </a-row>
        <a-row :gutter="16">
            <a-modal
                :visible="barcodeReader"
                :closable="false"
                :centered="true"
            >
                <StreamBarcodeReader
                    @decode="(value) => onDecode(value)"
                    @loaded="() => onLoaded()"
                ></StreamBarcodeReader>
                <template #footer>
                    <a-button key="back" @click="showBarcodereader">
                        Close</a-button
                    >
                </template>
            </a-modal>
        </a-row>
    </a-card>

    <SubscriptionModuleVisibilityMessage moduleName="order" />
    <SubscriptionModuleVisibility moduleName="order">
        <a-form layout="vertical">
            <a-row
                v-if="innerWidth >= 768"
                :gutter="[8, 8]"
                class="mt-5"
                style="margin: 10px 16px 0"
            >
                <!-- *For Layout 1 with Full Screen -->
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="21"
                    :lg="21"
                    :xl="21"
                    v-if="postLayout == 1"
                >
                    <div class="pos-left-wrapper">
                        <div class="pos-left-content">
                            <a-card
                                class="left-pos-middle-table"
                                :style="{ marginBottom: '10px' }"
                            >
                                <div class="bill-body">
                                    <div class="bill-table">
                                        <a-row class="mt-20 mb-30">
                                            <a-col
                                                :xs="24"
                                                :sm="24"
                                                :md="24"
                                                :lg="24"
                                            >
                                                <a-table
                                                    :row-key="
                                                        (record) => record.xid
                                                    "
                                                    :dataSource="
                                                        selectedProducts
                                                    "
                                                    :columns="orderItemColumns1"
                                                    :pagination="false"
                                                    size="small"
                                                    class="left-pos-middle-table"
                                                    v-if="cardLoader == false"
                                                >
                                                    <template
                                                        #bodyCell="{
                                                            column,
                                                            record,
                                                        }"
                                                    >
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'name'
                                                            "
                                                        >
                                                            {{ record.name }}

                                                            <small>
                                                                <a-typography-text
                                                                    code
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "product.avl_qty"
                                                                        )
                                                                    }}

                                                                    {{
                                                                        `${record.stock_quantity}${record.unit_short_name}`
                                                                    }}
                                                                </a-typography-text>
                                                            </small>
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'unit_quantity'
                                                            "
                                                        >
                                                            <a-input-number
                                                                id="inputNumber"
                                                                v-model:value="
                                                                    record.quantity
                                                                "
                                                                :min="0"
                                                                size="small"
                                                                @change="
                                                                    quantityChanged(
                                                                        record
                                                                    )
                                                                "
                                                            />
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'unit_price'
                                                            "
                                                        >
                                                            <div
                                                                style="
                                                                    display: flex;
                                                                    align-items: center;
                                                                "
                                                            >
                                                                <a-input-number
                                                                    id="inputsubtotal"
                                                                    v-model:value="
                                                                        record.unit_price
                                                                    "
                                                                    :min="0"
                                                                    size="small"
                                                                    :disabled="
                                                                        !(
                                                                            permsArray.includes(
                                                                                'price_edit'
                                                                            ) ||
                                                                            permsArray.includes(
                                                                                'admin'
                                                                            )
                                                                        )
                                                                    "
                                                                    @change="
                                                                        onEditPrice(
                                                                            record
                                                                        )
                                                                    "
                                                                />
                                                                <div
                                                                    v-if="
                                                                        selectedWarehouse.is_last_history ==
                                                                        1
                                                                    "
                                                                >
                                                                    <a-tooltip
                                                                        v-if="
                                                                            record.lastPrice <
                                                                                record.unit_price &&
                                                                            record.lastPrice !=
                                                                                'NaN'
                                                                        "
                                                                        :title="
                                                                            $t(
                                                                                `Last Time Price : ${formatAmountCurrency(
                                                                                    Math.abs(
                                                                                        record.lastPrice
                                                                                    )
                                                                                )}`
                                                                            )
                                                                        "
                                                                    >
                                                                        <a-typography-text>
                                                                            <ArrowUpOutlined
                                                                                :style="{
                                                                                    color: 'green',
                                                                                }"
                                                                            />
                                                                            <!-- Last Price : {{ formatAmountCurrency(Math.abs(record.lastPrice)) }} -->
                                                                        </a-typography-text>
                                                                    </a-tooltip>
                                                                    <a-tooltip
                                                                        v-if="
                                                                            record.lastPrice >
                                                                                record.unit_price &&
                                                                            record.lastPrice !=
                                                                                'NaN'
                                                                        "
                                                                        :title="
                                                                            $t(
                                                                                `Last Time Price : ${formatAmountCurrency(
                                                                                    Math.abs(
                                                                                        record.lastPrice
                                                                                    )
                                                                                )}`
                                                                            )
                                                                        "
                                                                    >
                                                                        <a-typography-text>
                                                                            <span
                                                                                v-if="
                                                                                    record.lastPrice >
                                                                                    record.unit_price
                                                                                "
                                                                            >
                                                                                <ArrowDownOutlined
                                                                                    :style="{
                                                                                        color: 'red',
                                                                                    }"
                                                                                />
                                                                            </span>
                                                                            <!-- Last Price : {{ formatAmountCurrency(record.lastPrice) }} -->
                                                                        </a-typography-text>
                                                                    </a-tooltip>
                                                                    <a-tooltip
                                                                        v-if="
                                                                            record.Last_selling <
                                                                                record.unit_price &&
                                                                            record.Last_selling !=
                                                                                'NaN' &&
                                                                            record.Last_selling !=
                                                                                0
                                                                        "
                                                                        :title="
                                                                            $t(
                                                                                `Last Billed Price : ${formatAmountCurrency(
                                                                                    Math.abs(
                                                                                        record.Last_selling
                                                                                    )
                                                                                )}`
                                                                            )
                                                                        "
                                                                    >
                                                                        <a-typography-text>
                                                                            <ArrowUpOutlined
                                                                                :style="{
                                                                                    color: 'green',
                                                                                }"
                                                                            />
                                                                            <!-- Last Sale : {{ formatAmountCurrency(Math.abs(record.Last_selling)) }} -->
                                                                        </a-typography-text>
                                                                    </a-tooltip>
                                                                    <a-tooltip
                                                                        v-if="
                                                                            record.Last_selling >
                                                                                record.unit_price &&
                                                                            record.Last_selling !=
                                                                                'NaN' &&
                                                                            record.Last_selling !=
                                                                                0
                                                                        "
                                                                        :title="
                                                                            $t(
                                                                                `Last Billed Price : ${formatAmountCurrency(
                                                                                    Math.abs(
                                                                                        record.Last_selling
                                                                                    )
                                                                                )}`
                                                                            )
                                                                        "
                                                                    >
                                                                        <a-typography-text>
                                                                            <span
                                                                                v-if="
                                                                                    record.Last_selling >
                                                                                    record.unit_price
                                                                                "
                                                                            >
                                                                                <ArrowDownOutlined
                                                                                    :style="{
                                                                                        color: 'red',
                                                                                    }"
                                                                                />
                                                                            </span>
                                                                            <!-- Last Sale : {{ formatAmountCurrency(record.Last_selling) }} -->
                                                                        </a-typography-text>
                                                                    </a-tooltip>
                                                                    <a-tooltip
                                                                        v-if="
                                                                            record.Last_selling !=
                                                                                'NaN' &&
                                                                            record.Last_selling !=
                                                                                0
                                                                        "
                                                                        :title="
                                                                            $t(
                                                                                `Last Billed Price : ${formatAmountCurrency(
                                                                                    Math.abs(
                                                                                        record.Last_selling
                                                                                    )
                                                                                )}`
                                                                            )
                                                                        "
                                                                    >
                                                                        <a-typography-text>
                                                                            <ArrowDownOutlined
                                                                                :style="{
                                                                                    color: 'red',
                                                                                }"
                                                                            />

                                                                            <!-- Last Sale : {{ formatAmountCurrency(record.Last_selling) }} -->
                                                                        </a-typography-text>
                                                                    </a-tooltip>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'discount_rate'
                                                            "
                                                        >
                                                            <a-input-group
                                                                compact
                                                                style="
                                                                    width: 120px;
                                                                "
                                                            >
                                                                <a-select
                                                                    v-model:value="
                                                                        record.discount_type
                                                                    "
                                                                    style="
                                                                        width: 30%;
                                                                    "
                                                                    @change="
                                                                        (
                                                                            value
                                                                        ) => {
                                                                            record.discount_type =
                                                                                value;
                                                                            record.discount = 0;
                                                                            record.discount_rate = 0;
                                                                            record.total_discount = 0;
                                                                            onEditPrice(
                                                                                record
                                                                            );
                                                                        }
                                                                    "
                                                                >
                                                                    <a-select-option
                                                                        value="percentage"
                                                                    >
                                                                        %
                                                                    </a-select-option>
                                                                    <a-select-option
                                                                        value="fixed"
                                                                    >
                                                                        {{
                                                                            appSetting
                                                                                .currency
                                                                                .symbol
                                                                        }}
                                                                    </a-select-option>
                                                                </a-select>
                                                                <a-input-number
                                                                    v-model:value="
                                                                        record.discount
                                                                    "
                                                                    :placeholder="
                                                                        $t(
                                                                            'common.placeholder_default_text',
                                                                            [
                                                                                $t(
                                                                                    'stock.discount'
                                                                                ),
                                                                            ]
                                                                        )
                                                                    "
                                                                    @change="
                                                                        onEditPrice(
                                                                            record
                                                                        )
                                                                    "
                                                                    :min="0"
                                                                    style="
                                                                        width: 70%;
                                                                    "
                                                                />
                                                            </a-input-group>
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'subtotal'
                                                            "
                                                        >
                                                            <div
                                                                style="
                                                                    text-align: right;
                                                                "
                                                            >
                                                                {{
                                                                    formatAmountCurrency(
                                                                        record.subtotal
                                                                    )
                                                                }}
                                                            </div>

                                                            <!-- <a-input-number
                                                                id="inputsubtotal"
                                                                v-model:value="
                                                                    record.subtotal
                                                                "
                                                                :min="0"
                                                                size="small"
                                                                @change="
                                                                    onEditPrice(
                                                                        record
                                                                    )
                                                                "
                                                            /> -->
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'action'
                                                            "
                                                        >
                                                            <a-button
                                                                type="primary"
                                                                @click="
                                                                    editItem(
                                                                        record
                                                                    )
                                                                "
                                                                style="
                                                                    margin-left: 4px;
                                                                    margin-top: 4px;
                                                                "
                                                                size="medium"
                                                            >
                                                                <template #icon>
                                                                    <EditOutlined />
                                                                </template>
                                                            </a-button>
                                                            <a-button
                                                                type="primary"
                                                                @click="
                                                                    showDeleteConfirm(
                                                                        record
                                                                    )
                                                                "
                                                                size="medium"
                                                                style="
                                                                    margin-left: 4px;
                                                                    margin-top: 4px;
                                                                "
                                                            >
                                                                <template #icon>
                                                                    <DeleteOutlined />
                                                                </template>
                                                            </a-button>
                                                        </template>
                                                    </template>
                                                </a-table>
                                                <div
                                                    class="payment_loader"
                                                    v-else
                                                >
                                                    <div class="loader_img">
                                                        <img
                                                            :src="`${location}/images/payment_cancel.gif`"
                                                            alt=""
                                                            v-if="
                                                                cardPaymentStatus ==
                                                                'CANCELED'
                                                            "
                                                        />
                                                        <img
                                                            :src="`${location}/images/payment_completed.gif`"
                                                            alt=""
                                                            v-if="
                                                                cardPaymentStatus ==
                                                                'COMPLETED'
                                                            "
                                                        />
                                                        <img
                                                            :src="`${location}/images/payment_progress.gif`"
                                                            alt=""
                                                            v-if="
                                                                cardPaymentStatus ==
                                                                'PENDING'
                                                            "
                                                        />
                                                        <a-button
                                                            type="primary"
                                                            danger
                                                            @click="
                                                                cancelCardPayment
                                                            "
                                                            v-if="
                                                                selectedWarehouse.is_card_gateway ==
                                                                1
                                                            "
                                                        >
                                                            Cancel
                                                        </a-button>
                                                    </div>
                                                </div>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </a-card>
                        </div>
                    </div>
                </a-col>
                <!-- <a-col
                    :xs="24"
                    :sm="24"
                    :md="20"
                    :lg="2"
                    :xl="2"
                    v-if="postLayout == 1"
                    class="rightButt"
                >
                    <a-button
                        id="submitButton"
                        type="primary"
                        size="big"
                        @click="completeOrder"
                        :disabled="
                            formData.subtotal <= 0 ||
                            formData.user_id == undefined ||
                            formData.user_id == '' ||
                            !formData.user_id
                        "
                        :loading="quickPayLoader"
                        :style="{ marginBottom: '10px' }"
                    >
                        F2 {{ $t("stock.quick_pay_now") }}
                    </a-button>
                    <a-button
                        type="primary"
                        size="big"
                        @click="payNow"
                        :disabled="
                            formData.subtotal <= 0 ||
                            formData.user_id == undefined ||
                            formData.user_id == '' ||
                            !formData.user_id
                        "
                        :style="{ marginBottom: '10px' }"
                    >
                        {{ $t("stock.pay_now") }}
                    </a-button>
                    <a-button size="big" :style="{ marginBottom: '10px' }">
                        <div>F4 Product</div>
                    </a-button>
                    <a-button size="big" :style="{ marginBottom: '10px' }">
                        <div>F8 Customer</div>
                    </a-button>

                    <a-button size="big" :style="{ marginBottom: '10px' }"
                        >F9 Barcode
                    </a-button>

                    <ProductAddButton
                        size="medium"
                        v-if="innerWidth >= 768"
                        style="width: 100%"
                        >{{ $t("product.product") }}
                    </ProductAddButton>
                    <StockAdd
                        size="medium"
                        v-if="innerWidth >= 768"
                        style="
                            margin-top: 10px;
                            width: 100%;
                            margin-bottom: 10px;
                        "
                        >{{ $t("stock.stock") }}</StockAdd
                    >
                    <a-button size="big" @click="resetPos">
                        {{ $t("stock.reset") }}
                    </a-button>
                </a-col> -->
                <!-- *For Layout 2 with Brand and Category Filter -->
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="11"
                    :xl="11"
                    v-if="postLayout == 2"
                >
                    <div class="pos-left-wrapper">
                        <div class="pos-left-content">
                            <a-card
                                class="left-pos-middle-table"
                                :style="{ marginBottom: '10px' }"
                            >
                                <div class="bill-body">
                                    <div class="bill-table">
                                        <a-row class="mt-20 mb-30">
                                            <a-col
                                                :xs="24"
                                                :sm="24"
                                                :md="24"
                                                :lg="24"
                                            >
                                                <a-table
                                                    :row-key="
                                                        (record) => record.xid
                                                    "
                                                    :dataSource="
                                                        selectedProducts
                                                    "
                                                    :columns="orderItemColumns"
                                                    :pagination="false"
                                                    v-if="cardLoader == false"
                                                >
                                                    <template
                                                        #bodyCell="{
                                                            column,
                                                            record,
                                                        }"
                                                    >
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'name'
                                                            "
                                                        >
                                                            {{ record.name }}

                                                            <br />

                                                            <small>
                                                                <a-typography-text
                                                                    code
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "product.avl_qty"
                                                                        )
                                                                    }}

                                                                    {{
                                                                        `${record.stock_quantity}${record.unit_short_name}`
                                                                    }}
                                                                </a-typography-text>
                                                            </small>
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'unit_quantity'
                                                            "
                                                        >
                                                            <a-input-number
                                                                id="inputNumber"
                                                                v-model:value="
                                                                    record.quantity
                                                                "
                                                                :min="0"
                                                                @change="
                                                                    quantityChanged(
                                                                        record
                                                                    )
                                                                "
                                                            />
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'subtotal'
                                                            "
                                                        >
                                                            {{
                                                                formatAmountCurrency(
                                                                    record.subtotal
                                                                )
                                                            }}
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'action'
                                                            "
                                                        >
                                                            <a-button
                                                                type="primary"
                                                                @click="
                                                                    editItem(
                                                                        record
                                                                    )
                                                                "
                                                                style="
                                                                    margin-left: 4px;

                                                                    margin-top: 4px;
                                                                "
                                                            >
                                                                <template #icon>
                                                                    <EditOutlined />
                                                                </template>
                                                            </a-button>
                                                            <a-button
                                                                type="primary"
                                                                @click="
                                                                    showDeleteConfirm(
                                                                        record
                                                                    )
                                                                "
                                                                style="
                                                                    margin-left: 4px;
                                                                    margin-top: 4px;
                                                                "
                                                            >
                                                                <template #icon>
                                                                    <DeleteOutlined />
                                                                </template>
                                                            </a-button>
                                                        </template>
                                                    </template>
                                                </a-table>
                                                <div
                                                    class="payment_loader"
                                                    v-else
                                                >
                                                    <div class="loader_img">
                                                        <img
                                                            :src="`${location}/images/payment_cancel.gif`"
                                                            alt=""
                                                            v-if="
                                                                cardPaymentStatus ==
                                                                'CANCELED'
                                                            "
                                                        />
                                                        <img
                                                            :src="`${location}/images/payment_completed.gif`"
                                                            alt=""
                                                            v-if="
                                                                cardPaymentStatus ==
                                                                'COMPLETED'
                                                            "
                                                        />
                                                        <img
                                                            :src="`${location}/images/payment_progress.gif`"
                                                            alt=""
                                                            v-if="
                                                                cardPaymentStatus ==
                                                                'PENDING'
                                                            "
                                                        />
                                                        <a-button
                                                            type="primary"
                                                            danger
                                                            @click="
                                                                cancelCardPayment
                                                            "
                                                            v-if="
                                                                selectedWarehouse.is_card_gateway ==
                                                                1
                                                            "
                                                        >
                                                            Cancel
                                                        </a-button>
                                                    </div>
                                                </div>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </a-card>
                        </div>
                    </div>
                </a-col>
                <!-- * FOR Layout 3 -->

                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="11"
                    :xl="11"
                    v-if="postLayout == 3"
                >
                    <a-table
                        :row-key="(record) => record.xid"
                        :dataSource="selectedProducts"
                        :columns="orderItemColumns"
                        :pagination="false"
                        size="small"
                        class="left-pos-middle-table"
                        v-if="cardLoader == false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'name'">
                                {{ record.name }}

                                <small>
                                    <a-typography-text code>
                                        {{ $t("product.avl_qty") }}

                                        {{
                                            `${record.stock_quantity}${record.unit_short_name}`
                                        }}
                                    </a-typography-text>
                                </small>
                            </template>
                            <template
                                v-if="column.dataIndex === 'unit_quantity'"
                            >
                                <a-input-number
                                    id="inputNumber"
                                    v-model:value="record.quantity"
                                    :min="1"
                                    size="small"
                                    @change="quantityChanged(record)"
                                />
                            </template>

                            <template v-if="column.dataIndex === 'subtotal'">
                                <div style="text-align: right">
                                    {{ formatAmountCurrency(record.subtotal) }}
                                </div>

                                <!-- <a-input-number

                                        id="inputsubtotal"

                                        v-model:value="record.subtotal"

                                        :min="0"

                                        size="small"

                                        @change="onEditPrice(record)"

                                    /> -->
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px; margin-top: 4px"
                                    size="medium"
                                >
                                    <template #icon>
                                        <EditOutlined />
                                    </template>
                                </a-button>
                                <a-button
                                    type="primary"
                                    @click="showDeleteConfirm(record)"
                                    size="medium"
                                    style="margin-left: 4px; margin-top: 4px"
                                >
                                    <template #icon>
                                        <DeleteOutlined />
                                    </template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                    <div class="payment_loader" v-else>
                        <div class="loader_img">
                            <img
                                :src="`${location}/images/payment_cancel.gif`"
                                alt=""
                                v-if="cardPaymentStatus == 'CANCELED'"
                            />
                            <img
                                :src="`${location}/images/payment_completed.gif`"
                                alt=""
                                v-if="cardPaymentStatus == 'COMPLETED'"
                            />
                            <img
                                :src="`${location}/images/payment_progress.gif`"
                                alt=""
                                v-if="cardPaymentStatus == 'PENDING'"
                            />
                            <a-button
                                type="primary"
                                danger
                                @click="cancelCardPayment"
                                v-if="selectedWarehouse.is_card_gateway == 1"
                            >
                                Cancel
                            </a-button>
                        </div>
                    </div>
                </a-col>

                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="12"
                    :xl="12"
                    v-if="postLayout == 4"
                >
                    <a-table
                        :row-key="(record) => record.xid"
                        :dataSource="selectedProducts"
                        :columns="orderItemColumns1"
                        :pagination="false"
                        size="small"
                        class="left-pos-middle-table"
                        v-if="cardLoader == false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'name'">
                                {{ record.name }}

                                <small>
                                    <a-typography-text code>
                                        {{ $t("product.avl_qty") }}

                                        {{
                                            `${record.stock_quantity}${record.unit_short_name}`
                                        }}
                                    </a-typography-text>
                                </small>
                            </template>
                            <template
                                v-if="column.dataIndex === 'unit_quantity'"
                            >
                                <a-input-number
                                    id="inputNumber"
                                    v-model:value="record.quantity"
                                    :min="1"
                                    size="small"
                                    @change="quantityChanged(record)"
                                />
                            </template>
                            <template v-if="column.dataIndex === 'unit_price'">
                                <a-input-number
                                    id="inputsubtotal"
                                    v-model:value="record.unit_price"
                                    :min="0"
                                    size="small"
                                    :disabled="
                                        !(
                                            permsArray.includes('price_edit') ||
                                            permsArray.includes('admin')
                                        )
                                    "
                                    @change="onEditPrice(record)"
                                />
                            </template>
                            <template
                                v-if="column.dataIndex === 'discount_rate'"
                            >
                                <a-input-group compact style="width: 120px">
                                    <a-select
                                        v-model:value="record.discount_type"
                                        style="width: 30%"
                                        @change="
                                            (value) => {
                                                record.discount_type = value;
                                                record.discount = 0;
                                                record.discount_rate = 0;
                                                record.total_discount = 0;
                                                onEditPrice(record);
                                            }
                                        "
                                    >
                                        <a-select-option value="percentage">
                                            %
                                        </a-select-option>
                                        <a-select-option value="fixed">
                                            {{ appSetting.currency.symbol }}
                                        </a-select-option>
                                    </a-select>
                                    <a-input-number
                                        v-model:value="record.discount"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('stock.discount')]
                                            )
                                        "
                                        @change="onEditPrice(record)"
                                        :min="0"
                                        style="width: 70%"
                                    />
                                </a-input-group>
                            </template>
                            <template v-if="column.dataIndex === 'subtotal'">
                                <div style="text-align: right">
                                    {{ formatAmountCurrency(record.subtotal) }}
                                </div>

                                <!-- <a-input-number

                                        id="inputsubtotal"

                                        v-model:value="record.subtotal"

                                        :min="0"

                                        size="small"

                                        @change="onEditPrice(record)"

                                    /> -->
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px; margin-top: 4px"
                                    size="medium"
                                >
                                    <template #icon>
                                        <EditOutlined />
                                    </template>
                                </a-button>
                                <a-button
                                    type="primary"
                                    @click="showDeleteConfirm(record)"
                                    size="medium"
                                    style="margin-left: 4px; margin-top: 4px"
                                >
                                    <template #icon>
                                        <DeleteOutlined />
                                    </template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                    <div class="payment_loader" v-else>
                        <div class="loader_img">
                            <img
                                :src="`${location}/images/payment_cancel.gif`"
                                alt=""
                                v-if="cardPaymentStatus == 'CANCELED'"
                            />
                            <img
                                :src="`${location}/images/payment_completed.gif`"
                                alt=""
                                v-if="cardPaymentStatus == 'COMPLETED'"
                            />
                            <img
                                :src="`${location}/images/payment_progress.gif`"
                                alt=""
                                v-if="cardPaymentStatus == 'PENDING'"
                            />
                            <a-button
                                type="primary"
                                danger
                                @click="cancelCardPayment"
                                v-if="selectedWarehouse.is_card_gateway == 1"
                            >
                                Cancel
                            </a-button>
                        </div>
                    </div>
                </a-col>

                <!-- *For Layout 2 and 3 -->
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="10"
                    :xl="10"
                    v-if="postLayout != 1"
                    class="pos-layout-2"
                >
                    <perfect-scrollbar
                        :options="{
                            wheelSpeed: 1,
                            swipeEasing: true,
                            suppressScrollX: true,
                        }"
                    >
                        <PosLayout1
                            v-if="postLayout == 1"
                            :brands="brands"
                            :categories="categories"
                            :formData="formData"
                            @changed="reFetchProducts"
                        />

                        <PosLayout2
                            v-if="postLayout == 2"
                            :brands="brands"
                            :categories="categories"
                            :formData="formData"
                            @changed="reFetchProducts"
                        />
                        <PosLayout3
                            v-if="postLayout == 3"
                            :brands="brands"
                            :categories="categories"
                            :formData="formData"
                            @changed="reFetchProducts"
                        />
                        <PosLayout3
                            v-if="postLayout == 4"
                            :brands="brands"
                            :categories="categories"
                            :formData="formData"
                            @changed="reFetchProducts"
                        />

                        <a-row
                            v-if="
                                (productLists.length > 0 && postLayout == 2) ||
                                postLayout == 3 ||
                                postLayout == 4
                            "
                            :gutter="0"
                        >
                            <a-col
                                v-for="item in productLists"
                                :key="item.xid"
                                :xxl="4"
                                :lg="4"
                                :md="12"
                                :xs="24"
                                @click="selectSaleProduct(item)"
                            >
                                <ProductCardNew :product="item" />
                            </a-col>
                        </a-row>
                        <a-row v-else>
                            <a-col :span="24">
                                <a-result
                                    :title="$t('stock.no_product_found')"
                                    :style="{ marginTop: '20%' }"
                                />
                            </a-col>
                        </a-row>
                    </perfect-scrollbar>
                </a-col>

                <a-col
                    :xs="24"
                    :sm="24"
                    :md="20"
                    :lg="3"
                    :xl="3"
                    class="rightButt"
                >
                    <!-- <a-button size="big" :style="{ marginBottom: '10px' }">
                        <div>F2 Bill</div>
                    </a-button> -->

                    <a-button
                        id="submitButton"
                        type="primary"
                        size="big"
                        @click="completeOrder"
                        :disabled="
                            formData.subtotal <= 0 ||
                            formData.user_id == undefined ||
                            formData.user_id == '' ||
                            !formData.user_id
                        "
                        :loading="quickPayLoader"
                        :style="{ marginBottom: '10px' }"
                    >
                        F2 {{ $t("stock.quick_pay_now") }}
                    </a-button>
                    <a-button
                        type="primary"
                        size="big"
                        @click="payNow"
                        :disabled="
                            formData.subtotal <= 0 ||
                            formData.user_id == undefined ||
                            formData.user_id == '' ||
                            !formData.user_id
                        "
                        :style="{ marginBottom: '10px' }"
                    >
                        {{ $t("stock.pay_now") }}
                    </a-button>
                    <a-button
                        size="big"
                        :style="{ marginBottom: '10px' }"
                        @click="selectProduct"
                    >
                        <div>F4 Product</div>
                    </a-button>
                    <a-button size="big" :style="{ marginBottom: '10px' }">
                        <div>F8 Customer</div>
                    </a-button>

                    <a-button
                        size="big"
                        :style="{ marginBottom: '10px' }"
                        @click="selectBarcode"
                        >F9 Barcode
                    </a-button>

                    <a-button
                        v-print="'#a4_invoice'"
                        size="big"
                        :style="{ marginBottom: '10px' }"
                        >Cash Drawer
                    </a-button>

                    <ProductAddButton
                        size="medium"
                        v-if="innerWidth >= 768"
                        style="width: 100%"
                        @onAddSuccess="ProductAdded"
                        >{{ $t("product.product") }}
                    </ProductAddButton>
                    <StockAdd
                        size="medium"
                        v-if="innerWidth >= 768"
                        style="
                            margin-top: 10px;
                            width: 100%;
                            margin-bottom: 10px;
                        "
                        >{{ $t("stock.stock") }}</StockAdd
                    >

                    <a-button
                        @click="openLastOrder"
                        style="width: 100%; margin-bottom: 10px"
                    >
                        Last Order
                    </a-button>
                    <ViewLastOrder
                        :visible="isOrderVisible"
                        @closed="closeLastOrder"
                    />
                    <a-button size="big" @click="resetPos">
                        {{ $t("stock.reset") }}
                    </a-button>

                    <p></p>

                    <a-select
                        v-if="selectedWarehouse.is_card_gateway == 1"
                        v-model:value="formData.square_device_id"
                        placeholder="Card Devices"
                        style="width: 100%"
                        show-search
                        @change="(e) => selectCardDevice(e)"
                    >
                        <a-select-option
                            v-for="device in cardDevices"
                            :key="device.device_id"
                            :value="device.device_id"
                        >
                            {{ device.code }}
                        </a-select-option>
                    </a-select>
                    <p></p>
                    <a-button @click="openDraftModal" type="primary">
                        Save To Draft
                    </a-button>
                </a-col>
                <!-- *-------------- -->
                <a-layout-footer class="pos--footer">
                    <a-row :gutter="16">
                        <a-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                            <a-row :gutter="16">
                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="4"
                                    :lg="4"
                                    :xl="4"
                                >
                                    <a-form-item :label="$t('stock.order_tax')">
                                        <a-select
                                            size="small"
                                            v-model:value="formData.tax_id"
                                            :placeholder="
                                                $t(
                                                    'common.select_default_text',
                                                    [$t('stock.order_tax')]
                                                )
                                            "
                                            :allowClear="true"
                                            style="width: 100%"
                                            @change="taxChanged"
                                        >
                                            <a-select-option
                                                v-for="tax in taxes"
                                                :key="tax.xid"
                                                :value="tax.xid"
                                                :tax="tax"
                                            >
                                                {{ tax.name }} ({{ tax.rate }}%)
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="4"
                                    :lg="4"
                                    :xl="4"
                                >
                                    <a-form-item :label="$t('stock.discount')">
                                        <a-input-group compact>
                                            <a-select
                                                size="small"
                                                v-model:value="
                                                    formData.discount_type
                                                "
                                                @change="recalculateFinalTotal"
                                                style="width: 30%"
                                            >
                                                <a-select-option
                                                    value="percentage"
                                                >
                                                    %
                                                </a-select-option>
                                                <a-select-option value="fixed">
                                                    {{
                                                        appSetting.currency
                                                            .symbol
                                                    }}
                                                </a-select-option>
                                            </a-select>
                                            <a-input-number
                                                size="small"
                                                v-model:value="
                                                    formData.discount_value
                                                "
                                                :placeholder="
                                                    $t(
                                                        'common.placeholder_default_text',
                                                        [$t('stock.discount')]
                                                    )
                                                "
                                                @change="recalculateFinalTotal"
                                                :min="0"
                                                style="width: 70%"
                                            />
                                        </a-input-group>
                                    </a-form-item>
                                </a-col>

                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="6"
                                    :lg="6"
                                    :xl="6"
                                >
                                    <a-form-item :label="$t('stock.shipping')">
                                        <a-input-number
                                            size="small"
                                            v-model:value="formData.shipping"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.shipping')]
                                                )
                                            "
                                            @change="recalculateFinalTotal"
                                            :min="0"
                                            :disabled="
                                                !(
                                                    permsArray.includes(
                                                        'shipping_edit'
                                                    ) ||
                                                    permsArray.includes('admin')
                                                )
                                            "
                                            style="width: 100%"
                                        >
                                            <template #addonBefore>
                                                {{ appSetting.currency.symbol }}
                                            </template>
                                        </a-input-number>
                                    </a-form-item>
                                </a-col>
                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="4"
                                    :lg="4"
                                    :xl="4"
                                >
                                    <small>
                                        {{ $t("product.tax") }} :
                                        {{
                                            formatAmountCurrency(
                                                formData.tax_amount
                                            )
                                        }}
                                        <br />
                                        {{ $t("product.discount") }} :
                                        {{
                                            formatAmountCurrency(
                                                formData.discount
                                            )
                                        }}
                                    </small>
                                </a-col>
                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="6"
                                    :lg="6"
                                    :xl="6"
                                >
                                    <a-row
                                        v-if="customerBalance"
                                        style="
                                            background: var(--primary-color);
                                            color: #ffff;
                                            padding: 5px;
                                        "
                                        >Current Balance :
                                        {{
                                            formatAmountCurrency(
                                                customerBalance
                                            )
                                        }}</a-row
                                    >
                                    <a-row
                                        v-if="creditPoints.length > 0"
                                        style="
                                            background: var(--primary-color);
                                            color: #ffff;
                                            padding: 5px;
                                        "
                                        >Credit Points :
                                        {{ creditPoints[0].point_balance }}
                                        Points</a-row
                                    >
                                </a-col>
                            </a-row>
                        </a-col>
                        <a-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                            <a-row :gutter="16">
                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="10"
                                    :lg="10"
                                    :xl="10"
                                >
                                    <a-col
                                        :xs="20"
                                        :sm="20"
                                        :md="20"
                                        :lg="20"
                                        :xl="20"
                                        style="
                                            background: var(--primary-color);
                                            color: #ffff;
                                        "
                                    >
                                        <span
                                            class="pos-grand-total_cap"
                                            type="primary"
                                        >
                                            {{ $t("stock.grand_total") }}
                                            :<br />
                                            <span class="pos-grand-total">
                                                {{
                                                    formatAmountCurrency(
                                                        formData.subtotal
                                                    )
                                                }}
                                            </span>
                                        </span>
                                    </a-col>
                                </a-col>
                                <a-col
                                    :xs="24"
                                    :sm="24"
                                    :md="14"
                                    :lg="14"
                                    :xl="14"
                                >
                                    <a-row
                                        :gutter="24"
                                        style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: baseline;
                                        "
                                    >
                                        <a-form-item
                                            name="payment_mode_id"
                                            :help="
                                                rules.payment_mode_id
                                                    ? rules.payment_mode_id
                                                          .message
                                                    : null
                                            "
                                            :validateStatus="
                                                rules.payment_mode_id
                                                    ? 'error'
                                                    : null
                                            "
                                        >
                                            <a-select
                                                v-model:value="
                                                    formData.payment_mode_id
                                                "
                                                :placeholder="
                                                    $t(
                                                        'common.select_default_text',
                                                        [
                                                            $t(
                                                                'payments.payment_mode'
                                                            ),
                                                        ]
                                                    )
                                                "
                                                size="big"
                                                :allowClear="true"
                                                style="width: 150px"
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

                                        <!-- <a-button
                                            id="submitButton"
                                            type="primary"
                                            size="big"
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
                                        </a-button> -->
                                        <!-- <a-button
                                            type="primary"
                                            size="big"
                                            @click="payNow"
                                            :disabled="
                                                formData.subtotal <= 0 ||
                                                formData.user_id == undefined ||
                                                formData.user_id == '' ||
                                                !formData.user_id
                                            "
                                        >
                                            {{ $t("stock.pay_now") }}
                                        </a-button> -->
                                    </a-row>
                                </a-col>
                            </a-row>
                        </a-col>
                    </a-row>
                </a-layout-footer>
            </a-row>
            <!-- Mobile screen -->
            <a-row
                v-else
                :gutter="[8, 8]"
                class="mt-5"
                style="margin: 10px 16px 0"
            >
                <a-col :span="24">
                    <div class="pos1-left-wrapper">
                        <div class="pos-left-header">
                            <!-- <PosLayout1
                                v-if="postLayout == 1"
                                :formData="formData"
                                @changed="reFetchProducts"
                            /> -->
                            <PosLayout2
                                v-if="postLayout == 2"
                                :brands="brands"
                                :categories="categories"
                                :formData="formData"
                                @changed="reFetchProducts"
                            />
                            <PosLayout3
                                v-if="postLayout == 3"
                                :brands="brands"
                                :categories="categories"
                                :formData="formData"
                                @changed="reFetchProducts"
                            />

                            <PosLayout3
                                v-if="postLayout == 4"
                                :brands="brands"
                                :categories="categories"
                                :formData="formData"
                                @changed="reFetchProducts"
                            />
                        </div>
                        <div class="pos-left-content">
                            <a-row
                                v-if="
                                    (productLists.length > 0 &&
                                        postLayout == 2) ||
                                    postLayout == 3
                                "
                                :gutter="0"
                                class="pos1-products-lists"
                            >
                                <a-col
                                    v-for="item in productLists"
                                    :key="item.xid"
                                    :xxl="8"
                                    :lg="8"
                                    :md="8"
                                    :sm="6"
                                    :xs="6"
                                    @click="selectSaleProduct(item)"
                                >
                                    <ProductCardNew :product="item" />
                                </a-col>
                            </a-row>
                            <a-card class="left-pos1-middle-table mt-10 mb-10">
                                <div class="bill-body">
                                    <div class="bill-table">
                                        <a-row class="mt-5 mb-5">
                                            <a-col
                                                :xs="24"
                                                :sm="24"
                                                :md="24"
                                                :lg="24"
                                            >
                                                <a-table
                                                    :row-key="
                                                        (record) => record.xid
                                                    "
                                                    :dataSource="
                                                        selectedProducts
                                                    "
                                                    :columns="orderItemColumns"
                                                    :pagination="false"
                                                >
                                                    <template
                                                        #bodyCell="{
                                                            column,
                                                            record,
                                                        }"
                                                    >
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'name'
                                                            "
                                                        >
                                                            {{ record.name }}

                                                            <!-- <br />

                                                                <small>

                                                                    <a-typography-text

                                                                        code

                                                                    >

                                                                        {{

                                                                            $t(

                                                                                "product.avl_qty"

                                                                            )

                                                                        }}

                                                                        {{

                                                                            `${record.stock_quantity}${record.unit_short_name}`

                                                                        }}

                                                                    </a-typography-text>

                                                                </small> -->
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'unit_quantity'
                                                            "
                                                        >
                                                            <a-input-number
                                                                id="inputNumber"
                                                                v-model:value="
                                                                    record.quantity
                                                                "
                                                                :min="0"
                                                                @change="
                                                                    quantityChanged(
                                                                        record
                                                                    )
                                                                "
                                                            />
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'subtotal'
                                                            "
                                                        >
                                                            <div
                                                                style="
                                                                    text-align: right;
                                                                "
                                                            >
                                                                {{
                                                                    formatAmountCurrency(
                                                                        record.subtotal
                                                                    )
                                                                }}
                                                            </div>
                                                        </template>
                                                        <template
                                                            v-if="
                                                                column.dataIndex ===
                                                                'action'
                                                            "
                                                        >
                                                            <a-button
                                                                type="primary"
                                                                @click="
                                                                    editItem(
                                                                        record
                                                                    )
                                                                "
                                                                style="
                                                                    margin-left: 4px;

                                                                    margin-top: 4px;
                                                                "
                                                            >
                                                                <template #icon>
                                                                    <EditOutlined />
                                                                </template>
                                                            </a-button>
                                                            <a-button
                                                                type="primary"
                                                                @click="
                                                                    showDeleteConfirm(
                                                                        record
                                                                    )
                                                                "
                                                                style="
                                                                    margin-left: 4px;
                                                                    margin-top: 4px;
                                                                "
                                                            >
                                                                <template #icon>
                                                                    <DeleteOutlined />
                                                                </template>
                                                            </a-button>
                                                        </template>
                                                    </template>
                                                </a-table>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </a-card>
                        </div>

                        <div class="pos-left-footer">
                            <a-card>
                                <div class="bill-footer">
                                    <a-row :gutter="[16, 16]">
                                        <a-col
                                            :xs="6"
                                            :sm="6"
                                            :md="6"
                                            :lg="6"
                                            :xl="6"
                                        >
                                            <a-form-item
                                                :label="$t('stock.order_tax')"
                                            >
                                                <a-select
                                                    v-model:value="
                                                        formData.tax_id
                                                    "
                                                    :placeholder="
                                                        $t(
                                                            'common.select_default_text',
                                                            [
                                                                $t(
                                                                    'stock.order_tax'
                                                                ),
                                                            ]
                                                        )
                                                    "
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    @change="taxChanged"
                                                >
                                                    <a-select-option
                                                        v-for="tax in taxes"
                                                        :key="tax.xid"
                                                        :value="tax.xid"
                                                        :tax="tax"
                                                    >
                                                        {{ tax.name }} ({{
                                                            tax.rate
                                                        }}%)
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                        <a-col
                                            :xs="6"
                                            :sm="6"
                                            :md="6"
                                            :lg="6"
                                            :xl="6"
                                        >
                                            <a-form-item
                                                :label="$t('stock.discount')"
                                            >
                                                <a-input-group compact>
                                                    <a-select
                                                        v-model:value="
                                                            formData.discount_type
                                                        "
                                                        @change="
                                                            recalculateFinalTotal
                                                        "
                                                        style="width: 30%"
                                                    >
                                                        <a-select-option
                                                            value="percentage"
                                                        >
                                                            %
                                                        </a-select-option>
                                                        <a-select-option
                                                            value="fixed"
                                                        >
                                                            {{
                                                                appSetting
                                                                    .currency
                                                                    .symbol
                                                            }}
                                                        </a-select-option>
                                                    </a-select>
                                                    <a-input-number
                                                        v-model:value="
                                                            formData.discount_value
                                                        "
                                                        :placeholder="
                                                            $t(
                                                                'common.placeholder_default_text',
                                                                [
                                                                    $t(
                                                                        'stock.discount'
                                                                    ),
                                                                ]
                                                            )
                                                        "
                                                        @change="
                                                            recalculateFinalTotal
                                                        "
                                                        :min="0"
                                                        style="width: 70%"
                                                    />
                                                </a-input-group>
                                            </a-form-item>
                                        </a-col>
                                        <a-col
                                            :xs="6"
                                            :sm="6"
                                            :md="6"
                                            :lg="6"
                                            :xl="6"
                                        >
                                            <a-form-item
                                                :label="$t('stock.shipping')"
                                            >
                                                <a-input-number
                                                    v-model:value="
                                                        formData.shipping
                                                    "
                                                    :placeholder="
                                                        $t(
                                                            'common.placeholder_default_text',
                                                            [
                                                                $t(
                                                                    'stock.shipping'
                                                                ),
                                                            ]
                                                        )
                                                    "
                                                    :disabled="
                                                        !(
                                                            permsArray.includes(
                                                                'shipping_edit'
                                                            ) ||
                                                            permsArray.includes(
                                                                'admin'
                                                            )
                                                        )
                                                    "
                                                    @change="
                                                        recalculateFinalTotal
                                                    "
                                                    :min="0"
                                                    style="width: 100%"
                                                >
                                                    <template #addonBefore>
                                                        {{
                                                            appSetting.currency
                                                                .symbol
                                                        }}
                                                    </template>
                                                </a-input-number>
                                            </a-form-item>
                                        </a-col>
                                        <a-col
                                            :xs="6"
                                            :sm="6"
                                            :md="6"
                                            :lg="6"
                                            :xl="6"
                                        >
                                            <small>
                                                {{ $t("product.tax") }} :
                                                {{
                                                    formatAmountCurrency(
                                                        formData.tax_amount
                                                    )
                                                }}
                                                <br />
                                                {{ $t("product.discount") }} :
                                                {{
                                                    formatAmountCurrency(
                                                        formData.discount
                                                    )
                                                }}
                                            </small>
                                        </a-col>
                                    </a-row>
                                </div>
                            </a-card>
                            <div
                                :style="{
                                    right: 0,
                                    bottom: 20,
                                    width: '100%',
                                    padding: '10px 16px',
                                    background: '#fff',
                                    textAlign: 'right',
                                    zIndex: 1,
                                }"
                            >
                                <a-row :gutter="16">
                                    <a-col
                                        :xs="24"
                                        :sm="24"
                                        :md="6"
                                        :lg="6"
                                        :xl="6"
                                    >
                                        <a-row
                                            :gutter="16"
                                            :style="{
                                                background: '#dbdbdb',
                                                padding: '5px',
                                            }"
                                        >
                                            <a-col
                                                :xs="12"
                                                :sm="12"
                                                :md="16"
                                                :lg="16"
                                                :xl="16"
                                            >
                                                <span class="pos-grand-total">
                                                    {{
                                                        $t("stock.grand_total")
                                                    }}
                                                    :
                                                </span>
                                            </a-col>
                                            <a-col
                                                :xs="12"
                                                :sm="12"
                                                :md="8"
                                                :lg="8"
                                                :xl="8"
                                            >
                                                <span class="pos-grand-total">
                                                    {{
                                                        formatAmountCurrency(
                                                            formData.subtotal
                                                        )
                                                    }}
                                                </span>
                                            </a-col>
                                        </a-row>
                                    </a-col>
                                    <a-col
                                        :xs="24"
                                        :sm="24"
                                        :md="8"
                                        :lg="8"
                                        :xl="8"
                                    >
                                        <a-form-item
                                            name="payment_mode_id"
                                            :help="
                                                rules.payment_mode_id
                                                    ? rules.payment_mode_id
                                                          .message
                                                    : null
                                            "
                                            :validateStatus="
                                                rules.payment_mode_id
                                                    ? 'error'
                                                    : null
                                            "
                                        >
                                            <a-select
                                                v-model:value="
                                                    formData.payment_mode_id
                                                "
                                                :placeholder="
                                                    $t(
                                                        'common.select_default_text',
                                                        [
                                                            $t(
                                                                'payments.payment_mode'
                                                            ),
                                                        ]
                                                    )
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
                                    <a-col
                                        :xs="24"
                                        :sm="24"
                                        :md="8"
                                        :lg="8"
                                        :xl="8"
                                    >
                                        <a-space>
                                            <a-button
                                                id="submitButton"
                                                type="primary"
                                                @click="completeOrder"
                                                :disabled="
                                                    formData.subtotal <= 0 ||
                                                    formData.user_id ==
                                                        undefined ||
                                                    formData.user_id == '' ||
                                                    !formData.user_id
                                                "
                                                :loading="quickPayLoader"
                                            >
                                                {{ $t("stock.quick_pay_now") }}
                                                (F2)
                                            </a-button>
                                            <a-button
                                                type="primary"
                                                @click="payNow"
                                                :disabled="
                                                    formData.subtotal <= 0 ||
                                                    formData.user_id ==
                                                        undefined ||
                                                    formData.user_id == '' ||
                                                    !formData.user_id
                                                "
                                            >
                                                {{ $t("stock.pay_now") }}
                                            </a-button>

                                            <a-button @click="resetPos">
                                                {{ $t("stock.reset") }}
                                            </a-button>
                                        </a-space>
                                    </a-col>
                                </a-row>
                            </div>
                        </div>
                    </div>
                </a-col>
            </a-row>
        </a-form>
    </SubscriptionModuleVisibility>
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
                            :disabled="
                                !(
                                    permsArray.includes('price_edit') ||
                                    permsArray.includes('admin')
                                )
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
                            :disabled="
                                !(
                                    permsArray.includes('discount_edit') ||
                                    permsArray.includes('admin')
                                )
                            "
                            :min="0"
                            style="width: 100%"
                        >
                            <template #addonAfter> % </template>
                        </a-input-number>
                    </a-form-item>
                </a-col>
            </a-row>
            <!-- <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('product.tax')"
                        name="tax_id"
                        :help="
                            addEditRules.tax_id
                                ? addEditRules.tax_id.message
                                : null
                        "
                        :validateStatus="addEditRules.tax_id ? 'error' : null"
                    >
                        <a-select
                            v-model:value="addEditFormData.tax_id"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('product.tax'),
                                ])
                            "
                            :allowClear="true"
                        >
                            <a-select-option
                                v-for="tax in taxes"
                                :key="tax.xid"
                                :value="tax.xid"
                            >
                                {{ tax.name }} ({{ tax.rate }}%)
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('product.tax_type')"
                        name="tax_type"
                        :help="
                            addEditRules.tax_type
                                ? addEditRules.tax_type.message
                                : null
                        "
                        :validateStatus="addEditRules.tax_type ? 'error' : null"
                    >
                        <a-select
                            v-model:value="addEditFormData.tax_type"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('product.tax_type'),
                                ])
                            "
                            :disabled="selectedWarehouse.gst_in_no == null"
                            :allowClear="true"
                        >
                            <a-select-option
                                v-for="taxType in taxTypes"
                                :key="taxType.key"
                                :value="taxType.key"
                            >
                                {{ taxType.value }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row> -->
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
    <a-modal
        :visible="DraftModal"
        :closable="false"
        :centered="true"
        title="Save To Draft"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-input
                        v-model:value="DraftName"
                        placeholder="Please Enter a name"
                        style="width: 100%"
                    />
                </a-col>
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-table
                        :dataSource="DraftData"
                        :columns="DraftColumns"
                        :pagination="false"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'name'">
                                {{ record.name }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button @Click="DratToOrder(record)"
                                    ><SendOutlined
                                /></a-button>
                                <a-button
                                    type="primary"
                                    @Click="deleteDraft(record.id)"
                                    ><DeleteOutlined
                                /></a-button>
                            </template>
                        </template>
                    </a-table>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button
                key="submit"
                type="primary"
                :loading="DraftModalLoading"
                :disabled="DraftName == '' || selectedProducts.length == 0"
                @click="saveToDraft"
            >
                <template #icon>
                    <SaveOutlined />
                </template>
                {{ $t("common.save") }}
            </a-button>
            <a-button @click="DraftModal = false">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-modal>
    <PayNow
        :visible="payNowVisible"
        @closed="payNowClosed"
        @success="payNowSuccess"
        :data="formData"
        :selectedProducts="selectedProducts"
    />
    <InvoiceModal
        :visible="printInvoiceModalVisible"
        :order="printInvoiceOrder"
        :orderinfo="printInvoiceOrderInfo"
        @closed="printInvoiceModalVisible = false"
    />
    {{ setShippingPrice }}
</template>

<script>
import {
    ref,
    onMounted,
    reactive,
    toRefs,
    nextTick,
    router,
    watch,
    computed,
    onBeforeUnmount,
} from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    SearchOutlined,
    SaveOutlined,
    BarcodeOutlined,
    SettingOutlined,
    CameraOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined,
    SendOutlined,
} from "@ant-design/icons-vue";
import { debounce } from "lodash-es";
import { useI18n } from "vue-i18n";
import { message } from "ant-design-vue";
import { includes, find } from "lodash-es";
import common from "../../../../common/composable/common";
import { OrderSummary } from "../../../../common/components/product/style";
import fields from "./fields";
import ProductCardNew from "../../../../common/components/product/ProductCardNew.vue";
import PayNow from "./PayNow.vue";
import CustomerAddButton from "../../users/CustomerAddButton.vue";
import InvoiceModal from "./Invoice.vue";
import PosLayout1 from "./PosLayout1.vue";
import PosLayout2 from "./PosLayout2.vue";
import PosLayout3 from "./PosLayout3.vue";
import { Modal } from "ant-design-vue";
import SubscriptionModuleVisibility from "../../../../common/components/common/visibility/SubscriptionModuleVisibility.vue";
import SubscriptionModuleVisibilityMessage from "../../../../common/components/common/visibility/SubscriptionModuleVisibilityMessage.vue";
import { useStore } from "vuex";
import apiAdmin from "../../../../common/composable/apiAdmin";
import ProductAddButton from "../../product-manager/products/AddQuickButton.vue";
import StockAdd from "../../stock-management/adjustment/AddButton.vue";
import { useRouter } from "vue-router";
import BarcodeScanner from "../../BarcodeScanner.vue";
import { StreamBarcodeReader } from "vue-barcode-reader";
import ViewLastOrder from "./ViewLastOrder.vue";
import moment from "moment";
import SetSocket from "../../../../common/composable/socket";
export default {
    components: {
        PlusOutlined,
        SearchOutlined,
        EditOutlined,
        DeleteOutlined,
        SaveOutlined,
        BarcodeOutlined,
        SettingOutlined,
        PosLayout1,
        PosLayout2,
        PosLayout3,
        ProductAddButton,
        StockAdd,
        ProductCardNew,
        OrderSummary,
        PayNow,
        CustomerAddButton,
        InvoiceModal,
        SubscriptionModuleVisibility,
        SubscriptionModuleVisibilityMessage,
        BarcodeScanner,
        StreamBarcodeReader,
        CameraOutlined,
        ArrowUpOutlined,
        ArrowDownOutlined,
        ViewLastOrder,
        SendOutlined,
    },
    setup() {
        const {
            taxes,
            customers,
            brands,
            categories,
            productLists,
            orderItemColumns,
            orderItemColumns1,
            customerUrl,
            getPreFetchData,
            posDefaultCustomer,
            formData,
            DraftColumns,
        } = fields();
        const store = useStore();
        const selectedProducts = ref([]);
        const selectedProductIds = ref([]);
        const removedOrderItemsIds = ref([]);
        const { selectedWarehouse, orderPageObject } = common();
        const socket = SetSocket;

        const postLayout = ref(selectedWarehouse.value.set_pos_type);
        const paymentModes = ref([]);
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const router = useRouter();

        const quickPayLoader = ref(false);
        const cardLoader = ref(false);

        const { t } = useI18n();
        const allPaymentRecords = ref([]);
        const paymentRecordsColumns = ref([
            {
                title: t("payments.payment_mode"),
                dataIndex: "payment_mode",
            },
            {
                title: t("payments.amount"),
                dataIndex: "amount",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ]);

        const state = reactive({
            orderSearchTerm: [],
            productFetching: false,
            products: [],
        });
        const {
            formatAmount,
            formatAmountCurrency,
            appSetting,
            taxTypes,
            permsArray,
        } = common();
        // const { t } = useI18n();

        // AddEdit
        const addEditVisible = ref(false);
        const addEditFormSubmitting = ref(false);
        const addEditFormData = ref({});
        const addEditRules = ref([]);
        const addEditPageTitle = ref("");

        // Pay Now
        const payNowVisible = ref(false);
        const printInvoiceModalVisible = ref(false);
        const printInvoiceOrder = ref({});
        const printInvoiceOrderInfo = ref({});
        const customerBalance = ref(null);
        const selectedCustomer = ref({});

        const creditPoints = ref([]);

        const location = window.location.origin;

        // For Barcode Search
        const isbarcode = ref(selectedWarehouse.value.is_barcode);

        if (selectedWarehouse.value.is_barcode == 0) {
            var bool1 = false;
        } else {
            var bool1 = true;
        }

        const searchBarcodeInput = ref(bool1);
        const barcodeSearchTerm = ref("");

        const cardPaymentStatus = ref("PENDING");
        const UPIPaymentStatus = ref("created");
        const cardDevices = ref([]);
        const isCustomerWholesale = ref(false);
        const DraftModal = ref(false);
        const DraftName = ref("");
        const DraftModalLoading = ref(false);
        const DraftData = ref([]);

        const openDraftModal = () => {
            getDraftData();
            DraftModal.value = true;
        };
        const getDraftData = () => {
            axiosAdmin
                .get("/getorderdraft")
                .then((res) => {
                    DraftData.value = res.data;
                })
                .catch((err) => {
                    console.log(err);
                });
        };

        const saveToDraft = () => {
            const order = JSON.stringify(selectedProducts.value);
            let data = {
                name: DraftName.value,
                data: order,
                user_id: formData.value.user_id,
            };
            axiosAdmin.post("/orderdraft", data).then((res) => {
                message.success("Drafts saved successfully");
            });
            DraftModal.value = false;
            DraftName.value = "";
            resetPos1();
        };

        const DratToOrder = (record) => {
            if (selectedProducts.value.length > 0) {
                Modal.confirm({
                    title: "Do you want to Clear or add to the existing?",
                    content: "",
                    centered: true,
                    okText: "Clear & add",
                    // okType: "danger",
                    cancelText: "Add",
                    onOk() {
                        resetPos1();
                        sendToOrder(record);
                    },
                    onCancel() {
                        sendToOrder(record);
                    },
                });
            } else {
                sendToOrder(record);
            }
        };

        const sendToOrder = (record) => {
            const products = JSON.parse(record.data);
            formData.value.user_id = record.user_id;
            formData.value.draft_id = record.id;
            customerChanged();
            products.map((product) => {
                searchValueSelected("", { product: product });
            });
            DraftModal.value = false;
        };

        const deleteDraft = (id) => {
            axiosAdmin.post(`/draftdelete/${id}`).then((res) => {
                getDraftData();
            });
        };

        onMounted(() => {
            axiosAdmin.get("payment-modes").then((response) => {
                paymentModes.value = response.data;
                setDefaultPayment();
            });

            if (selectedWarehouse.value.is_card_gateway == 1) {
                listDevices();
            }

            getPreFetchData();
        });

        const ProductAdded = (data) => {
            if (data != "") {
                let url = `search-product`;

                axiosAdmin
                    .post(url, {
                        order_type: "sales",
                        search_term: data,
                        products: selectedProductIds.value,
                        userid: formData.value.user_id,
                    })
                    .then((response) => {
                        var product = response.data[0];
                        searchValueSelected("", { product: product });
                    });
            }
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

        const listDevices = () => {
            axiosAdmin
                .post("pos/list_square_devices")
                .then((res) => {
                    let device_id = sessionStorage.getItem("device_id");
                    if (
                        res &&
                        res.device_codes &&
                        res.device_codes.length > 0
                    ) {
                        formData.value = {
                            ...formData.value,
                            square_device_id: device_id
                                ? device_id
                                : res.device_codes[0].device_id,
                        };
                    }
                    cardDevices.value = res.device_codes;
                })
                .catch((err) => {
                    console.log(err);
                });
        };

        const selectCardDevice = (e) => {
            formData.value.square_device_id = e;
            sessionStorage.setItem("device_id", e);
        };

        const reFetchProducts = () => {
            axiosAdmin
                .post("pos/products", {
                    brand_id: formData.value.brand_id,
                    category_id: formData.value.category_id,
                })
                .then((productResponse) => {
                    productLists.value = productResponse.data.products;
                });
        };

        const handleKeyPress = (event) => {
            if (event.key === "F2") {
                const submitButton = document.getElementById("submitButton");
                if (submitButton) {
                    submitButton.click();
                }
            }
            if (event.key === "F8") {
                const submitButton = document.getElementById("customerFocus");
                if (submitButton) {
                    submitButton.focus();
                }
            }
            if (event.key === "F4") {
                searchBarcodeInput.value = false;
                setTimeout(() => {
                    const submitButton =
                        document.getElementById("productFocus");
                    if (submitButton) {
                        submitButton.focus();
                    }
                }, 1000);
            }
            if (event.key === "F9") {
                const submitButton = document.getElementById("barcodeFocus");
                if (submitButton) {
                    submitButton.click();
                }
                setTimeout(() => {
                    const submitButton1 =
                        document.getElementById("bcinputFocus");
                    if (submitButton1) {
                        submitButton1.focus();
                    }
                }, 1000);
            }
        };

        const selectProduct = () => {
            searchBarcodeInput.value = false;
            setTimeout(() => {
                const submitButton = document.getElementById("productFocus");
                if (submitButton) {
                    submitButton.focus();
                }
            }, 1000);
        };
        const selectBarcode = () => {
            const submitButton = document.getElementById("barcodeFocus");
            if (submitButton) {
                submitButton.click();
            }
            setTimeout(() => {
                const submitButton1 = document.getElementById("bcinputFocus");
                if (submitButton1) {
                    submitButton1.focus();
                }
            }, 1000);
        };
        // Listen for the key press event on component mount
        onMounted(() => {
            window.addEventListener("keydown", handleKeyPress);
        });

        onBeforeUnmount(() => {
            window.removeEventListener("keydown", handleKeyPress);
        });

        // Clean up the event listener on component unmount
        const fetchProducts = debounce((value) => {
            state.products = [];

            if (value != "") {
                state.productFetching = true;
                let url = `search-product`;

                axiosAdmin
                    .post(url, {
                        order_type: "sales",
                        search_term: value,
                        products: selectedProductIds.value,
                        userid: formData.value.user_id,
                    })
                    .then((response) => {
                        state.products = response.data;
                        state.productFetching = false;
                    })
                    .catch((err) => {
                        message.error(err.data.message);
                        state.productFetching = false;
                    });
            }
        }, 300);

        const barcodeFetch = (value) => {
            nextTick(() => {
                if (value != "") {
                    state.productFetching = true;
                    let url = `search-barcode-product`;

                    axiosAdmin
                        .post(url, {
                            order_type: "sales",
                            search_term: value.target.value,
                            products: selectedProductIds.value,
                            userid: formData.value.user_id,
                        })
                        .then((response) => {
                            if (
                                response.data &&
                                response.data.product &&
                                response.data.product.xid
                            ) {
                                var product = response.data.product;
                                searchValueSelected("", { product: product });
                            }

                            barcodeSearchTerm.value = "";
                            state.productFetching = false;
                        })
                        .catch((err) => {
                            message.error(err.data.message);
                            barcodeSearchTerm.value = "";
                            state.productFetching = false;
                        });
                }
            });
        };

        const searchValueSelected = (value, option) => {
            const newProduct = option.product;
            selectSaleProduct(newProduct);
        };

        const setUnitPrice = (product) => {
            let updatedPrice = product.unit_price;

            const isWholesaleCustomer =
                product.is_wholesale_only == 1 && !isCustomerWholesale.value;

            const edited = product && product.is_edited;

            if (!edited && product.wholesale.length > 0) {
                // Initialize updatedPrice to default_price by default
                updatedPrice = product.default_price;
                for (const wholesaleObj of product.wholesale) {
                    const startQuantity = parseFloat(
                        wholesaleObj.start_quantity
                    );
                    const endQuantity = parseFloat(wholesaleObj.end_quantity);

                    const isInRange =
                        product.quantity >= startQuantity &&
                        product.quantity <= endQuantity;

                    if (!isWholesaleCustomer) {
                        // Update price if wholesale customer or non-wholesale customer
                        if (isInRange) {
                            updatedPrice =
                                wholesaleObj.wholesale_price == 0
                                    ? product.unit_price
                                    : wholesaleObj.wholesale_price;
                            // Don't break to consider the last valid range
                        }
                    }
                }
            }
            return updatedPrice;
        };

        const selectSaleProduct = (newProduct) => {
            const inputElement = document.getElementById("bcinputFocus");
            if (inputElement) {
                inputElement.focus();
            }
            if (newProduct.price_history != "") {
                var covertArray = JSON.parse(newProduct.price_history);
                if (covertArray != null)
                    var priceHistory =
                        covertArray[covertArray.length - 1].sale_price;
            } else {
                var priceHistory = "";
            }
            if (!includes(selectedProductIds.value, newProduct.xid)) {
                selectedProductIds.value.push(newProduct.xid);
                newProduct.default_price = formatAmount(newProduct.unit_price);
                selectedProducts.value.push({
                    ...newProduct,
                    sn: selectedProducts.value.length + 1,
                    unit_price: formatAmount(setUnitPrice(newProduct)),
                    discount_type: "fixed",
                    discount: 0,
                    single_unit_price: formatAmount(
                        newProduct.single_unit_price
                    ),
                    return_quantity: newProduct.quantity,
                    tax_amount: formatAmount(newProduct.tax_amount),
                    subtotal: formatAmount(newProduct.subtotal),
                    lastPrice: formatAmount(priceHistory),
                });
                state.orderSearchTerm = [];
                state.products = [];
                selectedProducts.value.map((selectedProduct) => {
                    if (selectedProduct.xid == newProduct.xid) {
                        quantityChanged(selectedProduct);
                    }
                });
                recalculateFinalTotal();

                var audioObj = new Audio(appSetting.value.beep_audio_url);
                audioObj.play();
            } else {
                const newResults = [];
                var foundRecord = {};

                selectedProducts.value.map((selectedProduct) => {
                    var newQuantity = selectedProduct.quantity;

                    if (selectedProduct.xid == newProduct.xid) {
                        newQuantity += 1;
                        selectedProduct.quantity = newQuantity;
                        foundRecord = selectedProduct;
                    }

                    newResults.push(selectedProduct);
                });
                selectedProducts.value = newResults;
                state.orderSearchTerm = [];
                state.products = [];

                var audioObj = new Audio(appSetting.value.beep_audio_url);
                audioObj.play();

                quantityChanged(foundRecord);
            }
        };

        const recalculateValues = (product, name) => {
            var quantityValue = parseFloat(
                product.quantity ? product.quantity : 0
            );
            var maxQuantity = parseFloat(product.stock_quantity);
            const unitPrice = parseFloat(setUnitPrice(product));

            // Check if entered quantity value is greater
            quantityValue =
                quantityValue > maxQuantity &&
                selectedWarehouse.value.is_negative_stock == 0
                    ? maxQuantity
                    : quantityValue;
            let discountRate = 0;
            let totalDiscount = 0;
            let fixedDiscountAmount = 0;
            let beforeTaxValue = product.unit_price;
            let totalPriceAfterDiscount = beforeTaxValue * quantityValue;

            // While Changing the Total Discount of the product
            if (product.discount_type == "fixed") {
                totalDiscount =
                    product.discount <= totalPriceAfterDiscount
                        ? product.discount
                        : totalPriceAfterDiscount;

                totalPriceAfterDiscount =
                    totalPriceAfterDiscount - totalDiscount;
                fixedDiscountAmount = totalDiscount;
                discountRate =
                    totalDiscount > 0
                        ? (totalDiscount / totalPriceAfterDiscount) * 100
                        : 0;
            }

            if (product.discount_type == "percentage") {
                totalDiscount =
                    product.discount <= 100 ? product.discount : 100;
                discountRate = totalDiscount;
                fixedDiscountAmount =
                    (totalDiscount / 100) * totalPriceAfterDiscount;
                totalPriceAfterDiscount =
                    totalDiscount > 0
                        ? totalPriceAfterDiscount - fixedDiscountAmount
                        : totalPriceAfterDiscount;
            }

            var taxAmount = 0;
            var subtotal = totalPriceAfterDiscount;

            var singleUnitPrice = unitPrice;

            // Tax Amount
            if (product.tax_rate > 0) {
                if (product.tax_type == "inclusive") {
                    taxAmount =
                        totalPriceAfterDiscount -
                        totalPriceAfterDiscount / (1 + product.tax_rate / 100);
                } else {
                    taxAmount =
                        totalPriceAfterDiscount * (product.tax_rate / 100);
                    subtotal = totalPriceAfterDiscount + taxAmount;
                }
            }
            const newObject = {
                ...product,
                discount_rate: formatAmount(discountRate),
                total_discount: fixedDiscountAmount,
                subtotal: subtotal,
                quantity: quantityValue,
                total_tax: taxAmount,
                discount: totalDiscount,
                max_quantity: maxQuantity,
                unit_price: singleUnitPrice,
                return_quantity: quantityValue,
            };

            return newObject;
        };

        const customerChanged = () => {
            for (let i = 0; i < customers.value.length; i++) {
                if (customers.value[i].xid == formData.value.user_id) {
                    customerBalance.value =
                        customers.value[i].details.due_amount;
                    isCustomerWholesale.value =
                        customers.value[i].is_wholesale_customer == 1
                            ? true
                            : false;
                    selectedProducts.value.map((product) => {
                        quantityChanged(product);
                    });
                }
            }
        };

        const getCreditBalance = computed(() => {
            if (formData.value.user_id) {
                axiosAdmin.get("customer-points").then((res) => {
                    let data = res.data.filter((item) => {
                        return item.xid == formData.value.user_id;
                    });

                    creditPoints.value = data;
                });
            }
        });

        const quantityChanged = (record, name) => {
            const newResults = [];

            selectedProducts.value.map((selectedProduct) => {
                if (selectedProduct.xid == record.xid) {
                    const newValueCalculated = recalculateValues(record, name);
                    newResults.push(newValueCalculated);
                } else {
                    newResults.push(selectedProduct);
                }
            });
            selectedProducts.value = newResults;

            recalculateFinalTotal();
        };

        const recalculateFinalTotal = () => {
            let total = 0;
            selectedProducts.value.map((selectedProduct) => {
                total += selectedProduct.subtotal;
            });

            var discountAmount = 0;
            if (formData.value.discount_type == "percentage") {
                discountAmount =
                    formData.value.discount_value != ""
                        ? (parseFloat(formData.value.discount_value) * total) /
                          100
                        : 0;
            } else if (formData.value.discount_type == "fixed") {
                discountAmount =
                    formData.value.discount_value != ""
                        ? parseFloat(formData.value.discount_value)
                        : 0;
            }

            const taxRate =
                formData.value.tax_rate != ""
                    ? parseFloat(formData.value.tax_rate)
                    : 0;

            total = total - discountAmount;

            const tax = total * (taxRate / 100);

            total = total + parseFloat(formData.value.shipping);

            console.log(selectedProducts.value);

            formData.value.subtotal = formatAmount(total + tax);
            formData.value.tax_amount = formatAmount(tax);
            formData.value.discount = discountAmount;
            formData.value.amount = formatAmount(total + tax);
        };

        const showDeleteConfirm = (product) => {
            // Delete selected product and rearrange SN
            const newResults = [];
            let counter = 1;
            selectedProducts.value.map((selectedProduct) => {
                if (selectedProduct.item_id != null) {
                    removedOrderItemsIds.value = [
                        ...removedOrderItemsIds.value,
                        selectedProduct.item_id,
                    ];
                }

                if (selectedProduct.xid != product.xid) {
                    newResults.push({
                        ...selectedProduct,
                        sn: counter,
                        single_unit_price: formatAmount(
                            selectedProduct.single_unit_price
                        ),
                        tax_amount: formatAmount(selectedProduct.tax_amount),
                        subtotal: formatAmount(selectedProduct.subtotal),
                    });

                    counter++;
                }
            });
            selectedProducts.value = newResults;

            // Remove deleted product id from lists
            const filterProductIdArray = selectedProductIds.value.filter(
                (newId) => {
                    return newId != product.xid;
                }
            );
            selectedProductIds.value = filterProductIdArray;
            recalculateFinalTotal();
        };

        const taxChanged = (value, option) => {
            formData.value.tax_rate = value == undefined ? 0 : option.tax.rate;
            recalculateFinalTotal();
        };

        // Edit a selected product
        const editItem = (product) => {
            addEditFormData.value = {
                id: product.xid,
                discount_rate: product.discount_rate,
                unit_price: product.unit_price,
                tax_id: product.x_tax_id,
                tax_type:
                    product.tax_type == null ? undefined : product.tax_type,
            };
            addEditVisible.value = true;
            addEditPageTitle.value = product.name;
        };

        const payNow = () => {
            payNowVisible.value = true;
        };

        const payNowClosed = () => {
            payNowVisible.value = false;
        };

        const back = () => {
            if (selectedProducts.value.length > 0) {
                Modal.confirm({
                    title: t("common.backheading") + "?",
                    // icon: createVNode(ExclamationCircleOutlined),
                    content: t("common.backmessage"),
                    centered: true,
                    okText: t("common.yes"),
                    okType: "danger",
                    cancelText: t("common.no"),
                    onOk() {
                        router.go(-1);
                    },
                    onCancel() {},
                });
            } else {
                router.go(-1);
            }
        };

        const resetPos = () => {
            Modal.confirm({
                title: t("common.resetheading") + "?",
                // icon: createVNode(ExclamationCircleOutlined),
                content: t("common.resetmessage"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    selectedProducts.value = [];
                    selectedProductIds.value = [];

                    formData.value = {
                        ...formData.value,
                        tax_id: undefined,
                        category_id: undefined,
                        brand_id: undefined,
                        tax_id: undefined,
                        tax_rate: 0,
                        tax_amount: 0,
                        discount_value: 0,
                        discount: 0,
                        shipping: 0,
                        subtotal: 0,
                        invoice_number: "",
                        mode: 0,
                        user_id:
                            posDefaultCustomer.value &&
                            posDefaultCustomer.value.xid
                                ? posDefaultCustomer.value.xid
                                : undefined,
                    };

                    customerBalance.value = null;

                    recalculateFinalTotal();
                },
                onCancel() {},
            });
        };

        const calculateShippingPrice = () => {
            return selectedProducts.value.reduce((totalShipping, product) => {
                if (product.is_shipping === 1) {
                    return (
                        totalShipping +
                        product.shipping_price * product.quantity
                    );
                }
                return totalShipping;
            }, 0);
        };

        const setShippingPrice = computed(() => {
            formData.value.shipping = calculateShippingPrice();
        });

        watch(
            () => formData.value.shipping,
            (newShipping, oldShipping) => {
                if (newShipping < calculateShippingPrice()) {
                    formData.value.shipping = calculateShippingPrice();
                }
                recalculateFinalTotal();
            }
        );

        const resetPos1 = () => {
            selectedProducts.value = [];
            selectedProductIds.value = [];

            formData.value = {
                ...formData.value,
                tax_id: undefined,
                category_id: undefined,
                brand_id: undefined,
                tax_id: undefined,
                tax_rate: 0,
                tax_amount: 0,
                discount_value: 0,
                discount: 0,
                shipping: 0,
                subtotal: 0,
                invoice_number: "",
                mode: 0,
                user_id:
                    posDefaultCustomer.value && posDefaultCustomer.value.xid
                        ? posDefaultCustomer.value.xid
                        : undefined,
            };

            customerBalance.value = null;

            setDefaultPayment();

            recalculateFinalTotal();
        };

        // Altered the Entire Function by saravanan
        const onEditPrice = (record, name) => {
            const product = selectedProducts.value.filter(
                (selectedProduct) => selectedProduct.xid == record.xid
            );
            const selecteTax = taxes.value.filter(
                (tax) => tax.xid == record.x_tax_id
            );

            const taxType =
                record.tax_type != undefined ? record.tax_type : "exclusive";
            const newData = {
                ...product[0],
                discount_rate: parseFloat(
                    record.discount_rate ? record.discount_rate : 0
                ),
                unit_price: parseFloat(
                    record.unit_price ? record.unit_price : 0
                ),
                tax_id: product[0].tax_id,
                tax_rate: selecteTax[0] ? selecteTax[0].rate : 0,
                tax_type: taxType,
                is_edited: true,
            };
            console.log("newData", newData);
            quantityChanged(newData, name);
        };

        // For Add Edit
        const onAddEditSubmit = () => {
            const record = selectedProducts.value.filter(
                (selectedProduct) =>
                    selectedProduct.xid == addEditFormData.value.id
            );

            const selecteTax = taxes.value.filter(
                (tax) => tax.xid == addEditFormData.value.tax_id
            );

            const taxType =
                addEditFormData.value.tax_type != undefined
                    ? addEditFormData.value.tax_type
                    : "exclusive";

            const newData = {
                ...record[0],
                discount_rate: parseFloat(addEditFormData.value.discount_rate),
                unit_price: parseFloat(addEditFormData.value.unit_price),
                tax_id: addEditFormData.value.tax_id,
                tax_rate: selecteTax[0] ? selecteTax[0].rate : 0,
                tax_type: taxType,
                is_edited: true,
            };
            quantityChanged(newData);
            onAddEditClose();
        };

        const onAddEditClose = () => {
            addEditFormData.value = {};
            addEditVisible.value = false;
        };

        // Customer
        const customerAdded = (xid) => {
            axiosAdmin.get(customerUrl).then((response) => {
                customers.value = response.data;
            });
            formData.value.user_id = xid;
        };

        const payNowSuccess = (invoiceOrder, res) => {
            resetPos1();
            var walkInCustomerId =
                posDefaultCustomer.value && posDefaultCustomer.value.xid
                    ? posDefaultCustomer.value.xid
                    : undefined;
            formData.value = {
                ...formData.value,
                user_id: walkInCustomerId,
            };

            reFetchProducts();
            payNowVisible.value = false;

            if (
                permsArray.value.includes("view_invoice") ||
                permsArray.value.includes("admin")
            ) {
                printInvoiceOrder.value = invoiceOrder;
                printInvoiceOrderInfo.value = res;
                printInvoiceModalVisible.value = true;
            }
            store.dispatch("auth/updateVisibleSubscriptionModules");
        };

        let selectedPayment;

        let SquarePaymentId = "";
        let UpiClientId = "";
        let PaytmClientId = "";

        let UpiCodeUrl;
        var UpiWindow;
        const cancelCardPayment = () => {
            if (
                SquarePaymentId != "" &&
                selectedWarehouse.value.is_card_gateway == 1
            ) {
                axiosAdmin
                    .post("pos/cancel_square_payment", {
                        id: SquarePaymentId,
                    })
                    .then((res) => {
                        cardPaymentStatus.value = res.checkout.status;
                        stopSquareInterval();
                        quickPayLoader.value = false;
                        cardLoader.value = false;
                        stopSquareInterval();
                        cardPaymentStatus.value = "PENDING";
                    })
                    .catch((err) => {
                        cardPaymentStatus.value = "CANCELED";
                        setTimeout(() => {
                            quickPayLoader.value = false;
                            cardLoader.value = false;
                            cardPaymentStatus.value = "PENDING";
                        }, 2000);
                    });
            }
        };

        const completeOrder = () => {
            paymentModes.value.filter((item) => {
                if (item.xid == formData.value.payment_mode_id) {
                    return (selectedPayment = item);
                }
            });
            customers.value.filter((item) => {
                if (item.xid == formData.value.user_id) {
                    return (selectedCustomer.value = item);
                }
            });

            if (
                selectedPayment.name.toLowerCase() == "card" &&
                selectedWarehouse.value.is_card_gateway == 1
            ) {
                cardLoader.value = true;
            }
            quickPayLoader.value = true;
            addEditRequestAdmin({
                url: "pos/payment",
                data: { ...formData.value, customer: selectedCustomer.value },
                success: (res) => {
                    allPaymentRecords.value = [
                        ...allPaymentRecords.value,
                        {
                            ...formData.value,
                            id: Math.random().toString(36).slice(2),
                        },
                    ];

                    if (
                        selectedPayment &&
                        selectedPayment.name.toLowerCase() == "card" &&
                        selectedWarehouse.value.is_card_gateway == 1 &&
                        res.checkout
                    ) {
                        let data = res.checkout;
                        quickPayLoader.value = true;
                        cardLoader.value = true;
                        SquarePaymentId = data.id;
                    } else if (
                        selectedPayment &&
                        selectedPayment.name.toLowerCase() == "upi gateway" &&
                        selectedWarehouse.value.upi_gateway == 1 &&
                        res &&
                        res.client_txn_id
                    ) {
                        let data = res.data.data;
                        UpiClientId = res.client_txn_id;
                        UpiCodeUrl = data.payment_url;
                        socket.emit(
                            "qrcode-send",
                            JSON.stringify({ ...data, status: "created" })
                        );
                        UpiWindow = window.open(
                            UpiCodeUrl,
                            "upiWindow",
                            "width=604,height=633"
                        );
                    } else if (
                        selectedPayment &&
                        selectedPayment.name.toLowerCase() == "paytm" &&
                        selectedWarehouse.value.paytm_gateway == 1
                        // &&
                        // res &&
                        // res.client_txn_id
                    ) {
                        console.log("Paytm");
                    } else {
                        setTimeout(() => {
                            saveOrder();
                        }, 1000);
                    }
                },
                error: (err) => {
                    quickPayLoader.value = false;
                    cardLoader.value = false;
                },
            });
        };

        const saveOrder = () => {
            const newFormDataObject = {
                all_payments: allPaymentRecords.value,
                product_items: selectedProducts.value,
                details: formData.value,
            };
            addEditRequestAdmin({
                url: "pos/save",
                data: newFormDataObject,
                success: (res) => {
                    if (formData.value.hasOwnProperty("draft_id")) {
                        deleteDraft(formData.value.draft_id);
                    }
                    quickPayLoader.value = false;
                    formData.value = {
                        amount: 0,
                        notes: "",
                    };
                    listDevices();
                    payNowSuccess(res.order, res);
                    cardPaymentStatus.value = "PENDING";

                    allPaymentRecords.value = [];
                    showAddForm.value = false;
                    emit("success", res.order);
                },
            });
        };

        const stopSquareInterval = () => {
            SquarePaymentId = "";
        };

        const stopUpiInterval = () => {
            UpiClientId = "";
        };

        setInterval(() => {
            if (
                SquarePaymentId != "" &&
                selectedWarehouse.value.is_card_gateway == 1
            ) {
                axiosAdmin
                    .post("pos/get_square_payment", {
                        id: SquarePaymentId,
                    })
                    .then((response) => {
                        cardPaymentStatus.value = response.checkout.status;
                        if (response.checkout.status == "CANCELED") {
                            quickPayLoader.value = false;
                            cardLoader.value = false;
                            stopSquareInterval();
                            cardPaymentStatus.value = "PENDING";
                        } else if (response.checkout.status == "COMPLETED") {
                            quickPayLoader.value = false;
                            cardLoader.value = false;
                            stopSquareInterval();
                            saveOrder();
                            cardPaymentStatus.value = "PENDING";
                        } else {
                            cardPaymentStatus.value = "PENDING";
                        }
                    })
                    .catch((error) => {
                        quickPayLoader.value = false;
                        cardLoader.value = false;
                        stopSquareInterval();
                        cardPaymentStatus.value = "PENDING";
                    });
            }

            if (UpiClientId != "") {
                axiosAdmin
                    .post("get-upi-payment", {
                        client_txn_id: UpiClientId,
                        txn_date: moment().format("DD-MM-YYYY"),
                    })
                    .then((result) => {
                        let data = result.data.data;
                        UPIPaymentStatus.value = data.status;
                        if (UPIPaymentStatus.value == "failure") {
                            quickPayLoader.value = false;
                            socket.emit("qrcode-send", JSON.stringify(data));
                            stopUpiInterval();
                            setTimeout(() => {
                                UpiWindow.close();
                            }, 5000);
                        }
                        if (UPIPaymentStatus.value == "success") {
                            saveOrder();
                            quickPayLoader.value = false;
                            socket.emit("qrcode-send", JSON.stringify(data));
                            stopUpiInterval();
                            setTimeout(() => {
                                UpiWindow.close();
                            }, 5000);
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }
        }, 5000);

        const barcodeReader = ref(false);
        const readedId = ref(null);

        const showBarcodereader = () => {
            barcodeReader.value = !barcodeReader.value;
        };

        const onDecode = (value) => {
            barcodeSearchTerm.value = value;
            barcodeReader.value = false;
        };

        const onLoaded = () => {};

        // For keeping the BarcodeSeach Input Box Focused afted the barecde is Scanned

        const focusInput = () => {
            if (barcodeSearchTerm.value !== "") {
                const inputElement = document.getElementById("bcinputFocus");
                if (inputElement) {
                    setTimeout(() => {
                        inputElement.focus();
                    }, 300);
                }
            }
        };

        const isOrderVisible = ref(false);

        const openLastOrder = () => {
            isOrderVisible.value = true;
        };
        const closeLastOrder = () => {
            isOrderVisible.value = false;
        };

        // Add a click event listener to the button

        watch(barcodeSearchTerm, focusInput);
        onMounted(focusInput);

        return {
            taxes,
            customers,
            categories,
            brands,
            productLists,
            formData,
            reFetchProducts,
            selectSaleProduct,

            allPaymentRecords,
            paymentRecordsColumns,

            taxChanged,
            quantityChanged,
            recalculateFinalTotal,
            customerChanged,
            // Pay Now
            payNow,
            payNowVisible,
            payNowClosed,
            resetPos,
            resetPos1,
            back,

            appSetting,
            permsArray,
            ...toRefs(state),
            fetchProducts,
            searchValueSelected,
            selectedProducts,
            orderItemColumns,
            orderItemColumns1,
            formatAmount,
            formatAmountCurrency,
            creditPoints,

            containerStyle: {
                height: window.innerHeight - 110 + "px",
                overflow: "scroll",
                "overflow-y": "scroll",
            },

            customerAdded,
            selectProduct,
            selectBarcode,
            // Add Edit
            editItem,
            addEditVisible,
            addEditFormData,
            addEditFormSubmitting,
            addEditRules,
            addEditPageTitle,
            onAddEditSubmit,
            onEditPrice,
            onAddEditClose,
            taxTypes,
            showDeleteConfirm,
            ProductAddButton,

            payNowSuccess,

            printInvoiceModalVisible,
            printInvoiceOrder,
            printInvoiceOrderInfo,
            customerBalance,

            postLayout,
            innerWidth: window.innerWidth,

            barcodeSearchTerm,
            searchBarcodeInput,
            barcodeFetch,
            rules,
            paymentModes,
            completeOrder,
            quickPayLoader,
            cardLoader,
            showBarcodereader,
            readedId,
            barcodeReader,
            onDecode,
            onLoaded,
            focusInput,
            getCreditBalance,
            location,
            cardPaymentStatus,
            cancelCardPayment,
            selectedPayment,
            selectedWarehouse,
            cardDevices,
            selectCardDevice,
            ProductAdded,
            isOrderVisible,
            openLastOrder,
            ViewLastOrder,
            closeLastOrder,
            DraftModal,
            openDraftModal,
            saveToDraft,
            DraftName,
            DraftModalLoading,
            DraftData,
            DraftColumns,
            DratToOrder,
            deleteDraft,
            setShippingPrice,
        };
    },
};
</script>

<style lang="less">
.right-pos-sidebar .ps {
    height: calc(100vh - 90px);
}

.right-icon {
    width: 15%;
    border: 1px solid #d9d9d9;
    border-left: 0px;
    height: 32px;

    span {
        padding-left: 14px;
        padding-top: 7px;
    }
}

.bill-body {
    height: 100%;
}

.bill-table {
    height: 100%;
}

.left-pos-top {
    .ant-card-body {
        padding-bottom: 0px;
    }
}

.left-pos-middle-table {
    height: calc(100vh - 185px);
    overflow-y: overlay;

    .ant-card-body {
        padding-bottom: 0px;
        padding-top: 0px;
    }
}

.pos-left-wrapper {
    display: flex;
    flex-direction: column;
}

.pos-left-content {
    flex: 1;
    overflow: auto;
}

.pos-left-footer {
    .ant-card-body {
        padding-bottom: 0px;
    }
}

.pos-grand-total_cap {
    font-size: 10px !important;
    font-weight: bold;
    line-height: 10px;
}

.pos-grand-total {
    font-size: 20px !important;
    font-weight: bold;
}

.pos1-left-wrapper {
    display: flex;
    flex-direction: column;
}

.pos1-left-content {
    flex: 1;
    overflow: auto;
}

.pos1-left-footer {
    .ant-card-body {
        padding-bottom: 0px;
    }
}

.pos-grand-total {
    font-size: 18px;
    font-weight: bold;
}

.pos1-products-lists {
    height: calc(100vh - 500px);
    overflow-y: overlay;

    img {
        height: 100px;
    }

    .product-pos {
        margin-top: 5px;
    }
}

.left-pos1-middle-table {
    height: calc(100vh - 500px);
    overflow-y: overlay;

    .ant-card-body {
        padding-bottom: 0px;
        padding-top: 0px;
    }
}

.pos1-bill-filters {
    .ant-card-body {
        padding: 10px 3px;
    }
}

.pos--footer {
    padding: 15px !important;
    width: 100%;
    background: white;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding-bottom: 0 !important;
}

.ant-btn-icon-only.ant-btn-sm {
    width: 18px;
    height: 18px;
    padding: 0px 0;
    font-size: 14px;
    border-radius: 2px;
}

.ant-btn-icon-only.ant-btn-sm > * {
    font-size: 12px;
}

.ant-card-body {
    padding: 5px !important;
}

.product-pos-top .quantity-box {
    position: absolute;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 15px;
    background-color: #fff;
    padding: 0 6px;
    left: 5px !important;
    top: 23px !important;
    font-size: 12px;
}

.product-pos-bottom {
    padding-left: 10px !important;
    padding-right: 10px !important;
    padding-bottom: 5px;
}

.product-pos-top img {
    height: 100px;
    max-width: 100%;
    width: 100%;
    -o-object-fit: cover;
    object-fit: cover;
    padding: 5px;
}

.pos--footer {
    padding: 10px !important;
    width: 100%;
    background: white;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding-bottom: 0 !important;
    height: 76px;
}

.ant-form-vertical .ant-form-item-label,
.ant-col-24.ant-form-item-label,
.ant-col-xl-24.ant-form-item-label {
    padding: 0px !important;
    line-height: 1.1715;
    white-space: initial;
    text-align: left;
}

.ant-table-thead > tr > th,
.ant-table-tbody > tr > td,
.ant-table tfoot > tr > th,
.ant-table tfoot > tr > td {
    position: relative;
    padding: 5px 5px;
    overflow-wrap: break-word;
}

.bill-table .ant-btn-icon-only {
    width: 25px;
    height: 25px;
    padding: 2.4px 0;
    font-size: 16px;
    border-radius: 2px;
    vertical-align: -3px;
}

.rightButt .ant-btn {
    width: 100%;
}

.rightButt .ant-btn {
    padding: 4px 4px !important;
    text-align: left !important;
}

.payment_loader {
    background: white;
    display: flex;
    justify-content: center;
}

.loader_img > img {
    width: 50%;
}

.loader_img {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 4rem;
}

@media only screen and (max-width: 600px) {
    .mobile {
        margin-top: 10px;
    }

    .product-pos-top img {
        height: 50px;
        max-width: 100%;
        width: 100%;
        -o-object-fit: cover;
        object-fit: cover;
        padding: 5px;
    }

    .product-pos-top .quantity-box {
        position: absolute;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 15px;
        background-color: #fff;
        padding: 0 6px;
        left: 18px !important;
        top: 8px !important;
        font-size: 12px;
    }

    .ant-input-number {
        box-sizing: border-box;
        font-variant: tabular-nums;
        list-style: none;
        font-feature-settings: "tnum";
        position: relative;
        width: 100%;
        min-width: 0;
        padding: 4px 11px;
        color: rgba(0, 0, 0, 0.85);
        font-size: 14px;
        line-height: 1.5715;
        background-color: #fff;
        background-image: none;
        transition: all 0.3s;
        display: inline-block;
        width: 60px;
        margin: 0;
        padding: 0;
        border: 1px solid #d9d9d9;
        border-radius: 2px;
    }
}

.pos-layout-2 {
    max-height: calc(100vh - 16vh);
    overflow: auto;
}
</style>
