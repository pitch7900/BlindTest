var gulp        = require('gulp');
var php         = require('gulp-connect-php');
var browserSync = require('browser-sync').create();
var reload      = browserSync.reload;

gulp.task('php', function(){
    php.server({base:'./public', port:8081, keepalive:true});
});

gulp.task('browser-sync-php', gulp.series('php'), function() {
    browserSync.init({
        proxy:"localhost:8081",
        baseDir: "./public",
        open:true,
        notify:false
    });
});

gulp.task('dev', gulp.series('browser-sync-php'), function() {
    gulp.watch('./*.php', browserSync.reload);
});

gulp.task('browser-sync', function () {
    browserSync.init({
        proxy: 'blindtest:8080',
        port: '8080',
        // online: true,
        // browser: ['chrome', 'firefox'],
        browser: ['firefox'],
    });
    gulp.watch('./**/*.php').on('change', reload);
});

gulp.task('sync', gulp.series('browser-sync'));