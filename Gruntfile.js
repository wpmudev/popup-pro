
module.exports = function( grunt ) {
	var paths = {
		js_files_concat: {
			'js/ace.js':         [
				'js/vendor/ace.js',
				'js/vendor/ext-beautify.js',
				'js/vendor/mode-css.js'
			],
			'js/worker-css.js':         ['js/vendor/worker-css.js'],
			'js/theme-chrome.js':       ['js/vendor/theme-chrome.js'],
			'js/popup-admin.js': ['js/src/popup-admin.js'],
			'js/public.js':      ['js/src/public.js']
		},
		css_files_compile: {
			'css/popup-admin.css':                  'css/sass/popup-admin.scss',
			'css/tpl/cabriolet/style.css':          'css/sass/tpl/cabriolet/style.scss',
			'css/tpl/minimal/style.css':            'css/sass/tpl/minimal/style.scss',
			'css/tpl/simple/style.css':             'css/sass/tpl/simple/style.scss',
			'css/tpl/old-default/style.css':        'css/sass/tpl/old-default/style.scss',
			'css/tpl/old-fixed/style.css':          'css/sass/tpl/old-fixed/style.scss',
			'css/tpl/old-fullbackground/style.css': 'css/sass/tpl/old-fullbackground/style.scss',
			'css/animate.css':                      'css/vendor/animate.scss'
		},
		plugin_dir: 'popover/'
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
				files: [{
					expand: true,
					src: ['*.js', '!*.min.js'],
					cwd: 'js/',
					dest: 'js/',
					ext: '.min.js',
					extDot: 'last'
				}],
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
				//tap: true,
				//testdox: true,
				//stopOnError: true,
				staticBackup: false,
				noGlobalsBackup: false
			}
		},


		sass:   {
			all: {
				options: {
					'sourcemap=none': true, // 'sourcemap': 'none' does not work...
					unixNewlines: true,
					style: 'expanded'
				},
				files: paths.css_files_compile
			}
		},


		autoprefixer: {
			options: {
				browsers: ['last 2 version', 'ie 8', 'ie 9'],
				diff: false
			},
			single_file: {
				files: [{
					expand: true,
					src: ['**/*.css', '!**/*.min.css'],
					cwd: 'css/',
					dest: 'css/',
					ext: '.css',
					extDot: 'last',
					flatten: false
				}]
			}
		},


		//compass - required for autoprefixer
		compass: {
			options: {
			},
			server: {
				options: {
					debugInfo: true
				}
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
				src: ['*.css', '!*.min.css'],
				cwd: 'css/',
				dest: 'css/',
				ext: '.min.css',
				extDot: 'last'
			}
		},


		watch:  {
			sass: {
				files: ['css/**/*.scss'],
				tasks: ['sass', 'autoprefixer'],
				options: {
					debounceDelay: 500
				}
			},

			scripts: {
				files: ['js/src/**/*.js', 'js/vendor/**/*.js'],
				tasks: ['jshint', 'concat'],
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
					'!.git/**',
					'!.git*',
					'!node_modules/**',
					'!release/**',
					'!.sass-cache/**',
					'!**/package.json',
					'!**/css/sass/**',
					'!**/css/vendor/**',
					'!**/js/src/**',
					'!**/js/vendor/**',
					'!**/img/src/**',
					'!**/tests/**',
					'!**/Gruntfile.js'
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
				dest: paths.plugin_dir
			}
		}

	} );

	// Load other tasks
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-autoprefixer');

	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-phpunit');

	grunt.registerTask( 'notes', 'Show release notes', function() {
		grunt.log.subhead( 'Release notes' );
		grunt.log.writeln( '  1. Check BITBUCKET for pull-requests' );
		grunt.log.writeln( '  2. Check ASANA for high-priority bugs' );
		grunt.log.writeln( '  3. Check EMAILS for high-priority bugs' );
		grunt.log.writeln( '  4. Check FORUM for open threads' );
		grunt.log.writeln( '  5. REPLY to forum threads + unsubscribe' );
		grunt.log.writeln( '  6. Update the TRANSLATION files' );
		grunt.log.writeln( '  7. Generate ARCHIVE' );
		grunt.log.writeln( '  8. INSTALL on a clean WordPress installation' );
		grunt.log.writeln( '  9. RELEASE the plugin!' );
	});

	// Default task.

	grunt.registerTask( 'default', ['clean:temp', 'jshint', 'concat', 'uglify', 'sass', 'autoprefixer', 'cssmin'] );
	grunt.registerTask( 'build', ['phpunit', 'default', 'clean', 'copy', 'compress', 'notes'] );
	grunt.registerTask( 'test', ['phpunit', 'jshint'] );

	grunt.util.linefeed = '\n';
};