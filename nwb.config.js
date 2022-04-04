const path = require('path');
// const webpack = require('webpack');
require('dotenv').config();
// const ignorePlugin = new webpack.IgnorePlugin({
//   resourceRegExp: /@blueprintjs\/(core|icons)/, // ignore optional UI framework dependencies
// });

console.log(process.env)

module.exports = {
  type: 'react-app',
  webpack: {
    publicPath: process.env.publicPath,
    extra: {
      output: {
        chunkFilename: 'mirador.[chunkhash:8].js',
        filename: 'mirador.js',
        path: '/Users/ortiz/tools/sites/findingaids/js',
        publicPath: '/js',
      },
      plugins: [
        // ignorePlugin,
      ],
    },
    // https://github.com/jantimon/html-webpack-plugin#readme
    html: {
      // inject: true,
      // filename: 'object.view.html',
      // template: 'src/object.view.html',
      // templateContent: ({ htmlWebpackPlugin }) => `${htmlWebpackPlugin.tags.bodyTags}`,
    },
    aliases: {
      '@material-ui/core': path.resolve('./', 'node_modules', '@material-ui/core'),
      '@material-ui/styles': path.resolve('./', 'node_modules', '@material-ui/styles'),
      react: path.resolve('./', 'node_modules', 'react'),
      'react-dom': path.resolve('./', 'node_modules', 'react-dom'),
    },
  },
};

// HtmlWebpackPlugin
// HtmlWebpackPlugin {
//   options: {
//     template: '/Users/ortiz/tools/sites/findingaids/node_modules/nwb/templates/webpack-template.html',
//     templateParameters: [Function: templateParametersGenerator],
//     filename: 'index.html',
//     hash: false,
//     inject: true,
//     compile: true,
//     favicon: false,
//     minify: false,
//     cache: true,
//     showErrors: true,
//     chunks: 'all',
//     excludeChunks: [],
//     chunksSortMode: 'dependency',
//     meta: {},
//     title: 'React App',
//     xhtml: false,
//     mountId: 'app'
//   }
// }
