<?php

// Requires PHP 7 or greater

//
// Some of the work here was based on code released by Joe Workman from Weaver's Space on GitHub
// (https://github.com/joeworkman/stacks-sparkle).
//

// This file usually gets published to '/appcasts/appcast.php'.
// The URLs it returns are usually to files that exist under '/archives/'.
// These paths are fully configurable.

// All configurable items have been moved to the ini file.
$ini = parse_ini_file(file_exists('appcast.local.ini') ? 'appcast.local.ini' : 'appcast.ini', TRUE, INI_SCANNER_RAW);

// These control what gets logged.
$log_updates = $ini['logging']['log_updates'] ?? TRUE;
$log_hacks = $ini['logging']['log_hacks'] ?? TRUE;

// Stacks API Version
//
// This is the most definitive list of versions I've seen ...
// https://github.com/yourhead/s3/wiki/API-Version-Numbers
$stacks_api = $_GET['StackAPIVersion'] ?? null;

function log_line($text) {
    // You can change the logfile name here
    $logfile = 'logs/appcast-' . date('Y-m') . '.log';
    $log_line = date('D M j Y G:i:s T') . ' ' . $text . "\n";
    $fh = fopen($logfile, 'a');
    if (FALSE != $fh) {
        fwrite($fh, $log_line);
        fclose($fh);
    }
}

function log_connection($prepend, $append = null) {
    global $stacks_api;

    $message = $prepend . ' : ' . $_SERVER['REMOTE_ADDR'];
    $message .= ', ' . 'USER_AGENT : ' . $_SERVER['HTTP_USER_AGENT'];
    if (!empty($stacks_api)) {
        $message .= ', ' . 'StackAPIVersion : ' . strval($stacks_api);
    }
    if (!empty($append)) {
        $message .= ', ' . $append;
    }
    log_line($message);
}

function build_url() {
    global $ini;
    global $stacks_api;

    $url = $ini['server']['appcast_site_url'] . $ini['server']['appcast_base_path'];
    // If we have a stack api version, let's add it to the appcast url. This will allow us to control which versions
    // of the api an update is compatible with.
    if (!empty($stacks_api)) {
        $url .= $stacks_api . '/';
    }

    // This line parses out the name of the product supplied by Sparkle
    //
    // This regex does not accept spaces in the product name.
    // I recommend not having a space in the product name at all, as doing that would require a %20 in the appcast_url
    // that we are building. You could choose to leave the product name out of appcast_url, but then you would have to
    // remember that some hash string is a certain product.
    //
    // Stacks 3 specific :
    // Sparkle's preference for what name it sends in the user agent string (at least the way that Stacks is using it)
    // appears to be CFBundleDisplayName, CFBundleName and then title. The title property is what gets displayed within
    // Stacks.
    // If you really insist on having a space in the product name as displayed by Stacks, you might consider adding
    // either CFBundleName or CFBundleDisplayName to your plist as a non-space alternative and that non-space
    // alternative will then be used for this user agent and only for this user agent.
    //
    // I'm using them as such, ...
    //     title               : GitHub
    //         this is displayed to the user within RapidWeaver 6+/Stacks 3+
    //     CFBundleName        : GitHub Stack
    //         this is used to fill in a couple name fields in appcast.xml
    //     CFBundleDisplayName : GitHubStack
    //         this is sent across the wire for updates, thus no space
    //         this is also used for zip archive file name
    //
    // In general, this whole setup seems like horribly wrong, but that's the way it seems to be. I don't think
    // a normal Mac application would necessarily have a title property. It would use CFBundleDisplayName and
    // CFBundleName as they were intended.
    // Apple says that CFBundleDisplayName shouldn't be added unless you are localizing it, and why would you
    // localize something that isn't being displayed to the user.
    $regex = '/^(\w+)\/\d/';
    preg_match($regex, $_SERVER['HTTP_USER_AGENT'], $matches);
    $product_name = $matches[1];

    // This section adds a hash value to the url. This makes it a bit more difficult for hackers to figure out
    // the URL scheme. If they work their way past this script and get the appcast, they only know the path to
    // download one product.
    $key = $ini['appcast']['key'];
    $secret = md5($product_name . $key);

    // The product name will be the actual name of the app.
    // Examples: MyApp.app => MyApp, Dispatch.stack => Dispatch
    // The product name is added so that we can tell which directory the product is in.
    $url .= $product_name . '_' . $secret . '/appcast.xml';

    return $url;
}

function legit_agent() {
    // Test to see if this request is coming from a Sparkle enabled app. This includes Stacks 2.x/3.x. Since I'm in
    // control of most of the user agent, I made the regex rather precise. I wanted maximum security.
    //      - I didn't include spaces in the product name. I explain why above.
    //      - I insisted on 3 groups of digits separated by dots for my product version strings.
    //      - I insisted on 4 digits for the sparkle version string. Although older versions of Sparkle didn't do it
    //        this way, this is how I've seen it.
    //        If this changes again, I'll start seeing failures and have to adapt.
    // Examples: GitHubStack/1.0.0-a3 Sparkle/2865
    //           GitHubStack/1.0.0-b15 Sparkle/2865
    //           GitHubStack/1.0.0-rc11 Sparkle/2865
    //           GitHubStack/0 Sparkle/2865
    $regex = '/^\w+\/(?:0|[1-9]\d?(?:\.[1-9]?\d){2}(?:-(?:a|b|rc)[1-9]\d?)?) Sparkle\/\d{4}$/';

    return preg_match($regex, $_SERVER['HTTP_USER_AGENT']);
}

//
// These are for debugging, don't leave any of them enabled for production code
//

//print_r($ini);
//print_r($_GET);
//print_r($_SERVER);
//print_r($log_updates);
//print_r($log_hacks);
//print_r($stacks_api);

//
// main begins here
//
if (legit_agent()) {
    $appcast_url = build_url();

    // Redirect to the appcast url
    header('Location: ' . $appcast_url);

    if (TRUE == $log_updates) {
        log_connection('UPDATE', 'APPCAST URL : ' . $appcast_url);
    }
} else {
    // Print a message to anyone who tries loading this page by some other means than a sparkle enabled app
    echo "<h1>Software Updates</h1>";
    echo "If you're looking for updates to any software products that you acquired";
    echo " from <a href='" . $ini['server']['site_url'] . "'>" . $ini['server']['site_name'] . "</a>,";
    echo " please do so from within the software or contact";
    echo " us via <a href='mailto:" . $ini['server']['sales_email'] . "'>email</a>.";
    echo "<br>";
    echo "This update server is reserved for automatic update checking performed by our software only.";

    if (TRUE == $log_hacks) {
        echo "<h3>Your IP has been logged... " . $_SERVER['REMOTE_ADDR'] . "</h3>";
        log_connection('PIRATE');
    }
}

?>
