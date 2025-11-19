const path = require('path');
const glob = require('glob');

function getEntry(globPath, pathDir) {
    var files = glob.sync(globPath);
    var entries = {},
        entry, dirname, basename, pathname, extname;

    for (var i = 0; i < files.length; i++) {
        entry = files[i];
        dirname = path.dirname(entry);
        extname = path.extname(entry);
        basename = path.basename(entry, extname);
        pathname = path.join(dirname, basename);
        pathname = pathDir ? pathname.replace(pathDir, '') : pathname;
        entries[pathname] = './' + entry;
    }
    return entries;
}
var htmls = getEntry('./tecev/dist/controller/**/*.js', 'src\\');
var entries = {};
for (var key in htmls) {
    entries[key] = htmls[key].replace('.html', '.js');
}
module.exports = {
    entry: entries,
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, '')
    }
};