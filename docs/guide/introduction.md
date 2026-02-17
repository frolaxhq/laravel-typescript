# Introduction

`laravel-typescript` is a powerful tool designed to bridge the gap between your Laravel backend and your TypeScript-based frontend. It automatically scans your Eloquent models and enums to generate accurate, up-to-date TypeScript interfaces and types.

## Why use this?

In modern web development, keeping your frontend type definitions in sync with your backend models can be a tedious and error-prone process. Every time you add a database column, a relation, or a cast, you have to manually update your TypeScript interfaces.

`laravel-typescript` automates this process, ensuring that:
- Your frontend always has accurate types.
- You catch type mismatches early in development.
- You save time by not writing boilerplate interfaces.

## Key Features

- **Automated Model Discovery**: Scans your configured directories for Eloquent models.
- **Support for Relations**: Correctly maps `HasOne`, `HasMany`, `BelongsTo`, and more.
- **Accessor & Mutator Detection**: Supports both traditional `getXAttribute` and the modern `Attribute` class.
- **Enum Support**: Automatically generates TypeScript enums or unions from PHP enums.
- **Highly Configurable**: Control output format, naming conventions, and custom type mappings.
- **Standalone Types**: Define custom interfaces in your configuration and reuse them across models.
- **Incremental Builds**: Fast generation by only processing changed files.
- **API Resource Wrappers**: Optional generation of `{ data: T }` wrappers for API responses.
