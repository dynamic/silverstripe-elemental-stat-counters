# Silverstripe Elemental Stat Counters

Statistic counters block for Silverstripe Elemental

[![CI](https://github.com/dynamic/silverstripe-elemental-stat-counters/actions/workflows/ci.yml/badge.svg)](https://github.com/dynamic/silverstripe-elemental-stat-counters/actions/workflows/ci.yml) [![Sponsors](https://img.shields.io/badge/GitHub-Sponsors-ff69b4?logo=github)](https://github.com/sponsors/dynamic)

[![Latest Stable Version](https://poser.pugx.org/dynamic/silverstripe-elemental-stat-counters/v/stable)](https://packagist.org/packages/dynamic/silverstripe-elemental-stat-counters)
[![Total Downloads](https://poser.pugx.org/dynamic/silverstripe-elemental-stat-counters/downloads)](https://packagist.org/packages/dynamic/silverstripe-elemental-stat-counters)
[![Latest Unstable Version](https://poser.pugx.org/dynamic/silverstripe-elemental-stat-counters/v/unstable)](https://packagist.org/packages/dynamic/silverstripe-elemental-stat-counters)
[![License](https://poser.pugx.org/dynamic/silverstripe-elemental-stat-counters/license)](https://packagist.org/packages/dynamic/silverstripe-elemental-stat-counters)


## Requirements

* PHP: ^8.3
* Silverstripe: ^6
* dnadesign/silverstripe-elemental: ^6.0

## Installation

```
composer require dynamic/silverstripe-elemental-stat-counters
```

## License

See [License](LICENSE.md)

## Features

* Display animated statistics counters in Elemental content areas
* Multiple statistics displayed in a responsive row layout
* Count-up animation from 0 to entered number
* Bootstrap-based default styling
* Customizable counter text and values
* Supports any numeric value format
* Mobile-responsive design

## Usage

Displays multiple statistics in a row. Stats will animate upwards from 0 until they hit the entered number.

## Upgrading from version 3.x

SilverStripe Elemental Stat Counters 4.0 is compatible with SilverStripe 6. Key changes:

- Updated to SilverStripe CMS 6
- Requires PHP 8.3 or higher
- Requires Elemental 6 (`dnadesign/silverstripe-elemental: ^6` - previously `^5.0`)
- Updated dependencies to SS6 compatible versions
- Stats are now versioned and owned by their block, see [Versioned stats](#versioned-stats). Templates and configuration are unchanged

## Versioned stats

Stats (`StatCounter`) are versioned and owned by their Stat Counters block. Editing, adding, reordering or
deleting stats on a published block changes the draft only; the live page updates when the block (or its page)
is published. Stats can be reverted with the page history.

Upgrading a site that already has stats:

- `dev/build` creates the `StatCounterObject_Live` and `StatCounterObject_Versions` tables.
- During the same `dev/build`, every stat that predates versioning (no live row and no version history) is
  published to Live once, so published blocks keep showing their stats. Only the stat rows are published: the
  parent blocks and their other draft changes are not.
- Stats that already have a live version are never overwritten, and stats created after the upgrade stay in draft
  until their block is published, so later builds publish nothing.
- The publish runs as part of the default records step, so a build that skips default records
  (`sake db:build --no-populate` or `dev/build?no-populate=1`) does not perform it. Run a normal `dev/build`
  after upgrading.

## Screen Shots

#### Default Bootstrap template with animated count-up
![Default Bootstrap template](./docs/en/_images/stat-counters-block.png)

## Getting more elements

See [Elemental modules by Dynamic](https://github.com/orgs/dynamic/repositories?q=elemental&type=all&language=&sort=)

## Configuration

See [SilverStripe Elemental Configuration](https://github.com/dnadesign/silverstripe-elemental#configuration)

## Maintainers

 *  [Dynamic](https://www.dynamicagency.com) (<dev@dynamicagency.com>)

## Bugtracker

Bugs are tracked in the issues section of this repository. Before submitting an issue please read over
existing issues to ensure yours is unique.

If the issue does look like a new bug:

 - Create a new issue
 - Describe the steps required to reproduce your issue, and the expected outcome. Unit tests, screenshots
 and screencasts can help here.
 - Describe your environment as detailed as possible: SilverStripe version, Browser, PHP version,
 Operating System, any installed SilverStripe modules.

Please report security issues to the module maintainers directly. Please don't file security issues in the bugtracker.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
