<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.company`)" class="p-0">
                <template
                    v-if="
                        permsArray.includes('companies_edit') ||
                        permsArray.includes('admin')
                    "
                    #extra
                >
                    <a-space>
                        <a-button type="primary" @click="onSubmit">
                            <template #icon> <SaveOutlined /> </template>
                            {{ $t("common.update") }}
                        </a-button>
                        <CreateMenuSetting @success="addMenuSettingUpdated" />
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
                    {{ $t("menu.settings") }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.company") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-row>
        <a-col
            :xs="24"
            :sm="24"
            :md="24"
            :lg="4"
            :xl="4"
            class="bg-setting-sidebar"
        >
            <SettingSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <a-card class="page-content-container">
                <a-form layout="vertical">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('company.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.short_name')"
                                name="short_name"
                                :help="
                                    rules.short_name
                                        ? rules.short_name.message
                                        : null
                                "
                                :validateStatus="
                                    rules.short_name ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.short_name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('company.short_name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('company.email'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('company.phone')"
                                name="phone"
                                :help="rules.phone ? rules.phone.message : null"
                                :validateStatus="rules.phone ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.phone"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('company.phone'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="4" :lg="4">
                            <a-form-item
                                :label="$t('company.max_mobile_digit')"
                                name="max_mobile_digit"
                                :help="rules.max_mobile_digit ? rules.max_mobile_digit.message : null"
                                :validateStatus="rules.max_mobile_digit ? 'error' : null"
                                class="required"
                            >
                                <a-input-number
                                    v-model:value="formData.max_mobile_digit"
                                    max="14"
                                    min="6"
                                    style="width: 100%"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('company.max_mobile_digit'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('company.address')"
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
                                            $t('company.address'),
                                        ])
                                    "
                                    :rows="4"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.left_sidebar_theme')"
                                name="left_sidebar_theme"
                                :help="
                                    rules.left_sidebar_theme
                                        ? rules.left_sidebar_theme.message
                                        : null
                                "
                                :validateStatus="
                                    rules.left_sidebar_theme ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.left_sidebar_theme"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('company.left_sidebar_theme'),
                                        ])
                                    "
                                >
                                    <a-select-option key="dark" value="dark">
                                        {{ $t("company.dark") }}
                                    </a-select-option>
                                    <a-select-option
                                        v-if="appThemeMode != 'dark'"
                                        key="light"
                                        value="light"
                                    >
                                        {{ $t("company.light") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.primary_color')"
                                name="primary_color"
                                :help="
                                    rules.primary_color
                                        ? rules.primary_color.message
                                        : null
                                "
                                :validateStatus="
                                    rules.primary_color ? 'error' : null
                                "
                            >
                                <color-picker
                                    v-model:pureColor="formData.primary_color"
                                    format="hex"
                                    useType="pure"
                                    v-model:gradientColor="gradientColor"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="`${$t('company.dark_logo')} 400x180`"
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
                                    folder="company"
                                    imageField="dark_logo"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.dark_logo = file.file;
                                            formData.dark_logo_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="`${$t('company.light_logo')} 400x180`"
                                name="light_logo"
                                :help="
                                    rules.light_logo
                                        ? rules.light_logo.message
                                        : null
                                "
                                :validateStatus="
                                    rules.light_logo ? 'error' : null
                                "
                            >
                                <Upload
                                    :formData="formData"
                                    folder="company"
                                    imageField="light_logo"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.light_logo = file.file;
                                            formData.light_logo_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="`${$t(
                                    'company.small_dark_logo'
                                )} 400x180`"
                                name="small_dark_logo"
                                :help="
                                    rules.small_dark_logo
                                        ? rules.small_dark_logo.message
                                        : null
                                "
                                :validateStatus="
                                    rules.small_dark_logo ? 'error' : null
                                "
                            >
                                <Upload
                                    :formData="formData"
                                    folder="company"
                                    imageField="small_dark_logo"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.small_dark_logo =
                                                file.file;
                                            formData.small_dark_logo_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="`${$t(
                                    'company.small_light_logo'
                                )} 400x180`"
                                name="small_light_logo"
                                :help="
                                    rules.small_light_logo
                                        ? rules.small_light_logo.message
                                        : null
                                "
                                :validateStatus="
                                    rules.small_light_logo ? 'error' : null
                                "
                            >
                                <Upload
                                    :formData="formData"
                                    folder="company"
                                    imageField="small_light_logo"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.small_light_logo =
                                                file.file;
                                            formData.small_light_logo_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.currency')"
                                name="currency_id"
                                :help="
                                    rules.currency_id
                                        ? rules.currency_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.currency_id ? 'error' : null
                                "
                                class="required"
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.currency_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('company.currency'),
                                            ])
                                        "
                                        :allowClear="true"
                                        optionFilterProp="label"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="currency in currencies"
                                            :key="currency.xid"
                                            :value="currency.xid"
                                        >
                                            {{
                                                `${currency.name} (${currency.symbol})`
                                            }}
                                        </a-select-option>
                                    </a-select>
                                    <CurrencyAddButton
                                        @onAddSuccess="currencyAdded"
                                    />
                                </span>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('warehouse.warehouse')"
                                name="warehouse_id"
                                :help="
                                    rules.warehouse_id
                                        ? rules.warehouse_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.warehouse_id ? 'error' : null
                                "
                                class="required"
                            >
                                <span style="display: flex">
                                    <a-select
                                        v-model:value="formData.warehouse_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('warehouse.warehouse'),
                                            ])
                                        "
                                        :allowClear="true"
                                        optionFilterProp="label"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="warehouse in warehouses"
                                            :key="warehouse.xid"
                                            :value="warehouse.xid"
                                            :label="warehouse.name"
                                        >
                                            {{ warehouse.name }}
                                        </a-select-option>
                                    </a-select>
                                    <WarehouseAddButton
                                        @onAddSuccess="warehouseAdded"
                                    />
                                </span>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.layout')"
                                name="rtl"
                                :help="rules.rtl ? rules.rtl.message : null"
                                :validateStatus="rules.rtl ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.rtl"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('company.layout'),
                                        ])
                                    "
                                    :allowClear="true"
                                    optionFilterProp="label"
                                    show-search
                                >
                                    <a-select-option key="rtl" :value="1">
                                        {{ $t("company.rtl") }}
                                    </a-select-option>
                                    <a-select-option key="ltr" :value="0">
                                        {{ $t("company.ltr") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.shortcut_menu_Placement')"
                                name="shortcut_menus"
                                :help="
                                    rules.shortcut_menus
                                        ? rules.shortcut_menus.message
                                        : null
                                "
                                :validateStatus="
                                    rules.shortcut_menus ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.shortcut_menus"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t(
                                                'company.shortcut_menu_Placement'
                                            ),
                                        ])
                                    "
                                    :allowClear="true"
                                    optionFilterProp="label"
                                    show-search
                                >
                                    <a-select-option
                                        key="top_bottom"
                                        value="top_bottom"
                                    >
                                        {{ $t("company.top_and_bottom") }}
                                    </a-select-option>
                                    <a-select-option key="top" value="top">
                                        {{ $t("company.top_header") }}
                                    </a-select-option>
                                    <a-select-option
                                        key="bottom"
                                        value="bottom"
                                    >
                                        {{ $t("company.bottom_corner") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="4" :lg="4">
                            <a-form-item
                                :label="$t('company.auto_detect_timezone')"
                                name="auto_detect_timezone"
                                :help="
                                    rules.auto_detect_timezone
                                        ? rules.auto_detect_timezone.message
                                        : null
                                "
                                :validateStatus="
                                    rules.auto_detect_timezone ? 'error' : null
                                "
                            >
                                <a-switch
                                    v-model:checked="
                                        formData.auto_detect_timezone
                                    "
                                    :checkedValue="1"
                                    :unCheckedValue="0"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <a-form-item
                                :label="$t('company.default_timezone')"
                                name="timezone"
                                :help="
                                    rules.timezone
                                        ? rules.timezone.message
                                        : null
                                "
                                :validateStatus="
                                    rules.timezone ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.timezone"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('company.default_timezone'),
                                        ])
                                    "
                                    :allowClear="true"
                                    optionFilterProp="value"
                                    show-search
                                >
                                    <a-select-option
                                        v-for="(
                                            timezoneValue, timezoneKey
                                        ) in timezones"
                                        :key="timezoneKey"
                                        :value="timezoneValue"
                                    >
                                        {{ timezoneValue }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col
                            v-if="appType == 'single'"
                            :xs="24"
                            :sm="24"
                            :md="4"
                            :lg="4"
                        >
                            <a-form-item
                                :label="$t('company.app_debug')"
                                name="app_debug"
                                :help="
                                    rules.app_debug
                                        ? rules.app_debug.message
                                        : null
                                "
                                :validateStatus="
                                    rules.app_debug ? 'error' : null
                                "
                            >
                                <a-switch
                                    v-model:checked="formData.app_debug"
                                    :checkedValue="1"
                                    :unCheckedValue="0"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col
                            v-if="appType == 'single'"
                            :xs="24"
                            :sm="24"
                            :md="4"
                            :lg="4"
                        >
                            <a-form-item
                                :label="$t('company.update_app_notification')"
                                name="update_app_notification"
                                :help="
                                    rules.update_app_notification
                                        ? rules.update_app_notification.message
                                        : null
                                "
                                :validateStatus="
                                    rules.update_app_notification
                                        ? 'error'
                                        : null
                                "
                            >
                                <a-switch
                                    v-model:checked="
                                        formData.update_app_notification
                                    "
                                    :checkedValue="1"
                                    :unCheckedValue="0"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.date_format')"
                                name="date_format"
                                :help="
                                    rules.date_format
                                        ? rules.date_format.message
                                        : null
                                "
                                :validateStatus="
                                    rules.date_format ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.date_format"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('company.date_format'),
                                        ])
                                    "
                                    :allowClear="true"
                                    optionFilterProp="value"
                                    show-search
                                >
                                    <a-select-option
                                        v-for="(
                                            dateFormatValue, dateFormatKey
                                        ) in dateFormats"
                                        :key="dateFormatKey"
                                        :value="dateFormatValue"
                                    >
                                        {{
                                            `(${dateFormatKey}) => ` +
                                            dayjsObject().format(
                                                dateFormatValue
                                            )
                                        }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('company.time_format')"
                                name="time_format"
                                :help="
                                    rules.time_format
                                        ? rules.time_format.message
                                        : null
                                "
                                :validateStatus="
                                    rules.time_format ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.time_format"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('company.time_format'),
                                        ])
                                    "
                                    :allowClear="true"
                                    optionFilterProp="value"
                                    show-search
                                >
                                    <a-select-option
                                        v-for="(
                                            timeFormatValue, timeFormatKey
                                        ) in timeFormats"
                                        :key="timeFormatKey"
                                        :value="timeFormatKey"
                                    >
                                        {{
                                            `(${timeFormatValue}) => ` +
                                            dayjsObject().format(timeFormatKey)
                                        }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16" v-if="appType == 'single'">
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="$t('company.login_image')"
                                name="login_image"
                                :help="
                                    rules.login_image
                                        ? rules.login_image.message
                                        : null
                                "
                                :validateStatus="
                                    rules.login_image ? 'error' : null
                                "
                            >
                                <Upload
                                    :formData="formData"
                                    folder="company"
                                    imageField="login_image"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.login_image = file.file;
                                            formData.login_image_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="14" :sm="14" :md="14" :lg="14">
                            <a-form-item name="invoice_template">
                                <template #label>
                                    <p>
                                        {{ $t("company.sync_label") }}
                                    </p>
                                </template>
                                <a-select
                                    v-model:value="formData.auto_sync_time"
                                    mode="multiple"
                                    style="width: 100%"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('company.auto_sync_time'),
                                        ])
                                    "
                                    :open="false"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="8" :sm="8" :md="8" :lg="8">
                            <a-form-item name="invoice_template">
                                <template #label>
                                    <p>{{ $t("company.sync_time") }}</p>
                                </template>
                                <a-time-picker
                                    v-model:value="SyncTime"
                                    @change="handlePickTime"
                                    value-format="HH:mm:ss"
                                    :allowClear="false"
                                    :disabled="disableSyncTime"
                                    style="width: 100%"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="23" :lg="23">
                            <a-form-item name="invoice_template">
                                <template #label>
                                    <p>Invoice Template</p>
                                </template>

                                <div class="invoice_customizer_wrapper">
                                    <p>Header:</p>
                                    <div class="invoice_customizer">
                                        <a-checkbox
                                            v-for="(
                                                value, name, index
                                            ) in invoiceTemplate.header"
                                            :key="index"
                                            :checked="value"
                                            @change="
                                                (e) =>
                                                    changeInvoiceCheck(
                                                        e,
                                                        'header',
                                                        name
                                                    )
                                            "
                                        >
                                            {{ $t("company.invoice_" + name) }}
                                        </a-checkbox>
                                    </div>
                                    <div
                                        class="invoice_customizer_thanks"
                                        v-if="
                                            invoiceTemplate.header
                                                .company_full_name
                                        "
                                    >
                                        <p>Company Full Name:</p>
                                        <a-input
                                            v-model:value="
                                                invoiceTemplate.company_full_name
                                            "
                                        />
                                    </div>
                                    <p>Customer Details:</p>
                                    <div class="invoice_customizer">
                                        <a-checkbox
                                            v-for="(
                                                value, name, index
                                            ) in invoiceTemplate.customer_details"
                                            :key="index"
                                            :checked="value"
                                            @change="
                                                (e) =>
                                                    changeInvoiceCheck(
                                                        e,
                                                        'customer_details',
                                                        name
                                                    )
                                            "
                                        >
                                            {{ $t("company.invoice_" + name) }}
                                        </a-checkbox>
                                    </div>
                                    <p>Table Settings:</p>
                                    <div class="invoice_customizer">
                                        <a-checkbox
                                            v-for="(
                                                value, name, index
                                            ) in invoiceTemplate.table_setting"
                                            :key="index"
                                            :checked="value"
                                            @change="
                                                (e) =>
                                                    changeInvoiceCheck(
                                                        e,
                                                        'table_setting',
                                                        name
                                                    )
                                            "
                                        >
                                            {{ $t("company.invoice_" + name) }}
                                        </a-checkbox>
                                    </div>
                                    <p>Total Details:</p>
                                    <div class="invoice_customizer">
                                        <a-checkbox
                                            v-for="(
                                                value, name, index
                                            ) in invoiceTemplate.total_details"
                                            :key="index"
                                            :checked="value"
                                            @change="
                                                (e) =>
                                                    changeInvoiceCheck(
                                                        e,
                                                        'total_details',
                                                        name
                                                    )
                                            "
                                        >
                                            {{ $t("company.invoice_" + name) }}
                                        </a-checkbox>
                                    </div>
                                    <p>TAX Wise Calculation:</p>
                                    <div class="invoice_customizer">
                                        <a-checkbox
                                            v-for="(
                                                value, name, index
                                            ) in invoiceTemplate.tax_wise_calculations"
                                            :key="index"
                                            :checked="value"
                                            @change="
                                                (e) =>
                                                    changeInvoiceCheck(
                                                        e,
                                                        'tax_wise_calculations',
                                                        name
                                                    )
                                            "
                                        >
                                            {{ $t("company.invoice_" + name) }}
                                        </a-checkbox>
                                    </div>
                                    <p>Footer:</p>
                                    <div class="invoice_customizer">
                                        <a-checkbox
                                            v-for="(
                                                value, name, index
                                            ) in invoiceTemplate.footer"
                                            :key="index"
                                            :checked="value"
                                            @change="
                                                (e) =>
                                                    changeInvoiceCheck(
                                                        e,
                                                        'footer',
                                                        name
                                                    )
                                            "
                                        >
                                            {{ $t("company.invoice_" + name) }}
                                        </a-checkbox>
                                    </div>
                                    <div
                                        class="invoice_customizer_thanks"
                                        v-if="invoiceTemplate.footer.message"
                                    >
                                        <p>Thanks Message:</p>
                                        <a-input
                                            v-model:value="
                                                invoiceTemplate.thanks_message
                                            "
                                        />
                                    </div>
                                    <div class="invoice_customizer_thanks">
                                        <p>Invoice Default Font Size:</p>

                                        <a-select
                                            v-model:value="
                                                invoiceTemplate.font_size
                                            "
                                            style="width: 120px"
                                        >
                                            <a-select-option :value="7"
                                                >7</a-select-option
                                            >
                                            <a-select-option :value="8"
                                                >8</a-select-option
                                            >
                                            <a-select-option :value="9"
                                                >9</a-select-option
                                            >
                                            <a-select-option :value="10"
                                                >10</a-select-option
                                            >
                                            <a-select-option :value="11"
                                                >11</a-select-option
                                            >
                                            <a-select-option :value="12"
                                                >12</a-select-option
                                            >
                                        </a-select>
                                    </div>
                                </div>
                            </a-form-item>
                        </a-col>
                        <!-- <a-col :xs="24" :sm="24" :md="8" :lg="8">
                            <div class="receipt-contaner">
                                <div
                                    id="example_invoice"
                                    class="receipt-wrap receipt-four"
                                >
                                    <div class="receipt-top">
                                        <div
                                            class="receipt-seperator"
                                            v-if="invoiceTemplate.header.logo"
                                        ></div>
                                        <div
                                            class="company-logo"
                                            v-if="invoiceTemplate.header.logo"
                                        >
                                            <img
                                                class="invoice-logo"
                                                :src="appSetting.light_logo_url"
                                                :alt="appSetting.name"
                                            />
                                        </div>
                                        <div class="receipt-seperator"></div>
                                        <div
                                            class="receipt-title"
                                            v-if="
                                                invoiceTemplate.header
                                                    .company_name
                                            "
                                        >
                                            {{ formData.name }}
                                        </div>
                                        <div
                                            class="company-name"
                                            v-if="
                                                invoiceTemplate.header
                                                    .company_full_name
                                            "
                                        >
                                            {{
                                                invoiceTemplate.company_full_name
                                            }}
                                        </div>
                                        <div
                                            class="company-address"
                                            v-if="
                                                invoiceTemplate.header
                                                    .company_address
                                            "
                                        >
                                            {{ formData.address }}
                                        </div>
                                        <div
                                            class="company-email"
                                            v-if="
                                                invoiceTemplate.header
                                                    .company_email
                                            "
                                        >
                                            Email: {{ formData.email }}
                                        </div>
                                        <div
                                            class="company-no"
                                            v-if="
                                                invoiceTemplate.header
                                                    .company_no
                                            "
                                        >
                                            Phone: {{ formData.phone }}
                                        </div>
                                        <div
                                            class="company-no"
                                            v-if="
                                                invoiceTemplate.header.tax_no &&
                                                selectedWarehouse.gst_in_no
                                            "
                                        >
                                            {{ $t("tax.tax_no") }}:
                                            {{ selectedWarehouse.gst_in_no }}
                                        </div>
                                    </div>
                                    <div
                                        class="receipt-seperator"
                                        v-if="
                                            invoiceTemplate.header
                                                .recipt_title ||
                                            invoiceTemplate.header
                                                .company_full_name ||
                                            invoiceTemplate.header
                                                .company_address ||
                                            invoiceTemplate.header
                                                .company_email ||
                                            invoiceTemplate.header.company_no ||
                                            invoiceTemplate.header.company_name
                                        "
                                    ></div>
                                    <div
                                        class="receipt-title"
                                        v-if="
                                            invoiceTemplate.header.recipt_title
                                        "
                                    >
                                        Cash Receipt
                                    </div>
                                    <div
                                        class="receipt-seperator"
                                        v-if="
                                            invoiceTemplate.header.recipt_title
                                        "
                                    ></div>
                                    <ul class="customer-list">
                                        <li
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .name
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("stock.customer") }}:
                                            </div>
                                            <div class="desc">John Doe</div>
                                        </li>
                                        <li
                                            class="text-right me-0"
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .invoice_no
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("sales.invoice") }}:
                                            </div>
                                            <div class="desc">CS132453</div>
                                        </li>
                                        <li
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .address
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("user.address") }}:
                                            </div>
                                            <div class="desc">
                                                19, Abcd street, X city, B state
                                            </div>
                                        </li>
                                        <li
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .staff_name
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("stock.sold_by") }}:
                                            </div>
                                            <div class="desc">#LL93784</div>
                                        </li>
                                        <li
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .phone
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("common.phone") }}:
                                            </div>
                                            <div class="desc">
                                                +1 9009000990
                                            </div>
                                        </li>
                                        <li
                                            class="text-right me-0"
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .date
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("common.date") }}:
                                            </div>
                                            <div class="desc">01.07.2022</div>
                                        </li>
                                        <li
                                            v-if="
                                                invoiceTemplate.customer_details
                                                    .tax_no
                                            "
                                        >
                                            <div class="title">
                                                {{ $t("tax.tax_no") }}:
                                            </div>
                                            <div class="desc">32ABCD12EF</div>
                                        </li>
                                    </ul>
                                    <div
                                        class="receipt-seperator"
                                        v-if="
                                            invoiceTemplate.customer_details
                                                .date ||
                                            invoiceTemplate.customer_details
                                                .invoice_no ||
                                            invoiceTemplate.customer_details
                                                .name ||
                                            invoiceTemplate.customer_details
                                                .staff_name
                                        "
                                    ></div>
                                    <table class="receipt-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ $t("common.item") }}</th>
                                                <th
                                                    v-if="
                                                        invoiceTemplate
                                                            .table_setting.mrp
                                                    "
                                                >
                                                    {{ $t("common.mrp") }}
                                                </th>
                                                <th>{{ $t("common.qty") }}</th>
                                                <th>{{ $t("common.rate") }}</th>
                                                <th>
                                                    {{ $t("common.total") }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1.</td>
                                                <td>Sugarfree</td>
                                                <td
                                                    v-if="
                                                        invoiceTemplate
                                                            .table_setting.mrp
                                                    "
                                                >
                                                    $60
                                                </td>
                                                <td>3</td>
                                                <td>$50</td>
                                                <td>$150</td>
                                            </tr>
                                            <tr>
                                                <td>2.</td>
                                                <td>Onion (Loose) (5kg)</td>
                                                <td
                                                    v-if="
                                                        invoiceTemplate
                                                            .table_setting.mrp
                                                    "
                                                >
                                                    $60
                                                </td>
                                                <td>2</td>
                                                <td>$50</td>
                                                <td>$100</td>
                                            </tr>
                                            <tr>
                                                <td>3.</td>
                                                <td>
                                                    Mushrooms - Button 1 pack
                                                </td>
                                                <td
                                                    v-if="
                                                        invoiceTemplate
                                                            .table_setting.mrp
                                                    "
                                                >
                                                    $60
                                                </td>
                                                <td>3</td>
                                                <td>$50</td>
                                                <td>$150</td>
                                            </tr>
                                            <tr>
                                                <td>4.</td>
                                                <td>Tea 1kg</td>
                                                <td
                                                    v-if="
                                                        invoiceTemplate
                                                            .table_setting.mrp
                                                    "
                                                >
                                                    $60
                                                </td>
                                                <td>3</td>
                                                <td>$50</td>
                                                <td>$150</td>
                                            </tr>
                                            <tr>
                                                <td>5.</td>
                                                <td>
                                                    Diet Coke Soft Drink 300ml
                                                </td>
                                                <td
                                                    v-if="
                                                        invoiceTemplate
                                                            .table_setting.mrp
                                                    "
                                                >
                                                    $60
                                                </td>
                                                <td>3</td>
                                                <td>$50</td>
                                                <td>$150</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="receipt-seperator"></div>
                                    <div class="bill-list">
                                        <div
                                            class="bill_list_in"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .sub_total
                                            "
                                        >
                                            <div class="bill_title">
                                                Sub-Total:
                                            </div>
                                            <div class="bill_value">
                                                $700.00
                                            </div>
                                        </div>
                                        <div
                                            class="bill_list_in"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .discount
                                            "
                                        >
                                            <div class="bill_title">
                                                {{ $t("stock.discount") }}:
                                            </div>
                                            <div class="bill_value">
                                                -$50.00
                                            </div>
                                        </div>
                                        <div
                                            class="receipt-seperator"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .sub_total ||
                                                invoiceTemplate.total_details
                                                    .discount
                                            "
                                        ></div>
                                        <div
                                            class="bill_list_in"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .service_charges
                                            "
                                        >
                                            <div class="bill_title">
                                                {{ $t("stock.shipping") }}:
                                            </div>
                                            <div class="bill_value">0.00</div>
                                        </div>
                                        <div
                                            class="bill_list_in"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .tax
                                            "
                                        >
                                            <div class="bill_title">
                                                {{ $t("stock.order_tax") }}:
                                            </div>
                                            <div class="bill_value">$5.00</div>
                                        </div>
                                        <div
                                            class="receipt-seperator"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .service_charges ||
                                                invoiceTemplate.total_details
                                                    .tax
                                            "
                                        ></div>
                                        <div
                                            class="bill_list_in"
                                            v-if="
                                                invoiceTemplate.total_details
                                                    .due
                                            "
                                        >
                                            <div class="bill_title bill_focus">
                                                {{ $t("payments.due_amount") }}:
                                            </div>
                                            <div class="bill_value bill_focus">
                                                $0.00
                                            </div>
                                        </div>
                                        <div class="bill_list_in total-payable">
                                            <div class="bill_title bill_focus">
                                                {{ $t("common.total") }}:
                                            </div>
                                            <div class="bill_value bill_focus">
                                                $655.00
                                            </div>
                                        </div>
                                    </div>
                                    <div class="receipt-seperator"></div>
                                    <div
                                        class="receipt-seperator"
                                        v-if="
                                            invoiceTemplate
                                                .tax_wise_calculations.enabled
                                        "
                                    ></div>
                                    <div
                                        class="sample_text"
                                        v-if="
                                            invoiceTemplate
                                                .tax_wise_calculations.enabled
                                        "
                                    >
                                        <div class="receipt_footer_calculation">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Taxable Amt</th>
                                                        <th>
                                                            {{
                                                                $t("tax.c_tax")
                                                            }}
                                                            %
                                                        </th>
                                                        <th>
                                                            {{
                                                                $t("tax.c_tax")
                                                            }}
                                                            Amt
                                                        </th>
                                                        <th>
                                                            {{
                                                                $t("tax.s_tax")
                                                            }}
                                                            %
                                                        </th>
                                                        <th>
                                                            {{
                                                                $t("tax.s_tax")
                                                            }}
                                                            Amt
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>$590.00</td>
                                                        <td>{{ 10 / 2 }}%</td>
                                                        <td>$32.75</td>
                                                        <td>{{ 10 / 2 }}%</td>
                                                        <td>$32.75</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <b>$590.00</b>
                                                        </td>
                                                        <td></td>

                                                        <td>
                                                            <b>$32.75</b>
                                                        </td>
                                                        <td></td>
                                                        <td>
                                                            <b>$32.75</b>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="receipt-seperator"></div>
                                    <div
                                        class="sample_text"
                                        v-if="invoiceTemplate.footer.barcode"
                                    >
                                        <vue-barcode
                                            value="12332345698234592384"
                                            :options="{
                                                height: 25,
                                                width: 1,
                                                fontSize: 15,
                                            }"
                                            tag="svg"
                                        ></vue-barcode>
                                    </div>
                                    <div
                                        class="sample_text"
                                        v-if="invoiceTemplate.footer.qr_code"
                                    >
                                        <div v-if="selectedWarehouse.upi_id">
                                            <QrcodeVue
                                                :value="
                                                    QrCodeValue(
                                                        selectedWarehouse.upi_id,
                                                        0.0
                                                    )
                                                "
                                                level="H"
                                            />
                                            <p
                                                style="
                                                    font-size: 10px;
                                                    margin: 0;
                                                "
                                            >
                                                <QrcodeOutlined
                                                    style="color: #000"
                                                />
                                                {{ selectedWarehouse.upi_id }}
                                            </p>
                                        </div>
                                        <div v-else>
                                            <a-tag color="red"
                                                >Kindly update your UPI ID in
                                                the selected warehouse</a-tag
                                            >
                                        </div>
                                    </div>
                                    <div class="receipt-seperator"></div>
                                    <div
                                        class="sample_text"
                                        v-if="invoiceTemplate.footer.message"
                                    >
                                        {{ invoiceTemplate.thanks_message }}
                                    </div>
                                    <div
                                        class="watermark_text"
                                        v-if="invoiceTemplate.footer.watermark"
                                    >
                                        Powered by Jnana Inventive Pvt ltd.,
                                    </div>
                                </div>
                            </div>
                            <div class="footer-button">
                                <a-button
                                    type="primary"
                                    v-print="'#example_invoice'"
                                >
                                    <template #icon>
                                        <PrinterOutlined />
                                    </template>
                                    {{ $t("common.print_invoice") }}
                                </a-button>
                            </div>
                        </a-col> -->
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item>
                                <a-button
                                    type="primary"
                                    @click="onSubmit"
                                    :loading="loading"
                                >
                                    <template #icon>
                                        <SaveOutlined />
                                    </template>
                                    {{ $t("common.update") }}
                                </a-button>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-form>
            </a-card>
        </a-col>
    </a-row>
</template>
<script>
import { computed, onMounted, ref, watch } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    SaveOutlined,
    SettingOutlined,
    QuestionCircleOutlined,
    PrinterOutlined,
    QrcodeOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import { changeAntdTheme } from "mini-dynamic-antd-theme";
import { ColorPicker } from "vue3-colorpicker";
import "vue3-colorpicker/style.css";
import Upload from "../../../../common/core/ui/file/Upload.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import CurrencyAddButton from "../../common/settings/currency/AddButton.vue";
import WarehouseAddButton from "../warehouses/AddButton.vue";
import common from "../../../../common/composable/common";
import SettingSidebar from "../SettingSidebar.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import CreateMenuSetting from "./CreateMenuSetting.vue";
import QrcodeVue from "qrcode.vue";
import dayjs from "dayjs";

export default {
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        ExclamationCircleOutlined,
        SaveOutlined,
        SettingOutlined,
        ColorPicker,
        QuestionCircleOutlined,
        PrinterOutlined,

        Upload,
        CurrencyAddButton,
        WarehouseAddButton,
        SettingSidebar,
        AdminPageHeader,
        CreateMenuSetting,
        QrcodeVue,
        QrcodeOutlined,
    },
    setup() {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const {
            permsArray,
            appSetting,
            dayjsObject,
            appType,
            QrCodeValue,
            selectedWarehouse,
        } = common();
        const { t } = useI18n();
        const store = useStore();
        const formData = ref({});
        const currencies = ref([]);
        const warehouses = ref([]);
        const timezones = ref([]);
        const dateFormats = ref([]);
        const timeFormats = ref([]);
        const SyncTime = ref("00:00:00");
        const company = appSetting.value;
        const currencyUrl = "currencies?limit=10000";
        const warehouseUrl = "warehouses?limit=10000";
        const timezoneUrl = "timezones";
        const gradientColor = ref(
            "linear-gradient(0deg, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 1) 100%)"
        );
        const appThemeMode = window.config.theme_mode;

        const invoicevalues = ref(JSON.parse(company.invoice_template));
        const invoiceTemplate = ref({
            header: {
                logo: invoicevalues.value.header.logo,
                company_name: invoicevalues.value.header.company_name,
                recipt_title: invoicevalues.value.header.recipt_title,
                company_full_name: invoicevalues.value.header.company_full_name,
                company_address: invoicevalues.value.header.company_address,
                company_email: invoicevalues.value.header.company_email,
                company_no: invoicevalues.value.header.company_no,
                tax_no: invoicevalues.value.header.tax_no ? true : false,
            },
            customer_details: {
                name: invoicevalues.value.customer_details.name,
                invoice_no: invoicevalues.value.customer_details.invoice_no,
                staff_name: invoicevalues.value.customer_details.staff_name,
                date: invoicevalues.value.customer_details.date,
                address: invoicevalues.value.customer_details.address
                    ? true
                    : false,
                phone: invoicevalues.value.customer_details.phone
                    ? true
                    : false,
                tax_no: invoicevalues.value.customer_details.tax_no
                    ? true
                    : false,
            },
            total_details: {
                sub_total: invoicevalues.value.total_details.sub_total,
                discount: invoicevalues.value.total_details.discount,
                service_charges:
                    invoicevalues.value.total_details.service_charges,
                tax: invoicevalues.value.total_details.tax,
                due: invoicevalues.value.total_details.due,
            },
            table_setting: {
                mrp: invoicevalues.value.table_setting.mrp,
                saving_amount: invoicevalues.value.table_setting.saving_amount,
                units:
                    invoicevalues.value.table_setting &&
                    invoicevalues.value.table_setting.units
                        ? invoicevalues.value.table_setting.units
                        : false,
            },
            tax_wise_calculations: {
                enabled: invoicevalues.value.tax_wise_calculations.enabled,
            },
            footer: {
                barcode: invoicevalues.value.footer.barcode,
                message: invoicevalues.value.footer.message,
                watermark: invoicevalues.value.footer.watermark,
                qr_code: invoicevalues.value.footer.qr_code,
                total_item_count:
                    invoicevalues.value.footer &&
                    invoicevalues.value.footer.total_item_count
                        ? invoicevalues.value.footer.total_item_count
                        : false,
            },
            thanks_message: invoicevalues.value.thanks_message,
            company_full_name: invoicevalues.value.company_full_name,
            font_size:
                invoicevalues.value && invoicevalues.value.font_size
                    ? invoicevalues.value.font_size
                    : 10,
        });

        const changeInvoiceCheck = (e, parent, name) => {
            invoiceTemplate.value[parent][name] = e.target.checked;
        };

        onMounted(() => {
            const warehousePromise = axiosAdmin.get(warehouseUrl);
            const currencyPromise = axiosAdmin.get(currencyUrl);
            const timezonePromise = axiosAdmin.get(timezoneUrl);

            Promise.all([
                warehousePromise,
                currencyPromise,
                timezonePromise,
            ]).then(
                ([
                    warehousesResponse,
                    currenciesResponse,
                    timezonesResponse,
                ]) => {
                    warehouses.value = warehousesResponse.data;
                    currencies.value = currenciesResponse.data;
                    timezones.value = timezonesResponse.data.timezones;
                    dateFormats.value = timezonesResponse.data.date_formates;
                    timeFormats.value = timezonesResponse.data.time_formates;

                    setFormData();
                }
            );
        });

        const setFormData = () => {
            formData.value = {
                name: company.name,
                short_name: company.short_name,
                email: company.email,
                phone: company.phone,
                max_mobile_digit: company.max_mobile_digit,
                address: company.address,
                left_sidebar_theme: company.left_sidebar_theme,
                dark_logo: company.dark_logo,
                dark_logo_url: company.dark_logo_url,
                light_logo: company.light_logo,
                light_logo_url: company.light_logo_url,
                small_dark_logo: company.small_dark_logo,
                small_light_logo: company.small_light_logo,
                small_dark_logo_url: company.small_dark_logo_url,
                small_light_logo_url: company.small_light_logo_url,
                login_image: company.login_image,
                login_image_url: company.login_image_url,
                shortcut_menus: company.shortcut_menus,
                rtl: company.rtl,
                currency_id: company.x_currency_id,
                warehouse_id: company.x_warehouse_id,
                primary_color: company.primary_color,
                timezone: company.timezone,
                date_format: company.date_format,
                time_format: company.time_format,
                auto_detect_timezone: company.auto_detect_timezone,
                app_debug: company.app_debug,
                update_app_notification: company.update_app_notification,
                invoice_template: {},
                auto_sync_time: company.auto_sync_time
                    ? JSON.parse(company.auto_sync_time)
                    : [],
                _method: "PUT",
            };
        };

        const onSubmit = () => {
            formData.value.invoice_template = JSON.stringify(
                invoiceTemplate.value
            );
            addEditRequestAdmin({
                url: `companies/${company.xid}`,
                data: formData.value,
                successMessage: t("company.updated"),
                success: (res) => {
                    changeAntdTheme(formData.value.primary_color);
                    store.dispatch("auth/updateApp");
                },
            });
        };

        const currencyAdded = () => {
            axiosAdmin.get(currencyUrl).then((response) => {
                currencies.value = response.data;
            });
        };

        const warehouseAdded = () => {
            axiosAdmin.get(warehouseUrl).then((response) => {
                warehouses.value = response.data;
            });
        };

        const addMenuSettingUpdated = (menuPosition) => {
            formData.value.shortcut_menus = menuPosition;
        };

        const handlePickTime = (time) => {
            formData.value.auto_sync_time.push(time);
            SyncTime.value = "00:00:00";
        };

        const disableSyncTime = computed(() => {
            if (
                formData.value.auto_sync_time &&
                formData.value.auto_sync_time.length >= 6
            )
                return true;
            else return false;
        });

        return {
            appType,
            permsArray,
            formData,
            invoiceTemplate,
            invoicevalues,
            changeInvoiceCheck,
            loading,
            rules,
            warehouses,
            currencies,
            onSubmit,
            currencyAdded,
            warehouseAdded,
            gradientColor,
            timezones,
            dateFormats,
            timeFormats,
            appSetting,
            dayjsObject,
            appThemeMode,
            addMenuSettingUpdated,
            QrCodeValue,
            selectedWarehouse,
            handlePickTime,
            SyncTime,
            disableSyncTime,
        };
    },
};
</script>

<style>
.invoice_customizer_wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.invoice_customizer {
    display: grid;
    grid-template-columns: 25% 25% 25% 25%;
    gap: 15px;
}
.invoice_customizer .ant-checkbox-wrapper {
    margin-left: 0;
}
.invoice_customizer_wrapper > p {
    margin: 0;
    font-size: 0.9rem;
    font-weight: bold;
}
.invoice_customizer_thanks > p {
    margin: 0;
    font-size: 0.9rem;
    font-weight: bold;
}
.receipt-contaner {
    max-width: 350px;
    margin: 20px auto;
    box-shadow: 0px 1px 5px 2px #e1e1e1;
}
.receipt-wrap {
    max-width: 350px;
    padding: 10px;
    background-color: #fff;
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
}
.receipt-wrap .receipt-top {
    text-align: center;
}

.bill_list_in.total-payable {
    font-size: 18px;
}
.receipt-wrap .receipt-seperator {
    width: 100%;
    border-top: 1px dashed #000;
    margin: 2px 0;
    margin-left: auto;
}
.receipt-four .receipt-top .company-logo {
    background: none;
    margin-bottom: 0;
}
.receipt-wrap .receipt-top .company-logo {
    background: #f4f4f4;
    padding: 10px;
    text-align: center;
}
.receipt-four .receipt-top .company-logo img {
    vertical-align: middle;
    width: 90px;
    height: auto;
}

.receipt-title {
    text-align: center;
    font-size: 18px;
    color: #000;
}
.receipt-four .receipt-top .company-name {
    color: #95979b;
    text-transform: uppercase;
    font-size: 12px;
}
.receipt-wrap .receipt-top .company-name {
    color: #000;
    font-weight: bold;
    margin-bottom: 10px;
}
.company-address {
    white-space: break-spaces;
}
.receipt-wrap .receipt-top .company-address,
.receipt-wrap .receipt-top .company-email,
.receipt-four .receipt-top .company-no {
    font-size: 12px;
}
.receipt-four .customer-list {
    margin-top: 10px;
}
.receipt-wrap .customer-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
}
.receipt-wrap .customer-list li {
    display: flex;
    width: 48%;
    font-size: 12px;
    line-height: 1.2em;
    margin-bottom: 5px;
    margin-right: 5px;
    gap: 2px;
}

.receipt-wrap .customer-list li .title {
    font-weight: bold;
}
.receipt-four .receipt-table {
    margin-top: 0;
}
.receipt-wrap .receipt-table {
    width: 100%;
    line-height: 1.3em;
    font-size: 12px;
}
.receipt-table table {
    border-collapse: collapse;
    border-spacing: 0;
}
.receipt-table thead {
    border: 0;
    font-size: 100%;
    vertical-align: baseline;
}
.receipt-wrap .receipt-table td:first-child,
.receipt-wrap .receipt-table th:first-child {
    padding-left: 0;
}
.receipt-four .receipt-table thead th {
    border-top: 0;
}
.receipt-wrap .receipt-table thead th {
    color: #000;
    text-align: center;
    padding: 5px 3px;
    /* border-top: 1px dashed #000; */
    border-bottom: 1px dashed #000;
}
.receipt-wrap .receipt-table tr:first-child td {
    padding-top: 10px;
}
.receipt-wrap .receipt-table td:first-child,
.receipt-wrap .receipt-table th:first-child {
    padding-left: 0;
}
.receipt-wrap .receipt-table td {
    padding: 6px;
}
.receipt-wrap .bill-list {
    margin: 0;
    padding: 5px 0;
    font-size: 10px;
}
.receipt-wrap .bill-list .bill_list_in {
    display: flex;
    text-align: right;
    justify-content: space-between;
    padding: 4px 0;
}
.receipt-wrap .bill-list .bill_list_in .bill_title {
    padding-right: 20px;
}
.receipt-wrap .bill-list .bill_list_in .bill_value {
    width: 90px;
}
.bill_focus {
    font-weight: bold;
}
.receipt-wrap .sample_text {
    text-align: center;
    padding: 10px 0;
    border-bottom: 1px dashed #000;
    line-height: 1.6em;
    color: #000;
    font-size: 14px;
}
.receipt-wrap .watermark_text {
    text-align: center;
    padding: 10px 0;
    border-bottom: 1px dashed #000;
    line-height: 1.6em;
    color: #575757;
    font-size: 10px;
}
@media print {
    .receipt-wrap {
        width: 100%;
        transform: scale(0.9);
    }
    .receipt-wrap .receipt-seperator {
        width: 100%;
        border-top: 1px dashed #000 !important;
        margin: 2px 0;
        margin-left: auto;
    }
}
</style>
