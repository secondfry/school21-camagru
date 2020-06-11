const path = require('path');

const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  devtool: process.env.NODE_ENV === 'production' ? null : 'eval-cheap-source-map',
  entry: "./src/app.js",
  output: {
    path: path.join(__dirname, './dist/bundle'),
  },
  module: {
    rules: [
      {
        test: /\.p?css$/,
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
      filename: '[name].css'
    }),
    new CleanWebpackPlugin({
      cleanAfterEveryBuildPatterns: ['style.js'],
      cleanOnceBeforeBuildPatterns: ['**/*', '!index.php'],
      protectWebpackAssets: false,
    }),
  ],
  watch: !Boolean(process.env.NODE_ENV === 'production'),
}
