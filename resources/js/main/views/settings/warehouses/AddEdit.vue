<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :visible="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <a-tabs v-model:activeKey="activeKey">
                <a-tab-pane key="basic_details">
                    <template #tab>
                        <span>
                            <FileTextOutlined />
                            {{ $t("warehouse.basic_details") }}
                        </span>
                    </template>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('warehouse.name')"
                                        name="name"
                                        :help="
                                            rules.name
                                                ? rules.name.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.name ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.name"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('warehouse.name')]
                                                )
                                            "
                                            v-on:keyup="
                                                formData.slug = slugify(
                                                    $event.target.value
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('warehouse.slug')"
                                        name="slug"
                                        :help="
                                            rules.slug
                                                ? rules.slug.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.slug ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.slug"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('warehouse.slug')]
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="16" :lg="16">
                                    <a-form-item
                                        :label="$t('warehouse.email')"
                                        name="email"
                                        :help="
                                            rules.email
                                                ? rules.email.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.email ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.email"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('warehouse.email')]
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                                    <a-form-item
                                        :label="
                                            $t(
                                                'warehouse.show_email_on_invoice'
                                            )
                                        "
                                        name="show_email_on_invoice"
                                        :help="
                                            rules.show_email_on_invoice
                                                ? rules.show_email_on_invoice
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.show_email_on_invoice
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-switch
                                            v-model:checked="
                                                formData.show_email_on_invoice
                                            "
                                            :checkedValue="1"
                                            :unCheckedValue="0"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="16" :lg="16">
                                    <a-form-item
                                        :label="$t('warehouse.phone')"
                                        name="phone"
                                        :help="
                                            rules.phone
                                                ? rules.phone.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.phone ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.phone"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('warehouse.phone')]
                                                )
                                            "
                                        />
                                    </a-form-item>
                                    <a-form-item
                                        :label="$t('warehouse.upi_id')"
                                        name="upi_id"
                                        :help="
                                            rules.upi_id
                                                ? rules.upi_id.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.upi_id ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.upi_id"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('warehouse.upi_id')]
                                                )
                                            "
                                        />
                                    </a-form-item>
                                    <a-form-item
                                        :label="$t('warehouse.gst_in_no')"
                                        name="gst_in_no"
                                        :help="
                                            rules.gst_in_no
                                                ? rules.gst_in_no.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.gst_in_no ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.gst_in_no"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('warehouse.gst_in_no')]
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                                    <a-form-item
                                        :label="
                                            $t(
                                                'warehouse.show_phone_on_invoice'
                                            )
                                        "
                                        name="show_phone_on_invoice"
                                        :help="
                                            rules.show_phone_on_invoice
                                                ? rules.show_phone_on_invoice
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.show_phone_on_invoice
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-switch
                                            v-model:checked="
                                                formData.show_phone_on_invoice
                                            "
                                            :checkedValue="1"
                                            :unCheckedValue="0"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-row :gutter="16">
                                <a-col :span="24">
                                    <a-form-item
                                        :label="`${$t(
                                            'warehouse.logo'
                                        )} 400x180`"
                                        name="logo"
                                        :help="
                                            rules.logo
                                                ? rules.logo.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.logo ? 'error' : null
                                        "
                                    >
                                        <Upload
                                            :formData="formData"
                                            folder="warehouses"
                                            imageField="logo"
                                            @onFileUploaded="
                                                (file) => {
                                                    formData.logo = file.file;
                                                    formData.logo_url =
                                                        file.file_url;
                                                }
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-row :gutter="16">
                                <a-col :span="24">
                                    <a-form-item
                                        :label="`${$t(
                                            'warehouse.dark_logo'
                                        )} 400x180`"
                                        name="dark_logo"
                                        :help="
                                            rules.dark_logo
                                                ? rules.dark_logo.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.dark_logo ? 'error' : null
                                        "
                                    >
                                        <Upload
                                            :formData="formData"
                                            folder="warehouses"
                                            imageField="dark_logo"
                                            @onFileUploaded="
                                                (file) => {
                                                    formData.dark_logo =
                                                        file.file;
                                                    formData.dark_logo_url =
                                                        file.file_url;
                                                }
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.address')"
                                name="address"
                                :help="
                                    rules.address ? rules.address.message : null
                                "
                                :validateStatus="rules.address ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.address"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('warehouse.address'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 2, maxRows: 3 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item>
                                <a-typography-paragraph type="warning" strong>
                                    <blockquote>
                                        {{
                                            $t(
                                                "warehouse.details_will_be_shown_on_invoice"
                                            )
                                        }}
                                    </blockquote>
                                </a-typography-paragraph>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.bank_details')"
                                name="bank_details"
                                :help="
                                    rules.bank_details
                                        ? rules.bank_details.message
                                        : null
                                "
                                :validateStatus="
                                    rules.bank_details ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.bank_details"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('warehouse.bank_details'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 2, maxRows: 3 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

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
                                        $t('common.placeholder_default_text', [
                                            $t('warehouse.terms_condition'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 2, maxRows: 3 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="8" :sm="24" :md="24" :lg="8">
                            <a-form-item name="invoice_template">
                                <template #label>
                                    <p>
                                        {{ $t("warehouse.add_pincode") }}
                                    </p>
                                </template>
                                <a-select
                                    v-model:value="formData.pincode"
                                    name="pincode"
                                    mode="tags"
                                    style="width: 100%"
                                    :placeholder="$t('warehouse.enter_pincode')"
                                    @change="ValidatePincode"
                                    :open="false"
                                />
                            </a-form-item>
                        </a-col>

                        <a-col :xs="8" :sm="24" :md="24" :lg="8">
                            <a-form-item name="invoice_template">
                                <template #label>
                                    <p>
                                        {{ $t("warehouse.add_location") }}
                                    </p>
                                </template>
                                <a-select
                                    v-model:value="formData.location"
                                    mode="tags"
                                    style="width: 100%"
                                    :placeholder="
                                        $t('warehouse.enter_location')
                                    "
                                    :open="false"
                                />
                            </a-form-item>
                        </a-col>

                        <a-col :xs="8" :sm="24" :md="24" :lg="8">
                            <a-form-item name="invoice_template">
                                <template #label>
                                    <p>
                                        {{ $t("warehouse.add_businesstype") }}
                                    </p>
                                </template>
                                <a-select
                                    v-model:value="formData.business_type"
                                    mode="tags"
                                    style="width: 100%"
                                    :placeholder="
                                        $t('warehouse.enter_businesstype')
                                    "
                                    :open="false"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :span="12">
                            <a-form-item
                                :label="$t('warehouse.signature')"
                                name="signature"
                                :help="
                                    rules.signature
                                        ? rules.signature.message
                                        : null
                                "
                                :validateStatus="
                                    rules.signature ? 'error' : null
                                "
                            >
                                <Upload
                                    :formData="formData"
                                    folder="warehouses"
                                    imageField="signature"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.signature = file.file;
                                            formData.signature_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item
                                :label="$t('warehouse.qr_code')"
                                name="qr_code"
                                :help="
                                    rules.qr_code ? rules.qr_code.message : null
                                "
                                :validateStatus="rules.qr_code ? 'error' : null"
                            >
                                <Upload
                                    :formData="formData"
                                    folder="warehouses"
                                    imageField="qr_code"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.qr_code = file.file;
                                            formData.qr_code_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-tab-pane>
                <a-tab-pane key="visibility">
                    <template #tab>
                        <span>
                            <EyeOutlined />
                            {{ $t("warehouse.visibility") }}
                        </span>
                    </template>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.customers_visibility')"
                                name="customers_visibility"
                                :help="
                                    rules.customers_visibility
                                        ? rules.customers_visibility.message
                                        : null
                                "
                                :validateStatus="
                                    rules.customers_visibility ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="
                                        formData.customers_visibility
                                    "
                                >
                                    <a-radio :style="radioStyle" value="all">
                                        {{ $t("warehouse.view_all_customers") }}
                                    </a-radio>
                                    <a-radio
                                        :style="radioStyle"
                                        value="warehouse"
                                    >
                                        {{
                                            $t(
                                                "warehouse.view_warehouse_customers"
                                            )
                                        }}
                                    </a-radio>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.suppliers_visibility')"
                                name="suppliers_visibility"
                                :help="
                                    rules.suppliers_visibility
                                        ? rules.suppliers_visibility.message
                                        : null
                                "
                                :validateStatus="
                                    rules.suppliers_visibility ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="
                                        formData.suppliers_visibility
                                    "
                                >
                                    <a-radio :style="radioStyle" value="all">
                                        {{ $t("warehouse.view_all_products") }}
                                    </a-radio>
                                    <a-radio
                                        :style="radioStyle"
                                        value="warehouse"
                                    >
                                        {{
                                            $t(
                                                "warehouse.view_warehouse_products"
                                            )
                                        }}
                                    </a-radio>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.products_visibility')"
                                name="products_visibility"
                                :help="
                                    rules.products_visibility
                                        ? rules.products_visibility.message
                                        : null
                                "
                                :validateStatus="
                                    rules.products_visibility ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.products_visibility"
                                >
                                    <a-radio :style="radioStyle" value="all">
                                        {{ $t("warehouse.view_all_products") }}
                                    </a-radio>
                                    <a-radio
                                        :style="radioStyle"
                                        value="warehouse"
                                    >
                                        {{
                                            $t(
                                                "warehouse.view_warehouse_products"
                                            )
                                        }}
                                    </a-radio>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.ecom_visibility')"
                                name="ecom_visibility"
                                :help="
                                    rules.ecom_visibility
                                        ? rules.ecom_visibility.message
                                        : null
                                "
                                :validateStatus="
                                    rules.ecom_visibility ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.ecom_visibility"
                                >
                                    <a-radio :style="radioStyle" :value="0">
                                        All users can access E-com products
                                    </a-radio>
                                    <a-radio :style="radioStyle" :value="1">
                                        Only Login users can access E-com
                                        products
                                    </a-radio>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.is_login_document')"
                                name="is_login_document"
                                :help="
                                    rules.is_login_document
                                        ? rules.is_login_document.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_login_document ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_login_document"
                                >
                                    <a-radio :style="radioStyle" :value="0">
                                        No need to submit addition document
                                    </a-radio>
                                    <a-radio :style="radioStyle" :value="1">
                                        User Should Submit Addition Document for
                                        Login
                                    </a-radio>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-tab-pane>
                <a-tab-pane key="pos_settings">
                    <template #tab>
                        <span>
                            <SettingOutlined />
                            {{ $t("menu.pos_settings") }}
                        </span>
                    </template>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <a-form-item
                                :label="
                                    $t('warehouse.default_pos_order_status')
                                "
                                name="default_pos_order_status"
                                :help="
                                    rules.default_pos_order_status
                                        ? rules.default_pos_order_status.message
                                        : null
                                "
                                :validateStatus="
                                    rules.default_pos_order_status
                                        ? 'error'
                                        : null
                                "
                            >
                                <a-select
                                    v-model:value="
                                        formData.default_pos_order_status
                                    "
                                    :placeholder="
                                        $t('warehouse.default_pos_order_status')
                                    "
                                    style="width: 100%"
                                >
                                    <a-select-option
                                        v-for="orderStatus in salesOrderStatus"
                                        :key="orderStatus.key"
                                        :value="orderStatus.key"
                                    >
                                        {{ orderStatus.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('warehouse.default_view')"
                                name="set_pos_type"
                                :help="
                                    rules.set_pos_type
                                        ? rules.set_pos_type.message
                                        : null
                                "
                                :validateStatus="
                                    rules.set_pos_type ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.set_pos_type"
                                    :placeholder="$t('warehouse.default_view')"
                                    style="width: 100%"
                                >
                                    <a-select-option
                                        v-for="modelType in modelTypes"
                                        :key="modelType.key"
                                        :value="modelType.key"
                                    >
                                        {{ modelType.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('warehouse.default_invoice')"
                                name="default_invoice"
                                :help="
                                    rules.default_invoice
                                        ? rules.default_invoice.message
                                        : null
                                "
                                :validateStatus="
                                    rules.default_invoice ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.default_invoice"
                                    :placeholder="
                                        $t('warehouse.default_invoice')
                                    "
                                    style="width: 100%"
                                >
                                    <a-select-option
                                        v-for="invoiceType in invoiceTypes"
                                        :key="invoiceType.key"
                                        :value="invoiceType.key"
                                    >
                                        {{ invoiceType.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :span="8">
                            <a-form-item
                                :label="$t('warehouse.show_mrp_on_invoice')"
                                name="show_mrp_on_invoice"
                                :help="
                                    rules.show_mrp_on_invoice
                                        ? rules.show_mrp_on_invoice.message
                                        : null
                                "
                                :validateStatus="
                                    rules.show_mrp_on_invoice ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.show_mrp_on_invoice"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item
                                :label="
                                    $t('warehouse.show_discount_tax_on_invoice')
                                "
                                name="show_discount_tax_on_invoice"
                                :help="
                                    rules.show_discount_tax_on_invoice
                                        ? rules.show_discount_tax_on_invoice
                                              .message
                                        : null
                                "
                                :validateStatus="
                                    rules.show_discount_tax_on_invoice
                                        ? 'error'
                                        : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="
                                        formData.show_discount_tax_on_invoice
                                    "
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item
                                :label="$t('warehouse.is_last_history')"
                                name="is_last_history"
                                :help="
                                    rules.is_last_history
                                        ? rules.is_last_history.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_last_history ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_last_history"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :span="8">
                            <a-form-item
                                label="Staff Base Customer"
                                name="is_staff_base"
                                :help="
                                    rules.is_staff_base
                                        ? rules.is_staff_base.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_staff_base ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_staff_base"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item
                                label="Enable Billing"
                                name="is_billing"
                                :help="
                                    rules.is_billing
                                        ? rules.is_billing.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_billing ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_billing"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item
                                label="Allow Negative Stock"
                                name="is_negative_stock"
                                :help="
                                    rules.is_negative_stock
                                        ? rules.is_negative_stock.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_negative_stock ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_negative_stock"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item
                                label="Deactivate Printing Capability for User Account on Mobile POS"
                                name="mobile_print_invoice"
                                :help="
                                    rules.mobile_print_invoice
                                        ? rules.mobile_print_invoice.message
                                        : null
                                "
                                :validateStatus="
                                    rules.mobile_print_invoice ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="
                                        formData.mobile_print_invoice
                                    "
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :span="12">
                            <a-form-item
                                :label="$t('warehouse.is_app_online')"
                                name="is_app_online"
                                :help="
                                    rules.is_app_online
                                        ? rules.is_app_online.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_app_online ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_app_online"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.online") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.offline") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :span="24">
                            <p>
                                {{ $t("common.invoice_number_preview") }}

                                <a-typography-text code>
                                    {{ formData.prefix_invoice
                                    }}{{ formData.invoice_spliter
                                    }}{{ formData.first_invoice_no
                                    }}{{ formData.invoice_spliter
                                    }}{{ formData.suffix_invoice }}
                                </a-typography-text>
                            </p>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :span="7">
                            <a-form-item
                                name="prefix_invoice"
                                :help="
                                    rules.prefix_invoice
                                        ? rules.prefix_invoice.message
                                        : null
                                "
                                :validateStatus="
                                    rules.prefix_invoice ? 'error' : null
                                "
                            >
                                <a-input
                                    :maxlength="5"
                                    v-model:value="formData.prefix_invoice"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            'Prefix',
                                        ])
                                    "
                                    @input="validateInvoicePrefix"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="3">
                            <a-form-item
                                name="invoice_spliter"
                                :help="
                                    rules.invoice_spliter
                                        ? rules.invoice_spliter.message
                                        : null
                                "
                                :validateStatus="
                                    rules.invoice_spliter ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.invoice_spliter"
                                    placeholder="Symbol"
                                    style="width: 100%"
                                >
                                    <a-select-option
                                        v-for="symbol in invoiceSymbol"
                                        :key="symbol.key"
                                        :value="symbol.key"
                                    >
                                        {{ symbol.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :span="7">
                            <a-form-item
                                name="first_invoice_no"
                                :help="
                                    rules.first_invoice_no
                                        ? rules.first_invoice_no.message
                                        : null
                                "
                                :validateStatus="
                                    rules.first_invoice_no ? 'error' : null
                                "
                            >
                                <a-input
                                    :maxlength="5"
                                    v-model:value="formData.first_invoice_no"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            'Start With Number',
                                        ])
                                    "
                                    @input="validateInvoiceNo"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="7">
                            <a-form-item
                                name="suffix_invoice"
                                :help="
                                    rules.suffix_invoice
                                        ? rules.suffix_invoice.message
                                        : null
                                "
                                :validateStatus="
                                    rules.suffix_invoice ? 'error' : null
                                "
                                @input="validateInvoiceSuffix"
                            >
                                <a-input
                                    :maxlength="10"
                                    v-model:value="formData.suffix_invoice"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            'Suffix',
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :span="8">
                            <a-form-item
                                label="Reset Invoice Month"
                                name="reset_invoice"
                                :help="
                                    rules.reset_invoice
                                        ? rules.reset_invoice.message
                                        : null
                                "
                                :validateStatus="
                                    rules.reset_invoice ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.reset_invoice"
                                    :placeholder="
                                        $t('common.rest_invoice_month', [
                                            'Suffix',
                                        ])
                                    "
                                    style="width: 100%"
                                    :disabled="true"
                                >
                                    <a-select-option
                                        v-for="reset in resetInvoice"
                                        :key="reset.key"
                                        :value="reset.key"
                                    >
                                        {{ reset.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item>
                                <a-typography-paragraph type="warning" strong>
                                    <blockquote>
                                        <!-- Note: Every "{{
                                            formData.reset_invoice
                                        }}" The Invoice Data will be Reset. -->
                                        {{ $t("common.rest_invoice_uc") }}
                                    </blockquote>
                                </a-typography-paragraph>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-tab-pane>
                <a-tab-pane key="payment_setting">
                    <template #tab>
                        <span>
                            <CreditCardOutlined />
                            {{ $t("menu.payment_setting") }}
                        </span>
                    </template>
                    <a-descriptions title="Square Gateway" />
                    <a-row :gutter="16">
                        <a-col :xs="8" :sm="8" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('warehouse.is_card_gateway')"
                                name="is_card_gateway"
                                :help="
                                    rules.is_card_gateway
                                        ? rules.is_card_gateway.message
                                        : null
                                "
                                :validateStatus="
                                    rules.is_card_gateway ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.is_card_gateway"
                                    size="small"
                                    buttonStyle="solid"
                                    @change="handleCardGateway"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="16" :sm="16" :md="16" :lg="16">
                            <a-form-item
                                :label="$t('warehouse.cardpayment_method')"
                                name="cardpayment_method"
                                :help="
                                    rules.cardpayment_method
                                        ? rules.cardpayment_method.message
                                        : null
                                "
                                :validateStatus="
                                    rules.cardpayment_method ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.cardpayment_method"
                                    placeholder="Please Select a card Method"
                                    style="width: 100%"
                                    :disabled="formData.is_card_gateway == 0"
                                >
                                    <a-select-option value="square">
                                        Square
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('warehouse.square_access_key')"
                                name="square_access_key"
                                :help="
                                    rules.square_access_key
                                        ? rules.square_access_key.message
                                        : null
                                "
                                :validateStatus="
                                    rules.square_access_key ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.square_access_key"
                                    :placeholder="`Please enter the ${
                                        formData.cardpayment_method
                                            ? formData.cardpayment_method
                                            : 'access'
                                    } key`"
                                    style="width: 100%"
                                    :disabled="formData.is_card_gateway == 0"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-descriptions title="UPI Gateway" />
                    <a-row :gutter="16">
                        <a-col :xs="8" :sm="8" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('warehouse.upi_gateway')"
                                name="upi_gateway"
                                :help="
                                    rules.upi_gateway
                                        ? rules.upi_gateway.message
                                        : null
                                "
                                :validateStatus="
                                    rules.upi_gateway ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.upi_gateway"
                                    size="small"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-descriptions title="Paytm Gateway" />
                    <a-row :gutter="16">
                        <a-col :xs="8" :sm="8" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('warehouse.paytm_gateway')"
                                name="paytm_gateway"
                                :help="
                                    rules.paytm_gateway
                                        ? rules.paytm_gateway.message
                                        : null
                                "
                                :validateStatus="
                                    rules.paytm_gateway ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.paytm_gateway"
                                    size="small"
                                    buttonStyle="solid"
                                    @change="handleCardGateway"
                                >
                                    <a-radio-button :value="1">
                                        {{ $t("common.yes") }}
                                    </a-radio-button>
                                    <a-radio-button :value="0">
                                        {{ $t("common.no") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-tab-pane>
                <a-tab-pane key="product_setting">
                    <template #tab>
                        <span>
                            <CreditCardOutlined />
                            {{ $t("menu.product_setting") }}
                        </span>
                    </template>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('warehouse.product_tax_type')"
                                name="product_tax_type"
                                :help="
                                    rules.product_tax_type
                                        ? rules.product_tax_type.message
                                        : null
                                "
                                :validateStatus="
                                    rules.product_tax_type ? 'error' : null
                                "
                            >
                                <a-radio-group
                                    v-model:value="formData.product_tax_type"
                                    buttonStyle="solid"
                                >
                                    <a-radio-button value="inclusive">
                                        {{ $t("common.with_tax") }}
                                    </a-radio-button>
                                    <a-radio-button value="exclusive">
                                        {{ $t("common.without_tax") }}
                                    </a-radio-button>
                                    <a-radio-button value="both">
                                        {{ $t("common.both") }}
                                    </a-radio-button>
                                </a-radio-group>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-tab-pane>
            </a-tabs>
        </a-form>
        <template #footer>
            <a-button
                type="primary"
                @click="onSubmit"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, reactive } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    FileTextOutlined,
    EyeOutlined,
    SettingOutlined,
    CreditCardOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import Upload from "../../../../common/core/ui/file/Upload.vue";
import common from "../../../../common/composable/common";
import { useStore } from "vuex";

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
    emits: ["addEditSuccess"],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        FileTextOutlined,
        EyeOutlined,
        SettingOutlined,
        Upload,
        CreditCardOutlined,
    },
    setup(props, { emit }) {
        const store = useStore();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const {
            slugify,
            salesOrderStatus,
            invoiceSymbol,
            resetInvoice,
            selectedWarehouse,
        } = common();
        const activeKey = ref("basic_details");
        const radioStyle = reactive({
            display: "flex",
            height: "30px",
            lineHeight: "30px",
        });

        const modelTypes = [
            { key: "1", value: "Model 1" },
            { key: "2", value: "Model 2" },
            { key: "3", value: "Model 3" },
        ];
        const invoiceTypes = [
            { key: "a4_invoice", value: "A4 Invoice" },
            { key: "a5_invoice", value: "A5 Invoice" },
        ];

        const validateInvoiceNo = (event) => {
            const inputValue = event.target.value;
            // Remove all non-numeric characters from the input value
            const numericValue = inputValue.replace(/[^0-9]/g, "");

            // Update the input field with the cleaned numeric value
            props.formData.first_invoice_no = numericValue;
        };

        const validateInvoiceSuffix = (event) => {
            const inputValue = event.target.value;
            // Remove all non-numeric characters from the input value
            const charValue = inputValue.replace(/[^a-zA-Z0-9-]/g, "");

            // Update the input field with the cleaned numeric value
            props.formData.suffix_invoice = charValue;
        };
        const validateInvoicePrefix = (event) => {
            const inputValue = event.target.value;
            // Remove all non-numeric characters from the input value
            const charValue = inputValue.replace(/[^a-zA-Z0-9]/g, "");

            // Update the input field with the cleaned numeric value
            props.formData.prefix_invoice = charValue;
        };

        const handleCardGateway = (e) => {
            props.formData.is_card_gateway = e.target.value;
            props.formData.square_access_key = "";
            props.formData.cardpayment_method = "";
        };

        const onSubmit = () => {
            let data = {
                ...props.formData,
                pincode: props.formData.pincode.toString(),
                location: props.formData.location.toString(),
                business_type: props.formData.business_type.toString(),
            };

            addEditRequestAdmin({
                url: props.url,
                data: data,
                successMessage: props.successMessage,
                success: (res) => {
                    store.dispatch("auth/updateAllWarehouses");

                    if (
                        selectedWarehouse.value &&
                        selectedWarehouse.value.xid &&
                        selectedWarehouse.value.xid == res.xid
                    ) {
                        axiosAdmin
                            .post("change-warehouse", {
                                warehouse_id: res.xid,
                            })
                            .then((response) => {
                                store.commit(
                                    "auth/updateWarehouse",
                                    response.data.warehouse
                                );
                            });
                    }

                    emit("addEditSuccess", res.xid);

                    activeKey.value = "basic_details";
                    rules.value = {};
                },
            });
        };

        const ValidatePincode = (value) => {
            if (value.length > 0) {
                const currentIndex = value.length - 1;
                // Pattern for Validating Length Min 4 Max 6 && Contains only Number
                if (/^\d{4,6}$/.test(props.formData.pincode[currentIndex])) {
                    props.formData.pincode = value;
                } else {
                    props.formData.pincode.pop();
                }
            }
        };
        const onClose = () => {
            activeKey.value = "basic_details";
            rules.value = {};
            emit("closed");
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            slugify,
            activeKey,
            modelTypes,
            salesOrderStatus,
            invoiceSymbol,
            resetInvoice,
            radioStyle,
            validateInvoiceNo,
            validateInvoiceSuffix,
            validateInvoicePrefix,
            handleCardGateway,
            invoiceTypes,
            ValidatePincode,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "65%",
        };
    },
});
</script>
