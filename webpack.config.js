const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public')
    .setPublicPath('/bundles/terminal42notificationcenter')
    .setManifestKeyPrefix('')
    .addEntry('autosuggester', './assets/autosuggester.ts')
    .addStyleEntry('backend', './assets/backend.scss')
    .disableSingleRuntimeChunk()
    .enableTypeScriptLoader()
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

module.exports = Encore.getWebpackConfig();