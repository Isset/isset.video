
module.exports = function(grunt) {
    grunt.initConfig({
        makepot: {
            target: {
                options: {
                    type: 'wp-plugin',
                    domainPath: '/languages',
                    language: ['nl', 'en'],
                    updatePoFiles: true
                }
            }
        },
        po2mo: {
            files: {
                src: 'languages/*.po',
                expand: true
            }
        }
    });

    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-po2mo');

    grunt.registerTask('dist', ['trans']);
    grunt.registerTask('trans', ['makepot', 'po2mo']);

    grunt.registerTask('default', ['trans']);

};
