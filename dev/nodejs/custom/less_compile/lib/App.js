var util = require("util");
var path = require('path');
var fs = require('fs');
var lessc = require('./LessCompiler.js');
var lessw = require('./LessWatcher.js');

var App = {};
/**
 * Execute our compile_less app logic, hence get our compiler up and running.
 */
App.run = function()
{
    var args = App.readArgs();

    lessc.compileDirectory(args.src, args.deploy);

    if (args.watch)
    {
        var watcher = lessw.create(args.src, args.deploy);
        watcher.startWatching();
    }
}
/**
 * Read and validate our commandline args,
 * which are an expected less and css directory.
 */
App.readArgs = function()
{
    if (process.argv.length < 4 || process.argv.length > 5)
    {
        throw "Invalid number of parameters.\nUsage: node less_compile.js less_directory deploy_directory [-watch]";
    }

    var less_dir = path.normalize(process.cwd() + '/' + process.argv[2]);
    var css_dir = path.normalize(process.cwd() + '/' + process.argv[3]);
    var watch_files = false;

    try
    {
        fs.statSync(less_dir);
    }
    catch(e)
    {
        throw new Error("The given less src directory '" + less_dir + "' does not exist.");
    }

    try
    {
        fs.statSync(css_dir);
    }
    catch(e)
    {
        throw new Error("The given css src directory '" + css_dir + "' does not exist.");
    }

    if (process.argv.length === 5)
    {
        if ('-watch' !== process.argv[4])
        {
            throw new Error("Only the '-watch' flag is supported as a third parameter.");
        }

        watch_files = true;
    }

    return {
        'src': less_dir,
        'deploy': css_dir,
        'watch': watch_files
    };
};

exports.run = App.run;