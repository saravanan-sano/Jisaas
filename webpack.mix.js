const mix = require("laravel-mix");

mix.webpackConfig({
    resolve: {
        extensions: [
            ".*",
            ".wasm",
            ".mjs",
            ".js",
            ".jsx",
            ".json",
            ".vue",
            ".*",
        ],
    },
    optimization: {
        minimize: true,
    },
});

mix.js("resources/js/app.js", "public/js").vue().minify('public/js/app.js');

mix.less("node_modules/ant-design-vue/dist/antd.less", "public/css/antd.css", {
    lessOptions: {
        javascriptEnabled: true,
        modifyVars: {
            "font-family": '"Nunito", sans-serif',
            // 'primary-color': '#5254cf',
            // 'link-color': '#BD10E0',
            // 'border-radius-base': '2px',
        },
    },
});

// mix.less('node_modules/ant-design-vue/dist/antd.dark.less', 'public/css/antd.dark.css', {
// 	lessOptions: {
// 		javascriptEnabled: true,
// 	}
// });

mix.less("resources/less/app.less", "public/css");
mix.css("resources/less/pos_invoice_css.css", "public/css");

mix.alias({ "@": "resources/js" });

if (mix.inProduction()) {
    mix.version();
}
