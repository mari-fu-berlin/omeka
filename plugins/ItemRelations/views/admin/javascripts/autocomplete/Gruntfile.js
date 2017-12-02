module.exports = function( grunt ) {

    grunt.initConfig({

        // Import package manifest
        pkg: grunt.file.readJSON( "package.json" ),

        // Banner definitions
        meta: {
            banner: "/*\n" +
            " *  <%= pkg.title || pkg.name %> - v<%= pkg.version %>\n" +
            " *  <%= pkg.description %>\n" +
            " *\n" +
            " *  Made by <%= pkg.author.name %>\n" +
            " *  Under <%= pkg.license %> License\n" +
            " */\n"
        },

        // Lint definitions
        jshint: {
            files: [ "src/*.js" ],
            options: {
                jshintrc: ".jshintrc"
            }
        },

        // Minify definitions
        uglify: {
            dist: {
                files: {
                    'dist/jquery.item-relations.min.js': ['src/jquery.autocomplete.js']
                }
            },
            options: {
                banner: "<%= meta.banner %>"
            }
        },

        // watch for changes to source
        // (call 'grunt watch')
        watch: {
            files: [ "src/*" ],
            tasks: [ "default" ]
        }

    });

    grunt.loadNpmTasks( "grunt-contrib-jshint" );
    grunt.loadNpmTasks( "grunt-contrib-uglify" );
    grunt.loadNpmTasks( "grunt-contrib-watch" );

    grunt.registerTask( "lint", [ "jshint" ] );
    grunt.registerTask( "build", [ "uglify" ] );
    grunt.registerTask( "default", [ "jshint", "build" ] );
};
