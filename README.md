# Wordpress Readonly

[![Packagist](https://img.shields.io/packagist/v/pivvenit/wordpress-readonly.svg?maxAge=3600)](https://packagist.org/packages/pivvenit/wordpress-readonly)[![Packagist](https://img.shields.io/packagist/l/pivvenit/wordpress-readonly.svg?maxAge=2592000)](https://github.com/pivvenit/wordpress-readonly/blob/master/LICENSE)![](https://github.com/pivvenit/wordpress-readonly/workflows/Master%20Build/badge.svg)
[![Dependabot](https://badgen.net/badge/Dependabot/enabled/green?icon=dependabot)](https://dependabot.com/)

A Wordpress plugin that makes Wordpress readonly. 
It's main use case is blue/green deployments, in which the active application slot requires a (short) readonly window to sync the database to the other slot.

Features:
- `WP-CLI` commands to enable and disable the readonly mode
- Notify logged-in users (in the admin) of upcoming `readonly` phase using notification.
- Disable login for all users during both `prepare` and `readonly` phase.
- Drop all `POST` requests during `readonly` phase with a `503 Service Unavailable`.
- Refreshes admin pages for logged-in users once readonly mode is disabled.

## Installation

This plugin is designed for Wordpress websites that use `Composer`, such as [Bedrock](https://packagist.org/packages/roots/bedrock).
```shell script
composer require pivvenit/wordpress-readonly
```

## Usage

**Enable readonly mode**
```shell script
./vendor/bin/wp readonly enable
```
**Disable readonly mode**
```shell script
./vendor/bin/wp readonly disable
```
