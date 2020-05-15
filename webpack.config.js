const path = require('path');
const webpack = require('webpack');
const fs = require("fs");
const MiniCssExtractPlugin = require('mini-css-extract-plugin');


module.exports = {
  mode: process.env.NODE_ENV || 'development',
  entry:
    fs.readdirSync(`${__dirname}/js`).reduce((carry, item) => {
      if (item.endsWith('.js') && fs.statSync(`${__dirname}/js/${item}`).isFile()) {
        carry[item.replace('.js', '')] = `${__dirname}/js/${item}`;
      }

      return carry
    }, {})
  ,
  output: {
    path: path.resolve(__dirname, "plugin/js"),
    filename: './[name].js'
  },

  plugins: [
    new webpack.ProgressPlugin(),
    new MiniCssExtractPlugin({
      filename: '../css/[name].css',
      ignoreOrder: false,
    }),
  ],

  module: {
    rules: [
      {
        test: /.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
      },
      { // regular css files
        test: /\.s?css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              hmr: process.env.NODE_ENV === 'development',
            },
          },
          'css-loader',
          'sass-loader',
        ]
      },
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: '../images/'
            }
          },
        ],
      },
      {
        test: /\.(woff(2)?|ttf|eot)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: '../fonts/'
            }
          }
        ]
      }
    ]
  },


  optimization: {
    minimize: true,
    /*
            splitChunks: {
                cacheGroups: {
                    vendors: {
                        priority: -10,
                        test:
                            /[\\/]node_modules[\\/]/
                    }
                }
                ,

                chunks: 'async',
                minChunks:
                    1,
                minSize:
                    30000,
                name:
                    true
            }*/
  }
  ,

  devServer: {
    open: true
  }
}
;
