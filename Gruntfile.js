
module.exports = function( grunt ) {
	var paths = {
		js_files_concat: {
			'js/popup-admin.js': ['js/src/popup-admin.js'],
			'js/public.js':      ['js/src/public.js']
		},
		js_files_min: {
			'js/popup-admin.min.js': ['js/popup-admin.js'],
			'js/public.min.js':      ['js/public.js']
		},
		css_files_compile: {
			'css/popup-admin.css':                  'css/sass/popup-admin.scss',
			'css/tpl/cabriolet/style.css':          'css/sass/tpl/cabriolet/style.scss',
			'css/tpl/minimal/style.css':            'css/sass/tpl/minimal/style.scss',
			'css/tpl/simple/style.css':             'css/sass/tpl/simple/style.scss',
			'css/tpl/old-default/style.css':        'css/sass/tpl/old-default/style.scss',
			'css/tpl/old-fixed/style.css':          'css/sass/tpl/old-fixed/style.scss',
			'css/tpl/old-fullbackground/style.css': 'css/sass/tpl/old-fullbackground/style.scss'
		}
	};

	// Project configuration
	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),

		concat: {
			options: {
				stripBanners: true,
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			scripts: {
				files: paths.js_files_concat
			}
		},


		jshint: {
			all: [
				'Gruntfile.js',
				'js/src/**/*.js',
				'js/test/**/*.js'
			],
			options: {
				curly:   true,
				eqeqeq:  true,
				immed:   true,
				latedef: true,
				newcap:  true,
				noarg:   true,
				sub:     true,
				undef:   true,
				boss:    true,
				eqnull:  true,
				globals: {
					exports: true,
					module:  false
				}
			}
		},


		uglify: {
			all: {
				files: paths.js_files_min,
				options: {
					banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
						' * <%= pkg.homepage %>\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
						' * Licensed GPLv2+' +
						' */\n',
					mangle: {
						except: ['jQuery']
					}
				}
			}
		},


		test:   {
			files: ['js/test/**/*.js']
		},


		phpunit: {
			classes: {
				dir: ''
			},
			options: {
				bin: 'phpunit',
				bootstrap: 'tests/php/bootstrap.php',
				testsuite: 'default',
				configuration: 'tests/php/phpunit.xml',
				colors: true,
				tap: true,
				//testdox: true,
				staticBackup: false,
				noGlobalsBackup: false
			}
		},


		sass:   {
			all: {
				files: paths.css_files_compile
			}
		},


		cssmin: {
			options: {
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			minify: {
				expand: true,

				cwd: 'css/',
				src: ['popup-admin.css'],

				dest: 'css/',
				ext: '.min.css'
			}
		},


		watch:  {
			sass: {
				files: ['css/sass/**/*.scss'],
				tasks: ['sass', 'cssmin'],
				options: {
					debounceDelay: 500
				}
			},

			scripts: {
				files: ['js/src/**/*.js', 'js/vendor/**/*.js'],
				tasks: ['jshint', 'concat', 'uglify'],
				options: {
					debounceDelay: 500
				}
			}
		},


		clean: {
			main: {
				src: ['release/<%= pkg.version %>']
			},
			temp: {
				src: ['**/*.tmp', '**/.afpDeleted*', '**/.DS_Store'],
				dot: true,
				filter: 'isFile'
			}
		},


		copy: {
			// Copy the plugin to a versioned release directory
			main: {
				src:  [
					'**',
					'!node_modules/**',
					'!release/**',
					'!.git/**',
					'!.sass-cache/**',
					'!css/src/**',
					'!css/sass/**',
					'!css/less/**',
					'!js/src/**',
					'!js/vendor/**',
					'!img/src/**',
					'!tests/**',
					'!Gruntfile.js',
					'!package.json',
					'!.git*',
					'!dev/**'
				],
				dest: 'release/<%= pkg.version %>/'
			}
		},


		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: [ '**/*' ],
				dest: 'popover/'
			}
		}

	} );

	// Load other tasks
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	grunt.loadNpmTasks('grunt-contrib-sass');

	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-phpunit');

	// Default task.

	grunt.registerTask( 'default', ['clean:temp', 'jshint', 'concat', 'uglify', 'sass', 'cssmin'] );

	grunt.registerTask( 'build', ['phpunit', 'default', 'clean', 'copy', 'compress'] );

	grunt.registerTask( 'test', ['phpunit', 'jshint'] );

	grunt.util.linefeed = '\n';
};