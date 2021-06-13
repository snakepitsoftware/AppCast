# AppCast

Add description of what this is.

## RapidWeaver and YourHead

AppCast control for use with RapidWeaver stacks.

When YourHead Stacks checks for updates for a particular stack, it will connect to the `appcast.php` script requesting an appcast (appcast.xml). The `appcast.php` script will try to redirect to an appropriate appcast containing information representing the latest release of the stack. If an appcast isn't found, a 404 Not Found will be returned.

The important parts of the appcast are versioning, a change description and a link to the most recent software download.

YourHead Stacks will determine from the versioning if an update is available. If one is, the change description will be shown to the user along with a button to initiate the update.

## Server Side

### Installation

1. `cp appcast.ini appcast.local.ini`
1. Edit appcast.local.ini to match your specific situation  
This is a mostly manual process and highly dependent on how you wish to configure your servers.
1. Create the appcast directory structure on your server  
`mkdir -p appcast/logs`
1. Change ownership of the log directory on your server  
`chown www-data:www-data appcast/logs`
1. Copy appcast.php to your server under /appcast/
1. Copy appcast.local.ini to your server as /appcast/appcast.ini
1. (Optional) Create /archives/ on your server  
`mkdir archives`
1. (Optional) Create /appcasts/ on your server  
`mkdir appcasts`

/archives/ is where you'll put your most recent release archive for each product. You only need to keep the most recent archive.  
/appcasts/ is where you'll put each products appcast.

### Testing

* Initiate a test as a pirate  
`./tests/pirate_test https://www.acmewidgets.com/`
* Initiate a test as a legitimate client  
`./tests/client_test https://www.acmewidgets.com/ NewWidget`

## Stack Development

### Usage (Initialization)

1. Generate keys (only needs to be done once)  
`./scripts/generate_keys`
1. Backup the keys
1. Add public key to your stacks  
`defaults write /path/to/repo/mystack.stack/Contents/Info.plist SUPublicDSAKeyFile "dsa_pub.pem"`  
`cp dsa_pub.pem /path/to/repo/mystack.stack/Contents/Resources/`
1. Set initial version values  
`defaults write /path/to/repo/mystack.stack/Contents/Info.plist CFBundleVersion "0"`  
`defaults write /path/to/repo/mystack.stack/Contents/Info.plist CFBundleShortVersionString "1.0.0"`

### Usage (Version Control)

To update the build version, but leave the version string alone.  
`./scripts/set_version /path/to/repo/mystack.stack/`

To update the build version and update the version string.  
`./scripts/set_version /path/to/repo/mystack.stack/ 1.0.1`

To create a tag when you are finally ready to release.  
`git tag -s -a 1.0.1 -m "v1.0.1"`

### Usage (Archives and AppCasts)

1. Create archive  
`./scripts/create_archive /path/to/repo/mystack.stack/`  
This script will work whether the stack in the repo uses `.stack` or `.devstack` for the extension.
1. Copy archive to server as directed

#### Create Archive (GitHub Hosted Releases)

These steps occur through GitHub.

1. Tag repo with version number
1. Create release from version tag

#### Create Appcast

1. Create appcast  
`STACKAPIVERSION=11 ./scripts/create_appcast /path/to/repo/mystack.stack/`
1. Copy appcast to server as directed

## Credits

Some of the work here was based on code released by Joe Workman from Weaver's Space on [GitHub](https://github.com/joeworkman/stacks-sparkle)

The read-ini-file script is copyright (c) 2018 Sebastian Gniazdowski and was released on [GitHub](https://github.com/zdharma/Zsh-100-Commits-Club/blob/master/Zsh-Native-Scripting-Handbook.adoc#parsing-ini-file)

The changelog format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
