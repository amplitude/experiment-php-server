## [1.2.4](https://github.com/amplitude/experiment-php-server/compare/1.2.3...1.2.4) (2025-06-04)


### Bug Fixes

* remove local evaluation boolean matching for 0/1 values ([#37](https://github.com/amplitude/experiment-php-server/issues/37)) ([1dc1953](https://github.com/amplitude/experiment-php-server/commit/1dc19533b5865b117d1858af73ab87e0b8878d9b))

## [1.2.3](https://github.com/amplitude/experiment-php-server/compare/1.2.2...1.2.3) (2025-05-29)


### Bug Fixes

* Use strict typing in EvaluationEngine and fix evaluation boolean handling ([#36](https://github.com/amplitude/experiment-php-server/issues/36)) ([b2c5621](https://github.com/amplitude/experiment-php-server/commit/b2c562130f2d0d1507de50734987022d187182a5))

## [1.2.2](https://github.com/amplitude/experiment-php-server/compare/1.2.1...1.2.2) (2025-01-15)


### Bug Fixes

* use explicit nullable type for AmplitudeConfig ([#32](https://github.com/amplitude/experiment-php-server/issues/32)) ([c5a91cd](https://github.com/amplitude/experiment-php-server/commit/c5a91cd0d000e07cfab6b527601631252ecd0a87))

## [1.2.1](https://github.com/amplitude/experiment-php-server/compare/1.2.0...1.2.1) (2024-09-17)


### Bug Fixes

* allow more supported versions of symfony/cache ([#31](https://github.com/amplitude/experiment-php-server/issues/31)) ([f66e592](https://github.com/amplitude/experiment-php-server/commit/f66e59243b81418d050fe94e2ce2bbb4fc6dc8a3))

# [1.2.0](https://github.com/amplitude/experiment-php-server/compare/1.1.0...1.2.0) (2024-09-13)


### Features

* Allow custom Assignment Filter to be configured ([#29](https://github.com/amplitude/experiment-php-server/issues/29)) ([4cb177c](https://github.com/amplitude/experiment-php-server/commit/4cb177cc181ca6f6e86aff12fb7d5544cc79190c))

# [1.1.0](https://github.com/amplitude/experiment-php-server/compare/1.0.1...1.1.0) (2024-09-10)


### Features

* Introduce Event::fromArray to create assignment event from array ([#27](https://github.com/amplitude/experiment-php-server/issues/27)) ([6ba0618](https://github.com/amplitude/experiment-php-server/commit/6ba0618f60a44c324cbec9a47a416d80f7cebb8e))

## [1.0.1](https://github.com/amplitude/experiment-php-server/compare/1.0.0...1.0.1) (2024-09-09)


### Bug Fixes

* remove API key from error logging ([#28](https://github.com/amplitude/experiment-php-server/issues/28)) ([cfe8267](https://github.com/amplitude/experiment-php-server/commit/cfe826710b258e56ba78a7eb4dcc6f4394428e02))

## [0.5.2](https://github.com/amplitude/experiment-php-server/compare/0.5.1...0.5.2) (2024-03-20)


### Bug Fixes

* add time field to Assignment event ([#17](https://github.com/amplitude/experiment-php-server/issues/17)) ([ae78a13](https://github.com/amplitude/experiment-php-server/commit/ae78a130e2d711e6642ce9f226fb949254126d2d))

## [0.5.1](https://github.com/amplitude/experiment-php-server/compare/0.5.0...0.5.1) (2024-02-22)


### Bug Fixes

* use namespaced version constant ([#16](https://github.com/amplitude/experiment-php-server/issues/16)) ([a9a6d50](https://github.com/amplitude/experiment-php-server/commit/a9a6d50d68d9de84f27dde9bbcd248f864f7cf04))

# [0.5.0](https://github.com/amplitude/experiment-php-server/compare/0.4.0...0.5.0) (2024-02-15)


### Features

* AssignmentTrackingProvider used to track local evaluation assignment events ([#14](https://github.com/amplitude/experiment-php-server/issues/14)) ([62a6696](https://github.com/amplitude/experiment-php-server/commit/62a66960c744b0c7b91793146da4979e8cb57bf0))

# [0.4.0](https://github.com/amplitude/experiment-php-server/compare/0.3.1...0.4.0) (2024-02-05)


### Features

* Use custom HTTP Client, custom Logger, PHPStan ([#13](https://github.com/amplitude/experiment-php-server/issues/13)) ([e33e12b](https://github.com/amplitude/experiment-php-server/commit/e33e12bfb79563e30297bd04c11e8c747f4223d9))

## [0.3.1](https://github.com/amplitude/experiment-php-server/compare/0.3.0...0.3.1) (2023-11-30)


### Bug Fixes

* Update Remote fetch variants to use flag keys array ([#9](https://github.com/amplitude/experiment-php-server/issues/9)) ([b5337c6](https://github.com/amplitude/experiment-php-server/commit/b5337c6b8c495783f63f984a3dba497d04eb9d38))

# [0.3.0](https://github.com/amplitude/experiment-php-server/compare/0.2.2...0.3.0) (2023-11-22)


### Features

* Automatic assignment tracking ([#8](https://github.com/amplitude/experiment-php-server/issues/8)) ([4520bfc](https://github.com/amplitude/experiment-php-server/commit/4520bfce8886a9cbdd0b7692480f6a22d915adc0))

## [0.2.2](https://github.com/amplitude/experiment-php-server/compare/0.2.1...0.2.2) (2023-11-21)


### Bug Fixes

* AmplitudeCookie util class to interact with Amplitude identity cookie ([#7](https://github.com/amplitude/experiment-php-server/issues/7)) ([2a3c460](https://github.com/amplitude/experiment-php-server/commit/2a3c460e13bb2846d49d01aa2e216632af78b529))

## [0.2.1](https://github.com/amplitude/experiment-php-server/compare/0.2.0...0.2.1) (2023-11-16)


### Bug Fixes

* Incorrect initialization of logger ([#6](https://github.com/amplitude/experiment-php-server/issues/6)) ([b41598d](https://github.com/amplitude/experiment-php-server/commit/b41598d468b7afc47805daacad14ef37a63fdcdb))

# [0.2.0](https://github.com/amplitude/experiment-php-server/compare/0.1.2...0.2.0) (2023-11-10)


### Features

* Local evaluation and core evaluation package ([#5](https://github.com/amplitude/experiment-php-server/issues/5)) ([aa4dc79](https://github.com/amplitude/experiment-php-server/commit/aa4dc795228d00bab005bc8233315ad7510f5500))

## [0.1.2](https://github.com/amplitude/experiment-php-server/compare/0.1.1...0.1.2) (2023-11-02)


### Bug Fixes

* Variant payload can be of any type ([#4](https://github.com/amplitude/experiment-php-server/issues/4)) ([c15b707](https://github.com/amplitude/experiment-php-server/commit/c15b7075d4d58c4107c97746809c0fbd6d131945))

## [0.1.1](https://github.com/amplitude/experiment-php-server/compare/v0.1.0...0.1.1) (2023-10-24)


### Bug Fixes

* RemoteEvaluationConfig fields are non-null ([#2](https://github.com/amplitude/experiment-php-server/issues/2)) ([df42ba4](https://github.com/amplitude/experiment-php-server/commit/df42ba4ad3a6fc6e071fafa4ee79fed1e759e728))

# [0.1.0](https://github.com/amplitude/experiment-php-server/compare/0.0.0...0.1.0) (2023-10-24)


### Features

* Remote evaluation ([597426a](https://github.com/amplitude/experiment-php-server/commit/597426a10a4ca4cdb901ab0468273c267fc90a6e))
* Remote evaluation ([4006cb8](https://github.com/amplitude/experiment-php-server/commit/4006cb8752d00dee490febefa66c148fa1690268))
