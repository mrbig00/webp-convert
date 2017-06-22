<?php
// http://test2/webp-convert.php?source=/var/www/test/images/subfolder/logo.jpg&quality=80&preferred_tools=imagewebp,cwebp&debug&destination-root=cold

/*
URL parameters:

source:
Path to source file. Can be absolute or relative (relative to document root). If it starts with "/", it is considered an absolute path.

destination-root (optional):
The final destination will be calculated like this: [desired destination root] + [relative path of source file] + ".webp". If you want converted files to be put in the same folder as the originals, you can set destination-root to ".", or leave it blank. If you on the other hand want all converted files to reside in their own folder, set the destination-root to point to that folder. The converted files will be stored in a hierarchy that matches the source files. With destination-root set to "webp-cache", the source file "images/2017/cool.jpg" will be stored at "webp-cache/images/2017/cool.jpg.webp". Both absolute paths and relative paths are accepted (if the path starts with "/", it is considered an absolute path). Double-dots in paths are allowed, ie "../webp-cache"

quality:
The quality of the generated WebP image, 0-100.

strip-metadata:
If set (if "&strip-metadata" is appended to the url), metadata will not be copied over in the conversion process. Note however that not all tools supports copying metadata. cwebp supports it, imagewebp does not. You can also assign a value. Any value but "no" counts as yes

preferred-tools (optional):
Setting this manipulates the default order in which the tools are tried. If you for example set it to "cwebp", it means that you want "cwebp" to be tried first. You can specify several favourite tools. Setting it to "cwebp,imagewebp" will put cwebp to the top of the list and imagewebp will be the next tool to try, if cwebp fails. The option will not remove any tools from the list, only change the order.

serve-image (optional):
If set (if "&serve-image" is appended to the URL), the converted image will be served. Otherwise the script will produce text output about the convertion process. You can also assign a value. Any value but "no" counts as yes.

destination (optional): (TODO)
Path to destination file. Can be absolute or relative (relative to document root). You can choose not to specify destination. In that case, the path will be created based upon source, destination-root and root-folder settings. If all these are blank, the destination will be same folder as source, and the filename will have ".webp" appended to it (ie image.jpeg.webp)

root-folder (optional): (TODO)
Usually, you will not need to supply anything. Might be relevant in rare occasions where the tool that generates the URL cannot pass all of the relative path. For example, an .htaccess located in a subfolder may have trouble passing the parent folders. 

debug (optional):
When WebPConvert is told to serve an image, but all tools fails to convert, the default action of WebPConvert is to serve the original image. End-users will not notice the fail, which is good on production servers, but not on development servers. With debugging enabled, WebPConvert will generate an image with the error message, when told to serve image, and things go wrong.
*/

$serve_converted_image = (isset($_GET['serve-image']) ? ($_GET['serve-image'] != 'no') : FALSE);
$debug = (isset($_GET['debug']) ? ($_GET['debug'] != 'no') : FALSE);

if ($debug) {
  error_reporting(E_ALL);
  ini_set('display_errors','On');
}
else {
  if ($serve_converted_image) {
    ini_set('display_errors','Off');
  }
}

include( __DIR__ . '/WebPConvertClass.php');
include( __DIR__ . '/WebPConvertPathHelperClass.php');

$source = WebPConvertPathHelper::abspath($_GET['source']);
$destination = WebPConvertPathHelper::get_destination_path($source, $_GET['destination-root']);
$quality = (isset($_GET['quality']) ? intval($_GET['quality']) : 85);
$strip_metadata = (isset($_GET['strip-metadata']) ? ($_GET['strip-metadata'] != 'no') : FALSE);

$preferred_tools = (isset($_GET['preferred-tools'])) ? explode(',', $_GET['preferred-tools']) : array()); 
//$preferred_tools = array('imagewebp', 'cwebp');


WebPConvert::$serve_converted_image = $serve_converted_image;
WebPConvert::$serve_original_image_on_fail = (!$debug);
WebPConvert::set_preferred_tools($preferred_tools);
WebPConvert::convert($source, $destination, $quality, $strip_metadata);

?>
