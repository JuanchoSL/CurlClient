# Change Log - Curl Client

## [1.0.7] - 2025-12-27

### Added

- use of commands in order to use library from terminal
- Checked full compatibility with php 8.5
- getHeaders method to CurlResponseInterface

### Changed

- Changed composer support from php v8.1
- Composer versions

### Fixed

- Fixed curl_close for avoid use for verions greather than 8.0, it has no effect
- Removed tests from ChartLyrics API, it is not available actually

## [1.0.6] - 2025-11-03

### Added

### Changed

### Fixed

- Initialized body Response as empty string

## [1.0.5] - 2025-05-21

### Added

- OPTIONS Method compatibility
- TRACE Method compatibility
- Checked php v8.4 compatibility
- create dir for cookies when does not exists
- New **PSR-18 Client** Wrapper in order to send a standard **PSR-7 Request** and receive a standar **PSR-7 Response**
- use **PSR-18 Client** Exceptions in order to unify with the standard

### Changed

- adapting tests to phpunit 9.6
- Don't remove the created cookies on destruct in order to retain it for future use
- removed realpath function used into setCookiePath class method
- CurlRequest executions returns binary response in order to delegate the headers separation to CurlResponse

### Fixed

- Explicit nullable parameters

## [1.0.4] - 2024-06-12

### Added

- Workflow for github action
- clean output on download
- check the php libs required into composer.json

### Changed

### Fixed

## [1.0.3] - 2023-03-16

### Added

- A class for read some UserAgents from distinct platforms

### Changed

- CurlRequest setters return this in order to concat calls

### Fixed

## [1.0.2] - 2022-11-12

### Added

- Response object with all information about the request result

### Changed

- CurlClient class change his name to CurlRequest in order to difference it of CurlResponse

### Fixed

## [1.0.1] - 2022-11-09

### Added

- Change Log file
- Unit tests
- Documentation into ./etc/doc/ folder

### Changed

- ApiGen version, compatible with PHP8

### Fixed

- getLastInfo use string indexes returned by curl*getinfo instead CURLINFO*\*\* constants
- incorrect var name used on prepareBody from DataConverter for url mode

## [1.0.0] - 2022-11-06

### Added

- Initial release, first version

### Changed

### Fixed
