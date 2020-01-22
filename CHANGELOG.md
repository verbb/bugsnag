# Bugsnag Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.6] - 2020-01-22

### Added
- Added `getClient` method to service. This exposes the client to other plugins / modules.
- Added `browserApiKey` for configuring frontend reporting

### Changed
- Parse `browserApiKey` and `serverApiKey` for env variables and aliases

### Fixed
- Fixed frontend Bugsnag asset to comply with latest version of JS client

## [2.0.5] - 2019-08-27

### Added
- Added asset bundle to capture JS errors in a more seamless fashion

### Changed
- The plugin can now capture early initialization errors (if manually setup in `app.php`)

### Fixed
- Fixed error when no items was added to exceptions blacklist

## [2.0.4] - 2019-03-14

### Fixed
- Fixed filters support

## [2.0.3] - 2019-03-09

### Added
- Added exceptions blacklist (lets you ignore those 404 errors that clogs up your log)

## [2.0.2] - 2019-02-22
### Added
- Added support for `appVersion`

## [2.0.1] - 2018-07-20
### Fixed
- Fixed check that passes user information to Bugsnag if set
- Fixed error when installing plugin through the console command
- Fixed api config key in config example

## [2.0.0] - 2017-12-08
### Added
- Initial release
