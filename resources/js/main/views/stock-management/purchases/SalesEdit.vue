<template>
    <div class="loader" v-if="loader">
        <a-spin></a-spin>
    </div>
    <div v-else>
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
                        {{ $t(`common.edit`) }}
                    </a-breadcrumb-item>
                </a-breadcrumb>
            </template>
        </AdminPageHeader>

        <a-card class="page-content-container">
            <a-alert
                v-if="editOrderDisable"
                :description="$t('messages.not_able_to_edit_order')"
                type="warning"
                class="mb-30"
                showIcon
            />
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <!-- Select Customer -->
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t(`${orderPageObject.langKey}.user`)"
                                name="user_id"
                                :help="
                                    rules.user_id ? rules.user_id.message : null
                                "
                                :validateStatus="rules.user_id ? 'error' : null"
                                class="required"
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.user_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t(
                                                    `${orderPageObject.langKey}.user`
                                                ),
                                            ])
                                        "
                                        :disabled="editOrderDisable"
                                        :allowClear="true"
                                        optionFilterProp="title"
                                        @change="customerChanged"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="user in users"
                                            :key="user.xid"
                                            :value="user.xid"
                                            :title="user.name"
                                        >
                                            {{ user.name }}
                                            <span
                                                v-if="
                                                    user.phone &&
                                                    user.phone != ''
                                                "
                                            >
                                                <br />
                                                {{ user.phone }}
                                            </span>
                                        </a-select-option>
                                    </a-select>
                                    <SupplierAddButton
                                        v-if="
                                            orderPageObject.userType ==
                                            'suppliers'
                                        "
                                        @onAddSuccess="userAdded"
                                    />
                                    <CustomerAddButton
                                        v-else
                                        @onAddSuccess="userAdded"
                                    />
                                </span>
                                <a-typography-text
                                    v-if="
                                        selectedCustomer &&
                                        selectedCustomer.address
                                    "
                                >
                                    <b>Address:</b>
                                    {{ selectedCustomer.address }}
                                </a-typography-text>
                            </a-form-item>
                        </a-col>
                        <!-- Delivered To -->
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t(`stock.delivery_to`)"
                                name="delivery_to"
                                :help="
                                    rules.delivery_to
                                        ? rules.delivery_to.message
                                        : null
                                "
                                :validateStatus="
                                    rules.delivery_to ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.delivery_to"
                                    placeholder="Delivered To"
                                    :disabled="editOrderDisable"
                                    :auto-size="{ minRows: 2, maxRows: 5 }"
                                />
                            </a-form-item>
                        </a-col>
                        <!-- Staff Member -->
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                label="Billing Staff"
                                name="staff_id"
                                :help="
                                    rules.staff_id
                                        ? rules.staff_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.staff_id ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.staff_id"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t(`staff_member.staff`),
                                        ])
                                    "
                                    :disabled="editOrderDisable"
                                    :allowClear="true"
                                    optionFilterProp="title"
                                    show-search
                                >
                                    <a-select-option
                                        v-for="staff in staffs"
                                        :key="staff.xid"
                                        :value="staff.xid"
                                        :title="staff.name"
                                    >
                                        {{ staff.name }}
                                        <span
                                            v-if="
                                                staff.phone && staff.phone != ''
                                            "
                                        >
                                            <br />
                                            {{ staff.phone }}
                                        </span>
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <!-- Reference User -->
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                label="Referral"
                                name="referral_id"
                                :help="
                                    rules.referral_id
                                        ? rules.referral_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.referral_id ? 'error' : null
                                "
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.referral_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t(`staff_member.referral`),
                                            ])
                                        "
                                        :allowClear="true"
                                        optionFilterProp="title"
                                        :disabled="editOrderDisable"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="referral in referrals"
                                            :key="referral.xid"
                                            :value="referral.xid"
                                            :title="referral.name"
                                        >
                                            {{ referral.name }}
                                            <span
                                                v-if="
                                                    referral.phone &&
                                                    referral.phone != ''
                                                "
                                            >
                                                <br />
                                                {{ referral.phone }}
                                            </span>
                                        </a-select-option>
                                    </a-select>
                                    <ReferralAddButton
                                        @onAddSuccess="referralAdded"
                                    />
                                </span>
                            </a-form-item>
                        </a-col>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-row :gutter="16">
                                <!-- Invoice Number -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.invoice_number`)"
                                        name="invoice_number"
                                        :help="
                                            rules.invoice_number
                                                ? rules.invoice_number.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.invoice_number
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.invoice_number
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.invoice_number')]
                                                )
                                            "
                                            :disabled="true"
                                        />
                                        <small class="small-text-message">
                                            {{
                                                $t("stock.invoie_number_blank")
                                            }}
                                        </small>
                                    </a-form-item>
                                </a-col>
                                <!-- Billing Date -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="
                                            $t(
                                                `${orderPageObject.langKey}.${orderPageObject.langKey}_date`
                                            )
                                        "
                                        name="order_date"
                                        :help="
                                            rules.order_date
                                                ? rules.order_date.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.order_date ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-date-picker
                                            :disabled="true"
                                            v-model:value="formData.order_date"
                                            :format="formatOrderDate"
                                            :disabled-date="disabledDate"
                                            show-time
                                            :placeholder="
                                                $t('common.date_time')
                                            "
                                            style="width: 100%"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-row :gutter="16">
                                <!-- Place of Supply -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.place_of_supply`)"
                                        name="place_of_supply"
                                        :help="
                                            rules.place_of_supply
                                                ? rules.place_of_supply.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.place_of_supply
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.place_of_supply
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [
                                                        $t(
                                                            'stock.place_of_supply'
                                                        ),
                                                    ]
                                                )
                                            "
                                            :disabled="editOrderDisable"
                                        />
                                    </a-form-item>
                                </a-col>
                                <!-- Reverse Charges -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.reverse_charge`)"
                                        name="reverse_charge"
                                        :help="
                                            rules.reverse_charge
                                                ? rules.reverse_charge.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.reverse_charge
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.reverse_charge
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.reverse_charge')]
                                                )
                                            "
                                            :disabled="editOrderDisable"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-row :gutter="16">
                                <!-- GR/RR No -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.gr_rr_no`)"
                                        name="gr_rr_no"
                                        :help="
                                            rules.gr_rr_no
                                                ? rules.gr_rr_no.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.gr_rr_no ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.gr_rr_no"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.gr_rr_no')]
                                                )
                                            "
                                            :disabled="editOrderDisable"
                                        />
                                    </a-form-item>
                                </a-col>
                                <!-- Transport -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.transport`)"
                                        name="transport"
                                        :help="
                                            rules.transport
                                                ? rules.transport.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.transport ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.transport"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.transport')]
                                                )
                                            "
                                            :disabled="editOrderDisable"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-row :gutter="16">
                                <!-- Vehicle No -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.vechile_no`)"
                                        name="vechile_no"
                                        :help="
                                            rules.vechile_no
                                                ? rules.vechile_no.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.vechile_no ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.vechile_no"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.vechile_no')]
                                                )
                                            "
                                            :disabled="editOrderDisable"
                                        />
                                    </a-form-item>
                                </a-col>
                                <!-- Station -->
                                <a-col :xs="12" :sm="12" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t(`stock.station`)"
                                        name="station"
                                        :help="
                                            rules.station
                                                ? rules.station.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.station ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.station"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('stock.station')]
                                                )
                                            "
                                            :disabled="editOrderDisable"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                        <!-- Buyer Order No -->
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t(`stock.buyer_order_no`)"
                                name="buyer_order_no"
                                :help="
                                    rules.buyer_order_no
                                        ? rules.buyer_order_no.message
                                        : null
                                "
                                :validateStatus="
                                    rules.buyer_order_no ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.buyer_order_no"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('stock.buyer_order_no'),
                                        ])
                                    "
                                    :disabled="editOrderDisable"
                                />
                            </a-form-item>
                        </a-col>
                    </a-col>
                </a-row>
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('product.product')"
                            name="orderSearchTerm"
                            :help="
                                rules.product_items
                                    ? rules.product_items.message
                                    : null
                            "
                            :validateStatus="
                                rules.product_items ? 'error' : null
                            "
                        >
                            <span style="display: flex">
                                <a-select
                                    :disabled="editOrderDisable"
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
                                    :not-found-content="
                                        productFetching ? undefined : null
                                    "
                                    @search="fetchProducts"
                                    size="large"
                                    option-label-prop="label"
                                    @focus="products = []"
                                    @select="searchValueSelected"
                                >
                                    <template #suffixIcon
                                        ><SearchOutlined
                                    /></template>
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
                                    :disabled="editOrderDisable"
                                    v-model:value="barcodeSearchTerm"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.barcode'),
                                        ])
                                    "
                                    @keyup.enter="barcodeFetch"
                                />
                                <a-button
                                    v-if="!searchBarcodeInput"
                                    :disabled="editOrderDisable"
                                    size="large"
                                    @click="() => (searchBarcodeInput = true)"
                                    class="ml-5"
                                >
                                    <template #icon>
                                        <BarcodeOutlined />
                                    </template>
                                    <slot></slot>
                                </a-button>
                                <a-button
                                    v-else
                                    :disabled="editOrderDisable"
                                    size="large"
                                    @click="() => (searchBarcodeInput = false)"
                                    class="ml-5"
                                >
                                    <template #icon>
                                        <SearchOutlined />
                                    </template>
                                    <slot></slot>
                                </a-button>
                                <ProductAddButton size="large" />
                            </span>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-table
                            :row-key="(record) => record.xid"
                            :dataSource="selectedProducts"
                            :columns="orderItemColumns"
                            :pagination="false"
                            class="sales-product-list"
                        >
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.dataIndex === 'name'">
                                    {{ record.name }} <br />
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
                                    v-if="column.dataIndex === 'identity_code'"
                                >
                                    <a-select
                                        v-model:value="record.identity_code"
                                        mode="tags"
                                        style="width: 200px"
                                        :open="false"
                                        :disabled="editOrderDisable"
                                    ></a-select>
                                </template>
                                <template
                                    v-if="column.dataIndex === 'unit_quantity'"
                                >
                                    <a-input-number
                                        id="inputNumber"
                                        v-model:value="record.quantity"
                                        @change="quantityChanged(record)"
                                        :min="1"
                                        :disabled="editOrderDisable"
                                    />
                                </template>
                                <template
                                    v-if="
                                        column.dataIndex === 'single_unit_price'
                                    "
                                >
                                    <a-input-number
                                        v-model:value="record.unit_price"
                                        style="width: 120px"
                                        @change="changeRate(record)"
                                        :min="0"
                                        :disabled="editOrderDisable"
                                    >
                                        <template #addonBefore>
                                            {{ appSetting.currency.symbol }}
                                        </template>
                                    </a-input-number>

                                    <a-tooltip
                                        v-if="
                                            record.Last_selling <
                                                record.unit_price &&
                                            record.Last_selling != 'NaN' &&
                                            record.Last_selling != 0
                                        "
                                        :title="$t('payments.you_will_pay')"
                                    >
                                        <a-typography-text>
                                            <ArrowUpOutlined
                                                :style="{ color: 'green' }"
                                            />
                                            Last Sale :
                                            {{
                                                formatAmountCurrency(
                                                    Math.abs(
                                                        record.Last_selling
                                                    )
                                                )
                                            }}
                                        </a-typography-text>
                                    </a-tooltip>
                                    <a-tooltip
                                        v-if="
                                            record.Last_selling >
                                                record.unit_price &&
                                            record.Last_selling != 'NaN' &&
                                            record.Last_selling != 0
                                        "
                                        :title="$t('payments.you_will_receive')"
                                    >
                                        <a-typography-text>
                                            <span
                                                v-if="
                                                    record.Last_selling >
                                                    record.unit_price
                                                "
                                            >
                                                <ArrowDownOutlined
                                                    :style="{ color: 'red' }"
                                                />
                                            </span>
                                            Last Sale :
                                            {{
                                                formatAmountCurrency(
                                                    record.Last_selling
                                                )
                                            }}
                                        </a-typography-text>
                                    </a-tooltip>
                                    <a-tooltip
                                        v-if="
                                            record.Last_selling ==
                                                record.unit_price &&
                                            record.Last_selling != 'NaN' &&
                                            record.Last_selling != 0
                                        "
                                        :title="$t('payments.you_will_pay')"
                                    >
                                        <a-typography-text>
                                            <br />
                                            Last Sale :
                                            {{
                                                formatAmountCurrency(
                                                    Math.abs(
                                                        record.Last_selling
                                                    )
                                                )
                                            }}
                                        </a-typography-text>
                                    </a-tooltip>
                                </template>
                                <template
                                    v-if="column.dataIndex === 'total_discount'"
                                >
                                    <a-input-group compact style="width: 120px">
                                        <a-select
                                            v-model:value="record.discount_type"
                                            style="width: 30%"
                                            :disabled="editOrderDisable"
                                            @change="
                                                (value) => {
                                                    record.discount_type =
                                                        value;
                                                    record.discount = 0;
                                                    record.discount_rate = 0;
                                                    record.total_discount = 0;
                                                    changeRate(record);
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
                                            :disabled="editOrderDisable"
                                            @change="changeRate(record)"
                                            :min="0"
                                            style="width: 70%"
                                        />
                                    </a-input-group>
                                </template>
                                <template
                                    v-if="column.dataIndex === 'total_tax'"
                                >
                                    {{ formatAmountCurrency(record.total_tax) }}
                                </template>
                                <template v-if="column.dataIndex === 'mrp'">
                                    {{
                                        record.mrp
                                            ? formatAmountCurrency(record.mrp)
                                            : "N/A"
                                    }}
                                </template>
                                <template
                                    v-if="column.dataIndex === 'subtotal'"
                                >
                                    {{ formatAmountCurrency(record.subtotal) }}
                                </template>
                                <template v-if="column.dataIndex === 'action'">
                                    <a-button
                                        type="primary"
                                        @click="showDeleteConfirm(record)"
                                        :disabled="editOrderDisable"
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
                                        :col-span="
                                            selectedWarehouse.show_mrp_on_invoice ==
                                            1
                                                ? 6
                                                : 5
                                        "
                                    ></a-table-summary-cell>
                                    <a-table-summary-cell>
                                        {{ $t("product.subtotal") }}
                                    </a-table-summary-cell>
                                    <a-table-summary-cell>
                                        {{
                                            formatAmountCurrency(
                                                productsAmount.tax
                                            )
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
                                    :help="
                                        rules.notes ? rules.notes.message : null
                                    "
                                    :validateStatus="
                                        rules.notes ? 'error' : null
                                    "
                                >
                                    <a-textarea
                                        v-model:value="formData.notes"
                                        :placeholder="$t('stock.notes')"
                                        :auto-size="{ minRows: 2, maxRows: 3 }"
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <a-form-item label="Grand Total In Wordss">
                                    <a-input
                                        :value="
                                            numberToWords(formData.subtotal)
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <a-form-item label="Bank Details">
                                    <a-input
                                        :value="
                                            selectedWarehouse &&
                                            selectedWarehouse.bank_details
                                                ? selectedWarehouse.bank_details
                                                : 'No Bank Details Is Provided'
                                        "
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
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <a-form-item :label="$t('stock.discount')">
                                    <a-input-group compact>
                                        <a-select
                                            :disabled="editOrderDisable"
                                            v-model:value="
                                                formData.discount_type
                                            "
                                            @change="recalculateFinalTotal"
                                            style="width: 30%"
                                        >
                                            <a-select-option value="percentage">
                                                %
                                            </a-select-option>
                                            <a-select-option value="fixed">
                                                {{ appSetting.currency.symbol }}
                                            </a-select-option>
                                        </a-select>
                                        <a-input-number
                                            v-model:value="
                                                formData.discount_value
                                            "
                                            :disabled="editOrderDisable"
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
                        </a-row>
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
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('stock.shipping')]
                                            )
                                        "
                                        :disabled="editOrderDisable"
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
                            <a-col :span="24">
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
                        </a-row>
                        <a-row :gutter="16" class="mt-20">
                            <a-button
                                type="primary"
                                :loading="loading"
                                @click="onSubmit"
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
    </div>
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
    {{ setShippingPrice }}
</template>
<style>
.loader {
    text-align: center !important;
    padding-top: 50px;
}
</style>

<script>
import { computed, watch, onBeforeUnmount, onMounted, ref, toRefs } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    SearchOutlined,
    SaveOutlined,
    BarcodeOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import apiAdmin from "../../../../common/composable/apiAdmin";
import stockManagement from "./stockManagement";
import common from "../../../../common/composable/common";
import fields from "./fields";
import SupplierAddButton from "../../users/SupplierAddButton.vue";
import CustomerAddButton from "../../users/CustomerAddButton.vue";
import ReferralAddButton from "../../users/ReferralAddButton.vue";
import TaxAddButton from "../../settings/taxes/AddButton.vue";
import WarehouseAddButton from "../../settings/warehouses/AddButton.vue";
import ProductAddButton from "../../product-manager/products/AddButton.vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { useStore } from "vuex";
import dayjs from "dayjs";
import InvoiceModal from "../invoice-template/a4/A4Invoice.vue";
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
        ReferralAddButton,
        TaxAddButton,
        WarehouseAddButton,
        ProductAddButton,
        DateTimePicker,
        AdminPageHeader,
        ArrowUpOutlined,
        ArrowDownOutlined,
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
            numberToWords,
            formatDateTime,
            disabledDate,
            user,
        } = common();
        const { orderItemColumns } = fields();
        const {
            changeRate,
            isCustomerWholesale,
            calculateShippingPrice,

            state,
            orderType,
            orderPageObject,
            route,
            selectedProducts,
            selectedProductIds,
            formData,
            productsAmount,
            taxes,

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
            removedOrderItemsIds,

            calculateProductAmount,

            barcodeSearchTerm,
            searchBarcodeInput,
            barcodeFetch,
        } = stockManagement();
        const { t } = useI18n();
        const users = ref([]);
        const staffs = ref([]);
        const referrals = ref([]);
        const warehouses = ref([]);
        const allUnits = ref([]);
        const orderId = route.params.id;
        const router = useRouter();
        const allOrderStatus = ref([]);
        const taxUrl = "taxes?limit=10000";
        const unitUrl = "units?limit=10000";
        const warehouseUrl = `warehouses?filters=id ne "${selectedWarehouse.value.xid}"&hashable=${selectedWarehouse.value.xid}&limit=10000`;
        const usersUrl = `${orderPageObject.value.userType}?limit=10000`;
        const staffUrl = `users?user_type=staff_members&fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,pincode,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&limit=10000`;
        const referralUrl = `referral?fields=id,xid,user_type,name,email,profile_image,profile_image_url,tax_number,is_walkin_customer,phone,address,shipping_address,status,created_at,details{opening_balance,opening_balance_type,credit_period,credit_limit,due_amount,warehouse_id,x_warehouse_id},details:warehouse{id,xid,name},role_id,role{id,xid,name,display_name},warehouse_id,x_warehouse_id,warehouse{xid,name}&order=id desc&offset=0&limit=1000`;
        const quickPayLoader = ref(false);
        const paymentModes = ref([]);
        const loader = ref(true);
        const editOrderDisable = ref(false);

        const printInvoiceModalVisible = ref(false);
        const printInvoiceOrder = ref({});
        const selectedCustomer = ref({});

        onMounted(() => {
            const orderPromise = axiosAdmin.get(
                `${orderType.value}/${orderId}`
            );
            const taxesPromise = axiosAdmin.get(taxUrl);
            const unitsPromise = axiosAdmin.get(unitUrl);
            const usersPromise = axiosAdmin.get(usersUrl);
            const staffPromise = axiosAdmin.get(staffUrl);
            const referralPromise = axiosAdmin.get(referralUrl);
            const warehousesPromise = axiosAdmin.get(warehouseUrl);

            axiosAdmin.get("payment-modes").then((response) => {
                paymentModes.value = response.data;

                formData.value = {
                    ...formData.value,
                    payment_mode_id: response.data[0].xid,
                };
            });

            Promise.all([
                orderPromise,
                usersPromise,
                taxesPromise,
                unitsPromise,
                warehousesPromise,
                staffPromise,
                referralPromise,
            ]).then(
                ([
                    orderResponse,
                    usersResponse,
                    taxResponse,
                    unitResponse,
                    warehousesResponse,
                    staffResponse,
                    referralResponse,
                ]) => {
                    const orderResponseData = orderResponse.data;
                    formData.value = {
                        invoice_number: orderResponseData.order.invoice_number,
                        order_date: orderResponseData.order.order_date,
                        delivery_to: orderResponseData.order.delivery_to,
                        staff_id: orderResponseData.order.x_staff_id,
                        referral_id: orderResponseData.order.x_referral_id,
                        place_of_supply:
                            orderResponseData.order.place_of_supply,
                        reverse_charge: orderResponseData.order.reverse_charge,
                        gr_rr_no: orderResponseData.order.gr_rr_no,
                        transport: orderResponseData.order.transport,
                        vechile_no: orderResponseData.order.vechile_no,
                        station: orderResponseData.order.station,
                        buyer_order_no: orderResponseData.order.buyer_order_no,
                        user_id: orderResponseData.order.x_user_id,
                        warehouse_id: orderResponseData.order.x_warehouse_id,
                        notes: orderResponseData.order.notes,
                        terms_condition:
                            orderResponseData.order.terms_condition,
                        order_status: orderResponseData.order.order_status,
                        tax_id: orderResponseData.order.x_tax_id,
                        tax_rate: orderResponseData.order.tax_rate,
                        tax_amount: orderResponseData.order.tax_amount
                            ? orderResponseData.order.tax_amount
                            : 0,
                        discount: orderResponseData.order.discount
                            ? orderResponseData.order.discount
                            : 0,
                        discount_type: orderResponseData.order.discount_type
                            ? orderResponseData.order.discount_type
                            : "fixed",
                        discount_value: orderResponseData.order.discount
                            ? orderResponseData.order.discount
                            : 0,
                        shipping: orderResponseData.order.shipping,
                        subtotal: orderResponseData.order.total,
                        order_type: orderResponseData.order.order_type,
                    };
                    selectedProductIds.value = orderResponseData.ids;
                    selectedProducts.value = orderResponseData.items.map(
                        (item) => {
                            return {
                                ...item,
                                identity_code: item.identity_code
                                    ? item.identity_code.split(",")
                                    : [],
                                discount_type: item.discount_type
                                    ? item.discount_type
                                    : "fixed",
                                discount: item.discount ? item.discount : 0,
                            };
                        }
                    );
                    formData.value.discount_type = "fixed";
                    formData.value.discount_value = 0;
                    calculateProductAmount();
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
                    taxes.value = taxResponse.data;
                    allUnits.value = unitResponse.data;
                    staffs.value = staffResponse.data;
                    warehouses.value = warehousesResponse.data;
                    referrals.value = referralResponse.data;

                    const BilledDate = dayjs(
                        orderResponseData.order.order_date
                    ).format("YYYY-MM-DD");
                    const CurrentDate = dayjs();
                    const DiffInDays = CurrentDate.diff(BilledDate, "day");

                    if (DiffInDays > 90) {
                        editOrderDisable.value = true;
                    }
                    loader.value = false;
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
                    selectedCustomer.value = users.value[i];
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

        // Added For Calculating Shipping Amount Based on the selected Products
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

        const onSubmit = () => {
            const newFormDataObject = {
                ...formData.value,
                total: formData.value.subtotal,
                total_items: selectedProducts.value.length,
                product_items: selectedProducts.value.map((item) => {
                    return {
                        ...item,
                        identity_code: item.identity_code
                            ? item.identity_code.toString()
                            : item.identity_code,
                    };
                }),
                removed_items: removedOrderItemsIds.value,
                _method: "PUT",
            };

            addEditRequestAdmin({
                url: `${orderType.value}/${orderId}`,
                data: newFormDataObject,
                successMessage: t(`${orderPageObject.value.langKey}.updated`),
                success: (res) => {
                    router.push({
                        name: `admin.stock.${orderType.value}.index`,
                    });
                },
            });
        };

        const userAdded = () => {
            axiosAdmin.get(usersUrl).then((response) => {
                users.value = response.data;
            });
        };
        const referralAdded = () => {
            axiosAdmin.get(referralUrl).then((response) => {
                referrals.value = response.data;
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

        const formatOrderDate = (newValue) => {
            return newValue ? formatDateTime(newValue) : undefined;
        };

        return {
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

            orderItemColumns,
            formatOrderDate,

            // Add Edit
            addEditVisible,
            addEditFormData,
            changeRate,
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
            paymentModes,
            printInvoiceModalVisible,
            printInvoiceOrder,
            numberToWords,
            selectedWarehouse,
            staffs,
            referrals,
            referralAdded,
            disabledDate,
            loader,
            customerChanged,
            setShippingPrice,
            selectedCustomer,
            formatAmount,
            editOrderDisable,
        };
    },
};
</script>
<style>
/* .sales-product-list tbody > tr:nth-child(1) {
    background: #eaeaea !important;
} */
</style>
