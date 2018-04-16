'use strict';

var gulp = require('gulp');
var imagemin = require('gulp-imagemin');
var spritesmith = require('gulp.spritesmith');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');

gulp.task('sprite', function () {
  var spriteData = gulp.src('images/sprite-src/*.png').pipe(spritesmith({
    imgName: 'sprite.png',
    cssName: '_sprite.scss',
    padding: 2
  }));
  spriteData.img.pipe(gulp.dest('images'));
  spriteData.css.pipe(gulp.dest('sass'));
});

gulp.task('imagemin', function () {
  return gulp.src('images/*')
    .pipe(imagemin())
    .pipe(gulp.dest('images'))
});

gulp.task('sass-global', function () {
  return gulp.src('./sass/*')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10
    }).on('error', sass.logError))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./css'));
});

gulp.task('sass-layouts', function () {
  return gulp.src('./sass/layouts/asu/*')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10
    }).on('error', sass.logError))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./layouts/asu'));
});

gulp.task('postcss-global', function () {
  return gulp.src('./*.css')
    .pipe(sourcemaps.init())
    .pipe(postcss([autoprefixer({
      browsers: ['last 3 versions']
    })]))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./'));
});

gulp.task('imagemin', function () {
  gulp.watch(['images/**'], ['imagemin']);
});

gulp.task('watch', function () {
  gulp.watch(
    ['images/sprite-src/*.png', './sass/*.scss', './sass/layouts/asu/*.scss'],
    ['sprite', 'sass-global', 'sass-layouts', 'postcss-global']);
});
