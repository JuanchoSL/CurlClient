# Change Log Curl Client


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

- getLastInfo use string indexes returned by curl_getinfo instead CURLINFO_** constants
- incorrect var name used on prepareBody from DataConverter for url mode


## [1.0.0] - 2022-11-06

### Added

- Initial release, first version

### Changed

### Fixed
