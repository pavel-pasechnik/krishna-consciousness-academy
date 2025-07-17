const path = require("path");

module.exports = {
  entry: {
    main: path.resolve(__dirname, "themes/krishna-academy/assets/js/index.js"),
    style: path.resolve(
      __dirname,
      "themes/krishna-academy/assets/css/reset.css"
    ),
  },
  output: {
    path: path.resolve(__dirname, "themes/krishna-academy/build"),
    filename: "[name].js",
  },
  module: {
    rules: [
      {
        test: /\.css$/i,
        use: ["style-loader", "css-loader"],
      },
      {
        test: /\.scss$/i,
        use: ["style-loader", "css-loader", "sass-loader"],
      },
    ],
  },
};
