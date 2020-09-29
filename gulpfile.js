"use strict";

const { watch, series, src, dest, parallel } = require("gulp");
const sass = require("gulp-sass");
const autoprefixer = require("gulp-autoprefixer");
const browserSync = require("browser-sync").create();
var compiler = require("webpack");
const named = require("vinyl-named");
const webpack = require("webpack-stream");

sass.compiler = require("node-sass");

function css() {
  return src("./sass/style.scss")
    .pipe(sass().on("error", sass.logError))
    .pipe(
      autoprefixer({
        cascade: false,
      })
    )
    .pipe(dest("./theme/"))
    .pipe(browserSync.stream());
}

function js() {
  return src("./src/site.js")
    .pipe(named())
    .pipe(
      webpack(
        {
          mode: "production",
          devtool: "source-map",
          output: {
            filename: "dist.js",
          },
        },
        compiler,
        function (err, stats) {}
      )
    )
    .pipe(dest("./theme/dist/"));
}

function bs() {
  browserSync.init({
    proxy: "walkative.test",
    files: ["./views/**/*.twig"],
  });
}

exports.build = parallel([css, js]);
exports.default = function () {
  bs();
  watch("./sass/*.scss", series([css]));
  watch("./src/*.js", series([js]));
};
