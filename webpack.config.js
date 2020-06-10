const path = require('path');

const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  entry: {
    style: "./src/style.css",
  },
  output: {
    path: path.join(__dirname, './dist/css/'),
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              // only enable hot in development
              hmr: process.env.NODE_ENV === 'development',
              // if hmr does not work, this is a forceful method.
              reloadAll: true,
            },
          },
          'css-loader',
          'postcss-loader',
        ]
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: process.env.NODE_ENV === 'production'
        ? '[name].[contenthash].css'
        : '[name].css',
    }),
    new CleanWebpackPlugin({
      cleanAfterEveryBuildPatterns: ['style.js'],
      cleanOnceBeforeBuildPatterns: ['**/*', '!index.php'],
      protectWebpackAssets: false,
    }),
  ],
  watch: !Boolean(process.env.NODE_ENV === 'production'),
}
