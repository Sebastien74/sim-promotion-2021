/**
 * WEBPACK
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 *
 *   1 - vendor
 *   2 - front
 *   3 - libraries
 *   4 - fonts
 *   5 - gdpr
 *   6 - admin
 *   7 - security
 *   8 - in_build
 *   9 - init
 *   10 - exception
 *   11 - module.exports
 */

let Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

let enableSourceMaps = !Encore.isProduction();
let enableVersioning = true; /** else Encore.isProduction() */
let enableIntegrity = true; /** else Encore.isProduction() */
let target = 'web';
let cache = Encore.isProduction();
let parallelism = 4;
let concatenateModules = false;
let providedExports = false;
let usedExports = false;
let removeEmptyChunks = true; /** else Encore.isProduction() */
let mergeDuplicateChunks = true; /** else Encore.isProduction() */
let sideEffects = true; /** else Encore.isProduction() */
let splitChunks = {
    chunks: 'async'
};
let minimize = Encore.isProduction()

/** 1 - vendor */

Encore
    .setOutputPath('public/build/vendor')
    .setPublicPath('/build/vendor')
    .addEntry('vendor-js', './assets/js/vendor/vendor.js')
    .addEntry('first-paint', './assets/js/vendor/first-paint.js')
    .addStyleEntry('vendor-css', ['./assets/scss/vendor/vendor.scss'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(enableVersioning)
    .enableIntegrityHashes(enableIntegrity)
    .autoProvideVariables({
        moment: 'moment'
    })
    .copyFiles({
        from: './assets/medias/images/vendor',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .copyFiles({
        from: './assets/lib/icons/animated',
        to: 'icons/animated/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/js/vendor/plugins/i18n',
        to: 'i18n/[path][name].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .autoProvidejQuery();

const vendor = Encore.getWebpackConfig();
vendor.name = 'vendor';
vendor.target = target;
vendor.cache = cache;
vendor.parallelism = parallelism;
vendor.optimization.concatenateModules = concatenateModules;
vendor.optimization.providedExports = providedExports;
vendor.optimization.usedExports = usedExports;
vendor.optimization.removeEmptyChunks = removeEmptyChunks;
vendor.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
vendor.optimization.sideEffects = sideEffects;
vendor.optimization.splitChunks = splitChunks;
vendor.optimization.minimize = minimize;
vendor.resolve.extensions.push('json');

/** 2 - front */

Encore.reset();

Encore
    .setOutputPath('public/build/front/default')
    .setPublicPath('/build/front/default')
    .addEntry('front-default-vendor', './assets/js/front/default/vendor.js')
    .addEntry('front-default-cms', './assets/js/front/default/templates/cms.js')
    .addEntry('front-default-components', './assets/js/front/default/templates/components.js')
    .addEntry('front-default-contact', './assets/js/front/default/templates/contact.js')
    .addEntry('front-default-home', './assets/js/front/default/templates/home.js')
    .addEntry('front-default-news', './assets/js/front/default/templates/news.js')
    .addEntry('front-default-security', './assets/js/front/default/templates/security.js')
    .addEntry('front-default-catalog', './assets/js/front/default/templates/catalog.js')
    .addStyleEntry('front-default-legacy', './assets/scss/front/default/templates/legacy.scss')
    .addStyleEntry('front-default-error', ['./assets/scss/front/default/templates/error.scss'])
    .addStyleEntry('front-default-desktop', ['./assets/scss/front/default/responsive/desktop.scss'])
    .addStyleEntry('front-default-tablet', ['./assets/scss/front/default/responsive/tablet.scss'])
    .addStyleEntry('front-default-mobile', ['./assets/scss/front/default/responsive/mobile.scss'])
    .addStyleEntry('front-default-noscript', ['./assets/scss/front/default/noscript.scss'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(enableVersioning)
    .enableIntegrityHashes(enableIntegrity)
    .autoProvideVariables({
        bootstrap: 'bootstrap/dist/js/bootstrap.bundle.js',
        moment: 'moment',
        Cookies: 'js-cookie'
    })
    .copyFiles({
        from: './assets/medias/images/front/default',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .enableSingleRuntimeChunk()
    .enableSassLoader();

const front_default = Encore.getWebpackConfig();
front_default.name = 'front_default';
front_default.target = target;
front_default.cache = cache;
front_default.parallelism = parallelism;
front_default.optimization.concatenateModules = concatenateModules;
front_default.optimization.providedExports = providedExports;
front_default.optimization.usedExports = usedExports;
front_default.optimization.removeEmptyChunks = removeEmptyChunks;
front_default.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
front_default.optimization.sideEffects = sideEffects;
front_default.optimization.splitChunks = splitChunks;
front_default.optimization.minimize = minimize;
front_default.resolve.extensions.push('json');

/** 3 - libraries */

Encore.reset();

Encore
    .setOutputPath('public/build/libraries')
    .setPublicPath('/build/libraries')
    .addStyleEntry('lib-simplebar-css', './assets/lib/components/simplebar.min.css')
    .addStyleEntry('lib-waves-effect-css', './assets/lib/components/waves-effect.min.css')
    .addStyleEntry('lib-magnific-popup-css', './assets/lib/components/magnific-popup.min.css')
    .addStyleEntry('lib-jquery-ui-css', './assets/lib/components/jquery-ui/jquery-ui.css')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(false)
    .enableIntegrityHashes(enableIntegrity)
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .autoProvidejQuery();

const libraries = Encore.getWebpackConfig();
libraries.name = 'library';
libraries.target = target;
libraries.cache = cache;
libraries.parallelism = parallelism;
libraries.optimization.concatenateModules = concatenateModules;
libraries.optimization.providedExports = providedExports;
libraries.optimization.usedExports = usedExports;
libraries.optimization.removeEmptyChunks = removeEmptyChunks;
libraries.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
libraries.optimization.sideEffects = sideEffects;
libraries.optimization.splitChunks = splitChunks;
libraries.optimization.minimize = minimize;
libraries.resolve.extensions.push('json');

/** 4 - fonts */

Encore.reset();

Encore
    .setOutputPath('public/build/fonts')
    .setPublicPath('/build/fonts')
    .addStyleEntry('font-ambroise', ['./assets/lib/fonts/ambroise.scss'])
    .addStyleEntry('font-basicsans', ['./assets/lib/fonts/basicsans.scss'])
    .addStyleEntry('font-blocklyncondensed', ['./assets/lib/fonts/blocklyncondensed.scss'])
    .addStyleEntry('font-blocklyngrunge', ['./assets/lib/fonts/blocklyngrunge.scss'])
    .addStyleEntry('font-cabin', ['./assets/lib/fonts/cabin.scss'])
    .addStyleEntry('font-catamaran', ['./assets/lib/fonts/catamaran.scss'])
    .addStyleEntry('font-centurygothic', ['./assets/lib/fonts/centurygothic.scss'])
    .addStyleEntry('font-firasans', ['./assets/lib/fonts/firasans.scss'])
    .addStyleEntry('font-geosans', ['./assets/lib/fonts/geosans.scss'])
    .addStyleEntry('font-gotham', ['./assets/lib/fonts/gotham.scss'])
    .addStyleEntry('font-lato', ['./assets/lib/fonts/lato.scss'])
    .addStyleEntry('font-material', ['./assets/lib/fonts/material.scss'])
    .addStyleEntry('font-montserrat', ['./assets/lib/fonts/montserrat.scss'])
    .addStyleEntry('font-mplusrounded', ['./assets/lib/fonts/mplusrounded.scss'])
    .addStyleEntry('font-noto', ['./assets/lib/fonts/noto.scss'])
    .addStyleEntry('font-opensans', ['./assets/lib/fonts/opensans.scss'])
    .addStyleEntry('font-playfairdisplay', ['./assets/lib/fonts/playfairdisplay.scss'])
    .addStyleEntry('font-poppins', ['./assets/lib/fonts/poppins.scss'])
    .addStyleEntry('font-roboto', ['./assets/lib/fonts/roboto.scss'])
    .addStyleEntry('font-upgrade', ['./assets/lib/fonts/upgrade.scss'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(false)
    .enableIntegrityHashes(enableIntegrity)
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .autoProvidejQuery();

const fonts = Encore.getWebpackConfig();
fonts.name = 'fonts';
fonts.target = target;
fonts.cache = Encore.isProduction();
fonts.parallelism = parallelism;
fonts.optimization.removeEmptyChunks = Encore.isProduction();
fonts.optimization.mergeDuplicateChunks = Encore.isProduction();
fonts.optimization.sideEffects = Encore.isProduction();
fonts.optimization.splitChunks = false;
fonts.optimization.minimize = Encore.isProduction();
fonts.resolve.extensions.push('json');

/** 5 - gdpr */

Encore.reset();

Encore
    .setOutputPath('public/build/gdpr')
    .setPublicPath('/build/gdpr')
    .addEntry('gdpr', './assets/js/gdpr/ghost.js')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(enableVersioning)
    .enableIntegrityHashes(enableIntegrity)
    .copyFiles({
        from: './assets/medias/images/gdpr',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader();

const gdpr = Encore.getWebpackConfig();
gdpr.name = 'gdpr';
gdpr.target = target;
gdpr.cache = cache;
gdpr.parallelism = parallelism;
gdpr.optimization.concatenateModules = concatenateModules;
gdpr.optimization.providedExports = providedExports;
gdpr.optimization.usedExports = usedExports;
gdpr.optimization.removeEmptyChunks = removeEmptyChunks;
gdpr.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
gdpr.optimization.sideEffects = sideEffects;
gdpr.optimization.splitChunks = splitChunks;
gdpr.optimization.minimize = minimize;
gdpr.resolve.extensions.push('json');

/** 6 - admin */

Encore.reset();

Encore
    .setOutputPath('public/build/admin')
    .setPublicPath('/build/admin')
    .addEntry('admin-vendor-default', './assets/js/admin/vendor-default.js')
    .addEntry('admin-vendor-felix', './assets/js/admin/vendor-felix.js')
    .addEntry('admin-vendor-clouds', './assets/js/admin/vendor-clouds.js')
    .addEntry('admin-seo', './assets/js/admin/pages/seo.js')
    .addEntry('admin-medias-library', './assets/js/admin/media/library.js')
    .addEntry('admin-medias-cropper', './assets/js/admin/media/cropper.js')
    .addEntry('admin-icons-library', './assets/js/admin/pages/icons-library.js')
    .addEntry('admin-translation', './assets/js/admin/pages/translation.js')
    .addEntry('admin-table', './assets/js/admin/pages/table.js')
    .addEntry('admin-menu', './assets/js/admin/pages/menu.js')
    .addEntry('admin-agenda', './assets/js/admin/pages/agenda.js')
    .addEntry('admin-catalog', './assets/js/admin/pages/catalog/catalog.js')
    .addEntry('admin-user-profile', './assets/js/admin/pages/user-profile.js')
    .addEntry('admin-google-analytics', './assets/js/admin/pages/analytics/google-analytics.js')
    .addEntry('admin-analytics', './assets/js/admin/pages/analytics/analytics.js')
    .addEntry('admin-development', './assets/js/admin/pages/development.js')
    .addEntry('admin-website', './assets/js/admin/pages/website.js')
    .addStyleEntry('admin-preloader', ['./assets/scss/admin/preloader.scss'])
    .addStyleEntry('admin-dashboard', ['./assets/scss/admin/pages/dashboard.scss'])
    .addStyleEntry('admin-extensions', ['./assets/scss/admin/pages/extensions.scss'])
    .addStyleEntry('admin-error', ['./assets/scss/admin/pages/error.scss'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(enableVersioning)
    .enableIntegrityHashes(enableIntegrity)
    .autoProvideVariables({
        moment: 'moment'
    })
    .copyFiles({
        from: './assets/medias/images/admin',
        to: 'images/theme/[path][name].[hash:8].[ext]'
    })
    .copyFiles({
        from: './assets/medias/docs/admin',
        to: 'docs/[path][name].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .autoProvidejQuery();

const admin = Encore.getWebpackConfig();
admin.name = 'admin';
admin.target = target;
admin.cache = cache;
admin.parallelism = parallelism;
admin.optimization.concatenateModules = concatenateModules;
admin.optimization.providedExports = providedExports;
admin.optimization.usedExports = usedExports;
admin.optimization.removeEmptyChunks = removeEmptyChunks;
admin.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
admin.optimization.sideEffects = sideEffects;
admin.optimization.splitChunks = splitChunks;
admin.optimization.minimize = minimize;
admin.resolve.extensions.push('json');

/** 7 - security */

Encore.reset();

Encore
    .setOutputPath('public/build/security')
    .setPublicPath('/build/security')
    .addEntry('security', './assets/js/security/vendor.js')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(enableVersioning)
    .enableIntegrityHashes(enableIntegrity)
    .copyFiles({
        from: './assets/medias/images/security',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader();

const security = Encore.getWebpackConfig();
security.name = 'security';
security.target = target;
security.cache = cache;
security.parallelism = parallelism;
security.optimization.concatenateModules = concatenateModules;
security.optimization.providedExports = providedExports;
security.optimization.usedExports = usedExports;
security.optimization.removeEmptyChunks = removeEmptyChunks;
security.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
security.optimization.sideEffects = sideEffects;
security.optimization.splitChunks = splitChunks;
security.optimization.minimize = minimize;
security.resolve.extensions.push('json');

/** 8 - in_build */

Encore.reset();

Encore
    .setOutputPath('public/build/in-build')
    .setPublicPath('/build/in-build')
    .addEntry('build', ['./assets/js/in-build/vendor.js'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(false)
    .enableIntegrityHashes(enableIntegrity)
    .autoProvideVariables({
        bootstrap: 'bootstrap/dist/js/bootstrap.bundle.js'
    })
    .copyFiles({
        from: './assets/medias/images/in-build',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader();

const in_build = Encore.getWebpackConfig();
in_build.name = 'core';
in_build.target = target;
in_build.cache = cache;
in_build.parallelism = parallelism;
in_build.optimization.concatenateModules = concatenateModules;
in_build.optimization.providedExports = providedExports;
in_build.optimization.usedExports = usedExports;
in_build.optimization.removeEmptyChunks = removeEmptyChunks;
in_build.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
in_build.optimization.sideEffects = sideEffects;
in_build.optimization.splitChunks = splitChunks;
in_build.optimization.minimize = minimize;
in_build.resolve.extensions.push('json');

/** 9 - init */

Encore.reset();

Encore
    .setOutputPath('public/build/init')
    .setPublicPath('/build/init')
    .addEntry('core-init', ['./assets/js/init/vendor.js'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(false)
    .enableIntegrityHashes(enableIntegrity)
    .copyFiles({
        from: './assets/medias/images/init',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .addPlugin(new CleanWebpackPlugin())
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .autoProvidejQuery();

const init = Encore.getWebpackConfig();
init.name = 'init';
init.target = target;
init.cache = cache;
init.parallelism = parallelism;
init.optimization.concatenateModules = concatenateModules;
init.optimization.providedExports = providedExports;
init.optimization.usedExports = usedExports;
init.optimization.removeEmptyChunks = removeEmptyChunks;
init.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
init.optimization.sideEffects = sideEffects;
init.optimization.splitChunks = splitChunks;
init.optimization.minimize = minimize;
init.resolve.extensions.push('json');

/** 10 - exception */

Encore
    .setOutputPath('public/build/exception')
    .setPublicPath('/build/exception')
    .addEntry('exception', ['./assets/js/exception/vendor.js'])
    .addStyleEntry('sf-exception', ['./assets/scss/exception/sf-exception.scss'])
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(enableSourceMaps)
    .enableVersioning(enableVersioning)
    .enableIntegrityHashes(enableIntegrity)
    .copyFiles({
        from: './assets/medias/images/exception',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: path.resolve(__dirname, "postcss.config.js")
        };
    })
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 4096}
    })
    .addPlugin(new CleanWebpackPlugin())
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 0;
    })
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .autoProvidejQuery();

const exception = Encore.getWebpackConfig();
exception.name = 'exception';
exception.target = target;
exception.cache = cache;
exception.parallelism = parallelism;
exception.optimization.concatenateModules = concatenateModules;
exception.optimization.providedExports = providedExports;
exception.optimization.usedExports = usedExports;
exception.optimization.removeEmptyChunks = removeEmptyChunks;
exception.optimization.mergeDuplicateChunks = mergeDuplicateChunks;
exception.optimization.sideEffects = sideEffects;
exception.optimization.splitChunks = splitChunks;
exception.optimization.minimize = minimize;
exception.resolve.extensions.push('json');

/** 11 - module.exports */
module.exports = [vendor, front_default, libraries, fonts, gdpr, admin, security, in_build, init, exception];