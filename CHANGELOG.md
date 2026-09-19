# Changelog

Формат: [Keep a Changelog](https://keepachangelog.com/).
Версии: [Semantic Versioning](https://semver.org/).

Changelog начинается с **0.5.0**. Более ранние релизы: [GitHub Releases](https://github.com/ximki-vinki/laravel-ready/releases).

## [Unreleased]

### Added

- `@skipCheck(YYYY-MM-DD)` — временный пропуск guard с обязательной датой истечения.
- Вердикты скипа: Active / Expired / Bare / Malformed; в теле секция `skip:`, при Active — footer `Skipped` и exit `0`.

### Changed

- Голый `@skipCheck` без даты больше не молчаливый пропуск: это нарушение (`skip: missing date`, exit `1`).
