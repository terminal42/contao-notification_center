const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore()
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42notificationcenter')
    .addEntry('autosuggester', './assets/autosuggester.ts')
    .addEntry('legacy/autosuggester', './assets/legacy/autosuggester.ts')
    .addStyleEntry('backend', './assets/backend.scss')
    .enableTypeScriptLoader()
    .getWebpackConfig()
;
