# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [unreleased]

### ToDo

* work out a method to have appcast script check for older api versions so we don't have to copy or symlink the file
* figure out secure updates - <http://api.yourhead.com/plist/>

## [1.0.0] - 2021-04-20

### Added

* added a script to help developers create the archive
* added a script to help developers create the appcast
* added support for local ini file to make it easier to do development on this repo
* added a test script that uses ~~wget~~ curl to simulate a call from a Sparkle enabled source
* added a test script that uses ~~wget~~ curl to simulate a call from a non Sparkle enabled source
* added a script that grabs the relevant section from the changelog to be included as the appcast description
* added a script to manage versioning - bumps build version everytime it's called, optional param to set the short version string

### Changed

* cleaned up some of the original scripting
* moved site specific variables to an ini file
* found a [zsh script](https://github.com/zdharma/Zsh-100-Commits-Club/blob/master/Zsh-Native-Scripting-Handbook.adoc#parsing-ini-file) that loads an ini file as an associative array
* started updating bash scripting to take advantage of zsh features
