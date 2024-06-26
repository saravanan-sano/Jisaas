import Admin from "../../common/layouts/Admin.vue";
import Payments from "../views/reports/payments/index.vue";
import StockAlert from "../views/reports/stock-alert/index.vue";
import Users from "../views/reports/users/index.vue";
import CashBank from "../views/reports/cash-bank/index.vue";
import SalesSummrary from "../views/reports/sales-summary/index.vue";
import StockSummrary from "../views/reports/stock-summary/index.vue";
import ReferralReport from "../views/reports/referral-sales-report/Index.vue";
import StaffReport from "../views/reports/staff-sales-report/Index.vue";
import RateList from "../views/reports/rate-list/index.vue";
import DailyReport from "../views/reports/daily-report/index.vue";
import ProductExpiry from "../views/reports/product-expiry/index.vue";
import SalesMargin from "../views/reports/sales-margin/index.vue";
import ProductSalesSummary from "../views/reports/product-sales-summary/index.vue";
import ProfitLoss from "../views/reports/profit-loss/index.vue";
import GSTReport from "../views/reports/gst-report/index.vue";
import AssignedStaffReport from "../views/reports/assigned-staff/index.vue";
import CheckInReport from "../views/reports/check-in/index.vue";
import CategoryReport from "../views/reports/category-report/index.vue";
import CustomerProductReport from "../views/reports/customer-product-report/index.vue";

export default [
    {
        path: "/admin/reports/",
        component: Admin,
        children: [
            {
                path: "payments",
                component: Payments,
                name: "admin.reports.payments.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "payments",
                },
            },
            {
                path: "stock-alert",
                component: StockAlert,
                name: "admin.reports.stock.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "stock_alert",
                },
            },
            {
                path: "users",
                component: Users,
                name: "admin.reports.users.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "users_reports",
                },
            },
            {
                path: "sales-summary",
                component: SalesSummrary,
                name: "admin.reports.sales_summary.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "sales_summary",
                    permission: "users_view",
                },
            },
            {
                path: "stock-summary",
                component: StockSummrary,
                name: "admin.reports.stock_summary.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "stock_summary",
                    permission: "products_view",
                },
            },
            {
                path: "rate-list",
                component: RateList,
                name: "admin.reports.rate_list.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "rate_list",
                    permission: "products_view",
                },
            },
            {
                path: "product-sales-summary",
                component: ProductSalesSummary,
                name: "admin.reports.product_sales_summary.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "product_sales_summary",
                    permission: "products_view",
                },
            },
            {
                path: "cash-bank",
                component: CashBank,
                name: "admin.reports.cash_bank.index",
                meta: {
                    requireAuth: true,
                    menuParent: "cash_bank",
                    menuKey: "cash_bank",
                },
            },
            {
                path: "profit-loss",
                component: ProfitLoss,
                name: "admin.reports.profit_loss.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "profit_loss",
                },
            },
            {
                path: "gst-report",
                component: GSTReport,
                name: "admin.reports.gst_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "gst_report",
                },
            },
            {
                path: "referral-reports",
                component: ReferralReport,
                name: "admin.reports.referral_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "referral_report",
                },
            },
            {
                path: "staff-reports",
                component: StaffReport,
                name: "admin.reports.staff_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "staff_report",
                },
            },
            {
                path: "daily-report",
                component: DailyReport,
                name: "admin.reports.daily_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "daily_report",
                },
            },
            {
                path: "assigned-staff-reports",
                component: AssignedStaffReport,
                name: "admin.reports.assigned_staff_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "assigned_staff_report",
                },
            },
            {
                path: "product-expiry",
                component: ProductExpiry,
                name: "admin.reports.product_expiry.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "product_expiry",
                },
            },
            {
                path: "sales-margin",
                component: SalesMargin,
                name: "admin.reports.sales_margin.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "sales_margin",
                },
            },
            {
                path: "check-in-report",
                component: CheckInReport,
                name: "admin.reports.check_in_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "check_in_report",
                },
            },
            {
                path: "category-report",
                component: CategoryReport,
                name: "admin.reports.category_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "category_report",
                },
            },
            {
                path: "customer-product-report",
                component: CustomerProductReport,
                name: "admin.reports.customer_product_report.index",
                meta: {
                    requireAuth: true,
                    menuParent: "reports",
                    menuKey: "customer_product_report",
                },
            },
        ],
    },
];
