# Contribution Guidelines

Thank you for considering contributing to our project! We welcome contributions from the community. Please follow these guidelines to ensure a smooth process.

## Reporting Issues

> [!IMPORTANT]
> When reporting issues in this repository, please ensure that they are related to the Fluid package itself, not to TYPO3 Core or other extensions.
> If a Fluid issue you encountered uses TYPO3 internals, please report it in [TYPO3 Forge](https://forge.typo3.org).

Before opening a new issue, please check the [existing issues](https://github.com/TYPO3/Fluid/issues) to see if your problem has already been reported. If there is,
feel free to provide additional information or context to help us resolve it.

When submitting an issue please describe the issue clearly, including how to reproduce the bug, what you expect to happen and what actually happens.

## Pull Requests

When submitting a pull request, please follow these steps:

1. If it doesn't exist yet, create an issue to discuss the changes you want to make. This helps us understand the context and purpose of your contribution.
2. Fork the repository and create a new branch for your changes.
3. Make your changes in the new branch.
4. Write clear and concise commit messages that explain the changes you made.
   * As this project is part of the TYPO3 ecosystem, please follow the [TYPO3 commit message guidelines](https://docs.typo3.org/m/typo3/guide-contributionworkflow/main/en-us/Appendix/CommitMessage.html#summary-line-first-line).
5. Ensure that your code adheres to the project's coding standards.
    * You can use tools like [PHP CS Fixer](#code-style-php-cs-fixer) to automatically fix code style issues.
6. If you add new features, please add tests to cover your changes. As there is no Fluid sandbox included in the project, tests are also the best way verify that your changes work as expected.
    * You can run the tests using [PHPUnit](#tests-phpunit).

## Development Setup

### Host

* Have PHP installed (at least [the minimum required version of the package](https://github.com/TYPO3/Fluid/blob/main/composer.json))
* Have [Composer](https://getcomposer.org) installed
* Install the project dependencies by running:
  ```bash
  composer install
  ```

### Docker-based (with DDEV)

* Have [DDEV](https://ddev.com) installed
* Run `ddev config` to set up the project
  * Just set up a plain `php` environment with defaults, nothing special required
* Run `ddev start` to start the environment
* Install the project dependencies by running:
  ```bash
  ddev composer install
  ```

### Development Tools

> [!TIP]
> If you are running inside DDEV, you can prefix all below commands with `ddev exec` to run them inside the container.

#### Tests (PHPUnit)

To run the tests, you can use the following command:

```bash
./vendor/bin/phpunit
```

#### Code Style (PHP CS Fixer)

To ensure code style consistency, we use PHP CS Fixer. You can run it with the following command:

```bash
./vendor/bin/php-cs-fixer fix
```

#### Static Analysis (PHPStan)

To perform static analysis, we use PHPStan. You can run it with the following command:

```bash
./vendor/bin/phpstan analyse
```

#### Fluid Documentation Generator

To generate the Fluid documentation, you can use the following commands.

Generate RST files from ViewHelpers:

```bash
FLUID_DOCUMENTATION_OUTPUT_DIR=Documentation/ViewHelpers vendor/bin/fluidDocumentation generate vendor/t3docs/fluid-documentation-generator/config/fluidStandalone/*
```

Build the documentation:

```bash
mkdir -p Documentation-GENERATED-temp \
&& docker run --rm --pull always -v $(pwd):/project \
ghcr.io/typo3-documentation/render-guides:latest --config=Documentation --no-progress --fail-on-log
```
