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
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('role.display_name')"
                        name="display_name"
                        :help="
                            rules.display_name
                                ? rules.display_name.message
                                : null
                        "
                        :validateStatus="rules.display_name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.display_name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.display_name'),
                                ])
                            "
                            v-on:keyup="
                                formData.name = slugify($event.target.value)
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('role.role_name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.role_name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('role.description')"
                        name="description"
                        :help="
                            rules.description ? rules.description.message : null
                        "
                        :validateStatus="rules.description ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.description"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.description'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('role.permissions')"
                        name="permissions"
                        :help="
                            rules.permissions ? rules.permissions.message : null
                        "
                        :validateStatus="rules.permissions ? 'error' : null"
                    >
                        <div class="d-flex flex-column scroll-y">
                            <div class="tbl-responsive">
                                <!-- <a-checkbox-group
                                    v-model:value="checkedAllPermissions"
                                    @change="handleCheckAll"
                                >
                                    <div class="d-grid-span-4">
                                        <label
                                            class="form-check form-check-custom me-5 me-lg-10"
                                        >
                                            <a-checkbox
                                                :value="all"
                                            >
                                                {{ $t("common.edit") }} Discount
                                            </a-checkbox>
                                        </label>
                                    </div>
                                </a-checkbox-group> -->
                                <a-checkbox-group
                                    v-model:value="checkedPermissions"
                                >
                                    <table
                                        class="table align-middle table-row-dashed row-gap"
                                    >
                                        <tbody class="text-gray-600 fw-bold">
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.dashboard") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-3">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-10"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'dashboard'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                                {{
                                                                    $t(
                                                                        "menu.dashboard"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.staff") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-3">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-10"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'view_all_customer'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                                All Customers
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.pos") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-10"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'discount_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                                Discount
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-10"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'price_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                                Price
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-10"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'shipping_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                                Shipping
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'view_invoice'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                                Invoice
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.brands") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'brands_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'brands_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'brands_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'brands_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.categories") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'categories_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'categories_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'categories_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'categories_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.products") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.expense_categories"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.expenses") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.purchases") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchases_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchases_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchases_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchases_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.purchase_returns"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchase_returns_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchase_returns_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchase_returns_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'purchase_returns_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.sales") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.sales_returns")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_returns_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_returns_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_returns_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_returns_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.quotation") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'quotations_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'quotations_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'quotations_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'quotations_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.order_payments"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'order_payments_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'order_payments_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.payment_in") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_in_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_in_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_in_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_in_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.payment_out") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_out_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_out_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_out_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_out_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.stock_adjustment"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'stock_adjustments_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'stock_adjustments_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'stock_adjustments_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'stock_adjustments_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.pos") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'pos_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.chatbot") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'bot_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    Referral
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'referral_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.company") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'companies_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.storagfront_settingss"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'storage_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.email_settings"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.update_app") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom "
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'update_app'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.update_app"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr> -->

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.cash_bank") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'cash_bank_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.warehouses") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'warehouses_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'warehouses_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'warehouses_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'warehouses_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.translations")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.roles") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.taxes") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'taxes_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'taxes_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'taxes_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'taxes_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.currencies") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.payment_modes")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_modes_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_modes_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_modes_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_modes_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.units") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'units_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'units_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'units_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'units_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.custom_fields")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'custom_fields_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'custom_fields_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'custom_fields_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'custom_fields_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.staff_members")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.customers") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'customers_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'customers_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'customers_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'customers_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.suppliers") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'suppliers_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'suppliers_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'suppliers_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'suppliers_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.delivery_partner"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'delivery_partner_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'delivery_partner_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'delivery_partner_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'delivery_partner_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- New Added For online orders, E-com Products, E-com Settings -->
                                            <!--! Online Orders Starts -->
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.online_orders")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_orders_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_orders_view_details'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "stock.view_order"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_order_confirm'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.confirm"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_order_processing'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.processing"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-800"></td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_orders_shipping'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.shipping"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_orders_confirm_delivery'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "online_orders.confirm_delivery"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'online_order_cancel'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.cancel"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!--! Online Orders Ends -->

                                            <!--! E-Com Products Start -->
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t("menu.product_cards")
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'product_cards_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'product_cards_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.add"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'product_cards_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'product_cards_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.delete"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!--! E-Com Products Ends -->

                                            <!--! E-Com Settings Start -->
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{
                                                        $t(
                                                            "menu.front_settings"
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'front_settings_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.view"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'front_settings_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "common.edit"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!--! E-Com Settings Ends -->

                                            <!--! Reports & Analytics Start -->
                                            <tr>
                                                <td
                                                    class="text-gray-800"
                                                    style="display: flex"
                                                >
                                                    {{ $t("menu.reports") }}
                                                </td>
                                                <td>
                                                    <div class="d-grid-span-4">
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'payment_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.payments"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'stock_alert_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.stock_alert"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_summary_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.sales_summary"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'stock_summary_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.stock_summary"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'rate_list_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.rate_list"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'product_expiry_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.product_expiry"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'product_sales_summary_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.product_sales_summary"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'sales_margin_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.sales_margin"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'gst_report'
                                                                    ]
                                                                "
                                                            >
                                                                GST Report
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'user_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.users_reports"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'referral_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.referral_report"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'staff_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.staff"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'staff_onboarding_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.assigned_staff_report"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'day_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.daily_report"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'attendance_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.check_in_report"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'category_wise_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.category_report"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'customer_product_wise_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.customer_product_report"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'profit_loss_report'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "menu.profit_loss"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!--! Reports & Analytics Ends -->
                                        </tbody>
                                    </table>
                                </a-checkbox-group>
                            </div>
                        </div>
                    </a-form-item>
                </a-col>
            </a-row>
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
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";

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
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const roles = ref([]);
        const { t } = useI18n();
        const permissions = ref([]);
        const checkedPermissions = ref([]);
        const { slugify } = common();

        onMounted(() => {
            axiosAdmin
                .get("permissions?fields=id,xid,name,display_name&limit=10000")
                .then((response) => {
                    const permissionArray = [];
                    response.data.map((res) => {
                        permissionArray[res.name] = res.xid;
                    });
                    permissions.value = permissionArray;
                });
        });

        const onSubmit = () => {
            const newFormData = {
                ...props.formData,
                permissions: checkedPermissions.value,
            };

            addEditRequestAdmin({
                url: props.url,
                data: newFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (newVal && props.addEditType == "edit") {
                    props.data.perms.forEach((value) => {
                        checkedPermissions.value.push(value.xid);
                    });
                } else {
                    checkedPermissions.value = [];
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,
            permissions,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
            checkedPermissions,
            slugify,
        };
    },
});
</script>

<style lang="less">
.flex-column {
    flex-direction: column !important;
}

.d-flex {
    display: flex !important;
}

.tbl-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
}

.align-middle {
    vertical-align: middle !important;
}
.table > tbody {
    vertical-align: inherit;
}
.text-gray-600 {
    color: #7e8299 !important;
}
.fw-bold {
    font-weight: 500 !important;
}
tbody,
td,
tfoot,
th,
thead,
tr {
    border-color: inherit;
    border-style: solid;
    border-width: 0;
}

.table.table-row-dashed tr {
    border-bottom-width: 1px;
    border-bottom-style: dashed;
    border-bottom-color: #eff2f5;
}

.table td:first-child,
.table th:first-child,
.table tr:first-child {
    padding-left: 0;
}

.form-check.form-check-custom {
    display: flex;
    align-items: center;
    padding-left: 0;
    margin: 0;
}

.me-9 {
    margin-right: 2.25rem !important;
}

.table.row-gap td,
.table.row-gap th {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.me-lg-20 {
    margin-right: 5rem !important;
}
.me-lg-10 {
    margin-right: 1.73rem !important;
}

.ant-checkbox-group {
    width: 100%;
}

.d-grid-span-4 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 1rem;
    align-items: center;
}
.d-grid-span-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    align-items: center;
}
</style>
