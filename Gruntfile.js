module.exports = function (grunt) {
    grunt.initConfig({
        sass: {
            options: {
                loadPath: [
                    './'
                ]
            },
            default: {
                files: {
                    'plugin/isset-video-publisher.css': 'scss/isset-video-publisher.scss'
                }
            }
        },
        browserify: {
            default: {
                options: {
                    expose: './node_modules',
                    browserifyOptions: {
                        debug: true
                    },
                    options: {
                        transform: ['uglifyify']
                    },
                    watch: true
                },
                files: {
                    'plugin/isset-video-publisher.js': ['js/isset-video-publisher.js']
                }
            }
        },
        uglify: {
            default: {
                files: {
                    'plugin/isset-video-publisher.min.js': ['isset-video-publisher.js']
                }
            }
        },
        watch: {
            sass: {
                files: ['scss/**/*.scss'],
                tasks: ['sass']
            },
            uglify: {
                files: ['plugin/isset-video-publisher.js'],
                tasks: ['uglify']
            }
        },
        makepot: {
            target: {
                options: {
                    type: 'wp-plugin',
                    cwd: 'plugin',
                    domainPath: '../languages/',
                    potFilename: 'isset-video-publisher.pot',
                    language: ['nl', 'en'],
                    updatePoFiles: true
                }
            }
        },
        po2mo: {
            files: {
                src: 'languages/*.po',
                dest: 'plugin/',
                expand: true
            }
        },
        copy: {
            main: {
                expand: true,
                src: ["languages/*.po", "languages/*.pot"],
                dest: "plugin/"
            }
        }
    });

    grunt.loadNpmTasks('grunt-browserify');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('@eater/grunt-po2mo');

    grunt.registerTask('watch-files', ['sass', 'browserify', 'uglify', 'watch']);
    grunt.registerTask('dist', ['trans']);
    grunt.registerTask('trans', ['makepot', 'po2mo', 'copy']);

    grunt.registerTask('default', ['trans']);
};
