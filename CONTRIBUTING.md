# Contributing

GrumPHP is an open source, community-driven project. If you'd like to contribute,
feel free to do this, but remember to follow this few simple rules:

## Branching strategy

- __Always__ base your changes on the `master` branch (all new development happens here),
- When you create Pull Request, always select `master` branch as target, otherwise it
will be closed (this is selected by default).

## Coverage

- All classes that interact solely with the core logic should be covered by Specs
- Any infrastructure adaptors should be covered by integration tests using PHPUnit
- All features should be covered with .feature descriptions automated with Behat

## Code style / Formatting

- All new classes must carry the standard copyright notice docblock
- All code in the `src` folder must follow the PSR-2 standard
